<script setup>
defineProps({
    products: { type: Array, default: () => [] },
    loading:  { type: Boolean, default: false },
    query:    { type: String, default: '' },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="search-results-wrap">
        <div v-if="loading" class="search-state">
            <div class="shop-spinner" />
        </div>

        <div v-else-if="!query || query.length < 2" class="search-state">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z" />
            </svg>
            <p>Введите название товара или вариант</p>
        </div>

        <div v-else-if="!products.length" class="search-state">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75s.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
            </svg>
            <p>По запросу «{{ query }}»<br>ничего не найдено</p>
        </div>

        <div v-else class="search-list">
            <div
                v-for="p in products"
                :key="p.id"
                class="search-row"
                @click="emit('open', p.id)"
            >
                <div class="search-thumb">
                    <img v-if="p.image" :src="p.image" :alt="p.name" />
                    <svg v-else fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 19.5h18M3.75 4.5h16.5a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75H3.75a.75.75 0 01-.75-.75V5.25a.75.75 0 01.75-.75z" />
                    </svg>
                </div>

                <div class="search-info">
                    <div class="search-name">
                        <span v-if="p.is_new" class="search-new-badge">Новинка</span>
                        {{ p.name }}
                    </div>
                </div>

                <div class="search-price-col">
                    <span v-if="p.price" class="search-price font-data">{{ p.price }}</span>
                    <svg class="search-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>
