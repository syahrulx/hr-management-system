<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {computed, onMounted} from "vue";
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import FlexButton from "@/Components/FlexButton.vue";
import {useExtractPersonalDetails} from "@/Composables/useExtractPersonalDetails.js";
import HistoryDescriptionList from "@/Components/DescriptionList/HistoryDescriptionList.vue";
import {initModals} from "flowbite";
import {useAgeCalculator} from "@/Composables/useAgeCalculator.js";
import Card from "@/Components/Card.vue";
import ModifyIcon from "@/Components/Icons/ModifyIcon.vue";
import DescriptionList from "@/Components/DescriptionList/DescriptionList.vue";
import DT from "@/Components/DescriptionList/DT.vue";
import DD from "@/Components/DescriptionList/DD.vue";
import DescriptionListItem from "@/Components/DescriptionList/DescriptionListItem.vue";
import GenericModal from "@/Components/GenericModal.vue";
import Table from "@/Components/Table/Table.vue";
import TableBody from "@/Components/Table/TableBody.vue";
import TableHead from "@/Components/Table/TableHead.vue";
import TableRow from "@/Components/Table/TableRow.vue";
import ToolTip from "@/Components/ToolTip.vue";
import {__} from "@/Composables/useTranslations.js";


let {extractPersonalDetails} = useExtractPersonalDetails()

onMounted(() => {
    initModals();
});

const props = defineProps({
    employee: Object,
})

// Removed - manages relationship deleted
</script>

<template>
    <Head :title="__('Employee View')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs/>
        </template>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="!mt-0">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="card-header">{{__('Employee View')}}</h1>
                        <div class="flex inline-flex gap-4">
                            <FlexButton v-if="$page.props.auth.user.role === 'admin'"
                                        :text="__('Modify Employee Data')" :href="route('employees.edit', {id: employee.id})">
                                <ModifyIcon/>
                            </FlexButton>
                            <FlexButton v-else :text="__('Modify Data')"
                                        :href="route('profile.edit', {id: employee.id})">
                                <ModifyIcon/>
                            </FlexButton>
                        </div>
                    </div>

                    <h2 class="card-subheader">{{__('Basic Info')}}</h2>
                    <DescriptionList>
                        <DescriptionListItem colored>
                            <DT>{{__('Name')}}</DT>
                            <DD>{{ employee.name }}</DD>
                        </DescriptionListItem>
                        <DescriptionListItem colored>
                            <DT>{{__('ID')}}</DT>
                            <DD>{{ employee.id }}</DD>
                        </DescriptionListItem>
                        <DescriptionListItem>
                            <DT>{{__('Phone')}}</DT>
                            <DD><a :href="'tel:' + employee.phone">{{ employee.phone }}</a></DD>
                        </DescriptionListItem>
                        <DescriptionListItem>
                            <DT>{{__('National ID')}}</DT>
                            <DD>{{ employee.national_id }}</DD>
                        </DescriptionListItem>
                        <DescriptionListItem colored>
                            <DT>{{__('Email')}}</DT>
                            <DD><a :href="'mailto:' + employee.email">{{ employee.email }}</a></DD>
                        </DescriptionListItem>

                        <DescriptionListItem colored>
                            <DT>{{__('Gender')}}</DT>
                            <DD>{{ extractPersonalDetails(employee.national_id).isMale ? __('Male') : __('Female') }}</DD>
                        </DescriptionListItem>

                        <DescriptionListItem>
                            <DT>{{__('Birthday')}}</DT>
                            <DD>{{
                                extractPersonalDetails(employee.national_id).date_localized +
                                ' - ' + useAgeCalculator(extractPersonalDetails(employee.national_id).date) + ' ' + __('Years')
                              }}</DD>
                        </DescriptionListItem>

                        <DescriptionListItem colored>
                            <DT>{{__('Hire Date')}}</DT>
                            <DD>{{ employee.hired_on }}</DD>
                        </DescriptionListItem>

                        <DescriptionListItem >
                            <DT>{{__('Address')}}</DT>
                            <DD><a :href="'https://www.google.com/maps/search/?api=1&query=' + props.employee.address" target=”_blank” >{{ employee.address }}</a></DD>

                        </DescriptionListItem>
                    </DescriptionList>
                </Card>
                <Card>
                    <h2 class="mb-2 ml-1 font-semibold">{{__('Technical Info')}}</h2>
                    <DescriptionList>
                        <DescriptionListItem>
                            <DT>{{__('Access Permissions')}}</DT>
                            <DD>{{ employee.user_role ? employee.user_role.replace(/_/g, ' ').replace(/\b\w/g, (match) => match.toUpperCase()) : __('Not Assigned') }}</DD>
                        </DescriptionListItem>
                    </DescriptionList>
                </Card>
                <Card>
                    <h2 class="mb-2 ml-1 font-semibold">{{__('History')}}</h2>

                    <HistoryDescriptionList>
                        <div class="px-4 py-3.5">
                            <dt class="text-sm font-medium">{{__('Previous Shifts')}}</dt>

                            <GenericModal modalId='Shifts Modal'
                                          :title="__('Click Here To See Shifts History')" :modalHeader="__('Previous Shifts')"
                                          :hasCancel="false" >

                                <Table :totalNumber="1" :enablePaginator="false">
                                    <template #Head>
                                        <TableHead>{{__('Shift')}}</TableHead>
                                        <TableHead>{{__('Starting From')}}</TableHead>
                                        <TableHead>{{__('Ending At')}}</TableHead>
                                    </template>

                                    <!--Iterate Here-->
                                    <template #Body>
                                        <TableRow v-for="shift in employee.employee_shifts" :key="shift.id">
                                            <TableBody>{{shift.shift?.name ?? __('DELETED SHIFT')}}</TableBody>
                                            <TableBody>{{shift.start_date}}</TableBody>
                                            <TableBody>{{shift.end_date ?? __('Current')}}</TableBody>
                                        </TableRow>
                                    </template>
                                </Table>
                            </GenericModal>
                        </div>

                    </HistoryDescriptionList>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
