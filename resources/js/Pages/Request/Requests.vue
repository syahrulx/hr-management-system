<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import FlexButton from "@/Components/FlexButton.vue";
import ReqTabs from "@/Components/Tabs/ReqTabs.vue";
import Card from "@/Components/Card.vue";
import PlusIcon from "@/Components/Icons/PlusIcon.vue";
import TableBody from "@/Components/Table/TableBody.vue";
import TableRow from "@/Components/Table/TableRow.vue";
import TableBodyHeader from "@/Components/Table/TableBodyHeader.vue";
import Table from "@/Components/Table/Table.vue";
import TableHead from "@/Components/Table/TableHead.vue";
import {__} from "@/Composables/useTranslations.js";
import {request_types} from "@/Composables/useRequestTypes.js";
import {request_status_types} from "@/Composables/useRequestStatusTypes.js";

const props = defineProps({
    requests: Object,
    leaveBalances: Array,
    leaveTotals: Object,
})

</script>
<template>
    <Head :title="__('Requests')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <ReqTabs />
        </template>
        <div class="py-10  min-h-screen">
            <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row gap-10">
                <!-- Sidebar: Leave Balances or Totals -->
                <aside class="w-full md:w-72 bg-[#18181b] backdrop-blur border border-red-500/20 rounded-2xl p-6 shadow-[0_0_20px_-5px_rgba(220,38,38,0.15)] mb-8 md:mb-0 flex-shrink-0">
                    <div v-if="['admin','owner'].includes($page.props.auth.user.role)">
                        <h2 class="text-base font-bold text-gray-100 mb-5 tracking-wide border-b border-red-900/30 pb-2">{{ __('Total Approved Leaves') }}</h2>
                        <ul class="space-y-4 mt-4">
                            <li v-for="type in ['Annual Leave', 'Emergency Leave', 'Sick Leave']" :key="type" class="flex items-center justify-between">
                                <span class="font-semibold flex items-center gap-2 text-gray-300">
                                  <svg v-if="type==='Annual Leave'" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
                                  <svg v-else-if="type==='Emergency Leave'" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><rect width="20" height="20" rx="4"/></svg>
                                  <svg v-else class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><ellipse cx="10" cy="10" rx="10" ry="7"/></svg>
                                  {{ type }}
                                </span>
                                <span :class="leaveTotals && leaveTotals[type] ? 'bg-red-900/40 text-red-200 border border-red-500/30' : 'bg-[#252529] text-gray-500 border border-white/5'" class="px-3 py-0.5 rounded-full text-xs font-bold min-w-[2rem] text-center shadow-sm">
                                  {{ leaveTotals && leaveTotals[type] ? leaveTotals[type] : 0 }}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div v-else>
                        <h2 class="text-base font-bold text-gray-100 mb-5 tracking-wide border-b border-red-900/30 pb-2">{{ __('My Leave Balances') }}</h2>
                        <ul class="space-y-4 mt-4">
                            <li v-for="type in ['Annual Leave', 'Emergency Leave', 'Sick Leave']" :key="type" class="flex items-center justify-between">
                                <span class="font-semibold flex items-center gap-2 text-gray-300">
                                  <svg v-if="type==='Annual Leave'" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
                                  <svg v-else-if="type==='Emergency Leave'" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><rect width="20" height="20" rx="4"/></svg>
                                  <svg v-else class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><ellipse cx="10" cy="10" rx="10" ry="7"/></svg>
                                  {{ type }}
                                </span>
                                <span :class="(leaveBalances && leaveBalances.find(l => l.leave_type === type)?.balance) ? 'bg-red-900/40 text-red-200 border border-red-500/30' : 'bg-[#252529] text-gray-500 border border-white/5'" class="px-3 py-0.5 rounded-full text-xs font-bold min-w-[2rem] text-center shadow-sm">
                                  {{ leaveBalances && leaveBalances.find(l => l.leave_type === type) ? leaveBalances.find(l => l.leave_type === type).balance : 0 }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </aside>
                <!-- Main Content -->
                <main class="flex-1">
                    <div class="bg-[#18181b] rounded-2xl shadow-xl border border-red-500/20 p-8 shadow-[0_0_20px_-5px_rgba(220,38,38,0.15)]">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                            <h1 class="text-2xl font-extrabold text-gray-100 tracking-tight">{{__('Current Requests')}}</h1>
                            <FlexButton v-if="!['admin','owner'].includes($page.props.auth.user.role)" :href="route('requests.create')"
                                        :text="__('Initiate A Request')"
                                        :class="'text-white px-6 py-2 rounded-full font-semibold text-base shadow transition'">
                                <PlusIcon/>
                            </FlexButton>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-red-500/10">
                        <table class="min-w-full text-sm text-gray-200">
                          <thead>
                            <tr class="bg-red-900/20 text-gray-100 uppercase text-xs">
                              <th class="w-12 text-center px-3 py-3 font-bold">{{__('ID')}}</th>
                              <th class="px-3 py-3 font-bold text-left">{{__('Created By')}}</th>
                              <th class="px-3 py-3 font-bold text-left">{{__('Type')}}</th>
                              <th class="px-3 py-3 font-bold text-center">{{__('Start Date')}}</th>
                              <th class="px-3 py-3 font-bold text-center">{{__('End Date')}}</th>
                              <th class="px-3 py-3 font-bold text-center">{{__('Status')}}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(request, idx) in requests.data" :key="request.id" class="hover:bg-red-900/10 transition border-b border-white/5 bg-[#18181b]">
                              <td class="w-12 text-center px-3 py-3 font-mono font-bold">
                                <a :href="route('requests.show', {request: request.id})" class="hover:underline text-red-400">{{request.id}}</a>
                              </td>
                              <td class="px-3 py-3 max-w-[180px] truncate" :title="request.employee_name">
                                <a :href="route('requests.show', {request: request.id})" class="hover:underline">{{request.employee_name}}</a>
                              </td>
                              <td class="px-3 py-3">
                                <a :href="route('requests.show', {request: request.id})" class="hover:underline">{{ request.type }}</a>
                              </td>
                              <td class="px-3 py-3 text-center">
                                <a :href="route('requests.show', {request: request.id})" class="hover:underline">{{request.start_date}}</a>
                              </td>
                              <td class="px-3 py-3 text-center">
                                <a :href="route('requests.show', {request: request.id})" class="hover:underline">{{request.end_date ?? __('N/A')}}</a>
                              </td>
                              <td class="px-3 py-3 text-center">
                                <span v-if="request.status === 'Pending'" class="px-3 py-1 rounded-full bg-yellow-900/50 text-yellow-200 text-xs font-bold shadow-sm border border-yellow-500/20">{{ request_status_types['pending'] }}</span>
                                <span v-else-if="request.status === 'Approved'" class="px-3 py-1 rounded-full bg-green-900/50 text-green-200 text-xs font-bold shadow-sm border border-green-500/20">{{ request_status_types['approved'] }}</span>
                                <span v-else class="px-3 py-1 rounded-full bg-red-900/50 text-red-200 text-xs font-bold shadow-sm border border-red-500/20">{{ request_status_types['rejected'] }}</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
