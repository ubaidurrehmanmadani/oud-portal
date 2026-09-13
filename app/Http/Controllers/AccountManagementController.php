<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountManagementController extends Controller
{
    public function edit(string $kind, int $id)
    {
        return view('content.account', [
            'title' => __('workspace.edit'), 'kind' => $kind, 'record' => $this->record($kind, $id),
            'departments' => Department::orderBy('name')->get(), 'availableProperties' => Property::orderBy('name')->get(),
            'landlords' => User::where('role', UserRole::LANDLORD)->get(),
            'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null,
        ]);
    }

    public function update(Request $request, string $kind, int $id)
    {
        $record = $this->record($kind, $id);
        $rules = ['name' => 'required|string|max:255'];
        $rules += match ($kind) {
            'user' => ['email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($record->id)], 'role' => ['required', Rule::in(UserRole::values())], 'department_id' => 'nullable|exists:departments,id', 'properties' => 'nullable|array', 'properties.*' => 'integer|exists:properties,id'],
            'property' => ['location' => 'nullable|string|max:255', 'type' => 'nullable|string|max:255', 'total_units' => 'required|integer|min:0|max:1000000', 'description' => 'nullable|string|max:5000', 'landlords' => 'nullable|array', 'landlords.*' => ['integer', Rule::exists('users', 'id')->where('role', UserRole::LANDLORD->value)]],
            'department' => ['description' => 'nullable|string|max:5000'],
        };
        $data = $request->validate($rules);
        if ($kind === 'user') {
            $request->validate(['can_submit_financial_reports' => 'nullable|boolean']);
            $record->can_submit_financial_reports = $data['role'] === UserRole::DEPARTMENT_MANAGER->value
                && ! empty($data['department_id']) && $request->boolean('can_submit_financial_reports');
        }
        abort_if($kind === 'user' && $record->id === $request->user()->id && $data['role'] !== UserRole::ADMIN->value, 422);
        DB::transaction(function () use ($record, $kind, $data) {
            $record->fill(collect($data)->except(['properties', 'landlords'])->all());
            if ($kind === 'user') {
                $record->role_id = Role::where('code', $data['role'])->value('id');
            }
            $record->save();
            if ($kind === 'user') {
                $record->properties()->sync($record->role === UserRole::LANDLORD || $record->canSubmitFinancialReports() ? ($data['properties'] ?? []) : []);
            } elseif ($kind === 'property') {
                $managerIds = $record->users()->where('role', UserRole::DEPARTMENT_MANAGER)->pluck('users.id')->all();
                $record->users()->sync(array_unique([...($data['landlords'] ?? []), ...$managerIds]));
            }
        });

        return back()->with('status', __('workspace.saved'));
    }

    private function record(string $kind, int $id)
    {
        return match ($kind) {
            'user' => User::findOrFail($id),
            'property' => Property::findOrFail($id),
            'department' => Department::findOrFail($id),
            default => abort(404),
        };
    }
}
