import { ref } from 'vue'

/**
 * Избранное — целиком на клиенте в localStorage (ключ 'shop-mobile-liked').
 * Состояние — общий реактивный Set id, так что все экземпляры карточки одного
 * товара синхронизируются автоматически (аналог syncFavUI из прототипа).
 *
 * Дополнительно кэшируем минимальные данные карточки ('shop-mobile-liked-cards'),
 * чтобы экран «Избранное» рисовался без сети и переживал перезагрузку.
 */
const STORAGE_KEY = 'shop-mobile-liked'
const CARDS_KEY = 'shop-mobile-liked-cards'

function loadIds() {
    try {
        const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
        return Array.isArray(raw) ? raw.map(Number) : []
    } catch {
        return []
    }
}

function loadCards() {
    try {
        return JSON.parse(localStorage.getItem(CARDS_KEY) || '{}') || {}
    } catch {
        return {}
    }
}

// Module-level singletons — общие для всех вызовов useFavorites().
const liked = ref(new Set(loadIds()))
const cards = ref(loadCards())

function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify([...liked.value]))
    localStorage.setItem(CARDS_KEY, JSON.stringify(cards.value))
}

export function useFavorites() {
    const isLiked = (id) => liked.value.has(Number(id))

    /**
     * Переключить избранное; вернёт новое состояние (true — добавлено).
     * При добавлении сохраняем минимальную карточку для экрана «Избранное».
     */
    function toggle(id, card = null) {
        id = Number(id)
        const nextIds = new Set(liked.value)
        const nextCards = { ...cards.value }
        const added = !nextIds.has(id)

        if (added) {
            nextIds.add(id)
            if (card) nextCards[id] = { id, ...card }
        } else {
            nextIds.delete(id)
            delete nextCards[id]
        }

        liked.value = nextIds
        cards.value = nextCards
        persist()
        return added
    }

    /** Карточки избранного в порядке добавления. */
    const favoriteCards = () => [...liked.value]
        .map((id) => cards.value[id])
        .filter(Boolean)

    const count = () => liked.value.size

    return { liked, isLiked, toggle, favoriteCards, count }
}
