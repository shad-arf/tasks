<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';

import type { User } from '@/types';

type AdminUser = {
    id: number;
    name: string;
    username: string;
    email: string;
    role: 'manager' | 'user';
    created_at: string | null;
};

type Stats = {
    total_users: number;
    total_managers: number;
    total_regular_users: number;
};

type FlashMessages = {
    success?: string | null;
    error?: string | null;
};

type PageProps = {
    auth: {
        user: User | null;
    };
    users: AdminUser[];
    stats: Stats;
    flash?: FlashMessages;
};

const props = defineProps<PageProps>();

const page = usePage<PageProps>();
const editableUsers = ref<AdminUser[]>(
    props.users.map((user) => ({ ...user })),
);

const currentUser = computed(() => props.auth.user);
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');

const createUserForm = useForm({
    name: '',
    username: '',
    email: '',
    role: 'user' as 'manager' | 'user',
    password: '',
});

watch(
    () => props.users,
    (users) => {
        editableUsers.value = users.map((user) => ({ ...user }));
    },
    { deep: true },
);

function submitUser(): void {
    createUserForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => {
            createUserForm.reset('name', 'username', 'email', 'password');
            createUserForm.role = 'user';
        },
    });
}

function updateUser(user: AdminUser): void {
    router.patch(
        `/admin/users/${user.id}`,
        {
            name: user.name,
            username: user.username,
            email: user.email,
            role: user.role,
            password: '',
        },
        {
            preserveScroll: true,
        },
    );
}

function deleteUser(userId: number): void {
    router.delete(`/admin/users/${userId}`, {
        preserveScroll: true,
    });
}

function logout(): void {
    router.post('/logout');
}
</script>

<template>
    <Head title="پەڕەی مانیجەر">
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
        class="min-h-screen bg-slate-100 px-4 py-8"
        style="font-family: 'Noto Kufi Arabic', sans-serif"
    >
        <div class="mx-auto max-w-7xl space-y-6">
            <header
                class="flex flex-col gap-4 rounded-[2rem] bg-slate-950 p-6 text-white shadow-[0_20px_60px_rgba(15,23,42,0.18)] lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold tracking-[0.3em] text-sky-300 uppercase"
                    >
                        Manager Panel
                    </p>
                    <h1 class="mt-3 text-3xl font-bold">
                        بەڕێوەبردنی بەکارهێنەران
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-slate-300">
                        مانیجەر تەنها بەکارهێنەران بەڕێوەدەبات. user ـەکان بە
                        username چوونەژوورەوە دەکەن و تاسک لە نێوان خۆیاندا
                        دابەش دەکەن.
                    </p>
                </div>

                <div class="flex items-center gap-3 self-start lg:self-auto">
                    <div class="text-right">
                        <p class="text-sm font-semibold">
                            {{ currentUser?.name }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ currentUser?.username }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                        @click="logout"
                    >
                        چوونەدەرەوە
                    </button>
                </div>
            </header>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p
                        class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase"
                    >
                        All Users
                    </p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">
                        {{ stats.total_users }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p
                        class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase"
                    >
                        Managers
                    </p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">
                        {{ stats.total_managers }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p
                        class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase"
                    >
                        Users
                    </p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">
                        {{ stats.total_regular_users }}
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p
                        class="text-xs font-semibold tracking-[0.24em] text-slate-500 uppercase"
                    >
                        Scope
                    </p>
                    <p
                        class="mt-3 text-sm leading-7 font-medium text-slate-700"
                    >
                        مانیجەر تەنها user بەڕێوەدەبات.
                    </p>
                </div>
            </div>

            <div
                v-if="flashSuccess"
                class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-medium text-red-700"
            >
                {{ flashError }}
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <section>
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-900">
                            زیادکردنی بەکارهێنەر
                        </h2>
                        <form
                            class="mt-6 space-y-4"
                            @submit.prevent="submitUser"
                        >
                            <input
                                v-model="createUserForm.name"
                                type="text"
                                placeholder="ناوی بەکارهێنەر"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                            <p
                                v-if="createUserForm.errors.name"
                                class="text-xs text-red-600"
                            >
                                {{ createUserForm.errors.name }}
                            </p>

                            <input
                                v-model="createUserForm.username"
                                type="text"
                                placeholder="username"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                            <p
                                v-if="createUserForm.errors.username"
                                class="text-xs text-red-600"
                            >
                                {{ createUserForm.errors.username }}
                            </p>

                            <input
                                v-model="createUserForm.email"
                                type="email"
                                placeholder="ئیمەیڵ"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                            <p
                                v-if="createUserForm.errors.email"
                                class="text-xs text-red-600"
                            >
                                {{ createUserForm.errors.email }}
                            </p>

                            <select
                                v-model="createUserForm.role"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            >
                                <option value="user">user</option>
                                <option value="manager">manager</option>
                            </select>
                            <p
                                v-if="createUserForm.errors.role"
                                class="text-xs text-red-600"
                            >
                                {{ createUserForm.errors.role }}
                            </p>

                            <input
                                v-model="createUserForm.password"
                                type="password"
                                placeholder="وشەی نهێنی"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                            <p
                                v-if="createUserForm.errors.password"
                                class="text-xs text-red-600"
                            >
                                {{ createUserForm.errors.password }}
                            </p>

                            <button
                                type="submit"
                                class="w-full rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:bg-sky-300"
                                :disabled="createUserForm.processing"
                            >
                                {{
                                    createUserForm.processing
                                        ? 'چاوەڕێ بکە...'
                                        : 'زیادکردنی بەکارهێنەر'
                                }}
                            </button>
                        </form>
                    </div>
                </section>

                <section>
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-900">
                            بەڕێوەبردنی بەکارهێنەران
                        </h2>
                        <div class="mt-6 space-y-4">
                            <article
                                v-for="user in editableUsers"
                                :key="user.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <div
                                    class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_140px_auto_auto] md:items-center"
                                >
                                    <input
                                        v-model="user.name"
                                        type="text"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                    />
                                    <input
                                        v-model="user.username"
                                        type="text"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                    />
                                    <input
                                        v-model="user.email"
                                        type="email"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                    />
                                    <select
                                        v-model="user.role"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                    >
                                        <option value="user">user</option>
                                        <option value="manager">manager</option>
                                    </select>
                                    <button
                                        type="button"
                                        class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700"
                                        @click="updateUser(user)"
                                    >
                                        نوێکردنەوە
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:bg-red-300"
                                        :disabled="currentUser?.id === user.id"
                                        @click="deleteUser(user.id)"
                                    >
                                        سڕینەوە
                                    </button>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
