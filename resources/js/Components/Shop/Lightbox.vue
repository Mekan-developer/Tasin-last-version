<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    images: { type: Array, default: () => [] },
    startIndex: { type: Number, default: 0 },
    open: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'open-product'])

const index = ref(0)
const stage = ref(null)
const dragging = ref(false)
const dragPx = ref(0)

watch(() => props.open, (v) => { if (v) { index.value = props.startIndex; dragPx.value = 0 } })

const count = computed(() => props.images.length)
const caption = computed(() => props.images[index.value]?.product_name ?? '')

const trackStyle = computed(() => ({
    transform: `translateX(calc(${-index.value * 100}% + ${dragPx.value}px))`,
    transition: dragging.value ? 'none' : 'transform .32s cubic-bezier(.4,0,.2,1)',
}))

// ── Pointer drag (§6.4) ──────────────────────────────────────────────────
let startX = 0
function onDown(e) {
    dragging.value = true
    startX = e.clientX
    dragPx.value = 0
    stage.value?.setPointerCapture?.(e.pointerId)
}
function onMove(e) {
    if (!dragging.value) return
    dragPx.value = e.clientX - startX
}
function onUp() {
    if (!dragging.value) return
    dragging.value = false
    const w = stage.value?.offsetWidth || window.innerWidth
    const threshold = w * 0.2
    if (dragPx.value <= -threshold && index.value < count.value - 1) index.value++
    else if (dragPx.value >= threshold && index.value > 0) index.value--
    dragPx.value = 0
}
</script>

<template>
    <div class="lightbox" :class="{ open }">
        <div class="lightbox-bar">
            <span class="lightbox-counter">{{ count ? index + 1 : 0 }} / {{ count }}</span>
            <button class="lightbox-close" @click="emit('close')" aria-label="Закрыть">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="lightbox-stage-wrap">
            <div
                ref="stage"
                class="lightbox-stage"
                @pointerdown="onDown"
                @pointermove="onMove"
                @pointerup="onUp"
                @pointercancel="onUp"
            >
                <div class="lightbox-track" :style="trackStyle">
                    <div v-for="(img, i) in images" :key="i" class="lightbox-slide">
                        <img :src="img.url" :alt="img.product_name" draggable="false" />
                    </div>
                </div>
            </div>

            <button
                class="lightbox-open-btn"
                @click="emit('open-product', images[index]?.product_id)"
                @pointerdown.stop
                aria-label="Открыть товар"
            >
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>

        <div class="lightbox-caption">{{ caption }}</div>
    </div>
</template>
