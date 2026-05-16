<script setup>
defineProps({
    open: Boolean,
    title: { type: String, default: 'Confirmar acción' },
    message: { type: String, default: 'Esta acción no se puede deshacer.' },
    confirmText: { type: String, default: 'Confirmar' },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ title }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ message }}</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700" @click="emit('cancel')">Cancelar</button>
                    <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700" @click="emit('confirm')">{{ confirmText }}</button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
