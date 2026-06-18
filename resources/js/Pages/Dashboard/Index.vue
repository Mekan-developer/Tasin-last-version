<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppDrawer from '@/Components/AppDrawer.vue'

const props = defineProps({
    stats:          { type: Object, required: true },
    topProducts:    { type: Array,  default: () => [] },
    recentActivity: { type: Array,  default: () => [] },
    settings:       { type: Object, default: () => ({}) },
    categories:     { type: Array,  default: () => [] },
})

const showDrawer = ref(false)

// ── Stat cards config ────────────────────────────────────────────────────
const statCards = computed(() => [
    {
        label:    'Категории',
        value:    props.stats.categoriesCount ?? 0,
        delta:    `▲ ${props.stats.activeCategories ?? 0} активных`,
        deltaCls: 'text-blue-500',
        iconBg:   'bg-blue-500/10',
        iconCls:  'text-blue-500',
        // grid 4 squares
        d: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    },
    {
        label:    'Товары',
        value:    props.stats.productsCount ?? 0,
        delta:    `▲ ${props.stats.newProductsCount ?? 0} новинок`,
        deltaCls: 'text-emerald-500',
        iconBg:   'bg-emerald-500/10',
        iconCls:  'text-emerald-500',
        // archive-box
        d: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    },
    {
        label:    'Слайды',
        value:    props.stats.slidesCount ?? 0,
        delta:    `▲ ${props.stats.discountSlidesCount ?? 0} со скидкой`,
        deltaCls: 'text-amber-500',
        iconBg:   'bg-amber-500/10',
        iconCls:  'text-amber-500',
        // computer-desktop / slides
        d: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
    },
    {
        label:    'Администраторы',
        value:    props.stats.adminCount ?? 0,
        delta:    `${props.stats.adminRoles?.admin ?? 0} админ · ${props.stats.adminRoles?.manager ?? 0} менеджера`,
        deltaCls: 'text-muted',
        iconBg:   'bg-violet-500/10',
        iconCls:  'text-violet-500',
        // users
        d: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    },
])

// ── Activity icon paths ───────────────────────────────────────────────────
function safeRoute(name) {
    try { return route(name) } catch { return '#' }
}

// ── Activity icon paths ───────────────────────────────────────────────────
const activityIcons = {
    box: {
        d: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        cls: 'bg-blue-500/10 text-blue-500',
    },
    folder: {
        d: 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z',
        cls: 'bg-emerald-500/10 text-emerald-500',
    },
    slide: {
        d: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
        cls: 'bg-amber-500/10 text-amber-500',
    },
}
</script>

<template>
    <AppLayout>
        <!-- ── Page Header ─────────────────────────────────────────── -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Главная</h1>
                <p class="text-xs text-muted mt-1">Добро пожаловать, Азиз! Последние события в Tasin-mobile.</p>
            </div>
            <button
                @click="showDrawer = true"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-sm font-semibold rounded-btn shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Добавить товар
            </button>
        </div>

        <!-- ── 4 Stat Cards ────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)] hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(0,0,0,.10)] transition-all duration-200"
            >
                <div class="flex items-center gap-4">
                    <!-- Icon -->
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0', card.iconBg]">
                        <svg :class="['w-6 h-6', card.iconCls]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.d"/>
                        </svg>
                    </div>
                    <!-- Value + label -->
                    <div>
                        <p class="text-[28px] font-bold font-data leading-none text-ink dark:text-white">
                            {{ card.value }}
                        </p>
                        <p class="text-xs text-muted mt-1">{{ card.label }}</p>
                    </div>
                </div>
                <!-- Delta -->
                <p :class="['text-xs font-medium mt-3', card.deltaCls]">{{ card.delta }}</p>
            </div>
        </div>

        <!-- ── 2-Column Content ────────────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-4 mb-4">

            <!-- Top Products -->
            <div class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-[15px] text-ink dark:text-white">Топ товаров по просмотрам</h2>
                    <Link
                        :href="safeRoute('products.index')"
                        class="text-xs text-blue-500 hover:text-blue-600 font-semibold transition-colors"
                    >
                        Все товары →
                    </Link>
                </div>

                <div v-if="topProducts.length" class="space-y-4">
                    <div v-for="(product, i) in topProducts" :key="product.id" class="flex items-center gap-3">
                        <!-- Rank -->
                        <span class="text-xs font-bold text-muted font-data w-4 flex-shrink-0 text-center">{{ i + 1 }}</span>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-ink dark:text-white truncate">{{ product.name }}</p>
                                <span class="text-xs text-muted font-data ml-2 flex-shrink-0">{{ product.views }}</span>
                            </div>
                            <p class="text-[11px] text-muted mb-1.5">{{ product.category }}</p>
                            <!-- Bar -->
                            <div class="h-1.5 bg-surface dark:bg-dbg rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-blue-500 rounded-full transition-all duration-500"
                                    :style="{ width: product.barWidth + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="py-10 text-center text-sm text-muted">
                    Товаров пока нет
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                <h2 class="font-bold text-[15px] text-ink dark:text-white mb-5">Последние действия</h2>

                <div v-if="recentActivity.length" class="space-y-3">
                    <div v-for="(item, i) in recentActivity" :key="i" class="flex items-start gap-3">
                        <!-- Icon -->
                        <div :class="['w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center', (activityIcons[item.iconType] ?? activityIcons.box).cls]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      :d="(activityIcons[item.iconType] ?? activityIcons.box).d"/>
                            </svg>
                        </div>
                        <!-- Text -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-ink dark:text-white leading-snug">
                                {{ item.prefix }} <strong class="font-semibold">{{ item.highlight }}</strong>
                            </p>
                            <p class="text-[11px] text-muted mt-0.5">{{ item.time }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="py-10 text-center text-sm text-muted">
                    Нет недавних действий
                </div>
            </div>
        </div>

        <!-- ── Config Strip ────────────────────────────────────────── -->
        <div class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)] grid grid-cols-3 divide-x divide-line dark:divide-dline">
            <div class="py-4 px-6 text-center">
                <p class="text-[11px] text-muted uppercase tracking-wide font-medium">Курс валюты</p>
                <p class="text-sm font-bold font-data text-ink dark:text-white mt-1">
                    1 USD = {{ settings.tmt_rate ?? 19.50 }} TMT
                </p>
            </div>
            <div class="py-4 px-6 text-center">
                <p class="text-[11px] text-muted uppercase tracking-wide font-medium">Наценка</p>
                <p class="text-sm font-bold font-data text-ink dark:text-white mt-1">
                    {{ settings.markup ?? 15 }}%
                </p>
            </div>
            <div class="py-4 px-6 text-center">
                <p class="text-[11px] text-muted uppercase tracking-wide font-medium">Название магазина</p>
                <p class="text-sm font-bold text-ink dark:text-white mt-1">
                    {{ settings.market_name ?? 'Tasin Mobil' }}
                </p>
            </div>
        </div>
        <!-- Product drawer -->
        <AppDrawer
            :show="showDrawer"
            type="product"
            :categories="categories"
            @close="showDrawer = false"
        />
    </AppLayout>
</template>
