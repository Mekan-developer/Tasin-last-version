<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    show:    { type: Boolean, default: false },
    product: { type: Object, default: null },
})

const emit = defineEmits(['close'])
const toast = useToastStore()

const variants = ref([])
const errors   = ref({})
const saving   = ref(false)

// ── Populate from product when opened ───────────────────────────────────────
watch(() => props.show, (val) => {
    if (val) {
        const r = props.product
        variants.value = (r?.variants ?? []).map(v => ({
            name:     v.name ?? '',
            price:    (v.price === null || v.price === undefined || v.price === '') ? '' : parseFloat(v.price),
            currency: v.currency ?? 'USD',
        }))
        errors.value = {}
        document.addEventListener('keydown', onKeydown)
        document.body.style.overflow = 'hidden'
    } else {
        document.removeEventListener('keydown', onKeydown)
        document.body.style.overflow = ''
    }
})

function onKeydown(e) { if (e.key === 'Escape') emit('close') }

function addVariant() {
    variants.value.push({ name: '', price: '', currency: 'USD' })
}

function removeVariant(i) {
    variants.value.splice(i, 1)
}

// ── Base price hint (shown as placeholder for empty variant prices) ──────────
const basePrice = computed(() => {
    if (!props.product) return ''
    const p = parseFloat(props.product.price)
    if (Number.isNaN(p)) return ''
    return props.product.currency === 'USD' ? '$' + p.toFixed(2) : p.toFixed(0) + ' TMT'
})

function save() {
    if (!props.product) return
    // Drop blank rows so an empty trailing row doesn't trip validation.
    const payload = variants.value
        .filter(v => String(v.name ?? '').trim() !== '')
        .map(v => ({
            name:     String(v.name).trim(),
            price:    v.price === null || v.price === undefined ? '' : v.price,
            currency: v.currency || 'USD',
        }))

    saving.value = true
    errors.value = {}
    router.put(route('products.variants.update', props.product.id), { variants: payload }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Варианты сохранены.')
            emit('close')
        },
        onError: (errs) => {
            errors.value = errs
            const first = Object.values(errs)[0]
            toast.error(first || 'Проверьте поля с ошибками.')
        },
        onFinish: () => { saving.value = false },
    })
}

// ── Input CSS (matches AppDrawer) ────────────────────────────────────────────
const inpSm = 'w-full px-2.5 py-1.5 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-[8px] text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all'
const selSm = inpSm + ' cursor-pointer'
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />

                <!-- Dialog -->
                <div class="relative z-10 w-full max-w-lg bg-white dark:bg-dcard rounded-card shadow-[0_16px_48px_rgba(0,0,0,.2)] flex flex-col max-h-[88vh]">
                    <!-- Header -->
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-line dark:border-dline flex-shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-base font-bold text-ink dark:text-white truncate">Варианты по моделям</h2>
                            <p class="text-[11px] text-muted mt-0.5 truncate">{{ product?.name }}</p>
                        </div>
                        <button
                            @click="$emit('close')"
                            class="w-8 h-8 flex items-center justify-center rounded-btn text-muted hover:text-ink dark:hover:text-white hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto p-5">
                        <div class="rounded-xl border border-line dark:border-dline overflow-hidden">
                            <!-- Section header -->
                            <div class="flex items-center justify-between px-4 py-3 bg-surface dark:bg-dbg border-b border-line dark:border-dline">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-ink dark:text-white">Список вариантов</span>
                                    <span v-if="variants.length" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-500 text-white text-[10px] font-bold">
                                        {{ variants.length }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="addVariant"
                                    class="flex items-center gap-1 text-xs font-semibold text-blue-500 hover:text-blue-600 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                    Добавить вариант
                                </button>
                            </div>

                            <!-- Variant rows -->
                            <div v-if="variants.length" class="divide-y divide-line dark:divide-dline">
                                <div
                                    v-for="(v, i) in variants"
                                    :key="i"
                                    class="flex items-center gap-1.5 px-3 py-2.5 hover:bg-surface/50 dark:hover:bg-dline/20 transition-colors group"
                                >
                                    <span class="w-5 h-5 rounded-full bg-surface dark:bg-dline flex items-center justify-center text-[10px] font-bold text-muted flex-shrink-0">
                                        {{ i + 1 }}
                                    </span>

                                    <input
                                        v-model="v.name"
                                        type="text"
                                        placeholder="Модель"
                                        :class="[inpSm, 'flex-1']"
                                    />

                                    <input style="width: 70px;"
                                        v-model="v.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :placeholder="basePrice || 'Цена'"
                                        :class="[inpSm, 'flex-shrink-0']"
                                    />

                                    <select style="width:70px" v-model="v.currency" :class="[selSm, 'flex-shrink-0']">
                                        <option value="USD">USD</option>
                                        <option value="TMT">TMT</option>
                                    </select>

                                    <button
                                        type="button"
                                        @click="removeVariant(i)"
                                        class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        title="Удалить вариант"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="py-6 px-4 text-center text-xs text-muted">
                                Нет вариантов. Нажмите «+ Добавить вариант», чтобы задать разные цены по моделям.
                            </div>

                            <!-- Hint -->
                            <p v-if="variants.length" class="px-4 py-2 text-[11px] text-muted border-t border-line dark:border-dline bg-surface/40 dark:bg-dbg/40">
                                Оставьте поле цены пустым — возьмётся цена основного товара{{ basePrice ? ` (${basePrice})` : '' }}.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex-shrink-0 flex items-center gap-3 px-5 py-4 border-t border-line dark:border-dline">
                        <button
                            @click="save"
                            :disabled="saving"
                            class="flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors"
                        >
                            <svg v-if="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ saving ? 'Сохранение…' : 'Сохранить' }}
                        </button>
                        <button
                            @click="$emit('close')"
                            :disabled="saving"
                            class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            Отмена
                        </button>
                        <span v-if="variants.length" class="ml-auto text-[11px] text-muted">{{ variants.length }} вар.</span>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
