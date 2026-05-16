export function useCurrency() {
    const formatCurrency = (amount) => {
        const value = Number(amount || 0);
        return `Q ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    return { formatCurrency };
}
