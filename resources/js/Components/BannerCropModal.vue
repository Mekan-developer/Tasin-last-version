<script setup>
import { ref } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

const props = defineProps({
    show: { type: Boolean, default: false },
    src:  { type: String,  default: '' },
})

const emit = defineEmits(['confirm', 'close'])

const cropperRef = ref(null)

function confirm() {
    const { canvas } = cropperRef.value.getResult()
    canvas.toBlob((blob) => emit('confirm', blob), 'image/webp', 0.92)
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[1100] flex items-center justify-center bg-black/75 backdrop-blur-sm p-4"
            >
                <div class="bg-white dark:bg-dcard rounded-2xl overflow-hidden shadow-2xl w-full max-w-sm flex flex-col" style="max-height: 88vh">

                    <!-- Заголовок -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-line dark:border-dline flex-shrink-0">
                        <div>
                            <p class="text-sm font-bold text-ink dark:text-white">Кадрировать изображение</p>
                            <p class="text-[11px] text-muted mt-0.5">Соотношение 1:1 — оптимально для баннера</p>
                        </div>
                        <button
                            @click="$emit('close')"
                            class="w-8 h-8 flex items-center justify-center rounded-btn text-muted hover:text-ink dark:hover:text-white hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Кроппер -->
                    <div class="flex-1 overflow-hidden bg-black" style="min-height: 280px; max-height: 480px">
                        <Cropper
                            v-if="show && src"
                            ref="cropperRef"
                            :src="src"
                            :stencil-props="{ aspectRatio: 1 }"
                            background-class="bg-black"
                            class="w-full h-full"
                            style="height: 100%; max-height: 480px"
                        />
                    </div>

                    <!-- Подсказка -->
                    <p class="text-[11px] text-muted text-center py-2 bg-surface dark:bg-dbg flex-shrink-0">
                        Перетащите рамку · Прокрутите колесо мыши для зума
                    </p>

                    <!-- Кнопки -->
                    <div class="flex gap-3 px-5 py-4 border-t border-line dark:border-dline flex-shrink-0">
                        <button
                            type="button"
                            @click="confirm"
                            class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-btn transition-colors"
                        >
                            Готово
                        </button>
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
