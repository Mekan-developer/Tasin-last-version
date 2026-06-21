<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppDrawer from '@/Components/AppDrawer.vue'
import DeleteModal from '@/Components/DeleteModal.vue'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    slides: { type: Array, default: () => [] },
})

const toast = useToastStore()

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
    router.delete(route('slides.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Слайд удалён.')
            showDelete.value = false
            deleteTarget.value = null
        },
        onFinish: () => { deleting.value = false },
    })
}

// ── Banner style helpers ──────────────────────────────────────────────────
function lighten(hex, amount) {
    const clean = hex.replace('#', '')
    const full  = clean.length === 3 ? clean.split('').map(c => c + c).join('') : clean
    const num   = parseInt(full, 16)
    const r = Math.min(255, (num >> 16) + amount)
    const g = Math.min(255, ((num >> 8) & 0xff) + amount)
    const b = Math.min(255, (num & 0xff) + amount)
    return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('')
}

function bannerStyle(s) {
    if (s.image_url) {
        return {
            backgroundImage:    `url(${s.image_url})`,
            backgroundSize:     'cover',
            backgroundPosition: 'center',
        }
    }
    const c1 = s.bg_color ?? '#1e40af'
    const c2 = lighten(c1, 55)
    return { background: `linear-gradient(135deg, ${c1} 0%, ${c2} 100%)` }
}
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-black text-ink dark:text-white leading-tight">Слайды</h1>
                <p class="text-xs text-muted mt-1">Управление баннерами главной страницы</p>
            </div>
            <button
                @click="openDrawer(null)"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-btn shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Добавить слайд
            </button>
        </div>

        <!-- Slides grid -->
        <div v-if="slides.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div
                v-for="slide in slides"
                :key="slide.id"
                class="overflow-hidden rounded-card border border-line dark:border-dline bg-white dark:bg-dcard shadow-[0_1px_8px_rgba(0,0,0,.08)] hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(0,0,0,.12)] transition-all duration-200"
            >
                <!-- sl-prev: 130px preview area -->
                <div class="relative aspect-square overflow-hidden" :style="bannerStyle(slide)">
                    <!-- sl-chip: glass badge top-left -->
                    <span
                        v-if="slide.badge"
                        class="absolute top-2.5 left-2.5 inline-flex items-center px-2.5 py-[5px] rounded-[6px] text-[11px] font-black tracking-wider text-white backdrop-blur-sm bg-white/20 border border-white/30 leading-none"
                    >
                        {{ slide.badge }}
                    </span>
                    <!-- sl-ov: dark gradient overlay + title at bottom -->
                    <div class="absolute inset-x-0 bottom-0 h-[60px] bg-gradient-to-t from-black/70 to-transparent flex items-end px-3 pb-2.5">
                        <h3 class="font-black text-sm text-white leading-tight truncate w-full">{{ slide.title }}</h3>
                    </div>
                </div>

                <!-- sl-body: white section with description + price -->
                <div class="px-3.5 py-3 border-b border-line dark:border-dline min-h-[56px]">
                    <p v-if="slide.description" class="text-xs text-muted line-clamp-2 leading-relaxed mb-2">{{ slide.description }}</p>
                    <div v-if="slide.price" class="flex items-baseline gap-1.5 flex-wrap">
                        <span class="text-sm font-black text-ink dark:text-white font-data">${{ parseFloat(slide.price).toFixed(0) }}</span>
                        <span v-if="slide.old_price" class="text-xs text-muted line-through font-data">${{ parseFloat(slide.old_price).toFixed(0) }}</span>
                        <span v-if="slide.discount" class="ml-auto text-[10px] font-bold px-1.5 py-[3px] rounded-[4px] bg-red-500 text-white leading-none">-{{ slide.discount }}%</span>
                    </div>
                </div>

                <!-- sl-ft: footer with edit + delete -->
                <div class="flex items-center px-3.5 py-2.5">
                    <button
                        @click="openDrawer(slide)"
                        class="flex items-center gap-1.5 text-xs font-semibold text-blue-500 hover:text-blue-600 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                        Редактировать
                    </button>
                    <button
                        @click="confirmDelete(slide)"
                        class="ml-auto flex items-center gap-1.5 text-xs font-semibold text-red-400 hover:text-red-500 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Удалить
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else class="bg-white dark:bg-dcard rounded-card border border-line dark:border-dline p-16 text-center shadow-[0_1px_8px_rgba(0,0,0,.06)]">
            <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/>
            </svg>
            <p class="text-muted">Слайдов пока нет. <button @click="openDrawer(null)" class="text-blue-500 hover:underline">Добавить первый</button></p>
        </div>

        <!-- Drawer -->
        <AppDrawer
            :show="showDrawer"
            type="slide"
            :record="editRecord"
            @close="showDrawer = false"
        />

        <!-- Delete modal -->
        <DeleteModal
            :show="showDelete"
            :name="deleteTarget?.title ?? ''"
            :loading="deleting"
            @confirm="deleteItem"
            @close="showDelete = false"
        />
    </AppLayout>
</template>
