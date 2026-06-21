<script setup>
import { reactive, computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import '../../../css/shop.css'

import TopBar from '@/Components/Shop/TopBar.vue'
import BottomNav from '@/Components/Shop/BottomNav.vue'
import BackHeader from '@/Components/Shop/BackHeader.vue'
import BannerCarousel from '@/Components/Shop/BannerCarousel.vue'
import CategoryGrid from '@/Components/Shop/CategoryGrid.vue'
import ProductGrid from '@/Components/Shop/ProductGrid.vue'
import FilterBar from '@/Components/Shop/FilterBar.vue'
import ProductDetail from '@/Components/Shop/ProductDetail.vue'
import Gallery from '@/Components/Shop/Gallery.vue'
import Lightbox from '@/Components/Shop/Lightbox.vue'
import Favorites from '@/Components/Shop/Favorites.vue'
import Profile from '@/Components/Shop/Profile.vue'
import Toast from '@/Components/Shop/Toast.vue'
import SearchResults from '@/Components/Shop/SearchResults.vue'

import { useScreens } from '@/Composables/useScreens'
import { useShopData } from '@/Composables/useShopData'
import { useTracking } from '@/Composables/useTracking'
import { useFavorites } from '@/Composables/useFavorites'

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    slides: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
})

const screens = useScreens('home')
const shop = useShopData()
const { trackProduct } = useTracking()
const { favoriteCards } = useFavorites()

// ── Поиск ────────────────────────────────────────────────────────────────
const searchQuery   = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
let   searchTimer   = null

const searchActive = computed(() => searchQuery.value.length > 0)

function onSearch(q) {
    searchQuery.value = q
    clearTimeout(searchTimer)
    if (q.length < 2) {
        searchResults.value = []
        return
    }
    searchTimer = setTimeout(() => doSearch(q), 350)
}

async function doSearch(q) {
    searchLoading.value = true
    try {
        const data = await shop.search(q)
        if (searchQuery.value === q) searchResults.value = data.products
    } finally {
        if (searchQuery.value === q) searchLoading.value = false
    }
}

function closeSearch() {
    searchQuery.value   = ''
    searchResults.value = []
    clearTimeout(searchTimer)
}

function openProductFromSearch(productId) {
    closeSearch()
    openProduct(productId)
}

// ── Toast (§6.7) ─────────────────────────────────────────────────────────
const toast = reactive({ message: '', show: false })
let toastTimer = null
function showToast(message) {
    toast.message = message
    toast.show = true
    clearTimeout(toastTimer)
    toastTimer = setTimeout(() => { toast.show = false }, 2500)
}
function onFav({ added }) {
    showToast(added ? 'Добавлено в избранное' : 'Удалено из избранного')
}

// ── Lightbox (общий оверлей) ──────────────────────────────────────────────
const lightbox = reactive({ open: false, images: [], index: 0 })
function openLightbox(images, i) {
    lightbox.images = images
    lightbox.index = i
    lightbox.open = true
}

// ── Категория ─────────────────────────────────────────────────────────────
function openCategory(categoryId) {
    const item = screens.push('category', {
        activeId: categoryId, loading: true, products: [], title: 'Категория', subtitle: '',
    })
    loadCategory(item, categoryId)
}
function openAll() {
    const item = screens.push('category', {
        activeId: 'all', loading: true, products: [], title: 'Все товары', subtitle: '',
    })
    loadAll(item)
}
async function loadCategory(item, categoryId) {
    item.params.loading = true
    try {
        const data = await shop.categoryProducts(categoryId)
        item.params.products = data.products
        item.params.title = data.category?.name ?? 'Категория'
        item.params.subtitle = `${data.category?.products_count ?? data.products.length} товаров`
        item.params.activeId = categoryId
    } catch {
        item.params.products = []
    } finally {
        item.params.loading = false
    }
}
async function loadAll(item) {
    item.params.loading = true
    try {
        const data = await shop.allProducts()
        item.params.products = data.products
        item.params.title = 'Все товары'
        item.params.subtitle = `${data.products.length} товаров`
        item.params.activeId = 'all'
    } catch {
        item.params.products = []
    } finally {
        item.params.loading = false
    }
}
function selectFilter(item, sel) {
    if (sel === item.params.activeId) return
    sel === 'all' ? loadAll(item) : loadCategory(item, sel)
}

// ── Товар ───────────────────────────────────────────────────────────────
function openProduct(productId) {
    const item = screens.push('product', { productId, loading: true, detail: null, title: '' })
    loadProduct(item, productId)
    trackProduct(productId)
}
async function loadProduct(item, productId) {
    item.params.loading = true
    try {
        const data = await shop.product(productId)
        item.params.detail = data
        item.params.title = data.name
    } finally {
        item.params.loading = false
    }
}

// ── Галерея ───────────────────────────────────────────────────────────────
function openGallery() {
    const item = screens.push('gallery', { loading: true, images: [] })
    loadGallery(item)
}
async function loadGallery(item) {
    item.params.loading = true
    try {
        const data = await shop.gallery()
        item.params.images = data.images
    } finally {
        item.params.loading = false
    }
}

function openFavorites() { screens.push('favorites', {}) }
function openProfile() { screens.push('profile', {}) }

// ── Нижняя навигация (только на главном экране) ──────────────────────────
function onNavigate(key) {
    if (key === 'home') screens.reset()
    else if (key === 'gallery') openGallery()
    else if (key === 'favorites') openFavorites()
    else if (key === 'profile') openProfile()
}

// ── Поделиться ──────────────────────────────────────────────────────────
function onShare(title, productId) {
    const url = window.location.origin + window.location.pathname + '?product=' + productId
    if (navigator.share) {
        navigator.share({ title, url }).catch((err) => {
            if (err.name !== 'AbortError') copyToClipboard(url)
        })
    } else {
        copyToClipboard(url)
    }
}
function copyToClipboard(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => showToast('Ссылка скопирована'))
    } else {
        showToast('Поделиться недоступно — откройте сайт через HTTPS')
    }
}

function onShareFavorites() {
    const list = favoriteCards()
    if (!list.length) {
        showToast('Список избранного пуст')
        return
    }
    const text = list
        .map((p) => p.price ? `• ${p.name} — ${p.price}` : `• ${p.name}`)
        .join('\n')
    const shareData = {
        title: 'Мои избранные товары',
        text: `Мои избранные товары:\n\n${text}\n\n${window.location.origin + window.location.pathname}`,
    }
    if (navigator.share) {
        navigator.share(shareData).catch((err) => {
            if (err.name !== 'AbortError') copyToClipboard(shareData.text)
        })
    } else {
        copyToClipboard(shareData.text)
    }
}

function stateClass(item) {
    return {
        'is-active': item.state === 'active',
        'is-behind': item.state === 'behind',
        'is-incoming': item.state === 'incoming',
        'is-leaving': item.state === 'leaving',
    }
}

const activeNav = computed(() => {
    const name = screens.current().name
    return ['home', 'gallery', 'favorites', 'profile'].includes(name) ? name : 'home'
})
</script>

<template>
    <Head title="Магазин" />

    <div class="shop-app">
        <div class="screen-stack">
            <section v-for="item in screens.stack" :key="item.id" class="screen" :class="stateClass(item)">
                <!-- ── Главная ── -->
                <template v-if="item.name === 'home'">
                    <TopBar v-model="searchQuery" @search="onSearch" />
                    <div class="scroll-area">
                        <BannerCarousel :slides="slides" />
                        <CategoryGrid :categories="categories" @open="openCategory" @open-all="openAll" />
                    </div>
                    <BottomNav :active="activeNav" @navigate="onNavigate" @search="onNavigate('home')" />
                </template>

                <!-- ── Категория ── -->
                <template v-else-if="item.name === 'category'">
                    <BackHeader :title="item.params.title" :subtitle="item.params.subtitle" @back="screens.back" />
                    <FilterBar :categories="categories" :active-id="item.params.activeId" @select="selectFilter(item, $event)" />
                    <div class="scroll-area">
                        <ProductGrid :products="item.params.products" :loading="item.params.loading" @open="openProduct" @fav="onFav" />
                    </div>
                </template>

                <!-- ── Товар ── -->
                <template v-else-if="item.name === 'product'">
                    <BackHeader :title="item.params.title" action="share" @back="screens.back" @action="onShare(item.params.title, item.params.productId)" />
                    <ProductDetail :product="item.params.detail" :loading="item.params.loading" @fav="onFav" />
                </template>

                <!-- ── Галерея ── -->
                <template v-else-if="item.name === 'gallery'">
                    <BackHeader title="Галерея" :subtitle="`${item.params.images.length} изображений`" @back="screens.back" />
                    <Gallery
                        :images="item.params.images"
                        :loading="item.params.loading"
                        @open="openLightbox(item.params.images, $event)"
                    />
                </template>

                <!-- ── Избранное ── -->
                <template v-else-if="item.name === 'favorites'">
                    <BackHeader title="Избранное" action="share" @back="screens.back" @action="onShareFavorites" />
                    <Favorites @open="openProduct" @fav="onFav" />
                </template>

                <!-- ── Профиль ── -->
                <template v-else-if="item.name === 'profile'">
                    <BackHeader title="Профиль" @back="screens.back" />
                    <Profile :settings="settings" />
                </template>
            </section>
        </div>

        <Toast :message="toast.message" :show="toast.show" />
        <Lightbox
            :images="lightbox.images"
            :start-index="lightbox.index"
            :open="lightbox.open"
            @close="lightbox.open = false"
            @open-product="(id) => { lightbox.open = false; openProduct(id) }"
        />

        <!-- ── Оверлей поиска ── -->
        <Transition name="search-overlay">
            <div v-if="searchActive" class="search-overlay">
                <div class="search-overlay-header">
                    <button class="back-btn" @click="closeSearch">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </button>
                    <span class="search-overlay-title">Результаты поиска</span>
                    <span v-if="searchResults.length" class="search-count-badge">{{ searchResults.length }}</span>
                </div>
                <div class="scroll-area">
                    <SearchResults
                        :products="searchResults"
                        :loading="searchLoading"
                        :query="searchQuery"
                        @open="openProductFromSearch"
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>
