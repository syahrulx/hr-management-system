<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const tt = computed(() => page.props.time_travel || {});
const enabled = computed(() => !!tt.value.enabled);
const active = computed(() => !!tt.value.active);
const serverNow = computed(() => tt.value.now || "");

const open = ref(false);
const picked = ref((serverNow.value || "").slice(0, 16).replace(" ", "T"));
const busy = ref(false);

const apply = () => {
    if (!picked.value) return;
    busy.value = true;
    router.post(
        route("time-travel.set"),
        { datetime: picked.value.replace("T", " ") },
        { preserveScroll: true, onFinish: () => (busy.value = false) }
    );
};

const reset = () => {
    busy.value = true;
    router.post(
        route("time-travel.reset"),
        {},
        { preserveScroll: true, onFinish: () => (busy.value = false) }
    );
};
</script>

<template>
    <div v-if="enabled" class="fixed bottom-4 right-4 z-[9999] text-xs font-mono">

        <!-- Collapsed pill -->
        <button
            v-if="!open"
            @click="open = true"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border backdrop-blur-md transition-all duration-200 text-[11px] font-semibold tracking-wide"
            :class="active
                ? 'bg-red-900/60 border-red-600/40 text-red-300 shadow-[0_0_12px_rgba(220,38,38,0.2)]'
                : 'bg-white/5 border-white/10 text-gray-400 hover:text-white hover:bg-white/10'"
        >
            <span class="w-1.5 h-1.5 rounded-full" :class="active ? 'bg-red-400 animate-pulse' : 'bg-gray-600'"></span>
            {{ active ? serverNow.slice(11, 16) + " (sim)" : "Set Time (for attendance testing only)" }}
        </button>

        <!-- Expanded panel — same glass style as the sidebar -->
        <div
            v-else
            class="w-60 rounded-2xl border border-white/10 bg-[#0f0505]/90 backdrop-blur-xl shadow-2xl shadow-black/50 overflow-hidden"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full" :class="active ? 'bg-red-400 animate-pulse' : 'bg-gray-600'"></span>
                    Set Time (for attendance testing only)
                </div>
                <button @click="open = false" class="text-gray-600 hover:text-gray-300 transition-colors text-base leading-none">✕</button>
            </div>

            <div class="p-3 space-y-3">
                <!-- Current simulated time display -->
                <div
                    class="rounded-xl px-3 py-2 text-center border"
                    :class="active
                        ? 'bg-red-900/20 border-red-800/40 text-red-300'
                        : 'bg-white/5 border-white/5 text-gray-500'"
                >
                    <div class="text-[9px] uppercase tracking-widest opacity-60 mb-0.5">
                        {{ active ? "Simulated (frozen)" : "Real time" }}
                    </div>
                    <div class="text-sm font-bold text-white tracking-wide">{{ serverNow }}</div>
                </div>

                <!-- Datetime picker -->
                <div>
                    <div class="text-[9px] uppercase tracking-widest text-gray-600 mb-1">Travel to</div>
                    <input
                        v-model="picked"
                        type="datetime-local"
                        step="60"
                        class="w-full rounded-lg bg-white/5 border border-white/10 text-white text-[11px] px-2.5 py-1.5 focus:outline-none focus:border-red-700/60 focus:ring-1 focus:ring-red-700/30 transition-all"
                    />
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button
                        @click="apply"
                        :disabled="busy"
                        class="flex-1 rounded-xl py-1.5 text-[11px] font-bold bg-red-800 hover:bg-red-700 disabled:opacity-40 text-white transition-all shadow-lg shadow-red-900/30"
                    >
                        Apply
                    </button>
                    <button
                        @click="reset"
                        :disabled="busy || !active"
                        class="flex-1 rounded-xl py-1.5 text-[11px] font-bold bg-white/5 hover:bg-white/10 disabled:opacity-30 text-gray-300 border border-white/10 transition-all"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
