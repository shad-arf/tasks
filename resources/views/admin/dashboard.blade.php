@extends('layouts.app')

@section('title', 'پەڕەی مانیجەر')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'min-h-screen bg-slate-100 px-4 py-8')

@section('content')
    @php($createErrors = $errors->createUser)
    @php($updateErrors = $errors->updateUser)
    @php($failedUserId = (string) old('user_id'))

    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-col gap-4 rounded-[2rem] bg-slate-950 p-6 text-white shadow-[0_20px_60px_rgba(15,23,42,0.18)] lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.3em] text-sky-300 uppercase">
                    Manager Panel
                </p>
                <h1 class="mt-3 text-3xl font-bold">بەڕێوەبردنی بەکارهێنەران</h1>
                <p class="mt-2 text-sm leading-7 text-slate-300">
                    مانیجەر تەنها بەکارهێنەران بەڕێوەدەبات. user ـەکان بە
                    username چوونەژوورەوە دەکەن و تاسک لە نێوان خۆیاندا دابەش دەکەن.
                </p>
            </div>

            <div class="flex items-center gap-3 self-start lg:self-auto">
                <div class="text-right">
                    <p class="text-sm font-semibold">{{ $currentUser?->name }}</p>
                    <p class="text-xs text-slate-400">{{ $currentUser?->username }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                    >
                        چوونەدەرەوە
                    </button>
                </form>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase">
                    All Users
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $stats['total_users'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase">
                    Managers
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $stats['total_managers'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase">
                    Users
                </p>
                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $stats['total_regular_users'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase">
                    Scope
                </p>
                <p class="mt-3 text-sm leading-7 font-medium text-slate-700">
                    مانیجەر تەنها user بەڕێوەدەبات.
                </p>
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

        @if ($updateErrors->any())
            <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                {{ $updateErrors->first() }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section>
                <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-900">زیادکردنی بەکارهێنەر</h2>

                    <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div>
                            <input
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                placeholder="ناوی بەکارهێنەر"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                            @if ($createErrors->has('name'))
                                <p class="mt-2 text-xs text-red-600">{{ $createErrors->first('name') }}</p>
                            @endif
                        </div>

                        <div>
                            <input
                                name="username"
                                type="text"
                                value="{{ old('username') }}"
                                placeholder="username"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                            @if ($createErrors->has('username'))
                                <p class="mt-2 text-xs text-red-600">{{ $createErrors->first('username') }}</p>
                            @endif
                        </div>

                        <div>
                            <input
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                placeholder="ئیمەیڵ"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                            @if ($createErrors->has('email'))
                                <p class="mt-2 text-xs text-red-600">{{ $createErrors->first('email') }}</p>
                            @endif
                        </div>

                        <div>
                            <select
                                name="role"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                                <option value="user" @selected(old('role', 'user') === 'user')>user</option>
                                <option value="manager" @selected(old('role') === 'manager')>manager</option>
                            </select>
                            @if ($createErrors->has('role'))
                                <p class="mt-2 text-xs text-red-600">{{ $createErrors->first('role') }}</p>
                            @endif
                        </div>

                        <div>
                            <input
                                name="password"
                                type="password"
                                placeholder="وشەی نهێنی"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                            @if ($createErrors->has('password'))
                                <p class="mt-2 text-xs text-red-600">{{ $createErrors->first('password') }}</p>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700"
                        >
                            زیادکردنی بەکارهێنەر
                        </button>
                    </form>
                </div>
            </section>

            <section>
                <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-900">بەڕێوەبردنی بەکارهێنەران</h2>

                    <div class="mt-6 space-y-4">
                        @foreach ($users as $managedUser)
                            @php($isFailedRow = $failedUserId === (string) $managedUser->id)
                            @php($rowName = $isFailedRow ? old('name', $managedUser->name) : $managedUser->name)
                            @php($rowUsername = $isFailedRow ? old('username', $managedUser->username) : $managedUser->username)
                            @php($rowEmail = $isFailedRow ? old('email', $managedUser->email) : $managedUser->email)
                            @php($rowRole = $isFailedRow ? old('role', $managedUser->role) : $managedUser->role)

                            <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-start">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.update', $managedUser) }}"
                                        class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_140px_1fr]"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="user_id" value="{{ $managedUser->id }}">

                                        <div>
                                            <input
                                                name="name"
                                                type="text"
                                                value="{{ $rowName }}"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                            >
                                            @if ($isFailedRow && $updateErrors->has('name'))
                                                <p class="mt-2 text-xs text-red-600">{{ $updateErrors->first('name') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <input
                                                name="username"
                                                type="text"
                                                value="{{ $rowUsername }}"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                            >
                                            @if ($isFailedRow && $updateErrors->has('username'))
                                                <p class="mt-2 text-xs text-red-600">{{ $updateErrors->first('username') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <input
                                                name="email"
                                                type="email"
                                                value="{{ $rowEmail }}"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                            >
                                            @if ($isFailedRow && $updateErrors->has('email'))
                                                <p class="mt-2 text-xs text-red-600">{{ $updateErrors->first('email') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <select
                                                name="role"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                            >
                                                <option value="user" @selected($rowRole === 'user')>user</option>
                                                <option value="manager" @selected($rowRole === 'manager')>manager</option>
                                            </select>
                                            @if ($isFailedRow && $updateErrors->has('role'))
                                                <p class="mt-2 text-xs text-red-600">{{ $updateErrors->first('role') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <input
                                                name="password"
                                                type="password"
                                                placeholder="نوێکردنەوەی وشەی نهێنی"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                            >
                                            @if ($isFailedRow && $updateErrors->has('password'))
                                                <p class="mt-2 text-xs text-red-600">{{ $updateErrors->first('password') }}</p>
                                            @endif
                                        </div>

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700 md:col-span-2 xl:col-span-5"
                                        >
                                            نوێکردنەوە
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:bg-red-300 lg:w-auto"
                                            @disabled($currentUser?->id === $managedUser->id)
                                        >
                                            سڕینەوە
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
