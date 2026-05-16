<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

defineOptions({ layout: FinanceLayout });

const categories = ['food', 'transport', 'education', 'rent', 'health', 'entertainment', 'clothing', 'services', 'other'];
const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const payload = ref({ data: [], total: 0, breakdown: [] });
const pendingDelete = ref(null);
const now = new Date();
const filters = reactive({ month: now.getMonth() + 1, year: now.getFullYear(), category: '' });
const form = reactive({ amount: '', category: 'food', note: '', date: now.toISOString().slice(0, 10) });

const badgeClass = { food: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', transport: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', education: 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300', rent: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300', health: 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300', entertainment: 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300', clothing: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', services: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300', other: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' };

const months = computed(() => l.value.months.map((label, i) => ({ value: i + 1, label })));

const load = async () => (payload.value = await api.getExpenses(filters));
const submit = async () => { await api.createExpense(form); api.toast(l.value.exp.saved); Object.assign(form, { amount: '', category: 'food', note: '', date: now.toISOString().slice(0, 10) }); await load(); };
const remove = async () => { await api.deleteExpense(pendingDelete.value.id); api.toast(l.value.exp.deleted); pendingDelete.value = null; await load(); };
onMounted(load);
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.exp.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.exp.sub(formatCurrency(payload.total)) }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.exp.register }}</h2>
            <form class="grid gap-4 md:grid-cols-5" @submit.prevent="submit">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.amountLabel }}</label>
                    <input v-model="form.amount" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="number" step="0.01" placeholder="0.00" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.categoryLabel }}</label>
                    <select v-model="form.category" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ l.cat[cat] }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.noteLabel }}</label>
                    <input v-model="form.note" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400" :placeholder="l.noteLabel" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.dateLabel }}</label>
                    <input v-model="form.date" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="date" />
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#166a4a]">{{ l.save }}</button>
                </div>
                <p v-for="(fieldErrors, field) in api.errors.value" :key="field" class="text-sm text-red-500 md:col-span-5">{{ fieldErrors[0] }}</p>
            </form>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.exp.filters }}</h2>
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.monthLabel }}</label>
                    <select v-model.number="filters.month" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" @change="load">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.yearLabel }}</label>
                    <input v-model.number="filters.year" type="number" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" @change="load" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.categoryLabel }}</label>
                    <select v-model="filters.category" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" @change="load">
                        <option value="">{{ l.all }}</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ l.cat[cat] }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="load">{{ l.exp.applyFilter }}</button>
                </div>
            </div>
        </div>

        <div v-if="payload.breakdown?.length" class="flex flex-wrap gap-2">
            <span v-for="item in payload.breakdown" :key="item.category" class="rounded-full bg-white px-3 py-1.5 text-sm font-medium shadow-sm dark:bg-slate-800">
                <span :class="badgeClass[item.category]" class="mr-1.5 rounded-full px-1.5 py-0.5 text-xs font-semibold">{{ l.cat[item.category] || item.category }}</span>
                {{ formatCurrency(item.total) }}
            </span>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
            <div v-for="expense in payload.data" :key="expense.id" class="flex items-center justify-between border-b border-slate-100 px-5 py-4 last:border-b-0 dark:border-slate-700">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass[expense.category]">{{ l.cat[expense.category] || expense.category }}</span>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-slate-100">{{ expense.note || l.noDesc }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ expense.date }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <strong class="text-slate-900 dark:text-white">{{ formatCurrency(expense.amount) }}</strong>
                    <button class="text-xs font-medium text-red-500 hover:text-red-700" @click="pendingDelete = expense">{{ l.delete }}</button>
                </div>
            </div>
            <div v-if="!payload.data.length" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500">{{ l.exp.noData }}</div>
        </div>

        <ConfirmDialog :open="!!pendingDelete" :title="l.exp.deleteTitle" :message="l.exp.deleteMsg" @cancel="pendingDelete = null" @confirm="remove" />
    </section>
</template>
