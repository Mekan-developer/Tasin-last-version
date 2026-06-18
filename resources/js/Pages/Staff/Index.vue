<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppDrawer from '@/Components/AppDrawer.vue'
import DeleteModal from '@/Components/DeleteModal.vue'
import PayModal from '@/Components/PayModal.vue'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    staff:   { type: Object, required: true },
    stats:   { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
})

const toast     = useToastStore()
const activeTab = ref('list')

const search    = ref(props.filters.search ?? '')
const deptFilter = ref(props.filters.department ?? '')
const activeF   = ref(props.filters.is_active ?? '')

const rows = ref(props.staff.data.map(s => ({ ...s })))
watch(() => props.staff.data, data => { rows.value = data.map(s => ({ ...s })) })

let timer = null
watch([search, deptFilter, activeF], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('staff.index'), {
            search:      search.value,
            department:  deptFilter.value,
            is_active:   activeF.value,
        }, { preserveState: true, replace: true })
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
    router.delete(route('staff.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Сотрудник удалён.')
            showDelete.value = false
            deleteTarget.value = null
        },
        onFinish: () => { deleting.value = false },
    })
}

// ── Pay modal ─────────────────────────────────────────────────────────────
const showPay  = ref(false)
const payTarget = ref(null)

function openPay(record) {
    payTarget.value = record
    showPay.value = true
}

// ── Helpers ───────────────────────────────────────────────────────────────
function remaining(s) { return Math.max(0, parseFloat(s.salary) - parseFloat(s.paid)) }
function fmtTmt(v) { return parseFloat(v ?? 0).toFixed(0) + ' TMT' }

const statCards = [
    { label: 'Фонд зарплат',    key: 'fund',      cls: 'bg-blue-500/10 text-blue-500' },
    { label: 'Выплачено',       key: 'paid',      cls: 'bg-emerald-500/10 text-emerald-500' },
    { label: 'Остаток',         key: 'remaining', cls: 'bg-amber-500/10 text-amber-500' },
    { label: 'Суммарный долг',  key: 'debt',      cls: 'bg-red-500/10 text-red-500' },
]

const palette = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4']
function avatarBg(i) { return palette[i % palette.length] }
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Контроль сотрудников</h1>
                <p class="text-xs text-muted mt-1">Зарплаты · Долги · Аналитика персонала</p>
            </div>
            <button
                @click="openDrawer(null)"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-btn shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Добавить сотрудника
            </button>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div
                v-for="card in statCards"
                :key="card.key"
                class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]"
            >
                <p class="text-xs text-muted font-medium">{{ card.label }}</p>
                <p class="text-[22px] font-bold font-data text-ink dark:text-white mt-1">{{ fmtTmt(stats[card.key]) }}</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mb-4 p-1 bg-surface dark:bg-dcard rounded-card w-fit border border-line dark:border-dline">
            <button
                v-for="t in [{ key: 'list', label: 'Сотрудники' }, { key: 'analytics', label: 'Аналитика зарплат' }]"
                :key="t.key"
                @click="activeTab = t.key"
                :class="[
                    'px-4 py-2 text-sm font-semibold rounded-[10px] transition-colors',
                    activeTab === t.key ? 'bg-white dark:bg-dbg text-ink dark:text-white shadow-sm' : 'text-muted hover:text-ink dark:hover:text-white',
                ]"
            >{{ t.label }}</button>
        </div>

        <!-- ── LIST TAB ── -->
        <div v-if="activeTab === 'list'" class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-line dark:border-dline">
                <div class="relative w-48">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Поиск…" class="w-full pl-9 pr-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 transition-all" />
                </div>
                <input v-model="deptFilter" type="text" placeholder="Отдел" class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 transition-all w-36" />
                <select v-model="activeF" class="px-3 py-2 text-sm bg-surface dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer">
                    <option value="">Все</option>
                    <option value="1">Активные</option>
                    <option value="0">Неактивные</option>
                </select>
                <span class="ml-auto text-sm text-muted">{{ staff.total }} сотрудников</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line dark:border-dline">
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-12">#</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Сотрудник</th>
                            <th class="text-left px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Отдел</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Оклад</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Выплачено</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Остаток</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide">Долг</th>
                            <th class="text-center px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-24">Статус</th>
                            <th class="text-right px-5 py-3 text-xs font-bold text-muted uppercase tracking-wide w-28">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line dark:divide-dline">
                        <tr
                            v-for="(s, i) in rows"
                            :key="s.id"
                            class="hover:bg-surface dark:hover:bg-dline/30 transition-colors"
                        >
                            <td class="px-5 py-3 text-xs text-muted font-data">{{ s.id }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                        :style="{ background: avatarBg(i) }"
                                    >
                                        {{ s.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-ink dark:text-white leading-tight">{{ s.name }}</p>
                                        <p class="text-[11px] text-muted">{{ s.position ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-xs text-muted">{{ s.department ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-data text-ink dark:text-white">{{ fmtTmt(s.salary) }}</td>
                            <td class="px-5 py-3 text-right font-data text-emerald-600 dark:text-emerald-400">{{ fmtTmt(s.paid) }}</td>
                            <td class="px-5 py-3 text-right font-data text-amber-600 dark:text-amber-400">{{ fmtTmt(remaining(s)) }}</td>
                            <td class="px-5 py-3 text-right font-data" :class="parseFloat(s.debt) > 0 ? 'text-red-500' : 'text-muted'">{{ fmtTmt(s.debt) }}</td>
                            <td class="px-5 py-3 text-center">
                                <span :class="[
                                    'inline-block text-[11px] font-bold px-2 py-0.5 rounded-[6px]',
                                    s.is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-gray-500/10 text-muted'
                                ]">{{ s.is_active ? 'Активен' : 'Неактивен' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openPay(s)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                        title="Выплата"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDrawer(s)"
                                        class="w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete(s)"
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
                            <td colspan="9" class="px-5 py-10 text-center text-sm text-muted">Сотрудников не найдено</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="staff.last_page > 1" class="flex items-center gap-1 px-5 py-4 border-t border-line dark:border-dline">
                <component
                    v-for="link in staff.links"
                    :key="link.label"
                    :is="link.url ? 'a' : 'span'"
                    :href="link.url ?? undefined"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1.5 text-xs rounded-btn font-medium transition-colors',
                        link.active ? 'bg-blue-500 text-white' : link.url ? 'text-muted hover:text-ink dark:hover:text-white hover:bg-surface dark:hover:bg-dline' : 'text-muted/40 cursor-default',
                    ]"
                />
            </div>
        </div>

        <!-- ── ANALYTICS TAB ── -->
        <div v-if="activeTab === 'analytics'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="(s, i) in staff.data"
                :key="s.id"
                class="bg-white dark:bg-dcard rounded-card p-5 border border-line dark:border-dline shadow-[0_1px_8px_rgba(0,0,0,.06)]"
            >
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
                        :style="{ background: avatarBg(i) }"
                    >{{ s.name.charAt(0).toUpperCase() }}</div>
                    <div>
                        <p class="font-semibold text-ink dark:text-white text-sm leading-tight">{{ s.name }}</p>
                        <p class="text-[11px] text-muted">{{ s.position ?? s.department ?? '—' }}</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-muted">Оклад</span>
                        <span class="font-data font-semibold text-ink dark:text-white">{{ fmtTmt(s.salary) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-muted">Выплачено</span>
                        <span class="font-data text-emerald-600 dark:text-emerald-400">{{ fmtTmt(s.paid) }}</span>
                    </div>
                    <!-- Progress bar -->
                    <div class="h-1.5 bg-surface dark:bg-dbg rounded-full overflow-hidden">
                        <div
                            class="h-full bg-emerald-500 rounded-full transition-all"
                            :style="{ width: Math.min(100, parseFloat(s.salary) > 0 ? (parseFloat(s.paid) / parseFloat(s.salary)) * 100 : 0) + '%' }"
                        />
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-muted">Долг</span>
                        <span class="font-data" :class="parseFloat(s.debt) > 0 ? 'text-red-500' : 'text-muted'">{{ fmtTmt(s.debt) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drawer -->
        <AppDrawer :show="showDrawer" type="staff" :record="editRecord" @close="showDrawer = false" />

        <!-- Delete modal -->
        <DeleteModal :show="showDelete" :name="deleteTarget?.name ?? ''" :loading="deleting" @confirm="deleteItem" @close="showDelete = false" />

        <!-- Pay modal -->
        <PayModal :show="showPay" :staff="payTarget" @close="showPay = false" />
    </AppLayout>
</template>
