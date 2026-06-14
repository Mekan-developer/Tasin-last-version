<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

function fieldCls(hasError) {
    return [
        'block w-full rounded-[10px] border-2 bg-surface py-2.5 text-sm text-ink transition-all',
        'placeholder:text-muted focus:bg-white focus:outline-none focus:ring-4 focus:ring-offset-0',
        'dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500',
        hasError
            ? 'border-red-400 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500'
            : 'border-line focus:border-blue-500 focus:ring-blue-500/10 dark:border-dline dark:focus:border-blue-400 dark:focus:ring-blue-500/10',
    ].join(' ')
}
</script>

<template>
    <AuthLayout>
        <Head title="Tasin Mobil — Giriş" />

        <!-- Heading -->
        <div class="mb-7">
            <h3 class="text-[28px] font-extrabold text-ink dark:text-slate-100">Hoş geldiňiz 👋</h3>
            <p class="mt-1.5 text-sm text-muted dark:text-slate-400">
                Dowam etmek üçin Tasin Mobil hasabyňyza giriň.
            </p>
        </div>

        <!-- Success status -->
        <div
            v-if="status"
            class="mb-5 rounded-[10px] bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-bold text-ink dark:text-slate-300">
                    E-poçta salgysy
                </label>
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="siz@mysal.com"
                        :class="[fieldCls(!!form.errors.email), 'pl-10 pr-4']"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-bold text-ink dark:text-slate-300">
                    Parol
                </label>
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </span>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="Parolyňyzy giriziň"
                        :class="[fieldCls(!!form.errors.password), 'pl-10 pr-10']"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted transition-colors hover:text-ink dark:hover:text-slate-300"
                        tabindex="-1"
                    >
                        <svg v-if="showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between gap-2">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-line text-blue-600 focus:ring-blue-500 dark:border-dline dark:bg-slate-800"
                    />
                    <span class="text-sm text-muted dark:text-slate-400">Meni ýatda sakla</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Paroly unutdyňyzmy?
                </Link>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full items-center justify-center gap-2 rounded-[10px] px-4 py-3 text-sm font-semibold text-white transition-opacity disabled:cursor-not-allowed disabled:opacity-70"
                style="background: linear-gradient(135deg, #3b82f6, #3a0ca3); box-shadow: 0 4px 16px rgba(59,130,246,0.35)"
            >
                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ form.processing ? 'Girilýär…' : 'Içeri gir' }}
            </button>
        </form>
    </AuthLayout>
</template>
