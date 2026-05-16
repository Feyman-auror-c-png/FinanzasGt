<script setup>
import { onMounted, reactive, ref } from 'vue';
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
const cards = ref([]);
const open = reactive({});
const purchases = reactive({});
const pending = ref(null);
const pendingPurchase = ref(null);
const form = reactive({ name: '', bank: '', credit_limit: '', due_day: 1 });

const used = (card) => Number(card.unpaid_total || 0);
const pct = (card) => Math.min(100, Math.round((used(card) / Number(card.credit_limit)) * 100));

const load = async () => {
    const result = await api.getCreditCards();
    result.forEach((card) => { purchases[card.id] ||= { amount: '', merchant: '', category: 'food', date: new Date().toISOString().slice(0, 10) }; });
    cards.value = result;
};
const submit = async () => { await api.createCreditCard(form); api.toast(l.value.cc.createdToast); Object.assign(form, { name: '', bank: '', credit_limit: '', due_day: 1 }); await load(); };
const addPurchase = async (card) => { await api.createCreditPurchase(card.id, purchases[card.id]); api.toast(l.value.cc.purchaseToast); purchases[card.id] = { amount: '', merchant: '', category: 'food', date: new Date().toISOString().slice(0, 10) }; await load(); };
const pay = async () => { await api.payCreditCard(pending.value.id); api.toast(l.value.cc.paidToast); pending.value = null; await load(); };
const deletePurchase = async () => { await api.deleteCreditPurchase(pendingPurchase.value.card.id, pendingPurchase.value.purchase.id); api.toast(l.value.cc.deletedPurchaseToast); pendingPurchase.value = null; await load(); };
onMounted(load);
</script>

<template>
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.cc.title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.cc.sub }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.cc.register }}</h2>
            <form class="grid gap-4 md:grid-cols-5" @submit.prevent="submit">
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.nameLabel }}</label><input v-model="form.name" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.nameLabel" /></div>
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.cc.bankLabel }}</label><input v-model="form.bank" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.cc.bankLabel" /></div>
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.cc.limitLabel }}</label><input v-model="form.credit_limit" type="number" step="0.01" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="0.00" /></div>
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.cc.dueDayLabel }}</label><input v-model.number="form.due_day" type="number" min="1" max="31" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" /></div>
                <div class="flex items-end"><button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#166a4a]">{{ l.save }}</button></div>
                <p v-for="(fe, f) in api.errors.value" :key="f" class="text-sm text-red-500 md:col-span-5">{{ fe[0] }}</p>
            </form>
        </div>

        <article v-for="card in cards" :key="card.id" class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                <div>
                    <h2 class="font-semibold text-slate-900 dark:text-white">{{ card.name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.bank }} · {{ l.cc.payDay }} {{ card.due_day }}</p>
                </div>
                <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="open[card.id] = !open[card.id]">
                    {{ open[card.id] ? l.cc.hidePurchases : l.cc.showPurchases }}
                </button>
            </div>
            <div class="space-y-4 px-5 py-4">
                <div class="grid gap-3 text-sm sm:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/50"><p class="text-xs text-slate-400 dark:text-slate-500">{{ l.cc.lLimit }}</p><p class="mt-0.5 font-semibold text-slate-800 dark:text-white">{{ formatCurrency(card.credit_limit) }}</p></div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/50"><p class="text-xs text-slate-400 dark:text-slate-500">{{ l.cc.lUsed }}</p><p class="mt-0.5 font-semibold" :class="pct(card) > 80 ? 'text-red-600' : 'text-slate-800 dark:text-white'">{{ formatCurrency(used(card)) }}</p></div>
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/50"><p class="text-xs text-slate-400 dark:text-slate-500">{{ l.cc.lAvailable }}</p><p class="mt-0.5 font-semibold text-slate-800 dark:text-white">{{ formatCurrency(Number(card.credit_limit) - used(card)) }}</p></div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between text-xs text-slate-500 dark:text-slate-400"><span>{{ l.cc.usage }}</span><span :class="pct(card) > 80 ? 'text-red-600 font-semibold' : ''">{{ pct(card) }}%</span></div>
                    <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-700"><div class="h-2.5 rounded-full transition-all duration-500" :class="pct(card) > 80 ? 'bg-red-500' : 'bg-[#1a7f5a]'" :style="{ width: pct(card) + '%' }"></div></div>
                </div>
                <div>
                    <p class="mb-3 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ l.cc.addPurchase }}</p>
                    <div class="grid gap-3 md:grid-cols-5">
                        <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">{{ l.amountLabel }}</label><input v-model="purchases[card.id].amount" type="number" step="0.01" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="0.00" /></div>
                        <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">{{ l.cc.merchantLabel }}</label><input v-model="purchases[card.id].merchant" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.cc.merchantLabel" /></div>
                        <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">{{ l.categoryLabel }}</label>
                            <select v-model="purchases[card.id].category" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ l.cat[cat] }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">{{ l.dateLabel }}</label><input v-model="purchases[card.id].date" type="date" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" /></div>
                        <div class="flex items-end"><button class="w-full rounded-lg bg-[#1a7f5a] px-3 py-2 text-sm font-medium text-white hover:bg-[#166a4a]" @click="addPurchase(card)">{{ l.cc.addBtn }}</button></div>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-3 dark:border-slate-700">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 disabled:opacity-40 dark:bg-slate-600 dark:hover:bg-slate-500" :disabled="used(card) === 0" @click="pending = card">
                        {{ l.cc.markPaid }} · {{ formatCurrency(used(card)) }}
                    </button>
                </div>
            </div>
            <div v-if="open[card.id]" class="border-t border-slate-100 dark:border-slate-700">
                <div v-for="purchase in card.purchases" :key="purchase.id" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 text-sm last:border-b-0 dark:border-slate-700">
                    <div><p class="font-medium text-slate-800 dark:text-slate-100">{{ purchase.merchant }}</p><p class="text-xs text-slate-400 dark:text-slate-500">{{ l.cat[purchase.category] || purchase.category }} · {{ purchase.date }}</p></div>
                    <div class="flex items-center gap-3"><strong class="text-slate-900 dark:text-white">{{ formatCurrency(purchase.amount) }}</strong><button class="text-xs font-medium text-red-500 hover:text-red-700" @click="pendingPurchase = { card, purchase }">{{ l.delete }}</button></div>
                </div>
                <div v-if="!card.purchases?.length" class="px-5 py-6 text-center text-sm text-slate-400 dark:text-slate-500">{{ l.cc.noPurchases }}</div>
            </div>
        </article>

        <div v-if="!cards.length && !api.loading.value" class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-500">{{ l.cc.noCards }}</div>

        <ConfirmDialog :open="!!pending" :title="l.cc.payTitle" :message="pending ? l.cc.payMsg(formatCurrency(used(pending))) : ''" :confirm-text="l.cc.pay" @cancel="pending = null" @confirm="pay" />
        <ConfirmDialog :open="!!pendingPurchase" :title="l.cc.delPurchaseTitle" :message="l.cc.delPurchaseMsg" @cancel="pendingPurchase = null" @confirm="deletePurchase" />
    </section>
</template>
