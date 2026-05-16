import { ref, watchEffect } from 'vue';

const dark = ref(localStorage.getItem('theme') === 'dark');

watchEffect(() => {
    if (dark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
});

export function useDarkMode() {
    const toggle = () => (dark.value = !dark.value);
    return { dark, toggle };
}
