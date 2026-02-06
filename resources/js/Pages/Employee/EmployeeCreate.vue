<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import GenericModal from "@/Components/GenericModal.vue";
import {useToast} from "vue-toastification";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import Card from "@/Components/Card.vue";
import {inject} from "vue";
import {__} from "@/Composables/useTranslations.js";
import dayjs from "dayjs";
import { UserPlusIcon, IdentificationIcon, PhoneIcon, EnvelopeIcon, MapPinIcon, CalendarIcon, BriefcaseIcon, ShieldCheckIcon, LockClosedIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    shifts: Object,
    roles: Object,
})

const form = useForm({
    name: '',
    ic_number: '',
    email: '',
    phone: '',
    address: '',
    hired_on: new Date(),
    role: '',
    password: '',
});

const submit = () => {
    form.hired_on = dayjs(form.hired_on).format('YYYY-MM-DD');
    form.post(route('employees.store'), {
        preserveScroll: true,
        onError: () => {
            useToast().error(__('Error Creating Employee'));
        },
        onSuccess: () => {
            useToast().success(__('Employee Created Successfully'));
        },
    });
};

</script>

<template>
    <Head :title="__('Employee Registration')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs/>
        </template>
        
        <div class="py-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 animate-fade-in-up">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black text-white tracking-tight">{{ __('Register New Employee') }}</h1>
                    <p class="text-gray-500 text-sm font-medium">{{ __('Onboard a new member to the organization with their essential details.') }}</p>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-2 bg-white/5 rounded-2xl border border-white/5 shadow-inner">
                    <div class="w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-600/20">
                        <UserPlusIcon class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <Card variant="glass" class="!mt-0 overflow-hidden relative group">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-red-600/5 rounded-full blur-3xl -mr-32 -mt-32"></div>
                    
                    <div class="p-8 md:p-10 relative space-y-12">
                        
                        <!-- Section: Personal Identity -->
                        <div class="space-y-8">
                            <div class="flex items-center gap-3 pb-4 border-b border-white/5">
                                <IdentificationIcon class="w-5 h-5 text-red-500" />
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ __('Personal Identity') }}</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <InputLabel for="name" :value="__('Full Name')"/>
                                    <TextInput id="name" type="text" v-model="form.name" required autofocus autocomplete="name" :placeholder="__('e.g. Ahmad Razif')" />
                                    <InputError :message="form.errors.name"/>
                                </div>
                                <div class="space-y-2">
                                    <InputLabel for="ic_number" :value="__('National ID (IC)')"/>
                                    <TextInput id="ic_number" type="number" v-model="form.ic_number" required :placeholder="__('e.g. 900101145522')" />
                                    <InputError :message="form.errors.ic_number"/>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Contact Information -->
                        <div class="space-y-8">
                            <div class="flex items-center gap-3 pb-4 border-b border-white/5">
                                <MapPinIcon class="w-5 h-5 text-red-500" />
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ __('Contact Information') }}</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <InputLabel for="phone" :value="__('Phone Number')"/>
                                    <TextInput id="phone" type="text" v-model="form.phone" required :placeholder="__('e.g. 0123456789')" />
                                    <InputError :message="form.errors.phone"/>
                                </div>
                                <div class="space-y-2">
                                    <InputLabel for="email" :value="__('Email Address')"/>
                                    <TextInput id="email" type="email" v-model="form.email" required :placeholder="__('e.g. ahmad.razif@mail.com')" />
                                    <InputError :message="form.errors.email"/>
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <InputLabel for="address" :value="__('Residential Address')"/>
                                    <TextInput id="address" type="text" v-model="form.address" required :placeholder="__('House No, Street Name, City, State, Postcode')" />
                                    <InputError :message="form.errors.address"/>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Employment Terms -->
                        <div class="space-y-8">
                            <div class="flex items-center gap-3 pb-4 border-b border-white/5">
                                <BriefcaseIcon class="w-5 h-5 text-red-500" />
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ __('Employment Terms') }}</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <InputLabel for="hired_on" :value="__('Hire Date')"/>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 font-bold opacity-30 group-focus-within:opacity-100 transition-opacity">
                                            <CalendarIcon class="w-4 h-4 text-red-500" />
                                        </div>
                                        <VueDatePicker
                                            id="hired_on"
                                            v-model="form.hired_on"
                                            class="datepicker-modern"
                                            :enable-time-picker="false"
                                            :placeholder="__('Select Date...')"
                                            :dark="inject('isDark').value"
                                            required
                                        ></VueDatePicker>
                                    </div>
                                    <InputError :message="form.errors.hired_on"/>
                                </div>
                                <div class="space-y-2 font-bold">
                                    <InputLabel for="role" :value="__('Permission Level')"/>
                                    <div class="relative group">
                                         <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10">
                                            <ShieldCheckIcon class="w-4 h-4 text-red-500 opacity-40 group-focus-within:opacity-100 transition-opacity" />
                                        </div>
                                        <select id="role" 
                                                v-model="form.role"
                                                class="block w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm text-gray-100 appearance-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500/50 transition-all outline-none"
                                                required>
                                            <option value="" disabled selected>{{ __('Select Permission Level') }}</option>
                                            <option value="admin" class="bg-gray-900">{{ __('Supervisor') }}</option>
                                            <option value="employee" class="bg-gray-900">{{ __('Standard Employee') }}</option>
                                        </select>
                                    </div>
                                    <InputError :message="form.errors.role"/>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Account Security -->
                        <div class="space-y-8">
                            <div class="flex items-center gap-3 pb-4 border-b border-white/5">
                                <LockClosedIcon class="w-5 h-5 text-red-500" />
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-white">{{ __('Account Security') }}</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <InputLabel for="password" :value="__('Default Password')"/>
                                    <TextInput id="password" type="password" v-model="form.password" required :placeholder="__('Minimum 8 characters')" autocomplete="new-password" />
                                    <InputError :message="form.errors.password"/>
                                    <p class="text-xs text-gray-500">{{ __('Employee will use this password for their first login.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="p-8 bg-white/[0.02] border-t border-white/5 flex items-center justify-between gap-6 backdrop-blur-md">
                        <div class="flex items-center gap-2 opacity-30">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Registration Stage') }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="submit" 
                                    :disabled="form.processing"
                                    class="flex items-center gap-2 px-10 py-3.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all shadow-xl shadow-red-600/30 active:scale-95 disabled:opacity-50">
                                <UserPlusIcon class="w-4 h-4" />
                                {{ __('Complete Registration') }}
                            </button>
                        </div>
                    </div>
                </Card>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.datepicker-modern :deep(.dp__input) {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 1rem !important;
    padding: 0.625rem 1rem 0.625rem 2.75rem !important;
    font-size: 0.875rem !important;
    color: #f3f4f6 !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.datepicker-modern :deep(.dp__input:focus) {
    border-color: rgba(239, 68, 68, 0.5) !important;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
}

.datepicker-modern :deep(.dp__theme_dark) {
    --dp-background-color: #111827;
    --dp-text-color: #f3f4f6;
    --dp-hover-color: #1f2937;
    --dp-primary-color: #ef4444;
}
</style>
