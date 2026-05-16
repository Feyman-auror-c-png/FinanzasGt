<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/composables/useLocale';

const { l } = useLocale();

const form = useForm({ password: '' });

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="l.auth.confirmPasswordTitle" />

        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ l.auth.confirmPasswordTitle }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ l.auth.confirmPasswordSub }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="password" :value="l.auth.passwordLabel" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end">
                <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    {{ l.auth.confirmBtn }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
