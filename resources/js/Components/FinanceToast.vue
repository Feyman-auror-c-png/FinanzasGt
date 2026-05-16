<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const messages = ref([]);
let nextId = 1;

const push = (event) => {
    const item = { id: nextId++, type: event.detail.type || 'success', message: event.detail.message };
    messages.value.push(item);
    window.setTimeout(() => {
        messages.value = messages.value.filter((m) => m.id !== item.id);
    }, 3000);
};

onMounted(() => window.addEventListener('finance-toast', push));
onUnmounted(() => window.removeEventListener('finance-toast', push));
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 top-4 z-50 space-y-2">
            <div
                v-for="msg in messages"
                :key="msg.id"
                class="rounded-lg px-4 py-3 text-sm font-medium shadow-lg"
                :class="msg.type === 'error' ? 'bg-red-600 text-white' : 'bg-[#1a7f5a] text-white'"
            >
                {{ msg.message }}
            </div>
        </div>
    </Teleport>
</template>
