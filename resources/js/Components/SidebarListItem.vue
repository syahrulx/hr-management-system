<script setup>
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    itemName: String,
    link: String,
    hasBadge: Boolean,
    badge: String,
    badgeContent: String,
    activeLinksRecursive: Array,
    activeLinks: Array, // ['employees.index, employees.create, etc ]
})

</script>

<template>
    <li>
        <Link :href="route(link)"
              class="group relative flex items-center p-3 my-1 rounded-xl transition-all duration-300 ease-out border border-transparent overflow-hidden"
              :class="activeLinksRecursive ? (activeLinksRecursive.some(item => $page.url.includes(item)) ? 'bg-gradient-to-r from-red-600/90 to-red-900/80 text-white shadow-lg shadow-red-500/30 border-red-500/50' : 'text-gray-400 hover:bg-white/5 hover:text-white hover:pl-4 hover:border-white/5') :
              (activeLinks.includes(route().current()) ? 'bg-gradient-to-r from-red-600/90 to-red-900/80 text-white shadow-lg shadow-red-500/30 border-red-500/50' : 'text-gray-400 hover:bg-white/5 hover:text-white hover:pl-4 hover:border-white/5')
        ">
            <!-- Icon Wrapper -->
            <div class="flex-shrink-0 w-6 h-6 transition-transform duration-300 group-hover:scale-110">
                <slot/>
            </div>

            <span class="flex-1 mx-4 whitespace-nowrap font-medium tracking-wide">{{ itemName }}</span>

            <span v-if="hasBadge"
                  class="inline-flex items-center justify-center px-2 py-0.5 ml-3 text-xs font-bold rounded-full shadow-inner"
                  :class="badge === 'number' ? 'bg-white text-red-700' : 'bg-red-500/20 text-red-200 border border-red-500/30'">
                {{ badgeContent }}
            </span>
        </Link>
    </li>
</template>

<style scoped>

</style>
