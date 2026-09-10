<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Support\FinancialReport;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContentController extends Controller
{
    public const KINDS = ['document', 'training', 'announcement', 'report', 'approval'];

    public function index(Request $request)
    {
        $this->authorizeManager($request);
        $query = WorkspaceItem::with(['department', 'property'])->latest();
        if ($request->user()->role !== UserRole::ADMIN) {
            $query->where('department_id', $request->user()->department_id)->where('audience', 'staff')->whereIn('kind', ['document', 'training', 'announcement']);
        }

        return view('content.index', $this->context() + ['items' => $query->paginate(20)]);
    }

    public function create(Request $request)
    {
        $this->authorizeManager($request);
        $kind = $request->query('kind', 'document');
        abort_unless(in_array($kind, $request->user()->role === UserRole::ADMIN ? [...self::KINDS, 'department', 'property', 'user'] : ['document', 'training', 'announcement'], true), 403);

        return view('content.form', $this->formData($kind));
    }

    public function edit(Request $request, int $item)
    {
        $record = $this->editable($request, $item);

        return view('content.form', $this->formData($record->kind) + ['record' => $record]);
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $kind = $request->validate(['kind' => ['required', Rule::in([...self::KINDS, 'department', 'property', 'user'])]])['kind'];
        if (in_array($kind, ['department', 'property', 'user'])) {
            abort_unless($request->user()->role === UserRole::ADMIN, 403);
            $this->storeAccountData($request, $kind);
        } else {
            $this->saveItem($request, new WorkspaceItem, $kind);
        }

        return redirect()->route('content.index')->with('status', __('workspace.saved'));
    }

    public function update(Request $request, int $item)
    {
        $record = $this->editable($request, $item);
        abort_if($record->kind === 'approval' && $record->status !== 'pending', 409);
        $this->saveItem($request, $record, $record->kind);

        return redirect()->route('content.index')->with('status', __('workspace.saved'));
    }

    private function saveItem(Request $request, WorkspaceItem $record, string $kind): void
    {
        $isAdmin = $request->user()->role === UserRole::ADMIN;
        abort_unless($isAdmin || in_array($kind, ['document', 'training', 'announcement']), 403);
        $data = $request->validate([
            'title' => 'required|string|max:255', 'body' => 'nullable|string|max:50000', 'category' => 'nullable|string|max:100',
            'audience' => ['required', Rule::in($isAdmin ? ['staff', 'landlord', 'admin'] : ['staff'])],
            'department_id' => 'nullable|integer|exists:departments,id', 'property_id' => 'nullable|integer|exists:properties,id',
            'status' => ['required', Rule::in($kind === 'approval' ? ['pending'] : ['draft', 'published'])],
            'published_at' => 'nullable|date', 'period' => 'nullable|string|max:100',
            'occupancy' => 'nullable|numeric|between:0,100', 'net_revenue' => 'nullable|numeric|min:0|max:99999999999999',
            'leased_area' => 'nullable|numeric|min:0|max:999999999999', 'amount' => 'nullable|numeric|min:0|max:99999999999999',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,mp4,zip,txt',
        ]);
        unset($data['file']);
        if ($kind === 'report') {
            $data = array_merge($data, $this->financialData($request, $record));
        }
        if (! $isAdmin) {
            $data['department_id'] = $request->user()->department_id;
            $data['property_id'] = null;
        }
        if (in_array($kind, ['report', 'approval'])) {
            $request->validate(['property_id' => 'required|exists:properties,id', 'audience' => Rule::in(['landlord'])]);
        }
        if ($data['audience'] === 'landlord') {
            $request->validate(['property_id' => 'required|exists:properties,id']);
            $data['department_id'] = null;
        } else {
            $data['property_id'] = null;
        }
        $newPath = null;
        $oldPath = $record->file_path;
        if ($request->hasFile('file')) {
            $newPath = $request->file('file')->store('workspace', 'local');
            $data['file_path'] = $newPath;
            $data['file_name'] = basename($request->file('file')->getClientOriginalName());
        }
        try {
            DB::transaction(function () use ($record, $data, $kind, $request) {
                if ($record->exists) {
                    $locked = WorkspaceItem::lockForUpdate()->findOrFail($record->id);
                    abort_if($kind === 'approval' && $locked->status !== 'pending', 409);
                }
                $record->fill($data);
                $record->kind = $kind;
                if (! $record->exists) {
                    $record->created_by = $request->user()->id;
                }
                $record->save();
            });
        } catch (\Throwable $error) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            if ($error instanceof UniqueConstraintViolationException && $kind === 'report') {
                throw ValidationException::withMessages(['report_month' => __('financial.duplicate_month')]);
            }
            throw $error;
        }
        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }

    private function storeAccountData(Request $request, string $kind): void
    {
        if ($kind === 'department') {
            Department::create($request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string|max:5000']));
        } elseif ($kind === 'property') {
            $data = $request->validate(['name' => 'required|string|max:255', 'location' => 'nullable|string|max:255', 'type' => 'nullable|string|max:255', 'description' => 'nullable|string|max:5000', 'total_units' => 'required|integer|min:0|max:1000000', 'landlords' => 'nullable|array', 'landlords.*' => ['integer', Rule::exists('users', 'id')->where('role', UserRole::LANDLORD->value)]]);
            DB::transaction(function () use ($data) {
                $property = Property::create(collect($data)->except('landlords')->all());
                $property->users()->sync($data['landlords'] ?? []);
            });
        } else {
            $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => 'required|string|min:8|max:255', 'role' => ['required', Rule::in(UserRole::values())], 'department_id' => 'nullable|exists:departments,id', 'properties' => 'nullable|array', 'properties.*' => 'integer|exists:properties,id']);
            DB::transaction(function () use ($data) {
                $user = User::create(collect($data)->except('properties')->all() + ['role_id' => Role::where('code', $data['role'])->value('id')]);
                $user->profile()->create(['preferred_locale' => app()->getLocale(), 'timezone' => 'Asia/Riyadh']);
                if ($user->role === UserRole::LANDLORD) {
                    $user->properties()->sync($data['properties'] ?? []);
                }
            });
        }
    }

    private function financialData(Request $request, WorkspaceItem $record): array
    {
        if (preg_match('/^\d{4}-\d{2}$/', $request->input('report_month', '') ?? '')) {
            $request->merge(['report_month' => $request->input('report_month').'-01']);
        }
        $rules = [
            'report_month' => ['nullable', 'date_format:Y-m-d', 'regex:/^(19|20|21)\d{2}-(0[1-9]|1[0-2])-01$/', 'before:2101-01-01', function ($attribute, $value, $fail) use ($request, $record) {
                if (WorkspaceItem::where('property_id', $request->input('property_id'))->whereDate('report_month', $value)->when($record->exists, fn ($query) => $query->whereKeyNot($record->id))->exists()) {
                    $fail(__('financial.duplicate_month'));
                }
            }],
            'financial_data' => 'nullable|array:'.implode(',', [...FinancialReport::FIELDS, 'components', 'source_name', 'source_notes', 'monthly_rows', 'annual_rows']),
            'financial_data.components' => 'nullable|array:'.implode(',', FinancialReport::COMPONENTS),
            'financial_data.source_name' => 'nullable|string|max:255',
            'financial_data.source_notes' => 'nullable|string|max:10000',
        ];
        foreach (FinancialReport::FIELDS as $field) {
            $rules['financial_data.'.$field] = str_ends_with($field, '_occupancy') ? 'nullable|numeric|between:0,100' : 'nullable|numeric|between:0,99999999999999';
        }
        foreach (FinancialReport::COMPONENTS as $component) {
            $rules['financial_data.components.'.$component] = 'nullable|array:'.implode(',', FinancialReport::COMPONENT_FIELDS);
            foreach (FinancialReport::COMPONENT_FIELDS as $field) {
                $rules['financial_data.components.'.$component.'.'.$field] = 'nullable|numeric|between:0,99999999999999';
            }
        }
        foreach (['monthly_rows', 'annual_rows'] as $table) {
            $rules['financial_data.'.$table] = 'nullable|array|max:100';
            $rules['financial_data.'.$table.'.*'] = 'array:label,reference,values';
            $rules['financial_data.'.$table.'.*.label'] = 'nullable|string|max:255';
            $rules['financial_data.'.$table.'.*.reference'] = 'nullable|string|max:100';
            $rules['financial_data.'.$table.'.*.values'] = 'nullable|array|max:6';
            $rules['financial_data.'.$table.'.*.values.*'] = 'nullable|string|max:100';
        }
        $data = $request->validate($rules);
        $prune = function (array $values) use (&$prune): array {
            return array_filter(array_map(fn ($value) => is_array($value) ? $prune($value) : $value, $values), fn ($value) => $value !== null && $value !== '' && $value !== []);
        };
        $financial = $prune($data['financial_data'] ?? []);
        if ($financial && empty($data['report_month'])) {
            throw ValidationException::withMessages(['report_month' => __('financial.month_required')]);
        }

        return ['report_month' => $data['report_month'] ?? null, 'financial_data' => $financial ?: null];
    }

    private function editable(Request $request, int $item): WorkspaceItem
    {
        $this->authorizeManager($request);
        $query = WorkspaceItem::query();
        if ($request->user()->role !== UserRole::ADMIN) {
            $query->where('department_id', $request->user()->department_id)->where('audience', 'staff')->whereIn('kind', ['document', 'training', 'announcement']);
        }

        return $query->findOrFail($item);
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()->role === UserRole::ADMIN || ($request->user()->role === UserRole::DEPARTMENT_MANAGER && $request->user()->department_id), 403);
    }

    private function formData(string $kind): array
    {
        return $this->context() + ['kind' => $kind, 'departments' => Department::orderBy('name')->get(), 'availableProperties' => Property::orderBy('name')->get(), 'landlords' => User::where('role', UserRole::LANDLORD)->orderBy('name')->get()];
    }

    private function context(): array
    {
        return ['title' => __('workspace.manage_content'), 'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null];
    }
}
