<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
    categories: { type: Array, default: () => [] },
    activeId: { type: [Number, String], default: 'all' },
})
const emit = defineEmits(['select'])

const bar = ref(null)

watch(() => props.activeId, () => nextTick(() => {
    const el = bar.value?.querySelector('.filter-chip.active')
    el?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })
}), { immediate: true })
</script>

<template>
    <div ref="bar" class="filter-bar">
        <button
            class="filter-chip"
            :class="{ active: activeId === 'all' }"
            @click="emit('select', 'all')"
        >Все</button>

        <button
            v-for="c in categories"
            :key="c.id"
            class="filter-chip"
            :class="{ active: activeId === c.id }"
            @click="emit('select', c.id)"
        >{{ c.name }}</button>
    </div>
</template>
