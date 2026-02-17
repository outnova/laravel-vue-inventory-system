<script setup>
defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: 'success'
    }
})

const emit = defineEmits(['close'])
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" 
            class="fixed top-5 right-5 z-[100] max-w-sm w-full shadow-2xl rounded-2xl pointer-events-auto overflow-hidden border"
            :class="type === 'success' ? 'bg-white border-green-100' : 'bg-white border-red-100'">
            <div class="p-4 flex items-center gap-3">
                <div v-if="type === 'success'" class="flex-shrink-0 w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                    <span class="text-xl">✓</span>
                </div>
                <div v-else class="flex-shrink-0 w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                    <span class="text-xl">✕</span>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900">
                        {{ type === 'success' ? '¡Logrado!' : 'Hubo un problema' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ message }}
                    </p>
                </div>
                
                <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600 px-2 text-xl font-light">
                    ×
                </button>
            </div>
            <div class="h-1 bg-gray-100 w-full">
                <div class="h-full bg-blue-500 transition-all duration-[3000ms] ease-linear"
                    :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'"
                    :style="{ width: show ? '100%' : '0%' }"></div>
            </div>
        </div>
    </Transition>
</template>