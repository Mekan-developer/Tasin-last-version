<script setup>
import { computed } from 'vue'
import { useFavorites } from '@/Composables/useFavorites'

const props = defineProps({
    product: { type: Object, required: true },
    index: { type: Number, default: 0 },
})
const emit = defineEmits(['open', 'fav'])

const { isLiked, toggle } = useFavorites()
const liked = computed(() => isLiked(props.product.id))

const gradients = [
    'linear-gradient(135deg,#dbeafe,#bfdbfe)',
    'linear-gradient(135deg,#ede9fe,#ddd6fe)',
    'linear-gradient(135deg,#d1fae5,#a7f3d0)',
    'linear-gradient(135deg,#fef3c7,#fde68a)',
    'linear-gradient(135deg,#cffafe,#a5f3fc)',
    'linear-gradient(135deg,#fee2e2,#fecaca)',
]
const bg = computed(() => gradients[props.index % gradients.length])

function onFav() {
    const p = props.product
    const added = toggle(p.id, { name: p.name, image: p.image, price: p.price, is_new: p.is_new })
    emit('fav', { id: p.id, added })
}
</script>

<template>
    <article class="prod-card" @click="emit('open', product.id)">
        <div class="prod-img" :style="product.image ? {} : { background: bg }">
            <img v-if="product.image" :src="product.image" :alt="product.name" loading="lazy" />
            <span v-if="product.is_new" class="prod-badge">Новинка</span>
            <button class="prod-fav" :class="{ liked }" @click.stop="onFav" aria-label="В избранное">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        </div>
        <div class="prod-info">
            <div class="prod-name">{{ product.name }}</div>
            <div v-if="product.price" class="prod-price font-data">{{ product.price }}</div>
            <div v-else class="prod-price muted">Цена по запросу</div>
        </div>
    </article>
</template>
