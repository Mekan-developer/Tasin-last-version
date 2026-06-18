<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToastStore } from '@/stores/useToastStore'

const props = defineProps({
    show:  { type: Boolean, default: false },
    staff: { type: Object, default: null },
})
const emit = defineEmits(['close'])
const toast = useToastStore()

const form = useForm({ amount: '', type: 'salary' })

const typeLabels = {
    salary:   'Выплата зарплаты',
    advance:  'Аванс (долг ↑)',
    debt_pay: 'Возврат долга работником (долг ↓)',
}

function submit() {
    if (!props.staff) return
    form.post(route('staff.pay', props.staff.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Операция выполнена.')
            form.reset()
            emit('close')
        },
        onError: (errors) => {
            const first = Object.values(errors)[0]
            toast.error(first || 'Maglumatlarda ýalňyşlyk bar. Meýdanlary barlaň.')
        },
    })
}
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
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />

                <div class="relative z-10 w-full max-w-md bg-white dark:bg-dcard rounded-card p-6 shadow-[0_16px_48px_rgba(0,0,0,.2)]">
                    <h3 class="text-base font-bold text-ink dark:text-white">Выплата / операция</h3>
                    <p class="text-sm text-muted mt-0.5">{{ staff?.name }}</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Сумма (TMT) <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full px-3 py-2 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 focus:ring-[3px] focus:ring-blue-500/10 transition-all"
                            />
                            <p v-if="form.errors.amount" class="text-xs text-red-500 mt-1">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink dark:text-white mb-1.5">Тип операции</label>
                            <select
                                v-model="form.type"
                                class="w-full px-3 py-2 text-sm bg-white dark:bg-dbg border-[1.5px] border-line dark:border-dline rounded-btn text-ink dark:text-white focus:outline-none focus:border-blue-500 transition-all cursor-pointer"
                            >
                                <option v-for="(label, val) in typeLabels" :key="val" :value="val">{{ label }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="$emit('close')"
                            :disabled="form.processing"
                            class="flex-1 py-2.5 border-[1.5px] border-line dark:border-dline text-ink dark:text-white text-sm font-semibold rounded-btn hover:bg-surface dark:hover:bg-dline transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-400 text-white text-sm font-semibold rounded-btn transition-colors"
                        >
                            {{ form.processing ? 'Выполнение…' : 'Подтвердить' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
