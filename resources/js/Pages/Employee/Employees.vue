<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import EmployeeTabs from "@/Components/Tabs/EmployeeTabs.vue";
import {ref, watch} from "vue";
import debounce from "lodash.debounce";
import Card from "@/Components/Card.vue";
import { UserPlusIcon, MagnifyingGlassIcon, IdentificationIcon, PhoneIcon, EnvelopeIcon, ChevronRightIcon, ArrowsUpDownIcon } from "@heroicons/vue/24/outline";
import {__} from "@/Composables/useTranslations.js";

const term = ref('');
const sort = ref('id');
const sort_dir = ref(true);
const search = debounce(() => {
    router.visit(route('employees.index', {term: term.value, sort: sort.value, sort_dir: sort_dir.value}),
        {preserveState: true, preserveScroll: true})
}, 400);

watch(term, search);
watch(sort, search);
watch(sort_dir, search);

const props = defineProps({
    employees: Object,
});

const handleSort = (field) => {
    if (sort.value === field) {
        sort_dir.value = !sort_dir.value;
    } else {
        sort.value = field;
        sort_dir.value = true;
    }
};

</script>

<template>
    <Head :title="__('Employees')"/>
    <AuthenticatedLayout>
        <template #tabs>
            <EmployeeTabs />
        </template>
        
        <div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                 <div class="space-y-1">
                    <h1 class="text-3xl font-black text-white tracking-tight">{{ __('Employee Directory') }}</h1>
                    <p class="text-gray-500 text-sm font-medium">{{ __('Manage and monitor your workforce across the organization.') }}</p>
                </div>
                
                <div class="flex items-center gap-4">
                     <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-4 w-4 text-gray-500 group-focus-within:text-red-500 transition-colors" />
                        </div>
                        <input type="text" 
                               v-model="term"
                               :placeholder="__('Find an employee...')"
                               class="block w-64 pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-red-500/20 focus:border-red-500/50 transition-all outline-none"
                        >
                    </div>

                    <a :href="route('employees.create')" 
                       class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-lg shadow-red-600/20 active:scale-95">
                        <UserPlusIcon class="w-5 h-5" />
                        {{ __('Add Employee') }}
                    </a>
                </div>
            </div>

            <Card variant="glass" class="!mt-0 overflow-hidden border border-white/5 shadow-2xl">
                <div class="overflow-x-auto text-gray-300">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/[0.02] border-b border-white/5">
                                <th @click="handleSort('id')" class="px-6 py-5 cursor-pointer group hover:bg-white/[0.03] transition-colors">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                        {{ __('ID') }}
                                        <ArrowsUpDownIcon class="w-3 h-3 group-hover:text-red-500 transition-colors" :class="{'text-red-500': sort === 'id'}" />
                                    </div>
                                </th>
                                <th @click="handleSort('name')" class="px-6 py-5 cursor-pointer group hover:bg-white/[0.03] transition-colors">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                        {{ __('Employee Name') }}
                                        <ArrowsUpDownIcon class="w-3 h-3 group-hover:text-red-500 transition-colors" :class="{'text-red-500': sort === 'name'}" />
                                    </div>
                                </th>
                                <th class="px-6 py-5">
                                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Contact Details') }}</div>
                                </th>
                                <th class="px-6 py-5">
                                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Identification') }}</div>
                                </th>
                                <th class="px-6 py-5 text-right">
                                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Actions') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="employee in employees.data" 
                                :key="employee.id" 
                                class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-5">
                                    <span class="text-xs font-mono font-bold text-gray-500 group-hover:text-red-500 transition-colors">
                                        #{{ employee.id.toString().padStart(4, '0') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600/20 to-rose-700/20 border border-red-500/20 flex items-center justify-center text-red-500 text-sm font-black shadow-inner group-hover:scale-110 transition-transform">
                                            {{ employee.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <a :href="route('employees.show', {employee: employee.id})" class="text-sm font-bold text-white hover:text-red-500 transition-colors">
                                                {{ employee.name }}
                                            </a>
                                            <span class="text-[10px] text-gray-500 font-medium uppercase tracking-tighter">{{ __('Active Staff') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 space-y-1">
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <EnvelopeIcon class="w-3.5 h-3.5 opacity-40" />
                                        {{ employee.email }}
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <PhoneIcon class="w-3.5 h-3.5 opacity-40" />
                                        {{ employee.phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full border border-white/10 text-[10px] font-bold text-gray-400 group-hover:border-red-500/20 group-hover:text-gray-300 transition-all">
                                        <IdentificationIcon class="w-3.5 h-3.5 opacity-60" />
                                        {{ employee.national_id }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a :href="route('employees.edit', {employee: employee.id})" 
                                           class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-white/5 hover:bg-red-600 rounded-xl text-[10px] font-black text-white border border-white/10 hover:border-red-500 transition-all uppercase tracking-widest group/btn active:scale-95 shadow-sm">
                                            {{ __('Edit') }}
                                            <ChevronRightIcon class="w-3 h-3 group-hover/btn:translate-x-0.5 transition-transform" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination Info -->
                <div class="p-6 bg-white/[0.01] border-t border-white/5 flex items-center justify-between">
                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                        {{ __('Showing :number of :total employees', {number: employees.data.length, total: employees.total}) }}
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, index) in employees.links" :key="index">
                            <a v-if="link.url" 
                               :href="link.url" 
                               v-html="link.label"
                               :class="[
                                   'px-3.5 py-1.5 rounded-lg text-[10px] font-black transition-all border',
                                   link.active ? 'bg-red-600 text-white border-red-500 shadow-lg shadow-red-600/20' : 'bg-white/5 text-gray-500 border-white/5 hover:bg-white/10 hover:text-gray-300'
                               ]"
                            ></a>
                            <span v-else v-html="link.label" class="px-3.5 py-1.5 text-[10px] font-black text-gray-700 opacity-50"></span>
                        </template>
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
