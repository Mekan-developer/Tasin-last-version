<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    slides: { type: Array, default: () => [] },
})

const index = ref(0)
const count = computed(() => props.slides.length)
let timer = null

function go(i) {
    if (!count.value) return
    index.value = (i + count.value) % count.value
    restart()
}
const next = () => go(index.value + 1)
const prev = () => go(index.value - 1)

function startAuto() {
    stopAuto()
    if (count.value > 1) timer = setInterval(() => { index.value = (index.value + 1) % count.value }, 3500)
}
function stopAuto() { if (timer) { clearInterval(timer); timer = null } }
function restart() { startAuto() }

onMounted(startAuto)
onBeforeUnmount(stopAuto)

// ── Свайп (порог 40px) ──────────────────────────────────────────────────
let startX = 0, dragging = false
function onTouchStart(e) { startX = e.touches[0].clientX; dragging = true; stopAuto() }
function onTouchMove() { /* без live-drag — переключаем по отпусканию */ }
function onTouchEnd(e) {
    if (!dragging) return
    dragging = false
    const dx = e.changedTouches[0].clientX - startX
    if (dx <= -40) next()
    else if (dx >= 40) prev()
    else startAuto()
}

function slideStyle(s) {
    return { background: s.bg_color || 'linear-gradient(135deg, #3b82f6, #2563eb)' }
}
</script>

<template>
    <div v-if="count" class="banner-wrap">
        <div
            class="banner-track"
            :style="{ transform: `translateX(-${index * 100}%)` }"
            @touchstart.passive="onTouchStart"
            @touchmove.passive="onTouchMove"
            @touchend="onTouchEnd"
        >
            <div v-for="s in slides" :key="s.id" class="banner-slide" :style="slideStyle(s)">
                <!-- текст — левая сторона -->
                <div class="banner-content">
                    <span v-if="s.badge" class="banner-badge">{{ s.badge }}</span>
                    <div class="banner-title">{{ s.title }}</div>
                    <div v-if="s.description" class="banner-desc">{{ s.description }}</div>
                    <div v-if="s.price" class="banner-prices">
                        <span class="banner-price font-data">{{ s.price }}</span>
                        <span v-if="s.old_price" class="banner-old font-data">{{ s.old_price }}</span>
                    </div>
                </div>
                <!-- картинка — правая сторона -->
                <div class="banner-img-wrap">
                    <img v-if="s.image_url" :src="s.image_url" :alt="s.title" class="banner-img" />
                </div>
                <span v-if="s.discount" class="banner-discount">−{{ s.discount }}%</span>
            </div>
        </div>

        <button v-if="count > 1" class="banner-arrow prev" @click="prev" aria-label="Назад">
            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </button>
        <button v-if="count > 1" class="banner-arrow next" @click="next" aria-label="Вперёд">
            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>

        <div v-if="count > 1" class="banner-dots">
            <span
                v-for="(s, i) in slides"
                :key="s.id"
                class="banner-dot"
                :class="{ active: i === index }"
                @click="go(i)"
            />
        </div>
    </div>
</template>
