<script setup>
defineProps({
    categories: { type: Array, default: () => [] },
})
const emit = defineEmits(['open', 'open-all'])

// Цветные градиенты-плейсхолдеры (fallback, когда у категории нет иконки).
const gradients = [
    'linear-gradient(135deg,#3b82f6,#2563eb)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
    'linear-gradient(135deg,#ef4444,#dc2626)',
    'linear-gradient(135deg,#ec4899,#db2777)',
    'linear-gradient(135deg,#6366f1,#4338ca)',
]
const bg = (i) => gradients[i % gradients.length]
</script>

<template>
    <section>
        <div class="section-head">
            <span class="section-title">Категории</span>
            <button v-if="categories.length" class="section-link" @click="emit('open-all')">Все</button>
        </div>

        <div class="cats-grid">
            <button
                v-for="(c, i) in categories"
                :key="c.id"
                class="cat-card"
                :class="{ wide: i === 0 }"
                :style="{ background: bg(i) }"
                @click="emit('open', c.id)"
            >
                <div class="cat-icon">
                    <img v-if="c.image_url" :src="c.image_url" :alt="c.name" />
                    <svg v-else fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="5" y="2" width="14" height="20" rx="2.5" /><path stroke-linecap="round" d="M10 18h4" />
                    </svg>
                </div>
                <div>
                    <div class="cat-name">{{ c.name }}</div>
                    <div class="cat-count">{{ c.products_count }} товаров</div>
                </div>
            </button>
        </div>
    </section>
</template>
