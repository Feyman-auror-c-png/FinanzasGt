<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

defineOptions({ layout: FinanceLayout });

const palette = ['#1a7f5a', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2'];
const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const goals = ref([]);
const pendingDelete = ref(null);
const funds = reactive({});
const form = reactive({ name: '', target_amount: '', saved_amount: 0, target_date: new Date().toISOString().slice(0, 10), color: palette[0] });

const monthsRemaining = (date) => Math.max(0, Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24 * 30)));
const progress = (goal) => Math.min(100, Math.round((Number(goal.saved_amount) / Number(goal.target_amount)) * 100));
const status = (goal) => {
    if (progress(goal) >= 100) return [l.value.status.completed, 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'];
    if (monthsRemaining(goal.target_date) === 0) return [l.value.status.behind, 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'];
    return [l.value.status.onTrack, 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'];
};
const needed = (goal) => { const m = monthsRemaining(goal.target_date); const r = Number(goal.target_amount) - Number(goal.saved_amount); return m > 0 ? r / m : r; };
const totalSaved = computed(() => goals.value.reduce((s, g) => s + Number(g.saved_amount), 0));
const yearEndProjection = computed(() => goals.value.reduce((s, g) => s + Number(g.year_end_projection || g.saved_amount || 0), 0));

const load = async () => (goals.value = await api.getSavingsGoals());
const submit = async () => { await api.createSavingsGoal(form); api.toast(l.value.sav.savedToast); Object.assign(form, { name: '', target_amount: '', saved_amount: 0, target_date: new Date().toISOString().slice(0, 10), color: palette[0] }); await load(); };
const addFunds = async (goal) => { await api.addSavingsFunds(goal.id, { amount: funds[goal.id] }); api.toast(l.value.sav.fundsToast); funds[goal.id] = ''; await load(); };
const remove = async () => { await api.deleteSavingsGoal(pendingDelete.value.id); api.toast(l.value.sav.deletedToast); pendingDelete.value = null; await load(); };
onMounted(load);
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.sav.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.sav.sub(formatCurrency(totalSaved), formatCurrency(yearEndProjection)) }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.sav.newGoal }}</h2>
            <form class="grid gap-4 md:grid-cols-6" @submit.prevent="submit">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.nameLabel }}</label>
                    <input v-model="form.name" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.nameLabel" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.sav.goalLabel }}</label>
                    <input v-model="form.target_amount" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="number" step="0.01" placeholder="0.00" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.sav.initialLabel }}</label>
                    <input v-model="form.saved_amount" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="number" step="0.01" placeholder="0.00" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.sav.deadlineLabel }}</label>
                    <input v-model="form.target_date" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" type="date" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.sav.colorLabel }}</label>
                    <div class="flex items-center gap-2 pt-1">
                        <button v-for="color in palette" :key="color" type="button" class="h-7 w-7 rounded-full ring-offset-2 transition" :class="form.color === color ? 'ring-2 ring-slate-800 dark:ring-white' : 'opacity-70 hover:opacity-100'" :style="{ backgroundColor: color }" @click="form.color = color"></button>
                    </div>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#166a4a]">{{ l.save }}</button>
                </div>
                <p v-for="(fieldErrors, field) in api.errors.value" :key="field" class="text-sm text-red-500 md:col-span-6">{{ fieldErrors[0] }}</p>
            </form>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <article v-for="goal in goals" :key="goal.id" class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <div class="h-1.5 w-full" :style="{ backgroundColor: goal.color }"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-white">{{ goal.name }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ formatCurrency(goal.saved_amount) }} / {{ formatCurrency(goal.target_amount) }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="status(goal)[1]">{{ status(goal)[0] }}</span>
                    </div>
                    <div class="mt-4 h-2.5 rounded-full bg-slate-100 dark:bg-slate-700">
                        <div class="h-2.5 rounded-full transition-all duration-500" :style="{ width: progress(goal) + '%', backgroundColor: goal.color }"></div>
                    </div>
                    <div class="mt-1.5 flex justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ progress(goal) }}%</span>
                        <span>{{ monthsRemaining(goal.target_date) }} {{ l.sav.months }} · {{ formatCurrency(needed(goal)) }}{{ l.sav.perMonth }}</span>
                    </div>
                    <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 space-y-1 dark:bg-slate-700/50 dark:text-slate-400">
                        <p><span class="font-medium">{{ l.sav.monthField }}:</span> {{ goal.current_savings_month }}</p>
                        <p><span class="font-medium">{{ l.sav.savedMonth }}:</span> {{ formatCurrency(goal.saved_this_month || 0) }}</p>
                        <p><span class="font-medium">{{ l.sav.savedYear }}:</span> {{ formatCurrency(goal.saved_this_year || 0) }}</p>
                        <p><span class="font-medium">{{ l.sav.yearEnd }}:</span> {{ formatCurrency(goal.year_end_projection || goal.saved_amount) }}</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <input v-model="funds[goal.id]" type="number" step="0.01" class="min-w-0 flex-1 rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400" :placeholder="l.sav.addFundsPlaceholder" />
                        <button class="shrink-0 rounded-lg bg-[#1a7f5a] px-3 py-2 text-sm font-medium text-white hover:bg-[#166a4a]" @click="addFunds(goal)">{{ l.sav.addFundsBtn }}</button>
                    </div>
                    <div v-if="goal.contributions?.length" class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-700">
                        <p class="mb-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400">{{ l.sav.contributions }}</p>
                        <p v-for="c in goal.contributions" :key="c.id" class="text-xs text-slate-500 dark:text-slate-500">{{ c.date }} · {{ formatCurrency(c.amount) }}</p>
                    </div>
                    <button class="mt-4 text-xs font-medium text-red-500 hover:text-red-700" @click="pendingDelete = goal">{{ l.sav.deleteGoal }}</button>
                </div>
            </article>
        </div>

        <div v-if="!goals.length && !api.loading.value" class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-500">{{ l.sav.noData }}</div>

        <ConfirmDialog :open="!!pendingDelete" :title="l.sav.deleteTitle" :message="l.sav.deleteMsg" @cancel="pendingDelete = null" @confirm="remove" />
    </section>
</template>
