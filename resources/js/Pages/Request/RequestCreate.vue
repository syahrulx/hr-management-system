<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import {useToast} from "vue-toastification";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import '@vuepic/vue-datepicker/dist/main.css'
import dayjs from "dayjs";
import Card from "@/Components/Card.vue";
import {inject, watch} from "vue";
import {__} from "@/Composables/useTranslations.js";
import { CalendarDaysIcon, InformationCircleIcon, PaperAirplaneIcon, ChartBarIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    types: Array,
    leaveBalances: Array,
})

const leaveTypes = ['Annual Leave', 'Emergency Leave', 'Sick Leave'];

const form = useForm({
    type: '',
    date: '',
    remark: '',
});

watch(() => form.type, (value) => {
    if (value === 'leave')
        form.date = '';
});

const submitForm = () => {
    Object.keys(form.date).forEach(function (key) {
        if (form.date[key] && !/^\d{4}-\d{2}-\d{2}$/.test(form.date[key])){
            form.date[key] = dayjs(form.date[key]).format('YYYY-MM-DD');
        }
    });
    form.post(route('requests.store'), {
        preserveScroll: true,
        onError: () => {
            if (usePage().props.errors.past_leave){
                useToast().error(usePage().props.errors.past_leave);
            } else {
                useToast().error(__('Error Creating Request'));
            }
        },
        onSuccess: () => {
            useToast().success(__('Request Created Successfully'));
            form.reset();
        }
    });
};

</script>

<template>
    <Head :title="__('Request Leave')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <ReqTabs />
        </template>
        
        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- SIDEBAR: LEAVE BALANCES -->
                <aside class="lg:col-span-3 space-y-6">
                    <Card variant="glass" class="!mt-0 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <ChartBarIcon class="w-32 h-32 text-white" />
                        </div>
                        
                        <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <CalendarDaysIcon class="w-5 h-5 text-red-500" />
                            {{ __('Leave Balances') }}
                        </h2>
                        <div class="space-y-6">
                            <div v-for="type in leaveTypes" :key="type" class="group/item">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-white">{{ __(type) }}</span>
                                        <span class="text-[10px] uppercase tracking-wider text-gray-500">{{ __('Remaining Days') }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-black text-red-500">
                                            {{ leaveBalances && leaveBalances.find(l => l.leave_type === type) ? leaveBalances.find(l => l.leave_type === type).balance : 0 }}
                                        </span>
                                    </div>
                                </div>
                                <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full group-hover/item:opacity-80 transition-all shadow-[0_0_10px_rgba(239,68,68,0.3)]"
                                         :style="{ width: `${Math.min((leaveBalances && leaveBalances.find(l => l.leave_type === type)?.balance || 0) * 7, 100)}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div class="p-6 rounded-2xl bg-emerald-500/5 border border-emerald-500/10 backdrop-blur-sm">
                         <h4 class="text-emerald-400 font-bold mb-2 flex items-center gap-2 uppercase tracking-tighter text-sm font-mono">
                            <InformationCircleIcon class="w-4 h-4" />
                            {{ __('Important') }}
                        </h4>
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            {{ __('Please ensure all required documents are ready if you are applying for Sick Leave or long-term Absence.') }}
                        </p>
                    </div>
                </aside>

                <!-- MAIN CONTENT: FORM -->
                <main class="lg:col-span-9">
                    <Card variant="glass" class="!mt-0 overflow-hidden relative">
                         <div class="p-8 border-b border-white/5 bg-white/[0.02]">
                            <h1 class="text-3xl font-black text-white tracking-tight">{{ __('Initiate Request') }}</h1>
                            <p class="text-gray-400 text-sm mt-1">{{ __('Apply for your time-off with details below.') }}</p>
                        </div>

                        <form @submit.prevent="submitForm" class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Leave Type -->
                                <div class="space-y-1.5">
                                    <InputLabel for="type_id" :value="__('Type of Leave')" class="!mb-0" />
                                    <div class="relative group">
                                        <select id="type_id" 
                                                class="w-full bg-white/5 border border-white/10 text-gray-100 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-3 pr-10 appearance-none transition-all"
                                                v-model="form.type">
                                            <option selected value="" class="bg-[#18181b]">{{__('Choose a Leave Type')}}</option>
                                            <option v-for="type in leaveTypes" :key="type" :value="type" class="bg-[#18181b]">
                                                {{ type }}
                                            </option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.type"/>
                                </div>

                                <!-- Date Picker -->
                                <div class="space-y-1.5">
                                    <InputLabel for="date" :value="__('Date Range selection')" class="!mb-0" />
                                    <VueDatePicker
                                        id="date"
                                        v-model="form.date"
                                        class="modern-datepicker"
                                        :placeholder="__('Select Date...')"
                                        :enable-time-picker="false"
                                        :min-date="form.type === 'leave'? dayjs().tz().format() : ''"
                                        :dark="true"
                                        range
                                        required
                                    ></VueDatePicker>
                                    <InputError class="mt-2" :message="form.errors.date"/>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="space-y-1.5">
                                <InputLabel for="remark" :value="__('Remark / Reason')" class="!mb-0" />
                                <textarea
                                    id="remark"
                                    class="w-full bg-white/5 border border-white/10 text-gray-100 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-4 min-h-[120px] transition-all placeholder:text-gray-600"
                                    v-model="form.remark"
                                    autocomplete="off"
                                    :placeholder="__('I will be absent for 3 days because...')"
                                />
                                <InputError class="mt-2" :message="form.errors.remark"/>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-white/5">
                                <PrimaryButton class="!bg-red-600 hover:!bg-red-700 !rounded-xl !px-10 !py-4 !text-white flex items-center gap-3 transition-all shadow-xl hover:shadow-red-500/20 group"
                                               :disabled="form.processing">
                                    <span class="font-black uppercase tracking-widest text-sm">{{__('Submit Request')}}</span>
                                    <PaperAirplaneIcon class="w-5 h-5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                                </PrimaryButton>
                            </div>
                        </form>
                    </Card>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.modern-datepicker .dp__input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    color: #f3f4f6;
    font-size: 0.875rem;
}
.modern-datepicker .dp__input:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 1px #ef4444;
}
</style>
