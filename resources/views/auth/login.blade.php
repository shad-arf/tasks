@extends('layouts.app')

@section('title', 'چوونەژوورەوە')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,#dbeafe_0%,#eff6ff_20%,#f3f4f6_55%)] px-4 py-10')

@section('content')
    <div class="grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-[0_30px_80px_rgba(37,99,235,0.12)] lg:grid-cols-[1.1fr_0.9fr]">
        <div class="bg-[linear-gradient(135deg,#1d4ed8_0%,#2563eb_45%,#60a5fa_100%)] p-8 text-white lg:p-12">
            <p class="text-sm font-semibold tracking-[0.28em] text-blue-100 uppercase">
                Tasks System
            </p>
            <h1 class="mt-5 text-4xl leading-tight font-bold lg:text-5xl">
                چوونەژوورەوە بۆ سیستەمی بەڕێوەبردنی تاسک
            </h1>
            <p class="mt-5 max-w-md text-sm leading-8 text-blue-50/90">
                بە username و وشەی نهێنی بچۆرە ژوورەوە. مانیجەر بەکارهێنەر
                بەڕێوەدەبات و user ـەکان تاسک لە نێوان خۆیاندا دابەش دەکەن.
            </p>
        </div>

        <div class="p-8 lg:p-12">
            <div class="mx-auto max-w-md">
                <h2 class="text-2xl font-bold text-gray-800">چوونەژوورەوە</h2>
                <p class="mt-2 text-sm text-gray-500">
                    username و وشەی نهێنی بنووسە بۆ دەستپێکردن
                </p>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="mt-8 space-y-5" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-gray-700">
                            username
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm transition focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="username"
                        >
                        @error('username')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                            وشەی نهێنی
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full rounded-2xl border border-gray-200 px-4 py-3 pr-20 text-sm transition focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="********"
                            >
                            <button
                                type="button"
                                class="absolute top-1/2 left-3 -translate-y-1/2 text-xs font-medium text-blue-600 transition hover:text-blue-700"
                                data-password-toggle="password"
                            >
                                پیشاندان
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm text-gray-600">
                        <input
                            name="remember"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            @checked(old('remember'))
                        >
                        من لەبیر بمێنە
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        چوونەژوورەوە
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);

                if (!input) {
                    return;
                }

                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                button.textContent = isHidden ? 'شاردنەوە' : 'پیشاندان';
            });
        });
    </script>
@endpush
