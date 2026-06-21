<script setup>
import { ref } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'search'])

const input = ref(null)
defineExpose({ focus: () => input.value?.focus() })

function onInput(e) {
    const val = e.target.value
    emit('update:modelValue', val)
    emit('search', val)
}

function clear() {
    emit('update:modelValue', '')
    emit('search', '')
}
</script>

<template>
    <header class="topbar">
        <div class="logo">Tasin<span class="logo-dot" /></div>
        <label class="topbar-search">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z" />
            </svg>
            <input
                ref="input"
                :value="modelValue"
                type="text"
                placeholder="Поиск товаров…"
                @input="onInput"
            />
            <button
                v-if="modelValue"
                class="topbar-clear"
                type="button"
                tabindex="-1"
                @click.prevent="clear"
            >
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </label>
    </header>
</template>
