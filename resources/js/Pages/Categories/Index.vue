<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppDrawer from '@/Components/AppDrawer.vue'
import DeleteModal from '@/Components/DeleteModal.vue'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    categories: { type: Object, required: true },
    filters:    { type: Object, default: () => ({}) },
})

const toast   = useToastStore()
const search  = ref(props.filters.search ?? '')
const rows    = ref(props.categories.data.map(c => ({ ...c })))

watch(() => props.categories.data, data => {
    rows.value = data.map(c => ({ ...c }))
})

// ── Search (debounced) ────────────────────────────────────────────────────
let searchTimer = null
watch(search, val => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get(route('categories.index'), { search: val }, { preserveState: true, replace: true })
    }, 350)
})

// ── Drawer ────────────────────────────────────────────────────────────────
const showDrawer = ref(false)
const editRecord = ref(null)

function openDrawer(record = null) {
    editRecord.value = record
    showDrawer.value = true
}

// ── Delete ────────────────────────────────────────────────────────────────
const showDelete  = ref(false)
const deleteTarget = ref(null)
const deleting    = ref(false)

function confirmDelete(record) {
    deleteTarget.value = record
    showDelete.value = true
}

function deleteItem() {
    if (!deleteTarget.value) return
    deleting.value = true
    router.delete(route('categories.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Категория удалена.')
            showDelete.value = false
            deleteTarget.value = null
        },
        onFinish: () => { deleting.value = false },
    })
}

// ── Inline toggle (optimistic) ─────────────────────────────────────────
function toggle(row, field) {
    const old = row[field]
    row[field] = !row[field]
    router.patch(route('categories.toggle', row.id), { field }, {
        preserveScroll: true,
        onError: () => { row[field] = old },
    })
}

// ── Palette for thumbnails ────────────────────────────────────────────────
const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16']
function thumbBg(i) { return palette[i % palette.length] }
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Категории</h1>
                <p class="text-xs text-muted mt-1">Управление категориями товаров</p>
            </div>
            <button
                @click="openDrawer(null)"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-btn shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Добавить категорию
            </button>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Filter bar -->
            <div class="flex items-center gap-3 px-5 py-4 border-b border-line dark:border-dline">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск категорий…"
                        class="w-full pl-9 pr-4 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all"
                    />
                </div>
                <span class="ml-auto text-sm text-muted font-medium">{{ categories.total }} категорий</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line dark:border-dline">
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-12">#</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Категория</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Порядок</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-28">Цена</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-28">Активна</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-28">Просмотры</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-28">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-dline">
                        <tr
                            v-for="(cat, i) in rows"
                            :key="cat.id"
                            @dblclick="openDrawer(cat)"
                            title="Двойной клик — редактировать"
                            class="hover:bg-surface dark:hover:bg-dline/30 transition-colors cursor-pointer select-none"
                        >
                            <td class="px-5 py-3.5 text-xs text-muted font-data">{{ cat.id }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden"
                                        :style="cat.image_url ? {} : { background: thumbBg(i) }"
                                    >
                                        <img v-if="cat.image_url" :src="cat.image_url" class="w-full h-full object-cover" :alt="cat.name" />
                                        <template v-else>{{ cat.name.charAt(0).toUpperCase() }}</template>
                                    </div>
                                    <span class="font-semibold text-ink dark:text-white">{{ cat.name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center font-data text-ink dark:text-white">{{ cat.order }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <button
                                    type="button"
                                    @click="toggle(cat, 'show_price')"
                                    @dblclick.stop
                                    :class="cat.show_price
                                        ? 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-blue-500 transition-colors'
                                        : 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors'"
                                >
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', cat.show_price ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button
                                    type="button"
                                    @click="toggle(cat, 'is_active')"
                                    @dblclick.stop
                                    :class="cat.is_active
                                        ? 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-blue-500 transition-colors'
                                        : 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors'"
                                >
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', cat.is_active ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </td>
                            <td class="px-5 py-3.5 text-center font-data text-muted text-xs">{{ cat.views }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1" @dblclick.stop>
                                    <button
                                        @click="openDrawer(cat)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                        title="Редактировать"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete(cat)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        title="Удалить"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Категорий не найдено</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="categories.last_page > 1" class="flex items-center gap-1 px-5 py-4 border-t border-line dark:border-dline">
                <component
                    v-for="link in categories.links"
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

        <!-- Drawer -->
        <AppDrawer
            :show="showDrawer"
            type="category"
            :record="editRecord"
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
