<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/composables/useLocale';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const { l } = useLocale();

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout>
        <Head :title="l.auth.loginTitle" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ l.auth.loginTitle }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ l.auth.loginSub }}</p>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ status }}</div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" :value="l.auth.emailLabel" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <InputLabel for="password" :value="l.auth.passwordLabel" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ l.auth.rememberLabel }}</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-[#1a7f5a] hover:underline">{{ l.auth.forgotPassword }}</Link>
            </div>
            <PrimaryButton class="w-full justify-center" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">{{ l.auth.loginBtn }}</PrimaryButton>
        </form>
    </GuestLayout>
</template>
