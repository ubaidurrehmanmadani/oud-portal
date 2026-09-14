@extends('layouts.workspace')
@section('content')
<section class="hero"><h1>{{ __('review.title') }}</h1></section>
<form class="workspace-filters" method="GET"><select name="status">@foreach(['pending','returned','rejected','approved'] as $status)<option value="{{ $status }}" @selected(request('status','pending') === $status)>{{ __('review.'.$status) }}</option>@endforeach</select><button class="button button-primary">{{ __('workspace.apply') }}</button></form>
@forelse($submissions as $submission)
<section class="card">
<h2>{{ $submission->title }}</h2>@if($submission->file_processing_required && !$submission->file_processed_at)<p>{{ __('review.processing') }}</p>@endif<p>{{ $submission->author?->name }} · {{ $submission->property->name }} · {{ $submission->report_month->format('Y-m') }}</p>
<p>{{ $submission->notes }}</p>
<dl>@foreach($submission->metrics ?? [] as $metric => $value)<dt>{{ __('portal.manager_'.$metric) }}</dt><dd>{{ $value ?? '—' }}</dd>@endforeach</dl>
<a href="{{ route('admin.report-reviews.download', $submission) }}">{{ __('workspace.download') }} · {{ $submission->file_name }}</a>
@foreach($submission->reviews as $review)<p>{{ __('review.'.$review->decision) }} · {{ $review->created_at }}<br>{{ $review->comment }}</p>@endforeach
@if($submission->status === 'pending')
<form method="POST" action="{{ route('admin.report-reviews.decide', $submission) }}" class="content-form">@csrf
<div class="field"><label for="decision-{{ $submission->id }}">{{ __('workspace.action') }}</label><select id="decision-{{ $submission->id }}" name="decision">@foreach(['returned','rejected','approved'] as $decision)<option value="{{ $decision }}">{{ __('review.'.$decision) }}</option>@endforeach</select></div>
<div class="field"><label for="comment-{{ $submission->id }}">{{ __('review.comment') }}</label><textarea id="comment-{{ $submission->id }}" name="comment" maxlength="5000"></textarea></div><button class="button button-primary">{{ __('review.decide') }}</button>
</form>
@endif
</section>
@empty<p class="card">{{ __('workspace.empty') }}</p>@endforelse
{{ $submissions->links('pagination.oud') }}
@endsection
