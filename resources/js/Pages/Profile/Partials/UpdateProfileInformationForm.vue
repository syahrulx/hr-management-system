<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";
import {
    UserCircleIcon,
    PhoneIcon,
    MapPinIcon,
    EnvelopeIcon,
    CheckBadgeIcon,
} from "@heroicons/vue/24/outline";
import { __ } from "@/Composables/useTranslations.js";

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: Object,
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    address: props.user.address,
    ic_number: props.user.ic_number,
});

import { useToast } from "vue-toastification";

const toast = useToast();

onMounted(() => {
    const page = usePage();
    console.log('[DEBUG] UpdateProfileForm mounted - flash props:', JSON.stringify(page.props.flash));
    console.log('[DEBUG] UpdateProfileForm mounted - status prop:', props.status);
});

const submitForm = () => {
    console.log('[DEBUG] Form submitting...');
    form.patch(route('profile.update'), {
        preserveState: false,
        onSuccess: (page) => {
            console.log('[DEBUG] Form onSuccess - page props:', JSON.stringify(page.props?.flash));
        },
        onError: (errors) => {
            console.log('[DEBUG] Form onError:', JSON.stringify(errors));
            toast.error(__('Missing or incorrect required information!'));
        },
        onFinish: () => {
            console.log('[DEBUG] Form onFinish');
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-red-500 mb-2">
                {{ __("Profile Information") }}
            </h2>

            <p class="mt-1 text-sm text-gray-300">
                {{
                    __(
                        "Update your account's profile information and email address"
                    )
                }}.
            </p>
        </header>

        <form
            @submit.prevent="submitForm"
            class="mt-8 space-y-8"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Name -->
                <div class="space-y-1.5">
                    <InputLabel for="name" class="flex items-center gap-2">
                        <UserCircleIcon class="w-4 h-4 text-red-500/60" />
                        {{ __("Full Name") }}
                    </InputLabel>
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Email (Read-only) -->
                <div class="space-y-1.5">
                    <InputLabel for="email" class="flex items-center gap-2">
                        <EnvelopeIcon class="w-4 h-4 text-red-500/60" />
                        {{ __("Email Address") }}
                        <span
                            class="text-[10px] text-gray-500 uppercase tracking-wider"
                            >({{ __("Read Only") }})</span
                        >
                    </InputLabel>
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full !bg-white/[0.02] !text-gray-500 cursor-not-allowed"
                        v-model="form.email"
                        disabled
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <InputLabel for="phone" class="flex items-center gap-2">
                        <PhoneIcon class="w-4 h-4 text-red-500/60" />
                        {{ __("Phone Number") }}
                    </InputLabel>
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        required
                        autocomplete="tel"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <!-- IC Number -->
                <div class="space-y-1.5">
                    <InputLabel for="ic_number" class="flex items-center gap-2">
                        <CheckBadgeIcon class="w-4 h-4 text-red-500/60" />
                        {{ __("IC Number") }}
                    </InputLabel>
                    <TextInput
                        id="ic_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.ic_number"
                        required
                        autocomplete="ic-number"
                    />
                    <InputError class="mt-2" :message="form.errors.ic_number" />
                </div>
            </div>

            <!-- Address (Full Width) -->
            <div class="space-y-1.5">
                <InputLabel for="address" class="flex items-center gap-2">
                    <MapPinIcon class="w-4 h-4 text-red-500/60" />
                    {{ __("Mailing Address") }}
                </InputLabel>
                <TextInput
                    id="address"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.address"
                    required
                    autocomplete="street-address"
                />
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-200">
                    {{ __("Your email address is unverified") }}.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-400 hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        {{
                            __("Click here to re-send the verification email")
                        }}.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    {{
                        __(
                            "A new verification link has been sent to your email address"
                        )
                    }}.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/5">
                <PrimaryButton
                    :disabled="form.processing"
                    class="!bg-red-600 hover:!bg-red-700 !rounded-xl !px-8 !py-3 flex items-center gap-2 transition-all"
                >
                    <span v-if="form.processing">{{ __("Saving...") }}</span>
                    <span v-else>{{ __("Update Profile") }}</span>
                </PrimaryButton>

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                    class="transition ease-in-out"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-emerald-400 flex items-center gap-2"
                    >
                        <CheckBadgeIcon class="w-4 h-4" />
                        {{ __("Saved successfully") }}.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
