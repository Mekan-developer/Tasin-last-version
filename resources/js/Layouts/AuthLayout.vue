<script setup>
import { Link } from '@inertiajs/vue3'
import { useThemeStore } from '@/stores/useThemeStore'

const theme = useThemeStore()
</script>

<template>
    <div class="flex min-h-screen">
        <!-- ═══════════════ LEFT BRANDING PANEL ═══════════════ -->
        <div
            class="relative hidden lg:flex lg:w-[52%] flex-col justify-between overflow-hidden p-10"
            style="background: linear-gradient(160deg, #1e2a3a 0%, #0f172a 100%)"
        >
            <!-- Glow: blue top-left -->
            <div
                class="pointer-events-none absolute -left-24 -top-24 h-[420px] w-[420px] rounded-full"
                style="background: radial-gradient(circle, rgba(59,130,246,0.22) 0%, transparent 65%)"
            />
            <!-- Glow: teal bottom-right -->
            <div
                class="pointer-events-none absolute -bottom-24 -right-24 h-[380px] w-[380px] rounded-full"
                style="background: radial-gradient(circle, rgba(6,182,212,0.18) 0%, transparent 65%)"
            />

            <!-- Logo -->
            <Link :href="route('login')" class="relative z-10 flex items-center gap-3 w-fit">
                <div
                    class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[10px]"
                    style="background: linear-gradient(135deg, #3b82f6, #06b6d4)"
                >
                    <svg class="h-[18px] w-[18px] text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
                        />
                    </svg>
                </div>
                <span class="text-lg font-black text-white tracking-tight">Tasin Mobil</span>
            </Link>

            <!-- Center hero -->
            <div class="relative z-10 flex flex-1 flex-col justify-center py-12">
                <h2 class="mb-4 font-black leading-[1.15] text-white" style="font-size: 38px">
                    Dükanyňyzy bir ýerden<br>
                    <span style="color: #06b6d4">dolandyryň</span>
                </h2>
                <p class="mb-9 max-w-sm text-[15px] leading-relaxed" style="color: rgba(255,255,255,0.6)">
                    Dolandyryş paneline giriň — harytlar, kategoriýalar, statistika we sazlamalar, hemmesi bir ýerde.
                </p>

                <div class="flex flex-col gap-3">
                    <!-- Pill 1 -->
                    <div
                        class="flex items-center gap-4 rounded-[12px] px-4 py-3"
                        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px)"
                    >
                        <div
                            class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[10px]"
                            style="background: rgba(59,130,246,0.2)"
                        >
                            <svg class="h-[18px] w-[18px]" style="color: #3b82f6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="block text-sm font-bold text-white">Harytlar we kategoriýalar</strong>
                            <span class="text-xs" style="color: rgba(255,255,255,0.5)">Katalogy aňsat dolandyrmak</span>
                        </div>
                    </div>

                    <!-- Pill 2 -->
                    <div
                        class="flex items-center gap-4 rounded-[12px] px-4 py-3"
                        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px)"
                    >
                        <div
                            class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[10px]"
                            style="background: rgba(6,182,212,0.2)"
                        >
                            <svg class="h-[18px] w-[18px]" style="color: #06b6d4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="block text-sm font-bold text-white">Satuw statistikasy</strong>
                            <span class="text-xs" style="color: rgba(255,255,255,0.5)">Görkezijiler we hasabatlar bir seretmede</span>
                        </div>
                    </div>

                    <!-- Pill 3 -->
                    <div
                        class="flex items-center gap-4 rounded-[12px] px-4 py-3"
                        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px)"
                    >
                        <div
                            class="flex h-[38px] w-[38px] flex-shrink-0 items-center justify-center rounded-[10px]"
                            style="background: rgba(16,185,129,0.2)"
                        >
                            <svg class="h-[18px] w-[18px]" style="color: #10b981" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="block text-sm font-bold text-white">Ygtybarly we howpsuz</strong>
                            <span class="text-xs" style="color: rgba(255,255,255,0.5)">Maglumatlaryňyz hemişe goragda</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: rgba(255,255,255,0.38)">
                <Link :href="route('login')" class="transition-colors hover:text-white">← Baş sahypa</Link>
                <span>·</span>
                <a href="#" class="transition-colors hover:text-white">Gizlinlik syýasaty</a>
                <span>·</span>
                <a href="#" class="transition-colors hover:text-white">Hyzmat şertleri</a>
            </div>
        </div>

        <!-- ═══════════════ RIGHT CONTENT PANEL ═══════════════ -->
        <div class="relative flex flex-1 flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-900">
            <!-- Theme toggle — top right -->
            <button
                @click="theme.toggle()"
                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-[10px] border border-line text-muted transition-colors hover:bg-surface hover:text-ink dark:border-dline dark:text-slate-400 dark:hover:bg-dcard dark:hover:text-slate-200"
                aria-label="Temany çalyş"
            >
                <!-- Sun (dark mode active → show sun to switch to light) -->
                <svg v-if="theme.isDark" class="h-[17px] w-[17px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                </svg>
                <!-- Moon (light mode active → show moon to switch to dark) -->
                <svg v-else class="h-[17px] w-[17px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z" />
                </svg>
            </button>

            <!-- Slot (max 400px) -->
            <div class="w-full max-w-[400px]">
                <slot />
            </div>
        </div>
    </div>
</template>
