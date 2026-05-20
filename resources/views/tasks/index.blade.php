@extends('layouts.app')

@section('title', 'تاسکەکان')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'min-h-screen bg-gray-100 px-4 py-10 antialiased sm:px-6 lg:px-8')

@section('content')
    @php($currentUserInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($currentUser->name), 0, 1)))

    <div class="mx-auto max-w-5xl space-y-8">
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">تاسکەکانم</h1>
                <p class="mt-1 text-sm text-gray-500">
                    هەموو کارە سپێردراوەکانت لێرە ببینە و تەواویان بکە
                </p>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                    >
                        چوونەدەرەوە
                    </button>
                </form>
                <div class="text-right">
                    <span class="text-sm font-medium text-gray-700">
                        {{ $currentUser->name }}
                    </span>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $currentUser->email }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-600">
                    {{ $currentUserInitial !== '' ? $currentUserInitial : '?' }}
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">زیادکردنی تاسکی نوێ</h2>

            <form class="space-y-4" method="POST" action="{{ route('tasks.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <input
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
                            placeholder="ناونیشانی تاسک..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                        @error('title')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        @php($selectedUser = (string) old('assigned_to', $users->first()?->id))
                        <select
                            name="assigned_to"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-600 transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            @disabled($users->isEmpty())
                        >
                            <option disabled value="">بۆ کێیە؟</option>
                            @foreach ($users as $assignableUser)
                                <option
                                    value="{{ $assignableUser->id }}"
                                    @selected($selectedUser === (string) $assignableUser->id)
                                >
                                    {{ $assignableUser->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-300"
                            @disabled($users->isEmpty())
                        >
                            زیادکردن
                        </button>
                    </div>
                </div>

                <div>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="وردەکاری زیاتر بۆ تاسکەکە..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    تاسکەکانی من
                </h2>

                @forelse ($assignedToMe as $task)
                    <form
                        method="POST"
                        action="{{ route('tasks.toggle', $task) }}"
                        class="mb-3 flex items-start gap-3 rounded-xl border border-gray-100 p-4 transition-all hover:border-blue-100 hover:bg-blue-50/30 {{ $task->is_completed ? 'bg-gray-50 opacity-75 hover:bg-gray-50' : '' }}"
                    >
                        @csrf
                        @method('PATCH')

                        <input
                            type="checkbox"
                            class="mt-1 h-5 w-5 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            @checked($task->is_completed)
                            onchange="this.form.submit()"
                        >
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-medium text-gray-800 {{ $task->is_completed ? 'text-gray-500 line-through' : '' }}">
                                {{ $task->title }}
                            </h3>
                            @if ($task->description)
                                <p class="mt-1 text-xs leading-6 text-gray-500">
                                    {{ $task->description }}
                                </p>
                            @endif
                            <p class="mt-1 text-xs {{ $task->is_completed ? 'text-gray-400' : 'text-gray-500' }}">
                                سپێردراوە لەلایەن:
                                {{ $task->assigner?->name ?? 'نادیار' }}
                            </p>
                        </div>
                    </form>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                        هیچ تاسکێکت نییە لە ئێستادا.
                    </div>
                @endforelse
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    ئەو تاسکانەی من دامنە
                </h2>

                @forelse ($assignedByMe as $task)
                    <div class="mb-3 flex items-start gap-3 rounded-xl border border-gray-100 p-4 transition-all hover:border-green-100 hover:bg-green-50/30">
                        <input
                            type="checkbox"
                            disabled
                            class="mt-1 h-5 w-5 cursor-not-allowed rounded border-gray-300"
                            @checked($task->is_completed)
                        >
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-medium text-gray-800 {{ $task->is_completed ? 'text-gray-500 line-through' : '' }}">
                                {{ $task->title }}
                            </h3>
                            @if ($task->description)
                                <p class="mt-1 text-xs leading-6 text-gray-500">
                                    {{ $task->description }}
                                </p>
                            @endif
                            <p class="mt-1 text-xs text-gray-500">
                                دۆخ:
                                {{ $task->is_completed ? 'تەواوبووە' : 'چاوەڕوانی تەواوکردن' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                سپێردراوە بۆ:
                                {{ $task->assignee?->name ?? 'نادیار' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                        تۆ هێشتا هیچ تاسکێکت بە کەسی تر نەداوە.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
