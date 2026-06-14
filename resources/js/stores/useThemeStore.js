import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(
        localStorage.getItem('tasin_dk') === '1' ||
        (localStorage.getItem('tasin_dk') === null && window.matchMedia('(prefers-color-scheme: dark)').matches)
    )

    function apply() {
        document.documentElement.classList.toggle('dark', isDark.value)
    }

    function toggle() {
        isDark.value = !isDark.value
    }

    watch(isDark, (val) => {
        localStorage.setItem('tasin_dk', val ? '1' : '0')
        apply()
    })

    apply()

    return { isDark, toggle }
})
