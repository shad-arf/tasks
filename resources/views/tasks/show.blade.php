@extends('layouts.app')

@section('title', 'وردەکاری تاسک')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'bg-body-tertiary py-4')

@push('head')
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
        crossorigin="anonymous"
    >
@endpush

@section('content')
    @php($isArchivedView = $currentView === 'archived')
    @php($showCompleted = $completedFilter === 'show')
    @php($focusRouteParameters = $currentFocus !== 'all' ? ['focus' => $currentFocus] : [])
    @php($backRouteParameters = $isArchivedView ? array_merge(['view' => 'archived'], $currentTab === 'delegated' ? ['tab' => 'delegated'] : []) : array_merge($showCompleted ? ['completed' => 'show'] : [], $currentTab === 'delegated' ? ['tab' => 'delegated'] : [], $focusRouteParameters))
    @php($isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed')

    <div class="container-xl px-3 px-lg-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div>
                <div class="small text-uppercase text-secondary fw-semibold mb-2">Task Details</div>
                <h1 class="h3 fw-bold text-dark mb-0">{{ $task->title }}</h1>
            </div>

            <a href="{{ route('tasks.index', $backRouteParameters) }}" class="btn btn-outline-dark rounded-4 px-4">
                گەڕانەوە بۆ تاسکەکان
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <span class="badge rounded-pill {{ $priorityClasses[$task->priority] ?? 'text-bg-secondary' }}">
                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                    </span>
                    <span class="badge rounded-pill {{ $statusClasses[$task->status] ?? 'text-bg-secondary' }}">
                        {{ $statusLabels[$task->status] ?? $task->status }}
                    </span>
                    @if ($isOverdue)
                        <span class="badge rounded-pill text-bg-danger">دوا کەوتووە</span>
                    @endif
                </div>

                <div class="small text-secondary d-flex flex-column flex-lg-row flex-wrap gap-2 gap-lg-4 mb-4">
                    <span>سپێردراوە لەلایەن: {{ $task->assigner?->name ?? 'نادیار' }}</span>
                    <span>سپێردراوە بۆ: {{ $task->assignee?->name ?? 'نادیار' }}</span>
                    <span>کاتی دواوە: {{ $task->due_date?->format('Y-m-d') ?? 'دیاری نەکراوە' }}</span>
                    <span>دروستکراوە: {{ $task->created_at?->format('Y-m-d H:i') }}</span>
                    @if ($task->archived_at)
                        <span>ئەرشیف: {{ $task->archived_at->format('Y-m-d H:i') }}</span>
                    @endif
                </div>

                <div>
                    <h2 class="h5 fw-bold text-dark mb-3">وردەکاری</h2>
                    @if ($task->description)
                        <p class="text-secondary mb-0">{{ $task->description }}</p>
                    @else
                        <div class="alert alert-light border-0 rounded-4 mb-0">هیچ وردەکارییەک زیاد نەکراوە.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <h2 class="h4 fw-bold text-dark mb-0">تێبینی و هاوپێچەکان</h2>
                    <span class="small text-secondary">{{ $task->comments->count() }} item</span>
                </div>

                <div class="d-grid gap-3">
                    @forelse ($task->comments as $comment)
                        <div class="bg-light rounded-4 p-3 shadow-sm">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 small text-secondary">
                                    <span class="fw-semibold text-dark">{{ $comment->user?->name ?? 'نادیار' }}</span>
                                    <span>{{ $comment->created_at?->diffForHumans() }}</span>
                                </div>

                                @if ($comment->comment)
                                    <p class="mb-0 text-secondary">{{ $comment->comment }}</p>
                                @endif

                                @if ($comment->attachment_path)
                                    <div>
                                        <a
                                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->attachment_path) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary rounded-4 mt-2"
                                        >
                                            {{ $comment->attachment_name ?: 'Attachment' }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border-0 rounded-4 mb-0">
                            هێشتا هیچ تێبینی یان فایلێک بۆ ئەم تاسکە زیاد نەکراوە.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"
    ></script>
@endpush
