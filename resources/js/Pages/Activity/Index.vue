<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    logs:    { type: Object, required: true },
    stats:   { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
})

const search  = ref(props.filters.search ?? '')
const region  = ref(props.filters.region ?? '')
const device  = ref(props.filters.device ?? '')
const period  = ref(props.filters.period ?? '')

function applyFilters() {
    router.get(route('activity'), {
        search: search.value,
        region: region.value,
        device: device.value,
        period: period.value,
    }, { preserveState: true, replace: true })
}

let timer = null
watch([search, region, device, period], () => {
    clearTimeout(timer)
    timer = setTimeout(applyFilters, 350)
})

const deviceLabel = { mobile: 'Мобильный', tablet: 'Планшет', desktop: 'ПК' }
const deviceIcon  = {
    mobile:  'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3',
    tablet:  'M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z',
    desktop: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
}

const statCards = [
    { key: 'total',     label: 'Всего записей',    icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', cls: 'bg-blue-500/10 text-blue-500' },
    { key: 'unique',    label: 'Уник. сессии',     icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', cls: 'bg-emerald-500/10 text-emerald-500' },
    { key: 'topRegion', label: 'Топ-регион',       icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z', cls: 'bg-amber-500/10 text-amber-500', isText: true },
]
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Активность пользователей</h1>
                <p class="text-xs text-muted mt-1">Трекинг визитов: кто · какой товар · регион · дата · устройство</p>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div
                v-for="card in statCards"
                :key="card.key"
                class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]"
            >
                <div class="flex items-center gap-4">
                    <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0', card.cls]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[22px] font-bold font-data leading-none text-ink dark:text-white">{{ stats[card.key] ?? '—' }}</p>
                        <p class="text-xs text-muted mt-1">{{ card.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-line dark:border-dline">
                <div class="relative w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Товар, регион…"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 transition-all"
                    />
                </div>
                <select v-model="region" class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">Все регионы</option>
                    <option v-for="r in ['Aşgabat','Ahal','Mary','Lebap','Daşoguz','Balkan']" :key="r" :value="r">{{ r }}</option>
                </select>
                <select v-model="device" class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">Все устройства</option>
                    <option value="mobile">Мобильный</option>
                    <option value="tablet">Планшет</option>
                    <option value="desktop">ПК</option>
                </select>
                <select v-model="period" class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">Всё время</option>
                    <option value="today">Сегодня</option>
                    <option value="7days">7 дней</option>
                    <option value="30days">30 дней</option>
                </select>
                <span class="ml-auto text-sm text-muted font-medium">{{ logs.total }} записей</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line dark:border-dline">
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-12">#</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Сессия</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Товар</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Регион</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Дата и время</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Устройство</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-dline">
                        <tr
                            v-for="(log, i) in logs.data"
                            :key="log.id ?? i"
                            class="hover:bg-surface dark:hover:bg-dline/30 transition-colors"
                        >
                            <td class="px-5 py-3 text-xs text-muted font-data">{{ (logs.current_page - 1) * logs.per_page + i + 1 }}</td>
                            <td class="px-5 py-3">
                                <code class="text-xs font-mono bg-surface dark:bg-dbg px-2 py-0.5 rounded-[6px] text-blue-600 dark:text-blue-400">{{ log.session }}</code>
                            </td>
                            <td class="px-5 py-3 text-ink dark:text-white text-xs">{{ log.product?.name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-ink dark:text-white">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ log.region }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-muted font-data">{{ log.created_at ? new Date(log.created_at).toLocaleString('ru') : '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-muted flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="deviceIcon[log.device] ?? deviceIcon.desktop"/>
                                    </svg>
                                    <span class="text-xs text-ink dark:text-white">{{ deviceLabel[log.device] ?? log.device }}</span>
                                    <span v-if="log.brand" class="text-xs text-muted">· {{ log.brand }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!logs.data?.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-muted">Данных нет</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="flex items-center gap-1 px-5 py-4 border-t border-line dark:border-dline">
                <component
                    v-for="link in logs.links"
                    :key="link.label"
                    :is="link.url ? 'a' : 'span'"
                    :href="link.url ?? undefined"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1.5 text-xs rounded-btn font-medium transition-colors',
                        link.active
                            ? 'bg-blue-500 text-white'
                            : link.url
                                ? 'text-muted hover:text-ink dark:hover:text-white hover:bg-surface dark:hover:bg-dline'
                                : 'text-muted/40 cursor-default',
                    ]"
                />
            </div>
        </div>
    </AppLayout>
</template>
