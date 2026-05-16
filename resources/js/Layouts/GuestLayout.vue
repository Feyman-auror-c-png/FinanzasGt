<script setup>
import { Link } from '@inertiajs/vue3';
import { useDarkMode } from '../composables/useDarkMode';
import { useLocale } from '../composables/useLocale';

const { dark, toggle: toggleDark } = useDarkMode();
const { locale, toggleLocale } = useLocale();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-12 dark:bg-slate-900">
        <!-- Controles en esquina superior -->
        <div class="fixed right-4 top-4 flex items-center gap-2">
            <button class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="toggleLocale">
                {{ locale === 'es' ? 'EN' : 'ES' }}
            </button>
            <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="toggleDark">
                <svg v-if="dark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
            </button>
        </div>

        <div class="w-full max-w-md space-y-6">
            <div class="text-center">
                <Link href="/" class="inline-flex flex-col items-center gap-3">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#1a7f5a] shadow-lg">
                        <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </span>
                    <span class="text-2xl font-bold text-slate-900 dark:text-white">Finanzas GT</span>
                </Link>
            </div>
            <div class="rounded-2xl bg-white px-8 py-8 shadow-sm dark:bg-slate-800">
                <slot />
            </div>
        </div>
    </div>
</template>
