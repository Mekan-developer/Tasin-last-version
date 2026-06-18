<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppDrawer from '@/Components/AppDrawer.vue'
import DeleteModal from '@/Components/DeleteModal.vue'
import { useToastStore } from '@/stores/useToastStore'
import axios from 'axios'

const props = defineProps({
    products:   { type: Object, required: true },
    categories: { type: Array,  default: () => [] },
    filters:    { type: Object, default: () => ({}) },
    tmtRate:    { type: Number, default: 19.50 },
})

const toast   = useToastStore()
const activeTab = ref('list')

// ── Filters ───────────────────────────────────────────────────────────────
const search     = ref(props.filters.search ?? '')
const catFilter  = ref(props.filters.category_id ?? '')
const activeF    = ref(props.filters.is_active ?? '')
const newF       = ref(props.filters.is_new ?? '')

const rows = ref(props.products.data.map(p => ({ ...p })))
watch(() => props.products.data, data => { rows.value = data.map(p => ({ ...p })) })

function applyFilters() {
    router.get(route('products.index'), {
        search:      search.value,
        category_id: catFilter.value,
        is_active:   activeF.value,
        is_new:      newF.value,
    }, { preserveState: true, replace: true })
}

let timer = null
watch([search, catFilter, activeF, newF], () => {
    clearTimeout(timer)
    timer = setTimeout(applyFilters, 350)
})

// ── Price helper ──────────────────────────────────────────────────────────
function usd(p) { return p.currency === 'USD' ? parseFloat(p.price) : parseFloat(p.price) / props.tmtRate }
function tmt(p) { return p.currency === 'TMT' ? parseFloat(p.price) : parseFloat(p.price) * props.tmtRate }

function fmt(val, cur) {
    return cur === 'USD'
        ? '$' + val.toFixed(2)
        : val.toFixed(0) + ' TMT'
}

// ── Drawer ────────────────────────────────────────────────────────────────
const showDrawer = ref(false)
const editRecord = ref(null)

function openDrawer(record = null) {
    editRecord.value = record
    showDrawer.value = true
}

// ── Delete ────────────────────────────────────────────────────────────────
const showDelete   = ref(false)
const deleteTarget = ref(null)
const deleting     = ref(false)

function confirmDelete(record) {
    deleteTarget.value = record
    showDelete.value = true
}

function deleteItem() {
    if (!deleteTarget.value) return
    deleting.value = true
    router.delete(route('products.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Товар удалён.')
            showDelete.value = false
            deleteTarget.value = null
        },
        onFinish: () => { deleting.value = false },
    })
}

// ── Inline toggle ─────────────────────────────────────────────────────────
function toggle(row) {
    const old = row.is_active
    row.is_active = !row.is_active
    router.patch(route('products.toggle', row.id), {}, {
        preserveScroll: true,
        onError: () => { row.is_active = old },
    })
}

// ── Palette ───────────────────────────────────────────────────────────────
const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6','#6366f1','#a855f7']
function thumbBg(i) { return palette[i % palette.length] }

// ── Excel bulk ────────────────────────────────────────────────────────────
let uidSeq = 1
function emptyRow() {
    return { uid: uidSeq++, name: '', category_id: '', price: '', currency: 'USD',
             is_new: false, is_active: true, imageFile: null, imagePreview: null,
             variantsOpen: false, variants: [] }
}

const bulkRows = ref(Array.from({ length: 5 }, () => emptyRow()))

function addBulkRows(n) {
    for (let i = 0; i < n; i++) bulkRows.value.push(emptyRow())
}

function deleteRow(uid) {
    bulkRows.value = bulkRows.value.filter(r => r.uid !== uid)
    if (!bulkRows.value.length) bulkRows.value.push(emptyRow())
}

function clearBulk() {
    bulkRows.value = Array.from({ length: 5 }, () => emptyRow())
    toast.success('Таблица очищена')
}

async function processImage(file) {
    const MAX_SIZE = 250 * 1024
    return new Promise((resolve) => {
        const objUrl = URL.createObjectURL(file)
        const img = new Image()
        img.onload = () => {
            URL.revokeObjectURL(objUrl)
            let w = img.naturalWidth, h = img.naturalHeight
            const needsResize = w > 700 || h > 600
            if (!needsResize && file.size <= MAX_SIZE) {
                resolve({ file, preview: URL.createObjectURL(file) }); return
            }
            if (w > 700) { h = Math.round(h * 600 / w); w = 600 }
            if (h > 600) { w = Math.round(w * 500 / h); h = 500 }
            const cv = document.createElement('canvas')
            cv.width = w; cv.height = h
            cv.getContext('2d').drawImage(img, 0, 0, w, h)
            const name = file.name.replace(/\.[^.]+$/, '.webp')
            cv.toBlob(blob => {
                resolve({ file: new File([blob], name, { type: 'image/webp' }), preview: URL.createObjectURL(blob) })
            }, 'image/webp', 0.85)
        }
        img.src = objUrl
    })
}

function triggerFilePick(uid) {
    document.getElementById('xlsfile' + uid)?.click()
}

async function onPickImage(row, e) {
    const file = e.target.files[0]; if (!file) return
    const { file: out, preview } = await processImage(file)
    if (row.imagePreview?.startsWith('blob:')) URL.revokeObjectURL(row.imagePreview)
    row.imageFile = out; row.imagePreview = preview
    e.target.value = ''
}

const bulkSaving = ref(false)

function saveBulk() {
    const valid = bulkRows.value.filter(r => r.name.trim() && r.category_id && r.price)
    if (!valid.length) { toast.error('Заполните хотя бы одну строку: название, категория и цена'); return }
    bulkSaving.value = true
    const fd = new FormData()
    valid.forEach((r, i) => {
        fd.append(`products[${i}][name]`, r.name.trim())
        fd.append(`products[${i}][category_id]`, r.category_id)
        fd.append(`products[${i}][price]`, r.price)
        fd.append(`products[${i}][currency]`, r.currency)
        fd.append(`products[${i}][is_new]`, r.is_new ? '1' : '0')
        fd.append(`products[${i}][is_active]`, r.is_active ? '1' : '0')
        if (r.imageFile) fd.append(`products[${i}][image]`, r.imageFile)
        r.variants.filter(v => v.name.trim()).forEach((v, vi) => {
            fd.append(`products[${i}][variants][${vi}][name]`, v.name.trim())
            fd.append(`products[${i}][variants][${vi}][price]`, v.price)
            fd.append(`products[${i}][variants][${vi}][currency]`, v.currency)
        })
    })
    router.post(route('products.bulkStore'), fd, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${valid.length} товаров успешно добавлено!`)
            bulkRows.value = bulkRows.value.filter(r => !r.name.trim() && !r.category_id && !r.price)
            if (!bulkRows.value.length) bulkRows.value = Array.from({ length: 5 }, () => emptyRow())
            setTimeout(() => { activeTab.value = 'list' }, 900)
        },
        onError: () => toast.error('Проверьте поля с ошибками'),
        onFinish: () => { bulkSaving.value = false },
    })
}

// ── Counts ────────────────────────────────────────────────────────────────
const totalShown = computed(() => props.products.total)

// ── Input CSS ─────────────────────────────────────────────────────────────
const inp = 'w-full px-2 py-1.5 text-xs bg-white dark:bg-dbg border border-line dark:border-dline rounded-[8px] text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 transition-all'
const sel = inp + ' cursor-pointer'

// ── Sort tab ──────────────────────────────────────────────────────────────
const sortCategoryId = ref('')
const sortItems      = ref([])
const sortLoading    = ref(false)
const sortSaving     = ref(false)
const dragSrcIdx     = ref(null)

watch(sortCategoryId, async (id) => {
    sortItems.value = []
    if (!id) return
    sortLoading.value = true
    try {
        const res = await axios.get(route('products.sortData'), { params: { category_id: id } })
        sortItems.value = res.data
    } catch {
        toast.error('Ошибка загрузки товаров')
    } finally {
        sortLoading.value = false
    }
})

function onDragStart(e, idx) {
    dragSrcIdx.value = idx
    e.dataTransfer.effectAllowed = 'move'
}

function onDragOver(e, idx) {
    e.preventDefault()
    e.dataTransfer.dropEffect = 'move'
    if (dragSrcIdx.value === null || dragSrcIdx.value === idx) return
    const arr  = [...sortItems.value]
    const item = arr.splice(dragSrcIdx.value, 1)[0]
    arr.splice(idx, 0, item)
    dragSrcIdx.value  = idx
    sortItems.value   = arr
}

function onDragEnd() { dragSrcIdx.value = null }

async function saveSort() {
    if (!sortItems.value.length) return
    sortSaving.value = true
    try {
        await axios.post(route('products.reorder'), {
            items: sortItems.value.map((p, i) => ({ id: p.id, order: i + 1 })),
        })
        toast.success('Порядок сохранён.')
    } catch {
        toast.error('Ошибка при сохранении.')
    } finally {
        sortSaving.value = false
    }
}
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Товары</h1>
                <p class="text-xs text-muted mt-1">Управление каталогом товаров</p>
            </div>
            <button
                @click="openDrawer(null)"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-btn shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Добавить товар
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mb-4 p-1 bg-surface dark:bg-dcard rounded-card w-fit border border-line dark:border-dline">
            <button
                v-for="t in [{ key: 'list', label: 'Список товаров' }, { key: 'sort', label: 'Сортировка' }, { key: 'excel', label: 'Добавить (Excel)' }]"
                :key="t.key"
                @click="activeTab = t.key"
                :class="[
                    'px-4 py-2 text-sm font-semibold rounded-[10px] transition-colors',
                    activeTab === t.key
                        ? 'bg-white dark:bg-dbg text-ink dark:text-white shadow-sm'
                        : 'text-muted hover:text-ink dark:hover:text-white',
                ]"
            >
                {{ t.label }}
            </button>
        </div>

        <!-- ── LIST TAB ── -->
        <div v-if="activeTab === 'list'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-line dark:border-dline">
                <div class="relative w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск товаров…"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 transition-all"
                    />
                </div>
                <select
                    v-model="catFilter"
                    class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer"
                >
                    <option value="">Все категории</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select
                    v-model="activeF"
                    class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer"
                >
                    <option value="">Все статусы</option>
                    <option value="1">Активные</option>
                    <option value="0">Неактивные</option>
                </select>
                <select
                    v-model="newF"
                    class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer"
                >
                    <option value="">Все типы</option>
                    <option value="1">Новинки</option>
                    <option value="0">Обычные</option>
                </select>
                <span class="ml-auto text-sm text-muted font-medium">{{ totalShown }} товаров</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line dark:border-dline">
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-12">#</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Товар</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Цена</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Тип</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Активен</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Просмотры</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-dline">
                        <tr
                            v-for="(product, i) in rows"
                            :key="product.id"
                            @dblclick="openDrawer(product)"
                            title="Двойной клик — редактировать"
                            class="hover:bg-surface dark:hover:bg-dline/30 transition-colors cursor-pointer select-none"
                        >
                            <td class="px-5 py-3.5 text-xs text-muted font-data">{{ product.id }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden"
                                        :style="product.image_urls?.length ? {} : { background: thumbBg(i) }"
                                    >
                                        <img v-if="product.image_urls?.length" :src="product.image_urls[product.active_image ?? 0] ?? product.image_urls[0]" class="w-full h-full object-cover" :alt="product.name" />
                                        <template v-else>{{ product.name.charAt(0).toUpperCase() }}</template>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-ink dark:text-white leading-tight">{{ product.name }}</p>
                                        <p class="text-[11px] text-muted mt-0.5">{{ product.category?.name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <p class="font-semibold text-ink dark:text-white font-data">{{ fmt(usd(product), 'USD') }}</p>
                                <p class="text-[11px] text-muted font-data">{{ fmt(tmt(product), 'TMT') }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span :class="[
                                    'inline-block text-[11px] font-bold px-2 py-0.5 rounded-[6px]',
                                    product.is_new
                                        ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                        : 'bg-gray-500/10 text-muted'
                                ]">
                                    {{ product.is_new ? 'Новинка' : 'Обычный' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button
                                    type="button"
                                    @click="toggle(product)"
                                    @dblclick.stop
                                    :class="product.is_active
                                        ? 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-blue-500 transition-colors'
                                        : 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors'"
                                >
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', product.is_active ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </td>
                            <td class="px-5 py-3.5 text-center text-xs text-muted font-data">{{ product.views }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1" @dblclick.stop>
                                    <button
                                        @click="openDrawer(product)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete(product)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Товаров не найдено</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="flex items-center gap-1 px-5 py-4 border-t border-line dark:border-dline">
                <component
                    v-for="link in products.links"
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

        <!-- ── SORT TAB ── -->
        <div v-if="activeTab === 'sort'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-line dark:border-dline">
                <select
                    v-model="sortCategoryId"
                    class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer min-w-[200px]"
                >
                    <option value="">— Выберите категорию —</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <button
                    v-if="sortItems.length"
                    @click="saveSort"
                    :disabled="sortSaving"
                    class="flex items-center gap-1.5 px-4 py-2 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors"
                >
                    <svg v-if="sortSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ sortSaving ? 'Сохранение…' : 'Сохранить порядок' }}
                </button>
                <span v-if="sortItems.length" class="ml-auto text-xs text-muted">{{ sortItems.length }} товаров</span>
            </div>

            <!-- Loading -->
            <div v-if="sortLoading" class="flex items-center justify-center py-16">
                <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>

            <!-- Empty state -->
            <div v-else-if="!sortCategoryId" class="flex flex-col items-center justify-center py-16 gap-3 text-muted">
                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"/>
                </svg>
                <p class="text-sm">Выберите категорию, чтобы упорядочить товары</p>
            </div>
            <div v-else-if="!sortLoading && !sortItems.length" class="py-16 text-center text-sm text-muted">
                В этой категории нет товаров
            </div>

            <!-- Drag list -->
            <div v-else class="p-5">
                <p class="text-xs text-muted mb-4">Перетащите товары, чтобы изменить порядок отображения</p>
                <div class="flex flex-col gap-2">
                    <div
                        v-for="(item, idx) in sortItems"
                        :key="item.id"
                        draggable="true"
                        @dragstart="onDragStart($event, idx)"
                        @dragover="onDragOver($event, idx)"
                        @dragend="onDragEnd"
                        :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-[10px] border cursor-grab active:cursor-grabbing select-none transition-all',
                            dragSrcIdx === idx
                                ? 'opacity-40 border-blue-400 bg-blue-50 dark:bg-blue-900/20'
                                : 'border-line dark:border-dline bg-surface dark:bg-dbg hover:border-blue-400 hover:shadow-sm',
                        ]"
                    >
                        <!-- Drag handle -->
                        <svg class="w-4 h-4 text-muted flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 9.75h16.5M3.75 14.25h16.5M3.75 18.75h16.5"/>
                        </svg>

                        <!-- Position -->
                        <span class="w-6 text-center text-xs font-bold text-muted font-data flex-shrink-0">{{ idx + 1 }}</span>

                        <!-- Thumb -->
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden"
                            :style="item.image_urls?.length ? {} : { background: thumbBg(idx) }"
                        >
                            <img v-if="item.image_urls?.length" :src="item.image_urls[0]" class="w-full h-full object-cover" :alt="item.name" />
                            <template v-else>{{ item.name.charAt(0).toUpperCase() }}</template>
                        </div>

                        <!-- Name -->
                        <span class="flex-1 text-sm font-semibold text-ink dark:text-white">{{ item.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── EXCEL TAB ── -->
        <div v-if="activeTab === 'excel'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-2 px-5 py-4 border-b border-line dark:border-dline">
                <button
                    @click="saveBulk"
                    :disabled="bulkSaving"
                    class="flex items-center gap-1.5 px-4 py-2 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-xs font-semibold rounded-btn transition-colors"
                >
                    <svg v-if="bulkSaving" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ bulkSaving ? 'Сохранение…' : 'Сохранить всё' }}
                </button>
                <button @click="addBulkRows(5)" class="px-3 py-2 text-xs font-semibold border border-line dark:border-dline rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors text-ink dark:text-white">+5 строк</button>
                <button @click="addBulkRows(10)" class="px-3 py-2 text-xs font-semibold border border-line dark:border-dline rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors text-ink dark:text-white">+10 строк</button>
                <button @click="clearBulk" class="px-3 py-2 text-xs font-semibold border border-red-200 dark:border-red-900/50 rounded-btn hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-500">Очистить</button>
                <span class="ml-auto text-xs text-muted">{{ bulkRows.length }} строк</span>
            </div>

            <!-- Grid -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-line dark:border-dline bg-surface dark:bg-dbg">
                            <th class="px-3 py-2 text-left font-bold text-muted uppercase tracking-wide w-8">#</th>
                            <th class="px-3 py-2 text-center font-bold text-muted uppercase tracking-wide w-14">Фото</th>
                            <th class="px-3 py-2 text-left font-bold text-muted uppercase tracking-wide min-w-[180px]">Название *</th>
                            <th class="px-3 py-2 text-left font-bold text-muted uppercase tracking-wide min-w-[150px]">Категория *</th>
                            <th class="px-3 py-2 text-left font-bold text-muted uppercase tracking-wide w-28">Цена *</th>
                            <th class="px-3 py-2 text-left font-bold text-muted uppercase tracking-wide w-20">Валюта</th>
                            <th class="px-3 py-2 text-center font-bold text-muted uppercase tracking-wide w-20">Новинка</th>
                            <th class="px-3 py-2 text-center font-bold text-muted uppercase tracking-wide w-20">Активен</th>
                            <th class="px-3 py-2 text-center font-bold text-muted uppercase tracking-wide w-24">Варианты</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-dline">
                        <template v-for="(row, idx) in bulkRows" :key="row.uid">
                            <tr class="hover:bg-surface/50 dark:hover:bg-dline/20">
                                <td class="px-3 py-2 text-center text-muted">{{ idx + 1 }}</td>
                                <!-- Photo -->
                                <td class="px-2 py-1.5 text-center">
                                    <button type="button" class="xls-imgph" :class="{ 'has-img': row.imagePreview }"
                                            @click="triggerFilePick(row.uid)" title="Загрузить фото">
                                        <img v-if="row.imagePreview" :src="row.imagePreview" alt="">
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </button>
                                    <input :id="'xlsfile' + row.uid" type="file" accept="image/*" class="hidden" @change="onPickImage(row, $event)">
                                </td>
                                <!-- Name -->
                                <td class="px-2 py-1.5">
                                    <input v-model="row.name" type="text" :class="inp" placeholder="Название товара…">
                                </td>
                                <!-- Category -->
                                <td class="px-2 py-1.5">
                                    <select v-model="row.category_id" :class="sel">
                                        <option value="">— выбрать —</option>
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </td>
                                <!-- Price -->
                                <td class="px-2 py-1.5">
                                    <input v-model="row.price" type="number" min="0" step="0.01" :class="inp" placeholder="0.00">
                                </td>
                                <!-- Currency -->
                                <td class="px-2 py-1.5">
                                    <select v-model="row.currency" :class="sel">
                                        <option value="USD">USD $</option>
                                        <option value="TMT">TMT</option>
                                    </select>
                                </td>
                                <!-- IsNew -->
                                <td class="px-2 py-1.5 text-center">
                                    <input v-model="row.is_new" type="checkbox" class="w-4 h-4 rounded accent-blue-500 cursor-pointer">
                                </td>
                                <!-- IsActive -->
                                <td class="px-2 py-1.5 text-center">
                                    <input v-model="row.is_active" type="checkbox" class="w-4 h-4 rounded accent-blue-500 cursor-pointer">
                                </td>
                                <!-- Variants toggle -->
                                <td class="px-2 py-1.5 text-center">
                                    <button type="button" class="xls-varsbtn" :class="{ 'has-vars': row.variants.length }"
                                            @click="row.variantsOpen = !row.variantsOpen">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 11 12 14 15 11"/>
                                        </svg>
                                        {{ row.variants.length ? row.variants.length + ' вар.' : '+ вар.' }}
                                    </button>
                                </td>
                                <!-- Delete row -->
                                <td class="px-2 py-1.5 text-center">
                                    <button type="button" @click="deleteRow(row.uid)"
                                            class="text-muted hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <!-- Variant sub-rows -->
                            <template v-if="row.variantsOpen">
                                <tr v-for="(v, vi) in row.variants" :key="row.uid + '-v-' + vi" class="xls-vrow">
                                    <td class="xls-vnum">↳{{ vi + 1 }}</td>
                                    <td></td>
                                    <td class="xls-vlead">
                                        <input class="xls-inp xls-vinp" v-model="v.name" placeholder="Модель (напр. S21 Ultra)">
                                    </td>
                                    <td colspan="2" class="px-2 py-1">
                                        <input :class="inp" type="number" min="0" step="0.01" v-model="v.price" placeholder="0.00">
                                    </td>
                                    <td class="px-2 py-1">
                                        <select :class="sel" v-model="v.currency">
                                            <option value="USD">USD $</option>
                                            <option value="TMT">TMT</option>
                                        </select>
                                    </td>
                                    <td></td><td></td><td></td>
                                    <td class="px-2 py-1 text-center">
                                        <button type="button" @click="row.variants.splice(vi, 1)"
                                                class="text-muted hover:text-red-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="xls-vrow">
                                    <td class="xls-vnum"></td>
                                    <td colspan="9" class="px-3 py-1.5">
                                        <button type="button" class="xls-varsbtn"
                                                @click="row.variants.push({ name: '', price: '', currency: 'USD' })">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                            + Добавить вариант
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Bottom actions -->
            <div class="flex items-center gap-2 px-5 py-4 border-t border-line dark:border-dline">
                <button @click="bulkRows.push(emptyRow())" class="text-xs font-semibold text-blue-500 hover:text-blue-600 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Добавить строку
                </button>
                <span class="text-muted text-xs mx-2">или</span>
                <button @click="addBulkRows(3)" class="text-xs text-muted hover:text-ink dark:hover:text-white transition-colors">+3</button>
                <button @click="addBulkRows(5)" class="text-xs text-muted hover:text-ink dark:hover:text-white transition-colors">+5</button>
                <button @click="addBulkRows(10)" class="text-xs text-muted hover:text-ink dark:hover:text-white transition-colors">+10</button>
            </div>
        </div>

        <!-- Drawer -->
        <AppDrawer
            :show="showDrawer"
            type="product"
            :record="editRecord"
            :categories="categories"
            @close="showDrawer = false"
        />

        <!-- Delete modal -->
        <DeleteModal
            :show="showDelete"
            :name="deleteTarget?.name ?? ''"
            :loading="deleting"
            @confirm="deleteItem"
            @close="showDelete = false"
        />
    </AppLayout>
</template>

<style scoped>
.xls-imgph {
    width: 50px; height: 50px;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    display: grid; place-items: center;
    cursor: pointer; background: #f8fafc;
    overflow: hidden; padding: 0; margin: 0 auto;
    transition: border-color .15s, background .15s;
}
.xls-imgph:hover { border-color: #3b82f6; background: rgba(59,130,246,.05); }
.xls-imgph svg  { width: 18px; height: 18px; color: #94a3b8; }
.xls-imgph img  { width: 100%; height: 100%; object-fit: cover; }
.xls-imgph.has-img { border-style: solid; border-color: #3b82f6; }

.xls-varsbtn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 10px; border-radius: 7px;
    border: 1.5px solid #e2e8f0; background: #ffffff;
    color: #94a3b8; font-size: 12px; font-weight: 700;
    cursor: pointer; white-space: nowrap;
    transition: border-color .15s, color .15s, background .15s;
}
.xls-varsbtn:hover { border-color: #8b5cf6; color: #8b5cf6; }
.xls-varsbtn.has-vars { background: rgba(139,92,246,.1); border-color: rgba(139,92,246,.4); color: #8b5cf6; }
.xls-varsbtn svg { width: 14px; height: 14px; }

.xls-vrow > td { background: rgba(139,92,246,.05); }
.xls-vnum { color: #8b5cf6; font-size: 10px; background: rgba(139,92,246,.07) !important; padding: 4px 12px; text-align: center; }
.xls-vlead { border-left: 3px solid rgba(139,92,246,.35); padding: 4px 8px; }
.xls-inp { width: 100%; padding: 4px 8px; font-size: 12px; background: transparent; border: 1px solid #e2e8f0; border-radius: 6px; outline: none; color: inherit; }
.xls-inp:focus { border-color: #3b82f6; }
.xls-vinp { color: #8b5cf6; font-weight: 700; }

.dark .xls-imgph { border-color: #334155; background: #1e293b; }
.dark .xls-imgph:hover { border-color: #3b82f6; background: rgba(59,130,246,.08); }
.dark .xls-varsbtn { border-color: #334155; background: #1e293b; color: #64748b; }
.dark .xls-varsbtn:hover { border-color: #8b5cf6; color: #8b5cf6; }
.dark .xls-varsbtn.has-vars { background: rgba(139,92,246,.15); border-color: rgba(139,92,246,.4); }
.dark .xls-vrow > td { background: rgba(139,92,246,.08); }
.dark .xls-vnum { background: rgba(139,92,246,.12) !important; }
.dark .xls-inp { border-color: #334155; }
.dark .xls-inp:focus { border-color: #3b82f6; }
</style>
