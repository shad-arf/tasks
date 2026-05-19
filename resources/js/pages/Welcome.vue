<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const metrics = [
    { label: 'Root route', value: '/' },
    { label: 'Stack', value: 'Laravel + Inertia + Vue' },
    { label: 'Status', value: 'Starter page replaced' },
];

const highlights = [
    'The default Laravel starter screen is gone.',
    'This page is now the actual root experience for the app.',
    'You can extend this into a dashboard, task board, or public homepage.',
];
</script>

<template>
    <Head title="Home" />

    <div
        class="min-h-screen bg-[linear-gradient(180deg,#f6efe4_0%,#efe5d6_52%,#e8dccd_100%)] text-stone-900"
    >
        <div
            class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-6 py-6 lg:px-10"
        >
            <header
                class="flex items-center justify-between rounded-full border border-black/10 bg-white/70 px-5 py-3 shadow-[0_12px_30px_rgba(40,26,16,0.08)] backdrop-blur"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#d36a34_0%,#8f3412_100%)] text-sm font-semibold text-white"
                    >
                        T
                    </div>
                    <div>
                        <p
                            class="text-sm font-semibold tracking-[0.28em] text-stone-500 uppercase"
                        >
                            Tasks
                        </p>
                        <p class="text-sm text-stone-600">Root page</p>
                    </div>
                </div>

                <div class="hidden items-center gap-3 md:flex">
                    <a
                        href="#overview"
                        class="rounded-full px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-black/5 hover:text-stone-900"
                    >
                        Overview
                    </a>
                    <a
                        href="#details"
                        class="rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-stone-700"
                    >
                        Updated root
                    </a>
                </div>
            </header>

            <main
                class="grid flex-1 items-center gap-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:py-12"
            >
                <section
                    id="overview"
                    class="relative overflow-hidden rounded-[2rem] border border-black/10 bg-white/72 p-8 shadow-[0_24px_60px_rgba(52,33,20,0.12)] backdrop-blur lg:p-12"
                >
                    <div
                        class="absolute top-0 right-0 h-52 w-52 translate-x-1/4 -translate-y-1/4 rounded-full bg-orange-300/40 blur-3xl"
                    />
                    <div
                        class="absolute bottom-0 left-0 h-40 w-40 -translate-x-1/4 translate-y-1/4 rounded-full bg-emerald-300/30 blur-3xl"
                    />

                    <div class="relative">
                        <span
                            class="inline-flex items-center rounded-full bg-orange-100 px-4 py-2 text-xs font-semibold tracking-[0.3em] text-orange-900 uppercase"
                        >
                            Root page fixed
                        </span>

                        <h1
                            class="mt-5 max-w-3xl text-5xl leading-none font-semibold tracking-[-0.06em] lg:text-7xl"
                        >
                            The app no longer opens on the default Laravel
                            screen.
                        </h1>

                        <p
                            class="mt-6 max-w-2xl text-base leading-8 text-stone-600 lg:text-lg"
                        >
                            The `/` route already points to an Inertia page in
                            <code
                                class="rounded bg-black/5 px-2 py-1 text-sm text-stone-800"
                                >routes/web.php</code
                            >. The visible issue was the stock
                            <code
                                class="rounded bg-black/5 px-2 py-1 text-sm text-stone-800"
                                >Welcome.vue</code
                            >
                            starter component, which has now been replaced.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a
                                href="#details"
                                class="rounded-full bg-[linear-gradient(135deg,#d36a34_0%,#8f3412_100%)] px-5 py-3 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(143,52,18,0.22)] transition hover:-translate-y-0.5"
                            >
                                View page details
                            </a>
                            <div
                                class="rounded-full border border-black/10 bg-white/85 px-5 py-3 text-sm font-medium text-stone-700"
                            >
                                File: resources/js/pages/Welcome.vue
                            </div>
                        </div>

                        <div class="mt-10 grid gap-4 md:grid-cols-3">
                            <article
                                v-for="metric in metrics"
                                :key="metric.label"
                                class="rounded-2xl border border-black/8 bg-white/78 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.24em] text-stone-500 uppercase"
                                >
                                    {{ metric.label }}
                                </p>
                                <p
                                    class="mt-2 text-lg font-semibold text-stone-900"
                                >
                                    {{ metric.value }}
                                </p>
                            </article>
                        </div>
                    </div>
                </section>

                <section
                    id="details"
                    class="rounded-[2rem] border border-black/10 bg-stone-950 p-6 text-stone-100 shadow-[0_24px_60px_rgba(20,12,8,0.22)] lg:p-8"
                >
                    <div
                        class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6"
                    >
                        <p
                            class="text-xs font-semibold tracking-[0.28em] text-orange-300 uppercase"
                        >
                            What changed
                        </p>
                        <ul class="mt-5 space-y-4">
                            <li
                                v-for="item in highlights"
                                :key="item"
                                class="flex gap-3 text-sm leading-7 text-stone-300"
                            >
                                <span
                                    class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-orange-400"
                                />
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="mt-5 rounded-[1.5rem] border border-white/10 bg-white/5 p-6"
                    >
                        <p
                            class="text-xs font-semibold tracking-[0.28em] text-orange-300 uppercase"
                        >
                            Route source
                        </p>
                        <div
                            class="mt-4 rounded-2xl bg-black/30 p-4 font-mono text-sm text-stone-200"
                        >
                            <p>Route::inertia('/', 'Welcome')->name('home');</p>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-stone-400">
                            That route remains valid. The fix was to replace the
                            page component it renders.
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>
