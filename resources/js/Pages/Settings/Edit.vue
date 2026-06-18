<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
})

const toast = useToastStore()
const activeTab = ref('general')

// ── Forms (one per tab) ───────────────────────────────────────────────────
const general = useForm({
    market_name: props.settings.market_name ?? 'Tasin Mobil',
    phone:       props.settings.phone       ?? '+993 12 34-56-78',
    address:     props.settings.address     ?? 'Aşgabat şäheri, Bitarap Türkmenistan şaýoly 80',
})

const pricing = useForm({
    markup:           props.settings.markup           ?? 15,
    default_currency: props.settings.default_currency ?? 'USD',
    dual_currency:    props.settings.dual_currency === '1' || props.settings.dual_currency === true,
    auto_tmt:         props.settings.auto_tmt         === '1' || props.settings.auto_tmt === true,
    round_tmt:        props.settings.round_tmt        === '1' || props.settings.round_tmt === true,
})

const currency = useForm({
    tmt_rate: props.settings.tmt_rate ?? 19.50,
})

const display = useForm({
    show_views:     props.settings.show_views     === '1' || props.settings.show_views === true,
    show_new_badge: props.settings.show_new_badge === '1' || props.settings.show_new_badge === true,
    show_cat_icons: props.settings.show_cat_icons === '1' || props.settings.show_cat_icons === true,
    enable_slider:  props.settings.enable_slider  === '1' || props.settings.enable_slider === true,
    show_inactive:  props.settings.show_inactive  === '1' || props.settings.show_inactive === true,
})

function save(form, tab) {
    form.put(route('settings.update', tab), {
        preserveScroll: true,
        onSuccess: () => toast.success('Настройки сохранены.'),
    })
}

// ── Computed preview ──────────────────────────────────────────────────────
function previewTmt(usd) { return (usd * (parseFloat(currency.tmt_rate) || 19.5)).toFixed(0) }

// ── Helpers ───────────────────────────────────────────────────────────────
function togClass(val) {
    return val
        ? 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-blue-500 transition-colors'
        : 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors'
}

const inp = 'w-full px-3 py-2 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all'
const sel = inp + ' cursor-pointer'

const tabs = [
    { key: 'general',  label: 'Основное' },
    { key: 'pricing',  label: 'Ценообразование' },
    { key: 'currency', label: 'Валюта' },
    { key: 'display',  label: 'Отображение' },
]
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Настройки</h1>
            <p class="text-xs text-muted mt-1">Конфигурация магазина Tasin Mobil</p>
        </div>

        <div class="flex gap-6">
            <!-- Left nav -->
            <div class="w-48 flex-shrink-0">
                <nav class="space-y-1 bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-2 shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                    <button
                        v-for="t in tabs"
                        :key="t.key"
                        @click="activeTab = t.key"
                        :class="[
                            'w-full text-left px-3 py-2.5 text-sm font-semibold rounded-[10px] transition-colors',
                            activeTab === t.key
                                ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                : 'text-muted hover:text-ink dark:hover:text-white hover:bg-surface dark:hover:bg-dline',
                        ]"
                    >{{ t.label }}</button>
                </nav>
            </div>

            <!-- Right panel -->
            <div class="flex-1">

                <!-- ── GENERAL ── -->
                <div v-if="activeTab === 'general'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-6 shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                    <h2 class="text-base font-bold text-ink dark:text-white mb-5">Основные настройки</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Название магазина</label>
                            <input v-model="general.market_name" type="text" :class="inp" />
                            <p v-if="general.errors.market_name" class="text-xs text-red-500 mt-1">{{ general.errors.market_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Контактный телефон</label>
                            <input v-model="general.phone" type="text" :class="inp" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Адрес</label>
                            <textarea v-model="general.address" rows="2" :class="inp" />
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="save(general, 'general')" :disabled="general.processing" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors">
                            {{ general.processing ? 'Сохранение…' : 'Save Changes' }}
                        </button>
                        <button @click="general.reset()" class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors">Cancel</button>
                    </div>
                </div>

                <!-- ── PRICING ── -->
                <div v-if="activeTab === 'pricing'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-6 shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                    <h2 class="text-base font-bold text-ink dark:text-white mb-5">Ценообразование</h2>
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Наценка %</label>
                                <input v-model.number="pricing.markup" type="number" min="0" max="100" step="0.5" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Валюта по умолчанию</label>
                                <select v-model="pricing.default_currency" :class="sel">
                                    <option value="USD">USD</option>
                                    <option value="TMT">TMT</option>
                                </select>
                            </div>
                        </div>
                        <div class="rounded-xl border border-line dark:border-dline divide-y divide-line dark:divide-dline">
                            <div v-for="item in [
                                { key: 'dual_currency', label: 'Показывать цены в двух валютах' },
                                { key: 'auto_tmt',      label: 'Автоматически рассчитывать TMT' },
                                { key: 'round_tmt',     label: 'Округлять цены TMT' },
                            ]" :key="item.key" class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">{{ item.label }}</span>
                                <button type="button" @click="pricing[item.key] = !pricing[item.key]" :class="togClass(pricing[item.key])">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', pricing[item.key] ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="save(pricing, 'pricing')" :disabled="pricing.processing" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors">
                            {{ pricing.processing ? 'Сохранение…' : 'Save Changes' }}
                        </button>
                        <button @click="pricing.reset()" class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors">Cancel</button>
                    </div>
                </div>

                <!-- ── CURRENCY ── -->
                <div v-if="activeTab === 'currency'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-6 shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                    <h2 class="text-base font-bold text-ink dark:text-white mb-5">Курсы валют</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Курс USD</label>
                                <input value="1" type="number" :class="inp" disabled />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Курс TMT за 1 USD</label>
                                <input v-model.number="currency.tmt_rate" type="number" min="0.01" step="0.01" :class="inp" />
                                <p v-if="currency.errors.tmt_rate" class="text-xs text-red-500 mt-1">{{ currency.errors.tmt_rate }}</p>
                            </div>
                        </div>
                        <div class="rounded-xl bg-surface dark:bg-dbg border border-line dark:border-dline p-4">
                            <p class="text-xs text-muted mb-1">Пример конвертации</p>
                            <p class="text-sm font-bold text-ink dark:text-white font-data">
                                $1,000 = {{ previewTmt(1000) }} TMT
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="save(currency, 'currency')" :disabled="currency.processing" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors">
                            {{ currency.processing ? 'Сохранение…' : 'Сохранить' }}
                        </button>
                        <button @click="currency.reset()" class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors">Отмена</button>
                    </div>
                </div>

                <!-- ── DISPLAY ── -->
                <div v-if="activeTab === 'display'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-6 shadow-[0_1px_8px_rgba(0,0,0,.06)]">
                    <h2 class="text-base font-bold text-ink dark:text-white mb-5">Отображение витрины</h2>
                    <div class="rounded-xl border border-line dark:border-dline divide-y divide-line dark:divide-dline">
                        <div v-for="item in [
                            { key: 'show_views',     label: 'Счётчик просмотров' },
                            { key: 'show_new_badge', label: 'Бейдж «НОВИНКА»' },
                            { key: 'show_cat_icons', label: 'Иконки категорий' },
                            { key: 'enable_slider',  label: 'Слайдер на главной' },
                            { key: 'show_inactive',  label: 'Показывать неактивные товары' },
                        ]" :key="item.key" class="flex items-center justify-between px-4 py-3.5">
                            <span class="text-sm text-ink dark:text-white">{{ item.label }}</span>
                            <button type="button" @click="display[item.key] = !display[item.key]" :class="togClass(display[item.key])">
                                <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', display[item.key] ? 'translate-x-5' : 'translate-x-1']" />
                            </button>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="save(display, 'display')" :disabled="display.processing" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors">
                            {{ display.processing ? 'Сохранение…' : 'Save Changes' }}
                        </button>
                        <button @click="display.reset()" class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors">Cancel</button>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
