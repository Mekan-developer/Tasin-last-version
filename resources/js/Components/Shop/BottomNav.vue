<script setup>
defineProps({
    active: { type: String, default: 'home' },
})
const emit = defineEmits(['navigate', 'search'])
</script>

<template>
    <nav class="bottom-nav">
        <button class="nav-item" :class="{ active: active === 'home' }" @click="emit('navigate', 'home')">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.95-8.95a1.13 1.13 0 011.6 0L21.75 12M4.5 9.75v9.75A1.5 1.5 0 006 21h3.75v-5.25a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5V21H18a1.5 1.5 0 001.5-1.5V9.75" />
            </svg>
            <span>Главная</span>
        </button>

        <button class="nav-item" :class="{ active: active === 'gallery' }" @click="emit('navigate', 'gallery')">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
            </svg>
            <span>Галерея</span>
        </button>

        <button class="nav-center" @click="emit('search')" aria-label="Поиск">
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z" />
            </svg>
        </button>

        <button class="nav-item" :class="{ active: active === 'favorites' }" @click="emit('navigate', 'favorites')">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span>Избранное</span>
        </button>

        <button class="nav-item" :class="{ active: active === 'profile' }" @click="emit('navigate', 'profile')">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0" />
            </svg>
            <span>Профиль</span>
        </button>
    </nav>
</template>
