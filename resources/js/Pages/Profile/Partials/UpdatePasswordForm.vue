<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import { KeyIcon, ShieldCheckIcon, LockOpenIcon } from '@heroicons/vue/24/outline';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-red-500 mb-2">{{__('Update Password')}}</h2>

            <p class="mt-1 text-sm text-gray-300">
                {{__('Ensure your account is using a long, random password to stay secure')}}.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-8 space-y-6">
            <div class="space-y-1.5">
                <InputLabel for="current_password" class="flex items-center gap-2">
                    <KeyIcon class="w-4 h-4 text-red-500/60" />
                    {{__('Current Password')}}
                </InputLabel>

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div class="space-y-1.5">
                <InputLabel for="password" class="flex items-center gap-2">
                    <LockOpenIcon class="w-4 h-4 text-red-500/60" />
                    {{__('New Password')}}
                </InputLabel>

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="space-y-1.5">
                <InputLabel for="password_confirmation" class="flex items-center gap-2">
                    <ShieldCheckIcon class="w-4 h-4 text-red-500/60" />
                    {{__('Confirm Password')}}
                </InputLabel>

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton :disabled="form.processing" class="!bg-[#18181b] !border !border-white/10 hover:!border-red-500/50 !rounded-xl !px-8 !py-3 flex items-center gap-2 transition-all shadow-lg hover:shadow-red-500/10">
                     <span v-if="form.processing">{{ __('Updating...') }}</span>
                     <span v-else>{{ __('Change Password') }}</span>
                </PrimaryButton>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-400">{{__('Updated')}}.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
