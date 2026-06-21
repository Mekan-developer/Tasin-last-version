import axios from 'axios'

/**
 * Трекинг просмотров товара. Шлёт POST /api/shop/track с анонимным
 * 8-символьным hex-id посетителя (localStorage 'shop-session'). Бэкенд пишет
 * строку в activity_logs, которую читает админка. Fire-and-forget, ошибки глушим.
 */
const SESSION_KEY = 'shop-session'

function sessionId() {
    let id = localStorage.getItem(SESSION_KEY)
    if (!id || !/^[a-f0-9]{8}$/.test(id)) {
        id = Array.from({ length: 8 }, () => Math.floor(Math.random() * 16).toString(16)).join('')
        localStorage.setItem(SESSION_KEY, id)
    }
    return id
}

export function useTracking() {
    function trackProduct(productId) {
        axios.post('/api/shop/track', {
            session: sessionId(),
            product_id: Number(productId),
        }).catch(() => {})
    }

    return { trackProduct }
}
