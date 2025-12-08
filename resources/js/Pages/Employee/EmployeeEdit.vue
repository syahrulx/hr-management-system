<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import GenericModal from "@/Components/GenericModal.vue";
import {useToast} from "vue-toastification";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import Swal from "sweetalert2";
import {Switch} from "@headlessui/vue";
import ToolTip from "@/Components/ToolTip.vue";
import Card from "@/Components/Card.vue";
import {inject} from "vue";
import {__} from "@/Composables/useTranslations.js";
import dayjs from "dayjs";


const props = defineProps(
    {
        employee: Object,
        href: String,
        shifts: Object,
        roles: Object,
    }
)

const form = useForm({
    name: props.employee.name,
    ic_number: props.employee.national_id,
    email: props.employee.email,
    phone: props.employee.phone,
    address: props.employee.address,
    hired_on: props.employee.hired_on,
    role: props.employee.user_role ?? '',
});

const shiftForm = useForm({
    name: '',
    start_time: '',
    end_time: '',
    shift_payment_multiplier: '',
    description: '',
});

const submit = () => {
    form.hired_on = dayjs(form.hired_on).format('YYYY-MM-DD');
    form.put(route('employees.update', { id: props.employee.id }), {
        preserveScroll: true,
        onError: () => {
            useToast().error(__('Error Editing Employee'));
        },
        onSuccess: () => {
            useToast().success(__('Employee Edited Successfully'));
        },
    });
};
const destroy = () => {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'mx-4 text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900',
            cancelButton: 'text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: __('Are you sure?'),
        text: __('You won\'t be able to revert this!'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: __('Yes, Delete!'),
        cancelButtonText: __('No, Cancel!'),
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('employees.destroy', { id: props.employee.id }), {
                preserveScroll: true,
                onError: () => {
                    useToast().error(__('Error Removing Employee'));
                },
                onSuccess: () => {
                    Swal.fire(__('Employee Removed!'), '', 'success')
                },
            });
        }
    })
};

const submitShift = () => {
    shiftForm.post(route('shifts.store'), {
        preserveScroll: true,
        onError: () => {
            useToast().error(__('Error Creating Shift'));
        },
        onSuccess: () => {
            useToast().success(__('Shift Created Successfully'));
            document.getElementById('closeShiftModal').click();
            shiftForm.reset();
            form.shift_id = props.shifts.length;
        }
    });
};

</script>

<template>
    <Head :title="__('Employee Edit')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs/>
        </template>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="!mt-0">
                    <p class="card-header">{{__('Edit :name Details', {name: employee.name})}}</p>
                    <form @submit.prevent="submit" class="form">
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <InputLabel for="name" :value="__('Full Name')"/>
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{'border-2 border-red-500 ': form.errors.name}"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="form.errors.name"/>
                            </div>
                            <div>
                                <InputLabel for="ic_number" :value="__('National ID')"/>
                                <TextInput
                                    id="ic_number"
                                    type="number"
                                    class="mt-1 block w-full"
                                    :class="{'border border-red-500': form.errors.ic_number}"
                                    v-model="form.ic_number"
                                    required
                                    pattern="[0-9]{14}"
                                    autocomplete="off"

                                />
                                <InputError class="mt-2" :message="form.errors.ic_number"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-8 mt-4">
                            <div>
                                <InputLabel for="phone" :value="__('Phone')"/>
                                <TextInput
                                    id="phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{'border border-red-500': form.errors.phone}"
                                    v-model="form.phone"
                                    required
                                    autocomplete="off"
                               
                                />
                                <InputError class="mt-2" :message="form.errors.phone"/>
                            </div>
                            <div>
                                <InputLabel for="email" :value="__('Email')"/>
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    :class="{'border border-red-500': form.errors.email}"
                                    v-model="form.email"
                                    required
                                    autocomplete="off"
                                />
                                <InputError class="mt-2" :message="form.errors.email"/>
                            </div>
                        </div>
                        <div class="mt-4">
                            <InputLabel for="address" :value="__('Address')"/>
                            <TextInput
                                id="address"
                                type="text"
                                class="mt-1 block w-full"
                                :class="{'border border-red-500': form.errors.address}"
                                v-model="form.address"
                                required
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.address"/>
                        </div>
                        <div class="mt-4">
                            <InputLabel for="hired_on" :value="__('Hire Date')"/>
                            <VueDatePicker
                                id="hired_on"
                                v-model="form.hired_on"
                                class="py-1 block w-full"
                                :class="{'border border-red-500': form.errors.hired_on}"
                                :enable-time-picker="false"
                                :dark="inject('isDark').value"
                                required
                            ></VueDatePicker>
                            <InputError class="mt-2" :message="form.errors.hired_on"/>
                        </div>
                        <div class="grid grid-cols-2 gap-8 mt-4">
                            <div>
                                <InputLabel for="role" :value="__('Permissions Level')"/>
                                <select id="role" class="fancy-selector" v-model="form.role">
                                    <option selected value="">{{__('Choose a Permission Level')}}</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role"/>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <form @submit.prevent="destroy" class=" inline">
                                <PrimaryButton class="bg-red-600 hover:bg-red-700 ml-4" >
                                    {{__('Delete Employee')}}
                                </PrimaryButton>
                            </form>
                            <PrimaryButton class="ltr:ml-4 rtl:mr-4" :class="{ 'opacity-25': form.processing }"
                                           :disabled="form.processing">
                                {{__('Edit Employee')}}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>




