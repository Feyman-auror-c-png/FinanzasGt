import axios from 'axios';
import { ref } from 'vue';

const toast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('finance-toast', { detail: { message, type } }));
};

const refreshCsrf = () => axios.get('/sanctum/csrf-cookie');

export function useFinanceApi() {
    const loading = ref(false);
    const errors = ref({});

    const request = async (method, url, data = {}, _retried = false) => {
        loading.value = true;
        errors.value = {};

        try {
            const response = await axios({ method, url, data });
            return response.data;
        } catch (error) {
            if (error.response?.status === 419 && !_retried) {
                await refreshCsrf();
                return request(method, url, data, true);
            }
            if (error.response?.status === 422) {
                errors.value = error.response.data.errors || { form: [error.response.data.message] };
                toast('Revisa los campos marcados.', 'error');
            } else if (error.response?.status === 403) {
                toast('No tienes permisos para este recurso.', 'error');
            } else {
                toast('No se pudo completar la acción.', 'error');
            }
            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        errors,
        toast,
        getDashboard: () => request('get', '/api/finance/dashboard'),
        getIncome: () => request('get', '/api/finance/income'),
        createIncome: (data) => request('post', '/api/finance/income', data),
        deleteIncome: (id) => request('delete', `/api/finance/income/${id}`),
        getExpenses: (params = {}) => request('get', `/api/finance/expenses?${new URLSearchParams(params)}`),
        createExpense: (data) => request('post', '/api/finance/expenses', data),
        deleteExpense: (id) => request('delete', `/api/finance/expenses/${id}`),
        getSavingsGoals: () => request('get', '/api/finance/savings-goals'),
        createSavingsGoal: (data) => request('post', '/api/finance/savings-goals', data),
        addSavingsFunds: (id, data) => request('patch', `/api/finance/savings-goals/${id}/add-funds`, data),
        deleteSavingsGoal: (id) => request('delete', `/api/finance/savings-goals/${id}`),
        getCreditCards: () => request('get', '/api/finance/credit-cards'),
        createCreditCard: (data) => request('post', '/api/finance/credit-cards', data),
        createCreditPurchase: (id, data) => request('post', `/api/finance/credit-cards/${id}/purchases`, data),
        payCreditCard: (id) => request('patch', `/api/finance/credit-cards/${id}/pay`),
        deleteCreditPurchase: (id, pid) => request('delete', `/api/finance/credit-cards/${id}/purchases/${pid}`),
        getShoppingLists: () => request('get', '/api/finance/shopping-lists'),
        createShoppingList: (data) => request('post', '/api/finance/shopping-lists', data),
        getShoppingList: (id) => request('get', `/api/finance/shopping-lists/${id}`),
        createShoppingItem: (id, data) => request('post', `/api/finance/shopping-lists/${id}/items`, data),
        checkShoppingItem: (id, iid, data) => request('patch', `/api/finance/shopping-lists/${id}/items/${iid}/check`, data),
        sendShoppingToExpenses: (id) => request('post', `/api/finance/shopping-lists/${id}/send-to-expenses`),
        deleteShoppingList: (id) => request('delete', `/api/finance/shopping-lists/${id}`),
        getMonthlyReport: (params) => request('get', `/api/finance/reports/monthly?${new URLSearchParams(params)}`),
    };
}
