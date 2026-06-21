<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToastStore } from '@/stores/useToastStore'
import BannerCropModal from '@/Components/BannerCropModal.vue'

const props = defineProps({
    show:       { type: Boolean, default: false },
    type:       { type: String, default: '' },   // category | product | slide | user | staff
    record:     { type: Object, default: null },
    categories: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])
const toast = useToastStore()

// ── Unified form (all fields from all types) ─────────────────────────────
const form = useForm({
    // category
    name: '', order: 0, views: 0, show_price: true, is_active: true,
    icon: null,
    // product
    category_id: '', description: '', price: '', currency: 'USD',
    active_image: 0, is_new: false, variants: [],
    images_upload: [],
    // slide
    title: '', badge: '', bg_color: '#1e40af', old_price: '', discount: '',
    image: null,
    // user
    email: '', phone: '', role: 'manager', password: '', password_confirmation: '',
    // staff
    position: '', department: '', salary: '', paid: 0, debt: 0, hired_at: '',
})

// ── Image preview state ────────────────────────────────────────────────────
const iconPreview          = ref(null)
const slideImagePreview    = ref(null)
const existingImageUrls    = ref([])
const productImagePreviews = ref([])

// ── Crop modal state ───────────────────────────────────────────────────────
const showCrop = ref(false)
const cropSrc  = ref('')

// ── Populate when drawer opens ────────────────────────────────────────────
watch([() => props.show, () => props.record, () => props.type], ([show]) => {
    if (!show) return
    const r = props.record

    form.clearErrors()
    form.name             = ''
    form.order            = 0
    form.is_active        = true
    form.views            = 0
    form.show_price       = true
    form.icon             = null
    form.category_id      = ''
    form.description      = ''
    form.price            = ''
    form.currency         = 'USD'
    form.active_image     = 0
    form.is_new           = false
    form.variants         = []
    form.images_upload    = []
    form.title            = ''
    form.badge            = ''
    form.bg_color         = '#1e40af'
    form.old_price        = ''
    form.discount         = ''
    form.image            = null
    form.email            = ''
    form.phone            = ''
    form.role             = 'manager'
    form.password         = ''
    form.password_confirmation = ''
    form.position         = ''
    form.department       = ''
    form.salary           = ''
    form.paid             = 0
    form.debt             = 0
    form.hired_at         = ''
    iconPreview.value          = null
    slideImagePreview.value    = null
    existingImageUrls.value    = []
    productImagePreviews.value = []

    if (!r) return

    if (props.type === 'category') {
        form.name       = r.name ?? ''
        form.order      = r.order ?? 0
        form.views      = r.views ?? 0
        form.show_price = r.show_price ?? true
        form.is_active  = r.is_active ?? true
        iconPreview.value = r.image_url ?? null

    } else if (props.type === 'product') {
        form.name         = r.name ?? ''
        form.category_id  = r.category_id ?? ''
        form.description  = r.description ?? ''
        form.price        = r.price ?? ''
        form.currency     = r.currency ?? 'USD'
        form.order        = r.order ?? 0
        form.active_image = r.active_image ?? 0
        form.is_new       = r.is_new ?? false
        form.is_active    = r.is_active ?? true
        // Load existing variants — each gets id for tracking
        form.variants = (r.variants ?? []).map(v => ({
            id:       v.id ?? null,
            name:     v.name ?? '',
            price:    (v.price === null || v.price === undefined || v.price === '') ? '' : parseFloat(v.price),
            currency: v.currency ?? 'USD',
        }))
        existingImageUrls.value = r.image_urls ?? []

    } else if (props.type === 'slide') {
        form.title       = r.title ?? ''
        form.description = r.description ?? ''
        form.badge       = r.badge ?? ''
        form.bg_color    = r.bg_color ?? '#1e40af'
        form.price       = r.price ?? ''
        form.currency    = r.currency ?? 'USD'
        form.old_price   = r.old_price ?? ''
        form.discount    = r.discount ?? ''
        form.order       = r.order ?? 0
        slideImagePreview.value = r.image_url ?? null

    } else if (props.type === 'user') {
        form.name  = r.name ?? ''
        form.email = r.email ?? ''
        form.phone = r.phone ?? ''
        form.role  = r.role ?? 'manager'

    } else if (props.type === 'staff') {
        form.name       = r.name ?? ''
        form.position   = r.position ?? ''
        form.department = r.department ?? ''
        form.salary     = r.salary ?? ''
        form.paid       = r.paid ?? 0
        form.debt       = r.debt ?? 0
        form.hired_at   = r.hired_at ? r.hired_at.substring(0, 10) : ''
        form.is_active  = r.is_active ?? true
    }
})

// ── Keyboard + scroll lock ────────────────────────────────────────────────
watch(() => props.show, (val) => {
    if (val) {
        document.addEventListener('keydown', onKeydown)
        document.body.style.overflow = 'hidden'
    } else {
        document.removeEventListener('keydown', onKeydown)
        document.body.style.overflow = ''
    }
})
function onKeydown(e) { if (e.key === 'Escape') emit('close') }

// ── File handlers ─────────────────────────────────────────────────────────
function onIconChange(e) {
    const f = e.target.files[0]
    if (!f) return
    form.icon = f
    iconPreview.value = URL.createObjectURL(f)
}

function onSlideImageChange(e) {
    const f = e.target.files[0]
    if (!f) return
    cropSrc.value  = URL.createObjectURL(f)
    showCrop.value = true
    e.target.value = ''
}

function onCropConfirm(blob) {
    const file = new File([blob], 'banner.webp', { type: 'image/webp' })
    form.image = file
    slideImagePreview.value = URL.createObjectURL(blob)
    showCrop.value = false
}

function clearSlideImage() {
    form.image = null
    slideImagePreview.value = null
}

function onProductImagesChange(e) {
    const files = Array.from(e.target.files)
    if (!files.length) return
    const existing = Array.isArray(form.images_upload) ? [...form.images_upload] : []
    form.images_upload = [...existing, ...files]
    productImagePreviews.value = [...productImagePreviews.value, ...files.map(f => URL.createObjectURL(f))]
}

function removeProductUpload(i) {
    const arr = [...form.images_upload]
    arr.splice(i, 1)
    form.images_upload = arr
    productImagePreviews.value.splice(i, 1)
}

// ── Variants ──────────────────────────────────────────────────────────────
function addVariant() {
    form.variants.push({ id: null, name: '', price: '', currency: 'USD' })
}

function removeVariant(i) {
    form.variants.splice(i, 1)
}

// ── Submit ────────────────────────────────────────────────────────────────
const isEdit      = computed(() => !!props.record)
const entityName  = computed(() => ({ category: 'Категория', product: 'Товар', slide: 'Слайд', user: 'Администратор', staff: 'Сотрудник' })[props.type] ?? '')
const drawerTitle = computed(() => isEdit.value ? `Редактировать: ${entityName.value}` : `Добавить: ${entityName.value}`)

const typeFields = {
    category: ['name', 'order', 'views', 'show_price', 'is_active', 'icon'],
    product:  ['name', 'category_id', 'description', 'price', 'currency', 'order', 'active_image', 'is_new', 'is_active', 'variants', 'images_upload'],
    slide:    ['title', 'description', 'badge', 'bg_color', 'price', 'currency', 'old_price', 'discount', 'order', 'image'],
    user:     ['name', 'email', 'phone', 'role', 'password', 'password_confirmation'],
    staff:    ['name', 'position', 'department', 'salary', 'paid', 'debt', 'hired_at', 'is_active'],
}

const FILE_TYPES = ['category', 'slide', 'product']

function getUrl() {
    const r = props.record
    return {
        category: r ? route('categories.update', r.id) : route('categories.store'),
        product:  r ? route('products.update', r.id)   : route('products.store'),
        slide:    r ? route('slides.update', r.id)     : route('slides.store'),
        user:     r ? route('users.update', r.id)      : route('users.store'),
        staff:    r ? route('staff.update', r.id)      : route('staff.store'),
    }[props.type]
}

function submit() {
    const fields        = typeFields[props.type] ?? []
    const needsFormData = FILE_TYPES.includes(props.type)
    const url           = getUrl()

    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            iconPreview.value          = null
            slideImagePreview.value    = null
            productImagePreviews.value = []
            toast.success(isEdit.value ? `${entityName.value} обновлён(а).` : `${entityName.value} добавлен(а).`)
            emit('close')
        },
        onError: (errors) => {
            const first = Object.values(errors)[0]
            toast.error(first || 'Maglumatlarda ýalňyşlyk bar. Meýdanlary barlaň.')
        },
    }

    if (needsFormData) {
        form.transform(data => {
            const out = {}
            fields.forEach(k => {
                const v = data[k]
                if (v === null || v === undefined) return
                if (k === 'images_upload' && Array.isArray(v) && v.length === 0) return
                // For variants: strip `id` field — backend re-creates them all
                if (k === 'variants') {
                    // Keep only named variants; strip `id` (backend re-creates them).
                    // An empty price is sent as '' → backend stores null → inherits product price.
                    out[k] = (v ?? [])
                        .filter(row => String(row.name ?? '').trim() !== '')
                        .map(({ name, price, currency }) => ({
                            name: String(name).trim(),
                            price: price === null || price === undefined ? '' : price,
                            currency,
                        }))
                    return
                }
                out[k] = v
            })
            if (isEdit.value) out._method = 'put'
            return out
        }).post(url, { ...opts, forceFormData: true })
    } else {
        const method = isEdit.value ? 'put' : 'post'
        form.transform(data => {
            const out = {}
            fields.forEach(k => { out[k] = data[k] })
            return out
        })[method](url, opts)
    }
}

// ── Toggle helper ─────────────────────────────────────────────────────────
function togClass(val) {
    return val
        ? 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-blue-500 transition-colors'
        : 'relative inline-flex h-[22px] w-10 items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors'
}

// ── Input CSS ─────────────────────────────────────────────────────────────
const inp = 'w-full px-3 py-2 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all'
const sel = inp + ' cursor-pointer'
const inpSm = 'w-full px-2.5 py-1.5 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-[8px] text-ink dark:text-white placeholder:text-muted focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all'
const selSm = inpSm + ' cursor-pointer'
</script>

<template>
    <Teleport to="body">
        <!-- Overlay -->
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-300"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[800] bg-black/40 backdrop-blur-sm" @click="$emit('close')" />
        </Transition>

        <!-- Panel -->
        <Transition
            enter-from-class="translate-x-full"
            enter-active-class="transition-transform duration-[350ms] ease-out"
            leave-active-class="transition-transform duration-[350ms] ease-in"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="show"
                class="fixed top-0 right-0 z-[900] h-full w-[500px] max-w-[96vw] bg-white dark:bg-dcard shadow-[-8px_0_24px_rgba(0,0,0,.12)] flex flex-col"
            >
                <!-- Header -->
                <div class="flex items-center gap-3 px-5 py-4 border-b border-line dark:border-dline flex-shrink-0">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-bold text-ink dark:text-white truncate">{{ drawerTitle }}</h2>
                        <p v-if="type === 'product' && isEdit && form.variants.length" class="text-[11px] text-muted mt-0.5">
                            {{ form.variants.length }} {{ form.variants.length === 1 ? 'вариант' : 'вариантов' }} · {{ existingImageUrls.length }} фото
                        </p>
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
                <div class="flex-1 overflow-y-auto p-5 space-y-4">

                    <!-- ════════════ CATEGORY ════════════ -->
                    <template v-if="type === 'category'">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">
                                Название категории <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.name" type="text" placeholder="Смартфоны" :class="inp" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Иконка категории</label>
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-xl border border-line dark:border-dline overflow-hidden flex-shrink-0 flex items-center justify-center bg-surface dark:bg-dbg">
                                    <img v-if="iconPreview" :src="iconPreview" class="w-full h-full object-cover" alt="icon" />
                                    <svg v-else class="w-6 h-6 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                    </svg>
                                </div>
                                <label class="flex-1 flex items-center gap-2 px-3 py-2.5 border-[1.5px] border-dashed border-line dark:border-dline rounded-btn cursor-pointer hover:border-blue-400 hover:text-blue-500 transition-colors text-sm text-muted">
                                    <input type="file" accept="image/*" class="hidden" @change="onIconChange" />
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    {{ iconPreview ? 'Заменить иконку' : 'Загрузить иконку' }}
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Порядок</label>
                                <input v-model.number="form.order" type="number" min="0" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Просмотры</label>
                                <input v-model.number="form.views" type="number" :class="inp" readonly />
                            </div>
                        </div>
                        <div class="rounded-xl border border-line dark:border-dline divide-y divide-line dark:divide-dline">
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">Показывать цену</span>
                                <button type="button" @click="form.show_price = !form.show_price" :class="togClass(form.show_price)">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', form.show_price ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">Активна</span>
                                <button type="button" @click="form.is_active = !form.is_active" :class="togClass(form.is_active)">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', form.is_active ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- ════════════ PRODUCT ════════════ -->
                    <template v-else-if="type === 'product'">

                        <!-- Basic info -->
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Название товара <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="iPhone 15 Pro" :class="inp" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Категория <span class="text-red-500">*</span></label>
                                <select v-model="form.category_id" :class="sel">
                                    <option value="">Выберите категорию</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Порядок</label>
                                <input v-model.number="form.order" type="number" min="0" :class="inp" />
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Базовая цена <span class="text-red-500">*</span></label>
                                <input v-model="form.price" type="number" min="0" step="0.01" :class="inp" placeholder="0.00" />
                                <p v-if="form.errors.price" class="text-xs text-red-500 mt-1">{{ form.errors.price }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Валюта</label>
                                <select v-model="form.currency" :class="sel">
                                    <option value="USD">USD</option>
                                    <option value="TMT">TMT</option>
                                </select>
                            </div>
                        </div>

                        <!-- ── VARIANTS (key section) ── -->
                        <div class="rounded-xl border border-line dark:border-dline overflow-hidden">
                            <!-- Section header -->
                            <div class="flex items-center justify-between px-4 py-3 bg-surface dark:bg-dbg border-b border-line dark:border-dline">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                                    </svg>
                                    <span class="text-xs font-bold text-ink dark:text-white">Варианты по моделям</span>
                                    <span v-if="form.variants.length" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-500 text-white text-[10px] font-bold">
                                        {{ form.variants.length }}
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
                            <div v-if="form.variants.length" class="divide-y divide-line dark:divide-dline">
                                <div
                                    v-for="(v, i) in form.variants"
                                    :key="i"
                                    class="flex items-center gap-1.5 px-3 py-2.5 hover:bg-surface/50 dark:hover:bg-dline/20 transition-colors group"
                                >
                                    <!-- Row number -->
                                    <span class="w-5 h-5 rounded-full bg-surface dark:bg-dline flex items-center justify-center text-[10px] font-bold text-muted flex-shrink-0">
                                        {{ i + 1 }}
                                    </span>

                                    <!-- Model name -->
                                    <input
                                        v-model="v.name"
                                        type="text"
                                        placeholder="Модель"
                                        :class="[inpSm, 'flex-1 ']"
                                    />

                                    <!-- Price -->
                                    <input
                                        v-model="v.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="Цена"
                                        :class="[inpSm, 'w-16 flex-shrink-0']"
                                    />

                                    <!-- Currency -->
                                    <select v-model="v.currency" :class="[selSm, 'w-[74px] flex-shrink-0']">
                                        <option value="USD">USD</option>
                                        <option value="TMT">TMT</option>
                                    </select>

                                    <!-- Delete -->
                                    <button
                                        type="button"
                                        @click="removeVariant(i)"
                                        class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-btn text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                        title="Удалить вариант"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="py-4 px-4 text-center text-xs text-muted">
                                Нет вариантов. Нажмите «+ Добавить вариант» чтобы задать разные цены по моделям.
                            </div>

                            <!-- Hint: empty price inherits the product price -->
                            <p v-if="form.variants.length" class="px-4 py-2 text-[11px] text-muted border-t border-line dark:border-dline bg-surface/40 dark:bg-dbg/40">
                                Оставьте поле цены пустым — возьмётся цена основного товара.
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Описание</label>
                            <textarea v-model="form.description" rows="3" :class="inp" placeholder="Краткое описание товара..." />
                        </div>

                        <!-- Image gallery -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-ink dark:text-white">Галерея фото</p>
                                <span class="text-[11px] text-muted">Нажмите на фото, чтобы выбрать главное</span>
                            </div>

                            <!-- Existing images -->
                            <div v-if="existingImageUrls.length" class="grid grid-cols-4 gap-2 mb-2">
                                <div
                                    v-for="(url, i) in existingImageUrls"
                                    :key="'ex-' + i"
                                    class="relative aspect-square rounded-xl overflow-hidden cursor-pointer border-2 transition-all"
                                    :class="form.active_image === i
                                        ? 'border-blue-500 shadow-[0_0_0_2px_rgba(59,130,246,.3)]'
                                        : 'border-line dark:border-dline hover:border-blue-300'"
                                    @click="form.active_image = i"
                                >
                                    <img :src="url" class="w-full h-full object-cover" />
                                    <div v-if="form.active_image === i" class="absolute inset-0 bg-blue-500/10" />
                                    <span v-if="form.active_image === i" class="absolute bottom-1 left-1/2 -translate-x-1/2 text-[9px] font-bold bg-blue-500 text-white px-1.5 py-0.5 rounded-full whitespace-nowrap">Главное</span>
                                </div>
                            </div>

                            <!-- New upload previews -->
                            <div v-if="productImagePreviews.length" class="grid grid-cols-4 gap-2 mb-2">
                                <div
                                    v-for="(url, i) in productImagePreviews"
                                    :key="'new-' + i"
                                    class="relative aspect-square rounded-xl overflow-hidden border-2 border-blue-300 dark:border-blue-700 group"
                                >
                                    <img :src="url" class="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        @click="removeProductUpload(i)"
                                        class="absolute top-1 right-1 w-5 h-5 flex items-center justify-center rounded-full bg-black/60 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <span class="absolute bottom-1 left-1 text-[8px] font-bold bg-blue-500 text-white px-1 rounded">NEW</span>
                                </div>
                            </div>

                            <!-- Upload zone -->
                            <label class="flex items-center gap-3 border-2 border-dashed border-line dark:border-dline rounded-xl px-4 py-3 hover:border-blue-400 transition-colors cursor-pointer">
                                <input type="file" accept="image/*" multiple class="hidden" @change="onProductImagesChange" />
                                <svg class="w-5 h-5 text-muted flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <span class="text-xs text-muted">Добавить фото <span class="text-[11px]">(PNG/JPG · макс. 4MB)</span></span>
                            </label>
                        </div>

                        <!-- Toggles -->
                        <div class="rounded-xl border border-line dark:border-dline divide-y divide-line dark:divide-dline">
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">Отметить как новинку</span>
                                <button type="button" @click="form.is_new = !form.is_new" :class="togClass(form.is_new)">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', form.is_new ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">Активен</span>
                                <button type="button" @click="form.is_active = !form.is_active" :class="togClass(form.is_active)">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', form.is_active ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- ════════════ SLIDE ════════════ -->
                    <template v-else-if="type === 'slide'">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Заголовок <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" placeholder="Лучшие смартфоны" :class="inp" />
                            <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Описание</label>
                            <textarea v-model="form.description" rows="2" :class="inp" placeholder="Слоган или краткое описание..." />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Бейдж (макс. 10)</label>
                                <input v-model="form.badge" type="text" maxlength="10" placeholder="NEW" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Цвет фона</label>
                                <div class="flex gap-2">
                                    <input v-model="form.bg_color" type="color" class="h-[38px] w-12 rounded-btn border-[1.5px] border-line dark:border-dline cursor-pointer p-0.5 bg-white dark:bg-dbg" />
                                    <input v-model="form.bg_color" type="text" :class="inp" />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Цена</label>
                                <input v-model="form.price" type="number" min="0" step="0.01" placeholder="0.00" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Валюта</label>
                                <select v-model="form.currency" :class="sel">
                                    <option value="USD">USD</option>
                                    <option value="TMT">TMT</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Старая цена</label>
                                <input v-model="form.old_price" type="number" min="0" step="0.01" placeholder="0.00" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Скидка %</label>
                                <input v-model.number="form.discount" type="number" min="0" max="100" placeholder="0" :class="inp" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Порядок</label>
                            <input v-model.number="form.order" type="number" min="0" :class="inp" />
                        </div>
                        <!-- Image upload -->
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Изображение баннера</label>
                            <div v-if="slideImagePreview" class="relative mb-2 rounded-xl overflow-hidden border border-line dark:border-dline" style="height:120px">
                                <img :src="slideImagePreview" class="w-full h-full object-cover" alt="preview" />
                                <button type="button" @click="clearSlideImage" class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <label class="block border-2 border-dashed border-line dark:border-dline rounded-xl p-5 text-center hover:border-blue-400 transition-colors cursor-pointer">
                                <input type="file" accept="image/*" class="hidden" @change="onSlideImageChange" />
                                <svg class="w-8 h-8 text-muted mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <p class="text-xs text-muted">{{ slideImagePreview ? 'Заменить изображение' : 'Нажмите или перетащите файл' }}</p>
                                <p class="text-[11px] text-muted mt-0.5">PNG, JPG · Кадрирование откроется автоматически</p>
                            </label>
                        </div>
                    </template>

                    <!-- ════════════ USER ════════════ -->
                    <template v-else-if="type === 'user'">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Полное имя <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Имя Фамилия" :class="inp" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input v-model="form.email" type="email" placeholder="admin@example.com" :class="inp" />
                            <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Телефон</label>
                                <input v-model="form.phone" type="text" placeholder="+993 ..." :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Роль</label>
                                <select v-model="form.role" :class="sel">
                                    <option value="manager">Менеджер</option>
                                    <option value="admin">Администратор</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">
                                {{ isEdit ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Пароль *' }}
                            </label>
                            <input v-model="form.password" type="password" autocomplete="new-password" :class="inp" />
                            <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Подтвердите пароль</label>
                            <input v-model="form.password_confirmation" type="password" autocomplete="new-password" :class="inp" />
                        </div>
                    </template>

                    <!-- ════════════ STAFF ════════════ -->
                    <template v-else-if="type === 'staff'">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Полное имя <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Имя Фамилия" :class="inp" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Должность</label>
                                <input v-model="form.position" type="text" placeholder="Продавец" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Отдел</label>
                                <input v-model="form.department" type="text" placeholder="Продажи" :class="inp" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Оклад (TMT/мес) <span class="text-red-500">*</span></label>
                                <input v-model="form.salary" type="number" min="0" step="0.01" placeholder="0.00" :class="inp" />
                                <p v-if="form.errors.salary" class="text-xs text-red-500 mt-1">{{ form.errors.salary }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Дата найма</label>
                                <input v-model="form.hired_at" type="date" :class="inp" />
                            </div>
                        </div>
                        <p class="text-xs font-bold text-muted uppercase tracking-wide">Текущий месяц</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Выплачено (TMT)</label>
                                <input v-model="form.paid" type="number" min="0" step="0.01" :class="inp" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Долг сотрудника</label>
                                <input v-model="form.debt" type="number" min="0" step="0.01" :class="inp" />
                            </div>
                        </div>
                        <div class="rounded-xl border border-line dark:border-dline">
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-ink dark:text-white">Активен</span>
                                <button type="button" @click="form.is_active = !form.is_active" :class="togClass(form.is_active)">
                                    <span :class="['absolute h-4 w-4 rounded-full bg-white shadow transition-transform duration-200', form.is_active ? 'translate-x-5' : 'translate-x-1']" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex-shrink-0 flex items-center gap-3 px-5 py-4 border-t border-line dark:border-dline">
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Сохранение…' : 'Сохранить' }}
                    </button>
                    <button
                        @click="$emit('close')"
                        :disabled="form.processing"
                        class="px-5 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors"
                    >
                        Отмена
                    </button>

                    <!-- Variant count hint (product only) -->
                    <span v-if="type === 'product' && form.variants.length" class="ml-auto text-[11px] text-muted">
                        {{ form.variants.length }} вар.
                    </span>
                </div>
            </div>
        </Transition>

        <BannerCropModal
            :show="showCrop"
            :src="cropSrc"
            @confirm="onCropConfirm"
            @close="showCrop = false"
        />
    </Teleport>
</template>
