<script setup>
import { ref, computed, watch } from 'vue'
import { useFavorites } from '@/Composables/useFavorites'

const props = defineProps({
    product: { type: Object, default: null },
    loading: { type: Boolean, default: false },
})
const emit = defineEmits(['fav'])

const { isLiked, toggle } = useFavorites()
const liked = computed(() => (props.product ? isLiked(props.product.id) : false))

// ── Галерея изображений ─────────────────────────────────────────────────
const imgIndex = ref(0)
const images = computed(() => props.product?.images ?? [])
watch(() => props.product?.id, () => { imgIndex.value = 0; selectedVariant.value = null })

let startX = 0, dragging = false
function onTouchStart(e) { startX = e.touches[0].clientX; dragging = true }
function onTouchEnd(e) {
    if (!dragging) return
    dragging = false
    const dx = e.changedTouches[0].clientX - startX
    const n = images.value.length
    if (n < 2) return
    if (dx <= -40) imgIndex.value = (imgIndex.value + 1) % n
    else if (dx >= 40) imgIndex.value = (imgIndex.value - 1 + n) % n
}

// ── Варианты (живая подмена цены) ───────────────────────────────────────
const selectedVariant = ref(null)
const displayPrice = computed(() => selectedVariant.value?.price ?? props.product?.price ?? null)
function selectVariant(v) {
    selectedVariant.value = selectedVariant.value?.id === v.id ? null : v
}

function onFav() {
    const p = props.product
    const added = toggle(p.id, { name: p.name, image: images.value[0] ?? null, price: p.price, is_new: p.is_new })
    emit('fav', { id: p.id, added })
}
</script>

<template>
    <div v-if="loading" class="scroll-area"><div class="shop-spinner" /></div>

    <template v-else-if="product">
        <div class="scroll-area">
            <!-- Карусель -->
            <div class="detail-imgs">
                <div
                    class="detail-img-track"
                    :style="{ transform: `translateX(-${imgIndex * 100}%)` }"
                    @touchstart.passive="onTouchStart"
                    @touchend="onTouchEnd"
                >
                    <template v-if="images.length">
                        <div v-for="(src, i) in images" :key="i" class="detail-img">
                            <img :src="src" :alt="product.name" />
                        </div>
                    </template>
                    <div v-else class="detail-img" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe)" />
                </div>

                <div v-if="images.length > 1" class="detail-dots">
                    <span v-for="(s, i) in images" :key="i" class="detail-dot" :class="{ active: i === imgIndex }" @click="imgIndex = i" />
                </div>
            </div>

            <!-- Тело -->
            <div class="detail-body">
                <div class="detail-name">{{ product.name }}</div>

                <div v-if="displayPrice" class="detail-price font-data">{{ displayPrice }}</div>
                <div v-else class="detail-price muted">Цена по запросу</div>

                <!-- Варианты -->
                <div v-if="product.variants?.length" class="detail-section">
                    <div class="detail-section-title">Доступные модели</div>
                    <div class="variant-grid">
                        <button
                            v-for="v in product.variants"
                            :key="v.id"
                            class="variant-card"
                            :class="{ active: selectedVariant?.id === v.id }"
                            @click="selectVariant(v)"
                        >
                            <span class="variant-name">{{ v.name }}</span>
                            <span class="variant-price font-data">{{ parseFloat(v.price) ? v.price : product.price }}</span>
                        </button>
                    </div>
                </div>

                <!-- Описание -->
                <div v-if="product.description" class="detail-section">
                    <div class="detail-section-title">О товаре</div>
                    <div class="detail-desc">{{ product.description }}</div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="detail-cta">
            <button class="fav-btn-large" :class="{ liked }" @click="onFav">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                {{ liked ? 'В избранном' : 'В избранное' }}
            </button>
        </div>
    </template>
</template>
