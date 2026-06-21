<script setup>
defineProps({
    images: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="scroll-area">
        <div v-if="loading" class="shop-spinner" />

        <div v-else-if="images.length" class="gallery-grid">
            <button
                v-for="(img, i) in images"
                :key="i"
                class="gallery-tile"
                @click="emit('open', i)"
            >
                <img :src="img.url" :alt="img.product_name" loading="lazy" />
            </button>
        </div>

        <div v-else class="fav-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
            </svg>
            <p>Изображений пока нет</p>
        </div>
    </div>
</template>
