<script setup>
defineProps({
    show:    { type: Boolean, default: false },
    name:    { type: String, default: '' },
    loading: { type: Boolean, default: false },
})

defineEmits(['confirm', 'close'])
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />

                <!-- Dialog -->
                <div class="relative z-10 w-full max-w-md bg-white dark:bg-dcard rounded-card p-6 shadow-[0_16px_48px_rgba(0,0,0,.2)]">
                    <!-- Icon -->
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </div>

                    <h3 class="text-center text-base font-bold text-ink dark:text-white mb-1">Удалить запись</h3>
                    <p class="text-center text-sm text-muted">
                        Вы уверены, что хотите удалить <strong class="font-semibold text-ink dark:text-white">{{ name }}</strong>?
                        Это действие нельзя отменить.
                    </p>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="$emit('close')"
                            :disabled="loading"
                            class="flex-1 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="$emit('confirm')"
                            :disabled="loading"
                            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 disabled:bg-red-400 text-white text-sm font-semibold rounded-btn transition-colors"
                        >
                            {{ loading ? 'Удаление…' : 'Удалить' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
