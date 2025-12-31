<script setup>
import {defineProps} from 'vue'
import {Link} from "@inertiajs/vue3";

defineProps({
    links: Object,
})

</script>


<template>
        <div v-if="links.length > 3">
            <div class="inline-flex items-center -space-x-px">
                <template v-for="(link, p) in links" :key="p">
                    <div v-if="link.url === null">
                        <div v-if="link.label.includes('Previous')">
                            <Link
                                href="#"
                                class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-[#18181b] border border-white/10 rounded-l-lg hover:bg-red-900/20 hover:text-white"
                                preserve-scroll >
                                <span class="sr-only">{{ link.label }}</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </Link>
                        </div>
                        <div v-else>
                            <Link href="#" class="block px-3 py-2 leading-tight text-gray-500 bg-[#18181b] border border-white/10 rounded-r-lg hover:bg-red-900/20 hover:text-white">
                                <span class="sr-only">{{ link.label }}</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            </Link>
                        </div>
                    </div>

                    <Link v-else
                          :class="{ 'z-10 px-3 py-2 leading-tight text-white border border-red-500/30 bg-red-600 hover:bg-red-700': link.active,
                           'px-3 py-2 leading-tight text-gray-500 bg-[#18181b] border border-white/10 hover:bg-red-900/20 hover:text-white': !link.active }"
                          :href="link.url"
                          v-html="link.label"
                          preserve-scroll />
                </template>
            </div>
        </div>
</template>
