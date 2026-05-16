<script setup>
import { Bar, Doughnut } from 'vue-chartjs';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, ArcElement, Tooltip } from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

ChartJS.register(BarElement, CategoryScale, LinearScale, ArcElement, Tooltip, Legend);

defineOptions({ layout: FinanceLayout });

const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const data = ref(null);
const colors = ['#1a7f5a', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2', '#be123c', '#65a30d'];

const totalCommitted = computed(() => Number(data.value?.total_expenses || 0) + Number(data.value?.monthly_savings || 0));
const hasData = computed(() => data.value && (data.value.total_income > 0 || data.value.total_expenses > 0 || data.value.monthly_savings > 0));
const warning = computed(() => data.value?.total_income > 0 && totalCommitted.value > data.value.total_income * 0.8);

const donutData = computed(() => ({
    labels: data.value?.expenses_by_category?.map((item) => l.value.cat[item.category] || item.category) || [],
    datasets: [{ data: data.value?.expenses_by_category?.map((item) => Number(item.total)) || [], backgroundColor: colors }],
}));
const barData = computed(() => ({
    labels: data.value?.monthly_chart_data?.map((item) => item.label) || [],
    datasets: [
        { label: l.value.dash.incLabel, data: data.value?.monthly_chart_data?.map((item) => item.income) || [], backgroundColor: '#1a7f5a' },
        { label: l.value.dash.expLabel, data: data.value?.monthly_chart_data?.map((item) => item.expenses) || [], backgroundColor: '#ef4444' },
        { label: l.value.dash.savLabel, data: data.value?.monthly_chart_data?.map((item) => item.savings) || [], backgroundColor: '#2563eb' },
    ],
}));
const barOptions = { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } } };

onMounted(async () => { data.value = await api.getDashboard(); });
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.dash.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.dash.sub }}</p>
        </div>

        <div v-if="api.loading.value" class="flex items-center justify-center py-16">
            <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-[#1a7f5a]"></div>
        </div>

        <div v-else-if="!hasData" class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-600 dark:bg-slate-800">
            <p class="text-slate-500 dark:text-slate-400">{{ l.dash.noData }}</p>
            <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ l.dash.noDataSub }}</p>
        </div>

        <template v-else>
            <div v-if="warning" class="flex items-start gap-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 dark:border-orange-900/50 dark:bg-orange-900/20">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-medium text-orange-800 dark:text-orange-300">{{ l.dash.warning }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border-l-4 border-l-emerald-500 bg-white p-5 shadow-sm dark:bg-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ l.dash.monthlyIncome }}</p>
                    <strong class="mt-2 block text-2xl font-bold text-slate-900 dark:text-white">{{ formatCurrency(data.total_income) }}</strong>
                </div>
                <div class="rounded-xl border-l-4 border-l-red-500 bg-white p-5 shadow-sm dark:bg-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ l.dash.monthlyExpenses }}</p>
                    <strong class="mt-2 block text-2xl font-bold text-slate-900 dark:text-white">{{ formatCurrency(data.total_expenses) }}</strong>
                </div>
                <div class="rounded-xl border-l-4 border-l-blue-500 bg-white p-5 shadow-sm dark:bg-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ l.dash.monthlySavings }}</p>
                    <strong class="mt-2 block text-2xl font-bold text-slate-900 dark:text-white">{{ formatCurrency(data.monthly_savings) }}</strong>
                </div>
                <div class="rounded-xl border-l-4 border-l-violet-500 bg-white p-5 shadow-sm dark:bg-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ l.dash.balance }}</p>
                    <strong class="mt-2 block text-2xl font-bold text-slate-900 dark:text-white">{{ formatCurrency(data.balance) }}</strong>
                </div>
                <div class="rounded-xl border-l-4 border-l-amber-500 bg-white p-5 shadow-sm dark:bg-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ l.dash.totalSaved }}</p>
                    <strong class="mt-2 block text-2xl font-bold text-slate-900 dark:text-white">{{ formatCurrency(data.savings_goals_summary.total_saved) }}</strong>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                <h2 class="font-semibold text-slate-800 dark:text-slate-100">{{ l.dash.projection }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400" v-html="l.dash.projText(
                    data.savings_goals_summary.current_savings_month,
                    `<strong class='text-slate-900 dark:text-white'>${formatCurrency(data.savings_goals_summary.monthly_savings)}</strong>`,
                    `<strong class='text-[#1a7f5a]'>${formatCurrency(data.savings_goals_summary.year_end_projection)}</strong>`
                )"></p>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                    <h2 class="font-semibold text-slate-800 dark:text-slate-100">{{ l.dash.byCategory }}</h2>
                    <div class="mx-auto mt-4 max-w-[240px]"><Doughnut :data="donutData" :options="{ plugins: { legend: { display: false } } }" /></div>
                    <div class="mt-4 grid grid-cols-2 gap-y-2 gap-x-3 text-sm">
                        <span v-for="(item, index) in data.expenses_by_category" :key="item.category" class="flex items-center gap-2">
                            <i class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: colors[index % colors.length] }"></i>
                            <span class="truncate text-slate-600 dark:text-slate-400">{{ l.cat[item.category] || item.category }}</span>
                        </span>
                    </div>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                    <h2 class="font-semibold text-slate-800 dark:text-slate-100">{{ l.dash.last6 }}</h2>
                    <div class="mt-4"><Bar :data="barData" :options="barOptions" /></div>
                </div>
            </div>
        </template>
    </section>
</template>
