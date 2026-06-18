<script setup>
import { useToastStore } from '@/stores/useToastStore'

const toast = useToastStore()

const icons = {
    success: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    error:   'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    info:    'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zM12 8.25h.008v.008H12V8.25z',
}
const colors = {
    success: 'text-emerald-500',
    error:   'text-red-500',
    info:    'text-blue-500',
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2">
            <TransitionGroup
                enter-from-class="translate-x-full opacity-0"
                enter-active-class="transition-all duration-300 ease-out"
                leave-active-class="transition-all duration-300 ease-in"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="t in toast.toasts"
                    :key="t.id"
                    class="flex items-center gap-3 min-w-[260px] max-w-sm bg-white dark:bg-dcard border border-line dark:border-dline rounded-card px-4 py-3 shadow-[0_8px_24px_rgba(0,0,0,.12)]"
                >
                    <svg :class="['w-5 h-5 flex-shrink-0', colors[t.type] ?? 'text-blue-500']"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[t.type] ?? icons.info"/>
                    </svg>
                    <span class="text-sm text-ink dark:text-white flex-1 leading-snug">{{ t.message }}</span>
                    <button
                        @click="toast.remove(t.id)"
                        class="ml-1 text-muted hover:text-ink dark:hover:text-white transition-colors flex-shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
