<script setup lang="ts">
import { computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';

import type { User } from '@/types';

type TaskUser = {
    id: number;
    name: string;
    email: string;
};

type TaskItem = {
    id: number;
    title: string;
    description: string | null;
    is_completed: boolean;
    assigner: TaskUser | null;
    assignee: TaskUser | null;
    created_at: string | null;
};

type FlashMessages = {
    success?: string | null;
    error?: string | null;
};

type PageProps = {
    auth: {
        user: User | null;
    };
    users: TaskUser[];
    assignedToMe: TaskItem[];
    assignedByMe: TaskItem[];
    flash?: FlashMessages;
};

const props = defineProps<PageProps>();

const page = usePage<PageProps>();

const currentUser = computed(() => props.auth.user);
const currentUserInitial = computed(
    () => currentUser.value?.name.trim().charAt(0) ?? '?',
);
const flashSuccess = computed(() => page.props.flash?.success ?? '');
const flashError = computed(() => page.props.flash?.error ?? '');

const form = useForm({
    title: '',
    description: '',
    assigned_to: props.users[0]?.id ?? null,
});

function logout(): void {
    router.post('/logout');
}

function submitTask(): void {
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('title', 'description');
            form.assigned_to = props.users[0]?.id ?? null;
        },
    });
}

function toggleTask(task: TaskItem): void {
    router.patch(
        `/tasks/${task.id}/toggle`,
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head title="تاسکەکان">
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
        class="min-h-screen bg-gray-100 px-4 py-10 antialiased sm:px-6 lg:px-8"
        style="font-family: 'Noto Kufi Arabic', sans-serif"
    >
        <div class="mx-auto max-w-5xl space-y-8">
            <div
                class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">تاسکەکانم</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        هەموو کارە سپێردراوەکانت لێرە ببینە و تەواویان بکە
                    </p>
                </div>

                <div class="flex items-center gap-3 self-start sm:self-auto">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                        @click="logout"
                    >
                        چوونەدەرەوە
                    </button>
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-700">
                            {{ currentUser?.name ?? 'بەکارهێنەر' }}
                        </span>
                        <p
                            v-if="currentUser"
                            class="mt-1 text-xs text-gray-400"
                        >
                            {{ currentUser.email }}
                        </p>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-600"
                    >
                        {{ currentUserInitial }}
                    </div>
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

            <div
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <h2 class="mb-4 text-lg font-semibold text-gray-800">
                    زیادکردنی تاسکی نوێ
                </h2>

                <form class="space-y-4" @submit.prevent="submitTask">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="ناونیشانی تاسک..."
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            />
                            <p
                                v-if="form.errors.title"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div>
                            <select
                                v-model="form.assigned_to"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-600 transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :disabled="users.length === 0"
                            >
                                <option disabled :value="null">بۆ کێیە؟</option>
                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.assigned_to"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.assigned_to }}
                            </p>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-300"
                                :disabled="
                                    form.processing || users.length === 0
                                "
                            >
                                {{
                                    form.processing
                                        ? 'چاوەڕێ بکە...'
                                        : 'زیادکردن'
                                }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="وردەکاری زیاتر بۆ تاسکەکە..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition-all focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <p
                            v-if="form.errors.description"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                >
                    <h2
                        class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800"
                    >
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        تاسکەکانی من
                    </h2>

                    <div
                        v-if="assignedToMe.length === 0"
                        class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500"
                    >
                        هیچ تاسکێکت نییە لە ئێستادا.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="task in assignedToMe"
                            :key="task.id"
                            class="flex items-start gap-3 rounded-xl border border-gray-100 p-4 transition-all hover:border-blue-100 hover:bg-blue-50/30"
                            :class="{
                                'bg-gray-50 opacity-75 hover:bg-gray-50':
                                    task.is_completed,
                            }"
                        >
                            <input
                                type="checkbox"
                                class="mt-1 h-5 w-5 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                :checked="task.is_completed"
                                @change="toggleTask(task)"
                            />
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="text-sm font-medium text-gray-800"
                                    :class="{
                                        'text-gray-500 line-through':
                                            task.is_completed,
                                    }"
                                >
                                    {{ task.title }}
                                </h3>
                                <p
                                    v-if="task.description"
                                    class="mt-1 text-xs leading-6 text-gray-500"
                                >
                                    {{ task.description }}
                                </p>
                                <p
                                    class="mt-1 text-xs text-gray-500"
                                    :class="{
                                        'text-gray-400': task.is_completed,
                                    }"
                                >
                                    سپێردراوە لەلایەن:
                                    {{ task.assigner?.name ?? 'نادیار' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                >
                    <h2
                        class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800"
                    >
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        ئەو تاسکانەی من دامنە
                    </h2>

                    <div
                        v-if="assignedByMe.length === 0"
                        class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500"
                    >
                        تۆ هێشتا هیچ تاسکێکت بە کەسی تر نەداوە.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="task in assignedByMe"
                            :key="task.id"
                            class="flex items-start gap-3 rounded-xl border border-gray-100 p-4 transition-all hover:border-green-100 hover:bg-green-50/30"
                        >
                            <input
                                type="checkbox"
                                disabled
                                class="mt-1 h-5 w-5 cursor-not-allowed rounded border-gray-300"
                                :checked="task.is_completed"
                            />
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="text-sm font-medium text-gray-800"
                                    :class="{
                                        'text-gray-500 line-through':
                                            task.is_completed,
                                    }"
                                >
                                    {{ task.title }}
                                </h3>
                                <p
                                    v-if="task.description"
                                    class="mt-1 text-xs leading-6 text-gray-500"
                                >
                                    {{ task.description }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    دۆخ:
                                    {{
                                        task.is_completed
                                            ? 'تەواوبووە'
                                            : 'چاوەڕوانی تەواوکردن'
                                    }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    سپێردراوە بۆ:
                                    {{ task.assignee?.name ?? 'نادیار' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
