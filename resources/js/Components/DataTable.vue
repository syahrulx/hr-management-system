<script setup>

import {onMounted} from "vue";
import Paginator from "@/Components/Table/Paginator.vue";
import {Link} from "@inertiajs/vue3";
import {initDropdowns} from "flowbite";
import {__} from "@/Composables/useTranslations.js";

onMounted(() => {
    initDropdowns();
});

const props = defineProps({
    data: Object,  // like this { "data": [ { "id": 1, "name": "Super Root", "email.." }, { "id": 2, "name"... ....} }
    head: Array,
    controller: {
        type: String,
        default: 'employees',
    },
    hasLink: Boolean,
    hasActions: Boolean,
    hasPaginator: {
        type: Boolean,
        default: true,
    },
    hasCustomParams: {
        type: Boolean,
        default: false,
    },
    customParamsHeader: {
        type: String,
        default: 'id',
    },
    customParamsIndex: {
        type: Number,
        default: 0,
    },
    undefinedText: {
        type: String,
        default: __('N/A'),
    },

    boolMessage: Array,
})

</script>

<template>
    <div class="relative overflow-x-auto shadow-2xl sm:rounded-2xl border border-red-500/20 bg-[#18181b]/90 backdrop-blur-xl">
        <div v-if="data.data.length === 0" class="text-center pb-8 pt-8 text-gray-400">
            {{ __('No data was found in the records.')}}
        </div>
        <table v-else class="w-full text-sm text-left text-gray-300">
            <thead class="text-xs text-gray-100 uppercase bg-red-900/20 border-b border-red-500/20">
            <tr>
                <th scope="col" class="px-6 py-4 font-bold tracking-wider" v-for="header in head" :key="header.id">
                    {{ header }}
                </th>
                <th v-if="hasActions" scope="col" class="px-6 py-4 font-bold tracking-wider">
                    {{__('Actions')}}
                </th>
            </tr>
            </thead>

            <tbody>
            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors duration-200"
                v-for="(value) in data.data" :key="value.id">
                <td class="px-6 py-4" v-for="(val, key) in value" :key="key">
                    <div v-if="hasLink">
                        <Link v-if="hasCustomParams" 
                              class="font-medium text-white hover:text-red-400 transition-colors"
                              :href="route(controller + '.show', { [customParamsHeader]: value[Object.keys(value)[customParamsIndex]] } )">{{ val ?? undefinedText }}</Link>
                        <Link v-else 
                              class="font-medium text-white hover:text-red-400 transition-colors"
                              :href="route(controller + '.show', { id: value.id } )">{{ val ?? undefinedText }}</Link>
                    </div>
                    <p v-else>{{ val ?? undefinedText }} </p>
                </td>
                <td v-if="hasActions" class="px-6 py-4">
                    <Link :href="route(controller + '.edit', { id: value.id } )"
                          class="font-medium text-red-400 hover:text-red-300 hover:underline transition-colors">{{__('Edit')}}
                    </Link>
                </td>
            </tr>

            </tbody>
        </table>

        <nav v-if="hasPaginator && data.data.length !== 0" class="flex items-center justify-between pt-6 px-6 pb-6 border-t border-white/10" aria-label="Table navigation">
            <span v-if="data.data.length !== data.total " class="text-sm font-normal text-gray-400">
                                {{__('Showing')}} <span class="font-semibold text-white">{{ data.data.length }}</span> {{__('of')}} <span class="font-semibold text-white">{{ data.total }}</span> {{__('Records')}}.</span>
            <Paginator :links="data.links" class="gap-2"/>
        </nav>
    </div>

</template>

