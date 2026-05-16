<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import FinanceLayout from '../../Layouts/FinanceLayout.vue';
import { useCurrency } from '../../composables/useCurrency';
import { useFinanceApi } from '../../composables/useFinanceApi';
import { useLocale } from '../../composables/useLocale';

defineOptions({ layout: FinanceLayout });

const categories = ['produce', 'dairy', 'meat', 'pantry', 'cleaning', 'other'];
const api = useFinanceApi();
const { formatCurrency } = useCurrency();
const { l } = useLocale();
const lists = ref([]);
const active = ref(null);
const pendingDelete = ref(null);
const listForm = reactive({ name: '' });
const itemForm = reactive({ name: '', estimated_price: '', quantity: 1, category: 'produce' });

const checkedItems = computed(() => active.value?.items?.filter((i) => i.checked_at && i.actual_price !== null && i.actual_price !== '') || []);
const subtotal = computed(() => checkedItems.value.reduce((s, i) => s + Number(i.actual_price) * Number(i.quantity), 0));
const canSend = computed(() => checkedItems.value.length > 0);

const loadLists = async () => (lists.value = await api.getShoppingLists());
const selectList = async (id) => (active.value = await api.getShoppingList(id));
const createList = async () => { const list = await api.createShoppingList(listForm); api.toast(l.value.shop.createdToast); listForm.name = ''; await loadLists(); await selectList(list.id); };
const createItem = async () => { await api.createShoppingItem(active.value.id, itemForm); api.toast(l.value.shop.itemToast); Object.assign(itemForm, { name: '', estimated_price: '', quantity: 1, category: 'produce' }); await selectList(active.value.id); await loadLists(); };
const toggle = async (item) => { await api.checkShoppingItem(active.value.id, item.id, { actual_price: item.actual_price }); await selectList(active.value.id); };
const send = async () => { await api.sendShoppingToExpenses(active.value.id); api.toast(l.value.shop.sentToast); await selectList(active.value.id); };
const remove = async () => { await api.deleteShoppingList(pendingDelete.value.id); api.toast(l.value.shop.deletedToast); pendingDelete.value = null; active.value = null; await loadLists(); };
onMounted(loadLists);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-[300px_1fr]">
        <div class="space-y-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ l.shop.title }}</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ l.shop.sub }}</p>
            </div>
            <form class="flex gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-800" @submit.prevent="createList">
                <input v-model="listForm.name" class="min-w-0 flex-1 rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400" :placeholder="l.shop.newListPlaceholder" />
                <button class="shrink-0 rounded-lg bg-[#1a7f5a] px-3 py-2 text-sm font-medium text-white hover:bg-[#166a4a]">{{ l.shop.createBtn }}</button>
            </form>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <button v-for="list in lists" :key="list.id" class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3.5 text-left transition last:border-b-0 dark:border-slate-700" :class="active?.id === list.id ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'hover:bg-slate-50 dark:hover:bg-slate-700'" @click="selectList(list.id)">
                    <span>
                        <strong class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ list.name }}</strong>
                        <small class="text-xs text-slate-400 dark:text-slate-500">{{ list.items_count }} {{ list.items_count !== 1 ? (l === l ? 'artículos' : 'items') : (l === l ? 'artículo' : 'item') }}</small>
                    </span>
                    <button class="text-xs font-medium text-red-500 hover:text-red-700" @click.stop="pendingDelete = list">{{ l.delete }}</button>
                </button>
                <div v-if="!lists.length" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">{{ l.shop.noLists }}</div>
            </div>
        </div>

        <div v-if="active" class="space-y-5">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ active.name }}</h2>
            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.shop.addItemTitle }}</h3>
                <form class="grid gap-4 md:grid-cols-5" @submit.prevent="createItem">
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.shop.itemLabel }}</label><input v-model="itemForm.name" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.shop.itemLabel" /></div>
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.shop.estPriceLabel }}</label><input v-model="itemForm.estimated_price" type="number" step="0.01" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="0.00" /></div>
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.shop.quantityLabel }}</label><input v-model.number="itemForm.quantity" type="number" min="1" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" /></div>
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ l.categoryLabel }}</label>
                        <select v-model="itemForm.category" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ l.shopCat[cat] }}</option>
                        </select>
                    </div>
                    <div class="flex items-end"><button class="w-full rounded-lg bg-[#1a7f5a] px-4 py-2 text-sm font-medium text-white hover:bg-[#166a4a]">{{ l.add }}</button></div>
                </form>
            </div>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-slate-800">
                <div v-for="item in active.items" :key="item.id" class="grid gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 dark:border-slate-700 md:grid-cols-[1fr_180px]">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" class="mt-1 rounded border-slate-300 text-[#1a7f5a]" :checked="!!item.checked_at" @change="toggle(item)" />
                        <span>
                            <strong class="block text-sm font-medium" :class="item.checked_at ? 'text-slate-400 line-through dark:text-slate-500' : 'text-slate-800 dark:text-slate-100'">{{ item.name }}</strong>
                            <small class="text-xs text-slate-400 dark:text-slate-500">{{ l.shopCat[item.category] || item.category }} · {{ item.quantity }} × {{ formatCurrency(item.estimated_price) }}</small>
                        </span>
                    </label>
                    <input v-if="item.checked_at" v-model="item.actual_price" type="number" step="0.01" class="rounded-lg border-slate-300 bg-white text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white" :placeholder="l.shop.estPriceLabel" @change="toggle(item)" />
                </div>
                <div v-if="!active.items.length" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500">{{ l.shop.noItems }}</div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-slate-800">
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">{{ l.shop.receipt }}</h3>
                <div v-if="checkedItems.length" class="mt-3 space-y-2">
                    <div v-for="item in checkedItems" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">{{ item.name }} ×{{ item.quantity }}</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ formatCurrency(Number(item.actual_price) * Number(item.quantity)) }}</span>
                    </div>
                </div>
                <div v-else class="mt-3 text-sm text-slate-400 dark:text-slate-500">{{ l.shop.checkedHint }}</div>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-700">
                    <strong class="text-slate-800 dark:text-slate-100">{{ l.shop.subtotal }}</strong>
                    <strong class="text-lg text-slate-900 dark:text-white">{{ formatCurrency(subtotal) }}</strong>
                </div>
                <button class="mt-4 w-full rounded-lg bg-[#1a7f5a] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#166a4a] disabled:cursor-not-allowed disabled:bg-slate-300 dark:disabled:bg-slate-600" :disabled="!canSend" @click="send">{{ l.shop.sendBtn }}</button>
            </div>
        </div>

        <div v-else class="flex items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-500">{{ l.shop.selectOrCreate }}</div>

        <ConfirmDialog :open="!!pendingDelete" :title="l.shop.deleteTitle" :message="l.shop.deleteMsg" @cancel="pendingDelete = null" @confirm="remove" />
    </section>
</template>
