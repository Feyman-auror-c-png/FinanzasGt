<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

defineOptions({ layout: FinanceLayout });

const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const income = ref({ salary: [], extra: [], total: 0 });
const pendingDelete = ref(null);
const form = reactive({ type: 'salary', label: '', amount: '', date: new Date().toISOString().slice(0, 10) });
const entries = computed(() => [...(income.value.salary || []), ...(income.value.extra || [])].sort((a, b) => new Date(b.date) - new Date(a.date)));

const typeColors = { salary: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', extra: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' };

const load = async () => (income.value = await api.getIncome());
const submit = async () => {
    await api.createIncome(form);
    api.toast(l.value.inc.saved);
    Object.assign(form, { type: 'salary', label: '', amount: '', date: new Date().toISOString().slice(0, 10) });
    await load();
};
const remove = async () => {
    await api.deleteIncome(pendingDelete.value.id);
    api.toast(l.value.inc.deleted);
    pendingDelete.value = null;
    await load();
};
onMounted(load);
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.inc.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.inc.sub(formatCurrency(income.total)) }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.inc.register }}</h2>
            <form class="grid gap-4 md:grid-cols-5" @submit.prevent="submit">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.typeLabel }}</label>
                    <select v-model="form.type" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="salary">{{ l.incType.salary }}</option>
                        <option value="extra">{{ l.incType.extra }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.inc.labelField }}</label>
                    <input v-model="form.label" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400" :placeholder="l.inc.labelField" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.amountLabel }}</label>
                    <input v-model="form.amount" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400" type="number" step="0.01" placeholder="0.00" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.dateLabel }}</label>
                    <input v-model="form.date" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="date" />
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#166a4a] disabled:opacity-50" :disabled="api.loading.value">{{ l.save }}</button>
                </div>
                <p v-for="(fieldErrors, field) in api.errors.value" :key="field" class="text-sm text-red-500 md:col-span-5">{{ fieldErrors[0] }}</p>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
            <div v-for="item in entries" :key="item.id" class="flex items-center justify-between border-b border-slate-100 px-5 py-4 last:border-b-0 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="typeColors[item.type] || 'bg-slate-100 text-slate-700'">
                        {{ l.incType[item.type] || item.type }}
                    </span>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-slate-100">{{ item.label }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ item.date }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <strong class="text-slate-900 dark:text-white">{{ formatCurrency(item.amount) }}</strong>
                    <button class="text-xs font-medium text-red-500 hover:text-red-700" @click="pendingDelete = item">{{ l.delete }}</button>
                </div>
            </div>
            <div v-if="!entries.length && !api.loading.value" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500">{{ l.inc.noData }}</div>
        </div>

        <ConfirmDialog :open="!!pendingDelete" :title="l.inc.deleteTitle" :message="l.inc.deleteMsg" @cancel="pendingDelete = null" @confirm="remove" />
    </section>
</template>
