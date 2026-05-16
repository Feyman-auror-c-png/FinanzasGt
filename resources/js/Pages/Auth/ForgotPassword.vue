<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/composables/useLocale';

defineProps({ status: { type: String } });

const { l } = useLocale();
const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>

<template>
    <GuestLayout>
        <Head :title="l.auth.forgotPassword" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                {{ l.auth.forgotPassword }}
            </h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ l.auth.forgotSub }}
            </p>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" :value="l.auth.emailLabel" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-between">
                <Link :href="route('login')" class="text-sm text-[#1a7f5a] hover:underline">
                    {{ l.auth.backToLogin }}
                </Link>
                <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    {{ l.auth.sendLinkBtn }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
