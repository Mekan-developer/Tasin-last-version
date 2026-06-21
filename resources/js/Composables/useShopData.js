import axios from 'axios'

/**
 * Кэширующие fetch'и к JSON-эндпоинтам витрины. Ответы держим в памяти,
 * чтобы повторный заход на категорию/товар был мгновенным (без перезагрузки).
 */
const categoryCache = new Map() // id -> { category, products }
const productCache = new Map()  // id -> product detail
let allCache = null
let galleryCache = null

export function useShopData() {
    async function categoryProducts(categoryId) {
        const key = Number(categoryId)
        if (categoryCache.has(key)) return categoryCache.get(key)

        const { data } = await axios.get(`/api/shop/categories/${key}/products`)
        categoryCache.set(key, data)
        return data
    }

    async function allProducts() {
        if (allCache) return allCache
        const { data } = await axios.get('/api/shop/products')
        allCache = data
        return data
    }

    async function product(productId) {
        const key = Number(productId)
        if (productCache.has(key)) return productCache.get(key)

        const { data } = await axios.get(`/api/shop/products/${key}`)
        productCache.set(key, data)
        return data
    }

    async function gallery() {
        if (galleryCache) return galleryCache
        const { data } = await axios.get('/api/shop/gallery')
        galleryCache = data
        return data
    }

    async function search(query) {
        const { data } = await axios.get('/api/shop/search', { params: { q: query } })
        return data
    }

    return { categoryProducts, allProducts, product, gallery, search }
}
