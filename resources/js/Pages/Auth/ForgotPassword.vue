<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineProps({
    status: { type: String },
})

const form = useForm({
    email: '',
})

function submit() {
    form.post(route('password.email'))
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
        <Head title="Tasin Mobil — Paroly dikelt" />

        <!-- Back link -->
        <div class="mb-6">
            <Link
                :href="route('login')"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-slate-200"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Girişe dolan
            </Link>
        </div>

        <!-- Heading -->
        <div class="mb-7">
            <h3 class="text-[28px] font-extrabold text-ink dark:text-slate-100">Paroly unutdyňyzmy?</h3>
            <p class="mt-1.5 text-sm text-muted dark:text-slate-400">
                E-poçtaňyzy giriziň, size paroly dikeltmek üçin baglanyşyk ibereris.
            </p>
        </div>

        <!-- Success status -->
        <div
            v-if="status"
            class="mb-5 rounded-[10px] bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
        >
            Dikeltmek baglanyşygy e-poçtaňyza iberildi.
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
                {{ form.processing ? 'Iberilýär…' : 'Dikeltmek baglanyşygyny iber' }}
            </button>
        </form>
    </AuthLayout>
</template>
