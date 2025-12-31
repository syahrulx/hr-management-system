<script setup>
defineProps({
    fancyP: {
        type: Boolean,
        default: true,
    },
    vl: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'glass', // default, glass, flat
    },
    noPadding: {
        type: Boolean,
        default: false,
    }
})
</script>

<template>
  <div class="mt-4 transition-all duration-300 ease-out border rounded-2xl"
       :class="[
           variant === 'glass'
            ? 'bg-[#18181b]/90 backdrop-blur-2xl border border-red-500/20 shadow-[0_0_30px_-5px_rgba(220,38,38,0.15)] text-gray-100 hover:border-red-500/50 transition-all duration-300'
            : variant === 'flat'
            ? 'bg-[#18181b] border-white/5 text-gray-100'
            : 'bg-white shadow-sm border-gray-200 dark:bg-gray-800 dark:border-gray-700',
            noPadding ? 'p-0' : ''
       ]">
    <div :class="{'p-6': fancyP && !noPadding, 'h-full flex flex-col items-center justify-center': vl}">
        <slot />
    </div>
  </div>
</template>
