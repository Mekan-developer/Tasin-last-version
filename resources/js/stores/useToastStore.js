import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])

    function add(type, message, duration = 5000) {
        const id = Date.now() + Math.random()
        toasts.value.push({ id, type, message })
        setTimeout(() => remove(id), duration)
    }

    function remove(id) {
        const idx = toasts.value.findIndex(t => t.id === id)
        if (idx !== -1) toasts.value.splice(idx, 1)
    }

    return {
        toasts,
        success: (msg) => add('success', msg),
        error:   (msg) => add('error', msg),
        info:    (msg) => add('info', msg),
        remove,
    }
})
