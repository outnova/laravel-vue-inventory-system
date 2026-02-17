<script setup>
const props = defineProps({
    showModal: Boolean,
    isEditing: Boolean,
    form: Object,
    categories: Array,
    loading: Boolean
})

const emit = defineEmits(['close', 'save'])
</script>

<template>
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full my-auto overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
            </div>

            <form @submit.prevent="emit('save')" class="p-6 overflow-y-auto max-h-[70vh] space-y-4">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nombre del Producto</label>
                    <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-xl p-3 mt-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Ej. Harina de Trigo" required>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">SKU</label>
                        <input v-model="form.sku" type="text" class="w-full border-gray-300 rounded-xl p-3 mt-1 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="CÓDIGO-01" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Categoría</label>
                        <select v-model="form.category_id" class="w-full border-gray-300 rounded-xl p-3 mt-1 bg-white focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <option value="" disabled>Seleccionar...</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Precio ($)</label>
                        <input v-model.number="form.price" type="number" step="0.01" class="w-full border-gray-300 rounded-xl p-3 mt-1 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Stock</label>
                        <input v-model.number="form.stock" type="number" class="w-full border-gray-300 rounded-xl p-3 mt-1 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" required>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8 pb-2">
                    <button 
                        type="button" 
                        @click="emit('close')" 
                        class="w-full sm:w-auto px-6 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg transition-all duration-300 relative overflow-hidden"
                        :class="{ 'bg-blue-400 cursor-wait opacity-80': loading }"
                    >
                        <div v-if="loading" class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
                        
                        <span :class="{ 'animate-pulse': loading }">
                            {{ isEditing ? 'Guardar Cambios' : 'Crear Producto' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>