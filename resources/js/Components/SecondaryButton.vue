<script setup>
import {Link} from "@inertiajs/vue3";


defineProps({
    type: {
        type: String,
        default: 'button',
        href: String,
    },
});
</script>

<template>
    <Link
        :type="type"
        :href="href"
        class="inline-flex items-center justify-center px-6 py-3 bg-white/5 border border-white/10 rounded-full font-bold text-xs text-gray-200 uppercase tracking-widest shadow-lg shadow-black/10 backdrop-blur-md hover:bg-white/10 hover:text-white hover:border-white/30 focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all duration-300 ease-out"
    >
        <slot />
    </Link>
</template>
