<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showPassword = ref(false);
const form = useForm({
    email: ref(''),
    password: ref(''),
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Log in" />
  <div
    class="min-h-screen w-full flex items-center justify-center bg-cover bg-center"
    :style="{ backgroundImage: 'url(/images/backgroundgym.png)' }"
  >
    <div class="flex flex-col md:flex-row gap-8 bg-transparent">
      <!-- Left: Login Form -->
      <div class="backdrop-blur-md bg-white/30 rounded-2xl shadow-2xl p-10 w-[350px] flex flex-col justify-center">
        <h2 class="text-2xl font-bold text-center text-white mb-6">Login</h2>
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <InputLabel for="email" value="Email" class="text-white" />
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
          <div class="relative">
            <InputLabel for="password" value="Password" class="text-white" />
            <TextInput
              id="password"
              :type="showPassword ? 'text' : 'password'"
              class="mt-1 block w-full pr-10"
              v-model="form.password"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-10 text-gray-500 focus:outline-none"
              tabindex="-1"
              aria-label="Toggle password visibility"
            >
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.671-2.634A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.293 5.03M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6.364 6.364L19.07 4.93" />
              </svg>
            </button>
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <button
            type="submit"
            class="w-full py-3 rounded-lg bg-gradient-to-r from-red-700 to-red-500 text-white font-semibold shadow-lg hover:from-red-800 hover:to-red-600 transition"
            :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
          >
            Login
          </button>
        </form>
      </div>
      <!-- Right: Welcome Panel -->
      <div class="hidden md:flex flex-col items-center justify-center backdrop-blur-md bg-white/20 rounded-2xl shadow-2xl p-10 w-[350px]">
        <img src="/images/gymlogo.png" alt="Gym Logo" class="h-20 mb-6" />
        <h2 class="text-2xl font-bold text-white mb-2">Hi, Welcome Back!</h2>
      </div>
    </div>
  </div>
</template>
