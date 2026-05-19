<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/login');
}
</script>

<template>
    <Head title="چوونەژوورەوە">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        dir="rtl"
        lang="ckb"
        class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,#dbeafe_0%,#eff6ff_20%,#f3f4f6_55%)] px-4 py-10"
        style="font-family: 'Noto Kufi Arabic', sans-serif"
    >
        <div
            class="grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-[0_30px_80px_rgba(37,99,235,0.12)] lg:grid-cols-[1.1fr_0.9fr]"
        >
            <div
                class="bg-[linear-gradient(135deg,#1d4ed8_0%,#2563eb_45%,#60a5fa_100%)] p-8 text-white lg:p-12"
            >
                <p
                    class="text-sm font-semibold tracking-[0.28em] text-blue-100 uppercase"
                >
                    Tasks System
                </p>
                <h1 class="mt-5 text-4xl leading-tight font-bold lg:text-5xl">
                    چوونەژوورەوە بۆ سیستەمی بەڕێوەبردنی تاسک
                </h1>
                <p class="mt-5 max-w-md text-sm leading-8 text-blue-50/90">
                    مانیجەر دەتوانێت بەکارهێنەر بەڕێوەببات و تاسک دابەش بکات.
                    بەکارهێنەر دەتوانێت تاسکەکانی خۆی ببینێت و دۆخیان بگۆڕێت.
                </p>

                <div class="mt-10 space-y-4">
                    <div
                        class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15"
                    >
                        <p class="text-xs text-blue-100">هەژماری مانیجەر</p>
                        <p class="mt-2 font-semibold">manager@example.com</p>
                    </div>

                    <div
                        class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15"
                    >
                        <p class="text-xs text-blue-100">هەژماری بەکارهێنەر</p>
                        <p class="mt-2 font-semibold">shad@example.com</p>
                    </div>
                </div>
            </div>

            <div class="p-8 lg:p-12">
                <div class="mx-auto max-w-md">
                    <h2 class="text-2xl font-bold text-gray-800">
                        چوونەژوورەوە
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        زانیاری هەژمارەکەت بنووسە بۆ دەستپێکردن
                    </p>

                    <form class="mt-8 space-y-5" @submit.prevent="submit">
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                ئیمەیڵ
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm transition focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="name@example.com"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                وشەی نهێنی
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm transition focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                placeholder="********"
                            />
                            <p
                                v-if="form.errors.password"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <label
                            class="flex items-center gap-3 text-sm text-gray-600"
                        >
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            من لەبیر بمێنە
                        </label>

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-300"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'چاوەڕێ بکە...'
                                    : 'چوونەژوورەوە'
                            }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
