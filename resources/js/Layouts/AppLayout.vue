<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useThemeStore } from '@/stores/useThemeStore'
import { useToastStore } from '@/stores/useToastStore'
import AppToast from '@/Components/AppToast.vue'
import axios from 'axios'

const page = usePage()
const themeStore = useThemeStore()
const toast = useToastStore()

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success)
    if (flash?.error)   toast.error(flash.error)
    if (flash?.info)    toast.info(flash.info)
}, { immediate: true, deep: true })

const collapsed = ref(localStorage.getItem('tasin_sb') === '1')
const nav = computed(() => page.props.nav ?? {})
const authUser = computed(() => page.props.auth?.user)

function toggleSidebar() {
    collapsed.value = !collapsed.value
    localStorage.setItem('tasin_sb', collapsed.value ? '1' : '0')
}

function isActive(routeName) {
    try { return route().current(routeName) } catch { return false }
}

function href(routeName) {
    try { return route(routeName) } catch { return '#' }
}

// ─── Global search ───────────────────────────────────────────────────────
const searchQuery   = ref('')
const searchGroups  = ref([])
const searchOpen    = ref(false)
const searchLoading = ref(false)
const activeIndex   = ref(-1)
const searchBox     = ref(null)
const searchInput   = ref(null)
let   searchTimer   = null
let   searchSeq     = 0
let   searchAbort   = null

// Flat list of items (across groups) for keyboard navigation.
const flatResults = computed(() =>
    searchGroups.value.flatMap(g => g.items.map(it => ({ ...it, type: g.type })))
)

const groupIcon = {
    product:  'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    category: 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z',
    slide:    'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
    staff:    'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    user:     'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
}

watch(searchQuery, (val) => {
    clearTimeout(searchTimer)
    activeIndex.value = -1
    const q = val.trim()
    if (q.length < 2) {
        searchGroups.value = []
        searchLoading.value = false
        searchOpen.value = q.length > 0
        return
    }
    searchLoading.value = true
    searchOpen.value = true
    searchTimer = setTimeout(() => runSearch(q), 250)
})

async function runSearch(q) {
    const seq = ++searchSeq
    // Cancel any still-pending request so fast typing never piles up work.
    if (searchAbort) searchAbort.abort()
    searchAbort = new AbortController()
    try {
        const { data } = await axios.get(route('search'), { params: { q }, signal: searchAbort.signal })
        if (seq !== searchSeq) return // a newer request superseded this one
        searchGroups.value = data.groups ?? []
    } catch (e) {
        if (axios.isCancel?.(e) || e?.name === 'CanceledError') return
        if (seq === searchSeq) searchGroups.value = []
    } finally {
        if (seq === searchSeq) searchLoading.value = false
    }
}

function goTo(item) {
    searchOpen.value = false
    searchQuery.value = ''
    searchGroups.value = []
    router.visit(item.url)
}

function onArrow(dir) {
    if (!flatResults.value.length) return
    const n = flatResults.value.length
    activeIndex.value = (activeIndex.value + dir + n) % n
}

function onEnter() {
    if (activeIndex.value >= 0 && flatResults.value[activeIndex.value]) {
        goTo(flatResults.value[activeIndex.value])
    }
}

function closeSearch() {
    searchOpen.value = false
}

function onDocClick(e) {
    if (searchBox.value && !searchBox.value.contains(e.target)) {
        searchOpen.value = false
    }
}

// Ctrl/⌘+K focuses the search.
function onKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        nextTick(() => searchInput.value?.focus())
        searchOpen.value = searchQuery.value.trim().length > 0
    }
}

onMounted(() => {
    document.addEventListener('click', onDocClick)
    document.addEventListener('keydown', onKeydown)
})
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick)
    document.removeEventListener('keydown', onKeydown)
})

const sections = [
    {
        title: 'ГЛАВНОЕ',
        items: [
            {
                label: 'Главная',
                route: 'dashboard',
                badgeKey: null,
                // grid 4 squares
                d: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
            },
            {
                label: 'Категории',
                route: 'categories.index',
                badgeKey: 'categoriesCount',
                // folder
                d: 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z',
            },
            {
                label: 'Товары',
                route: 'products.index',
                badgeKey: 'productsCount',
                // archive-box / cube
                d: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
            },
            {
                label: 'Слайды',
                route: 'slides.index',
                badgeKey: 'slidesCount',
                // presentation / computer screen
                d: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
            },
        ],
    },
    {
        title: 'АНАЛИТИКА',
        items: [
            {
                label: 'Активность',
                route: 'activity',
                badgeKey: null,
                // chart-bar
                d: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            },
        ],
    },
    {
        title: 'ПЕРСОНАЛ',
        items: [
            {
                label: 'Сотрудники',
                route: 'staff.index',
                badgeKey: 'staffCount',
                // briefcase
                d: 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z',
            },
        ],
    },
    {
        title: 'СИСТЕМА',
        items: [
            {
                label: 'Настройки',
                route: 'settings.edit',
                badgeKey: null,
                // cog-6-tooth
                d: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            },
            {
                label: 'Администраторы',
                route: 'users.index',
                badgeKey: null,
                // users
                d: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            },
        ],
    },
]
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-surface dark:bg-dbg font-sans">

        <!-- ─── SIDEBAR ─── -->
        <aside
            :class="[
                'flex flex-col flex-shrink-0 bg-navy transition-all duration-300 ease-in-out overflow-hidden',
                collapsed ? 'w-[68px]' : 'w-60',
            ]"
        >
            <!-- Logo + toggle -->
            <div
                :class="[
                    'flex items-center h-16 flex-shrink-0 border-b border-white/10 px-4',
                    collapsed ? 'justify-center' : 'justify-between',
                ]"
            >
                <!-- Logo (hidden when collapsed) -->
                <div v-if="!collapsed" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                         style="background: linear-gradient(135deg,#3b82f6,#06b6d4)">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3" />
                        </svg>
                    </div>
                    <span class="text-white font-extrabold text-lg tracking-tight">Tasin</span>
                </div>

                <!-- Collapse button -->
                <button
                    @click="toggleSidebar"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors flex-shrink-0"
                >
                    <svg
                        class="w-4 h-4 transition-transform duration-300"
                        :class="collapsed ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-2 scrollbar-none">
                <template v-for="section in sections" :key="section.title">
                    <!-- Section header -->
                    <div v-if="!collapsed" class="px-4 pt-4 pb-1">
                        <span class="text-[10px] font-bold text-white/25 tracking-[0.15em] uppercase">
                            {{ section.title }}
                        </span>
                    </div>
                    <div v-else class="mx-3 my-3 border-t border-white/10" />

                    <!-- Items -->
                    <Link
                        v-for="item in section.items"
                        :key="item.route"
                        :href="href(item.route)"
                        :class="[
                            'relative group flex items-center gap-3 mx-2 my-0.5 rounded-xl py-2.5 text-sm font-medium transition-all duration-150',
                            collapsed ? 'px-0 justify-center' : 'px-3',
                            isActive(item.route)
                                ? 'bg-blue-500/20 text-white border-r-[3px] border-r-blue-500'
                                : 'text-white/55 hover:text-white hover:bg-white/8',
                        ]"
                    >
                        <!-- Icon -->
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.d" />
                        </svg>

                        <!-- Label + badge -->
                        <template v-if="!collapsed">
                            <span class="flex-1 truncate">{{ item.label }}</span>
                            <span
                                v-if="item.badgeKey && nav[item.badgeKey]"
                                class="flex-shrink-0 text-[11px] font-bold text-blue-300 bg-blue-500/35 rounded-full px-2 py-0.5 leading-none"
                            >
                                {{ nav[item.badgeKey] }}
                            </span>
                        </template>

                        <!-- Tooltip on collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full ml-3 z-50 hidden group-hover:block bg-gray-900 text-white text-xs rounded-lg px-2.5 py-1.5 whitespace-nowrap shadow-xl"
                        >
                            {{ item.label }}
                        </div>
                    </Link>
                </template>
            </nav>

            <!-- User card -->
            <div class="flex-shrink-0 border-t border-white/10 p-3">
                <div :class="['flex items-center gap-3', collapsed ? 'justify-center' : '']">
                    <div
                        class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white"
                        style="background: linear-gradient(135deg,#3b82f6,#8b5cf6)"
                    >
                        {{ authUser?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                    </div>
                    <div v-if="!collapsed" class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ authUser?.name }}</p>
                        <p class="text-xs text-white/35 truncate">
                            {{ authUser?.role === 'admin' ? 'Администратор' : 'Менеджер' }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ─── MAIN WRAP ─── -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <!-- Topbar -->
            <header class="flex-shrink-0 h-16 flex items-center gap-4 px-6 bg-white dark:bg-dcard border-b border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">

                <!-- Search -->
                <div ref="searchBox" class="relative flex-1 max-w-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        ref="searchInput"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Поиск товаров, категорий, сотрудников…"
                        autocomplete="off"
                        class="w-full pl-9 pr-10 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all"
                        @focus="searchQuery.trim().length > 0 && (searchOpen = true)"
                        @keydown.down.prevent="onArrow(1)"
                        @keydown.up.prevent="onArrow(-1)"
                        @keydown.enter.prevent="onEnter"
                        @keydown.esc="closeSearch"
                    />
                    <!-- Clear / shortcut hint -->
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''; searchOpen = false"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded text-muted hover:text-ink dark:hover:text-white"
                        title="Очистить"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <kbd
                        v-else
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden md:flex items-center gap-0.5 text-[10px] font-medium text-muted border border-line dark:border-dline rounded px-1.5 py-0.5 pointer-events-none"
                    >⌘K</kbd>

                    <!-- Results dropdown -->
                    <Transition
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-active-class="transition duration-150"
                        leave-active-class="transition duration-100"
                        leave-to-class="opacity-0 -translate-y-1"
                    >
                        <div
                            v-if="searchOpen"
                            class="absolute left-0 right-0 top-full mt-2 z-[700] max-h-[70vh] overflow-y-auto bg-white dark:bg-dcard border border-line dark:border-dline rounded-card shadow-[0_12px_40px_rgba(0,0,0,.16)] py-1.5"
                        >
                            <!-- Loading -->
                            <div v-if="searchLoading" class="px-4 py-6 text-center text-sm text-muted">
                                Поиск…
                            </div>

                            <!-- Hint (too short) -->
                            <div v-else-if="searchQuery.trim().length < 2" class="px-4 py-6 text-center text-sm text-muted">
                                Введите минимум 2 символа
                            </div>

                            <!-- Empty -->
                            <div v-else-if="!searchGroups.length" class="px-4 py-6 text-center text-sm text-muted">
                                Ничего не найдено по запросу «{{ searchQuery.trim() }}»
                            </div>

                            <!-- Grouped results -->
                            <template v-else>
                                <div v-for="group in searchGroups" :key="group.type" class="mb-1 last:mb-0">
                                    <div class="px-4 pt-2 pb-1 text-[10px] font-bold text-muted tracking-[0.12em] uppercase">
                                        {{ group.label }}
                                    </div>
                                    <button
                                        v-for="item in group.items"
                                        :key="group.type + '-' + item.id"
                                        @click="goTo({ ...item, type: group.type })"
                                        :class="[
                                            'w-full flex items-center gap-3 px-4 py-2 text-left transition-colors',
                                            flatResults[activeIndex] && flatResults[activeIndex].type === group.type && flatResults[activeIndex].id === item.id
                                                ? 'bg-blue-500/10'
                                                : 'hover:bg-surface dark:hover:bg-dline',
                                        ]"
                                    >
                                        <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-lg bg-blue-500/10 text-blue-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" :d="groupIcon[group.type]" />
                                            </svg>
                                        </span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm font-medium text-ink dark:text-white truncate">{{ item.title }}</span>
                                            <span v-if="item.subtitle" class="block text-xs text-muted truncate">{{ item.subtitle }}</span>
                                        </span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </Transition>
                </div>

                <div class="flex items-center gap-1.5 ml-auto">

                    <!-- Theme toggle -->
                    <button
                        @click="themeStore.toggle"
                        class="w-[38px] h-[38px] flex items-center justify-center rounded-[10px] text-muted hover:text-ink dark:text-white/50 dark:hover:text-white hover:bg-surface dark:hover:bg-dline transition-colors"
                        title="Тема"
                    >
                        <!-- Moon — visible in light mode -->
                        <svg v-if="!themeStore.isDark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                        </svg>
                        <!-- Sun — visible in dark mode -->
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <button
                        class="relative w-[38px] h-[38px] flex items-center justify-center rounded-[10px] text-muted hover:text-ink dark:text-white/50 dark:hover:text-white hover:bg-surface dark:hover:bg-dline transition-colors"
                        title="Уведомления"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <!-- Red dot -->
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-dcard"></span>
                    </button>

                    <!-- Divider -->
                    <div class="w-px h-6 bg-line dark:bg-dline mx-1"></div>

                    <!-- User -->
                    <div class="flex items-center gap-2.5 pl-1">
                        <div
                            class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white"
                            style="background: linear-gradient(135deg,#3b82f6,#8b5cf6)"
                        >
                            {{ authUser?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-ink dark:text-white leading-tight">{{ authUser?.name }}</p>
                            <p class="text-[11px] text-muted leading-tight">Администратор</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-[26px]">
                <slot />
            </main>
        </div>
    </div>

    <!-- Global toasts -->
    <AppToast />
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
/* Subtle hover for non-active nav items */
.hover\:bg-white\/8:hover { background-color: rgba(255,255,255,0.08); }
</style>
