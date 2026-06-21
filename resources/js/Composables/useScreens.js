import { reactive, nextTick } from 'vue'

/**
 * Экранный стек витрины с теми же слайд-переходами, что и в прототипе (§6.1):
 *  • вперёд  — новый экран приезжает 100% → 0, предыдущий уезжает 0 → −30%
 *  • назад   — текущий уезжает 0 → 100%, предыдущий возвращается −30% → 0
 *
 * Состояние каждого экрана: 'incoming' | 'active' | 'behind' | 'leaving'.
 * CSS-классы и тайминги — в shop.css (.screen.is-*), очистка через 360ms.
 */
const TRANSITION_MS = 360

let seq = 0
const uid = () => `scr-${++seq}`

export function useScreens(rootName = 'home', rootParams = {}) {
    const stack = reactive([
        { id: uid(), name: rootName, params: rootParams, state: 'active' },
    ])

    function push(name, params = {}) {
        const top = stack[stack.length - 1]
        const item = reactive({ id: uid(), name, params, state: 'incoming' })
        stack.push(item)

        // Кадр на монтаж в позиции 100%, затем — запуск анимации въезда.
        nextTick(() => requestAnimationFrame(() => {
            item.state = 'active'
            if (top) top.state = 'behind'
        }))

        return item
    }

    function back() {
        if (stack.length <= 1) return
        const leaving = stack[stack.length - 1]
        const prev = stack[stack.length - 2]

        leaving.state = 'leaving'
        if (prev) prev.state = 'active'

        setTimeout(() => {
            const idx = stack.indexOf(leaving)
            if (idx !== -1) stack.splice(idx, 1)
        }, TRANSITION_MS)
    }

    /** Сброс к корню (например, нажатие «Главная» в нижней навигации). */
    function reset() {
        while (stack.length > 1) {
            stack.splice(1, stack.length - 1)
        }
        stack[0].state = 'active'
    }

    /** Текущий (верхний) экран. */
    function current() {
        return stack[stack.length - 1]
    }

    return { stack, push, back, reset, current }
}
