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
    console.log(form.date);
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
        <div class="py-10 min-h-screen">
            <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row gap-10">
                <!-- Sidebar: Leave Balances -->
                <aside class="w-full md:w-72 bg-gray-800/80 backdrop-blur border border-gray-700 rounded-2xl p-6 shadow-xl mb-8 md:mb-0 flex-shrink-0">
                    <h2 class="text-base font-bold text-gray-200 mb-5 tracking-wide border-b border-gray-700 pb-2">{{ __('My Leave Balances') }}</h2>
                    <ul class="space-y-4 mt-4">
                        <li v-for="type in ['Annual Leave', 'Emergency Leave', 'Sick Leave']" :key="type" class="flex items-center justify-between">
                            <span class="font-semibold flex items-center gap-2 text-gray-300">
                              <svg v-if="type==='Annual Leave'" class="h-4 w-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
                              <svg v-else-if="type==='Emergency Leave'" class="h-4 w-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><rect width="20" height="20" rx="4"/></svg>
                              <svg v-else class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><ellipse cx="10" cy="10" rx="10" ry="7"/></svg>
                              {{ type }}
                            </span>
                            <span :class="(leaveBalances && leaveBalances.find(l => l.leave_type === type)?.balance) ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-400'" class="px-3 py-0.5 rounded-full text-xs font-bold min-w-[2rem] text-center shadow-sm">
                                {{ leaveBalances && leaveBalances.find(l => l.leave_type === type) ? leaveBalances.find(l => l.leave_type === type).balance : 0 }}
                            </span>
                        </li>
                    </ul>
                </aside>
                <!-- Main Content -->
                <main class="flex-1">
                    <div class="bg-gray-800/90 rounded-2xl shadow-xl border border-gray-700 p-8">
                        <h1 class="text-2xl font-extrabold text-gray-100 tracking-tight mb-6">{{__('Request Leave')}}</h1>
                        <form @submit.prevent="submitForm" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <InputLabel for="type_id" :value="__('Type of Leave')" class="mb-2"/>
                                    <select id="type_id" class="w-full rounded-lg bg-gray-900 text-gray-100 border border-gray-700 px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" v-model="form.type">
                                        <option selected value="">{{__('Choose a Leave Type')}}</option>
                                        <option v-for="type in leaveTypes" :key="type" :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type"/>
                                </div>
                                <div>
                                    <InputLabel for="date" :value="__('Date (Range selection is available)')" class="mb-2"/>
                                    <VueDatePicker
                                        id="date"
                                        v-model="form.date"
                                        class="w-full rounded-lg bg-gray-900 text-gray-100 border border-gray-700 px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                                        :class="{'border border-red-500': form.errors.date}"
                                        :placeholder="__('Select Date...')"
                                        :enable-time-picker="false"
                                        :min-date="form.type === 'leave'? dayjs().tz().format() : ''"
                                        :dark="inject('isDark').value"
                                        range
                                        required
                                    ></VueDatePicker>
                                    <InputError class="mt-2" :message="form.errors.date"/>
                                </div>
                            </div>
                                <div>
                                <InputLabel for="remark" :value="__('Remark')" class="mb-2"/>
                                <textarea
                                        id="remark"
                                    class="w-full rounded-lg bg-gray-900 text-gray-100 border border-gray-700 px-4 py-2 focus:ring-2 focus:ring-red-500 focus:outline-none"
                                        :class="{'border border-red-500': form.errors.remark}"
                                        v-model="form.remark"
                                        autocomplete="off"
                                    rows="3"
                                        :placeholder="__('I will be absent for 3 days because I\'m sick.')"
                                    />
                                    <InputError class="mt-2" :message="form.errors.remark"/>
                            </div>
                            <div class="flex items-center justify-end">
                                <PrimaryButton class="px-6 py-2 rounded-full font-semibold text-base bg-gradient-to-r from-red-600 to-red-800 hover:shadow-red-600/40 text-white shadow transition ltr:ml-4 rtl:mr-4" :class="{ 'opacity-25': form.processing }"
                                               :disabled="form.processing">
                                    {{__('Initiate Request')}}
                                </PrimaryButton>
                            </div>
                        </form>
                </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
