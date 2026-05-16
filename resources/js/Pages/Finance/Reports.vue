<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

defineOptions({ layout: FinanceLayout });

const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const now = new Date();
const filters = reactive({ month: now.getMonth() + 1, year: now.getFullYear() });
const report = ref(null);

const months = computed(() => l.value.months.map((label, i) => ({ value: i + 1, label })));
const currentMonthLabel = computed(() => months.value.find((m) => m.value === filters.month)?.label || filters.month);

const load = async () => (report.value = await api.getMonthlyReport(filters));
const copy = async () => {
    const lines = [
        l.value.rep.reportHeader(currentMonthLabel.value, filters.year),
        `${l.value.rep.rowIncome}: ${formatCurrency(report.value.income_total)}`,
        ...report.value.expenses_by_category.map((row) => `${l.value.cat[row.category] || row.category}: ${formatCurrency(row.actual)}`),
        `${l.value.rep.rowSavings}: ${formatCurrency(report.value.savings_contributions)}`,
        `${l.value.rep.rowBalance}: ${formatCurrency(report.value.balance)}`,
    ];
    await navigator.clipboard.writeText(lines.join('\n'));
    api.toast(l.value.rep.copiedToast);
};
onMounted(load);
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.rep.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.rep.sub }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.rep.period }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.monthLabel }}</label>
                    <select v-model.number="filters.month" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.yearLabel }}</label>
                    <input v-model.number="filters.year" type="number" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" />
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white hover:bg-[#166a4a]" @click="load">{{ l.rep.viewBtn }}</button>
                </div>
            </div>
        </div>

        <div v-if="report" class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">{{ currentMonthLabel }} {{ filters.year }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-700/50 dark:text-slate-400">
                            <th class="px-5 py-3">{{ l.rep.colCat }}</th>
                            <th class="px-5 py-3 text-right">{{ l.rep.colBudgeted }}</th>
                            <th class="px-5 py-3 text-right">{{ l.rep.colActual }}</th>
                            <th class="px-5 py-3 text-right">{{ l.rep.colDiff }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="row in report.expenses_by_category" :key="row.category" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300">{{ l.cat[row.category] || row.category }}</td>
                            <td class="px-5 py-3 text-right text-slate-600 dark:text-slate-400">{{ formatCurrency(row.budgeted) }}</td>
                            <td class="px-5 py-3 text-right text-slate-800 dark:text-slate-200">{{ formatCurrency(row.actual) }}</td>
                            <td class="px-5 py-3 text-right font-medium" :class="Number(row.difference) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">{{ formatCurrency(row.difference) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 dark:border-slate-600">
                        <tr class="bg-slate-50 font-semibold dark:bg-slate-700/50">
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ l.rep.rowIncome }}</td><td class="px-5 py-3"></td>
                            <td class="px-5 py-3 text-right text-emerald-700 dark:text-emerald-400">{{ formatCurrency(report.income_total) }}</td><td class="px-5 py-3"></td>
                        </tr>
                        <tr class="font-semibold">
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ l.rep.rowSavings }}</td><td class="px-5 py-3"></td>
                            <td class="px-5 py-3 text-right text-blue-700 dark:text-blue-400">{{ formatCurrency(report.savings_contributions) }}</td><td class="px-5 py-3"></td>
                        </tr>
                        <tr class="bg-slate-50 font-bold dark:bg-slate-700/50">
                            <td class="px-5 py-3 text-slate-900 dark:text-white">{{ l.rep.rowBalance }}</td><td class="px-5 py-3"></td>
                            <td class="px-5 py-3 text-right text-lg" :class="Number(report.balance) >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'">{{ formatCurrency(report.balance) }}</td><td class="px-5 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="flex justify-end border-t border-slate-100 px-5 py-4 dark:border-slate-700">
                <button class="flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="copy">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                    {{ l.rep.copyBtn }}
                </button>
            </div>
        </div>

        <div v-else-if="api.loading.value" class="flex items-center justify-center py-12">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-[#1a7f5a]"></div>
        </div>
    </section>
</template>
