<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/composables/useLocale';

const { l } = useLocale();

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
const submit = () => form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <GuestLayout>
        <Head :title="l.auth.registerTitle" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ l.auth.registerTitle }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ l.auth.registerSub }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" :value="l.auth.nameLabel" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="email" :value="l.auth.emailLabel" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <InputLabel for="password" :value="l.auth.passwordLabel" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div>
                <InputLabel for="password_confirmation" :value="l.auth.confirmPasswordLabel" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>
            <div class="flex items-center justify-between pt-1">
                <Link :href="route('login')" class="text-sm text-[#1a7f5a] hover:underline">{{ l.auth.alreadyRegistered }}</Link>
                <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing">{{ l.auth.registerBtn }}</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
