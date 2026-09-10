<section class="admin-screen admin-screen-{{ $section }}">
    <section class="hero"><div><p class="eyebrow">{{ __('workspace.manage_content') }}</p><h1>{{ $title }}</h1></div>@if ($createKind)<a class="button button-secondary" href="{{ route('content.create', ['kind' => $createKind]) }}">{{ __('workspace.create') }}</a>@endif</section>
    @if ($records)
        <form method="GET" class="workspace-filters admin-filter-panel"><label for="q">{{ __('workspace.search') }}</label><input type="search" id="q" name="q" value="{{ request('q') }}" maxlength="200"><button class="button button-primary">{{ __('workspace.search') }}</button></form>
    @endif
    <section class="card table-scroll"><table class="content-table admin-table"><thead><tr>@foreach ($columns as $column)<th>{{ $column }}</th>@endforeach @if ($createKind)<th>{{ __('workspace.action') }}</th>@endif</tr></thead><tbody>
        @forelse ($rows as $row)
            <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach
                @if ($createKind)<td>@php($entry = $records[$loop->index])
                    @if (in_array($createKind, ['user', 'property', 'department']))<a href="{{ route('accounts.edit', ['kind' => $createKind, 'id' => $entry->id]) }}">{{ __('workspace.edit') }}</a>
                    @elseif ($entry->kind !== 'approval' || $entry->status === 'pending')<a href="{{ route('content.edit', $entry) }}">{{ __('workspace.edit') }}</a>@else{{ __('workspace.'.$entry->status) }}@endif
                </td>@endif
            </tr>
        @empty<tr><td colspan="{{ count($columns) + ($createKind ? 1 : 0) }}">{{ in_array($module, ['integrations', 'notifications']) ? __('workspace.not_configured') : __('workspace.empty') }}</td></tr>@endforelse
    </tbody></table></section>
    @if ($module === 'settings')<p class="muted">{{ __('workspace.settings_note') }}</p>@endif
    @if ($records){{ $records->links('pagination.oud') }}@endif
</section>
