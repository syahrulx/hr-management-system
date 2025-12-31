<script setup>
import {Link} from "@inertiajs/vue3";
import RightArrowIcon from "@/Components/Icons/RightArrowIcon.vue";
import LeftArrowIcon from "@/Components/Icons/LeftArrowIcon.vue";

defineProps({
    ctaText: {
        type: String,
        default: 'Learn More',
    },
    heading: String,
    text: String,
    href: String,
})

const isLTR = document.dir === 'ltr';

</script>

<template>

    <div class="relative flex px-6 flex-col rounded-xl bg-[#18181b]/90 backdrop-blur-xl border border-red-500/20 text-gray-100 shadow-[0_0_20px_-5px_rgba(220,38,38,0.15)] hover:border-red-500/50 transition-all duration-300">
        <div class="pt-6 pb-2 flex flex-col items-center">

            <!--SVG-->
            <slot />
            <h5 class="block font-sans text-xl font-bold leading-snug tracking-normal text-white antialiased drop-shadow-md">
                {{ heading ?? 'Heading' }}
            </h5>
            <p v-if="text" class="text-center block font-sans text-base font-light leading-relaxed text-gray-300 antialiased">
                {{text}}
            </p>
        </div>

        <div class="pb-6 pt-0 flex justify-center">
            <a
                v-if="href && (href.startsWith('http://') || href.startsWith('https://'))"
                :href="href"
                target="_blank"
                rel="noopener noreferrer"
                class="!font-medium !text-gray-100 !transition-colors hover:!text-red-500"
            >
                <button
                    class="flex select-none items-center gap-2 rounded-lg py-2 px-4 text-center align-middle font-sans text-xs font-bold uppercase text-red-500 transition-all hover:bg-red-500/10 active:bg-red-500/30 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                    type="button"
                    data-ripple-dark="true"
                >

                    {{ ctaText }}
                    <RightArrowIcon v-if="isLTR"/>
                    <LeftArrowIcon v-if="!isLTR"/>
                </button>
            </a>
            <Link
                v-else-if="href"
                class="!font-medium !text-gray-100 !transition-colors hover:!text-red-500"
                :href="href"
            >
                <button
                    class="flex select-none items-center gap-2 rounded-lg py-2 px-4 text-center align-middle font-sans text-xs font-bold uppercase text-red-500 transition-all hover:bg-red-500/10 active:bg-red-500/30 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                    type="button"
                    data-ripple-dark="true"
                >
                    {{ ctaText }}
                    <RightArrowIcon v-if="isLTR"/>
                    <LeftArrowIcon v-if="!isLTR"/>
                </button>
            </Link>
        </div>
    </div>

</template>

