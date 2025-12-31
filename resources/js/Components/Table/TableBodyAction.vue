<script setup>
import {Link} from "@inertiajs/vue3";

defineProps({
    href: String,
})

</script>

<template>
    <td class="px-6 py-4 ">
        <Link :href="href" class="font-medium text-red-400 hover:text-red-300 hover:underline transition-colors">
            <slot />
        </Link>
    </td>
</template>
