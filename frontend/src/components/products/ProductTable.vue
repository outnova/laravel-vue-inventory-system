<script setup>
defineProps({
    products: Array,
    loading: Boolean,
    pagination: Object,
    filters: Object,
    formatCurrency: Function
})

const emit = defineEmits(['edit', 'delete'])
</script>

<template>
    <div class="relative bg-white rounded-xl shadow overflow-hidden border border-gray-100">

        <div v-if="loading" class="absolute top-0 left-0 w-full h-1 z-20 overflow-hidden">
            <div class="w-full h-full bg-blue-100 relative">
                <div class="absolute inset-0 bg-blue-600 animate-progress-loop"></div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table 
                class="w-full text-left border-collapse min-w-[800px] transition-all duration-500"
                :class="{ 'opacity-50 blur-[2px] grayscale-[50%] pointer-events-none': loading }"
            >
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 font-semibold text-gray-600">Producto</th>
                        <th class="p-4 font-semibold text-gray-600">SKU</th>
                        <th class="p-4 font-semibold text-gray-600">Categoría</th>
                        <th class="p-4 font-semibold text-gray-600">Precio</th>
                        <th class="p-4 font-semibold text-gray-600 text-center">Stock</th>
                        <th class="p-4 font-semibold text-gray-600 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-4 font-medium">{{ product.name }}</td>
                        <td class="p-4 text-sm text-gray-500 font-mono">{{ product.sku }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs uppercase font-bold">
                                {{ product.category?.name || 'S/C' }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold">
                            ${{ formatCurrency(product.price) }}
                        </td>
                        <td class="p-4 text-center">
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-bold',
                                product.stock < 5 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-green-100 text-green-600'
                            ]">
                                {{ product.stock }} unidades
                            </span>
                        </td>
                        <td class="p-4 text-right space-x-3">
                            <button 
                                @click="emit('edit', product)"
                                class="text-blue-600 hover:text-blue-800 font-medium text-sm transition"
                            >
                                Editar
                            </button>
                            
                            <button 
                                @click="emit('delete', product.id)"
                                class="text-red-600 hover:text-red-800 font-medium text-sm transition"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="border-t">
            <div v-if="pagination.total > 0" class="p-4 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-sm text-gray-600 font-medium">
                    Mostrando página <span class="text-blue-600">{{ pagination.current_page }}</span> de {{ pagination.last_page }}
                </span>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <button 
                        @click="filters.page--" 
                        :disabled="filters.page <= 1 || loading"
                        class="flex-1 sm:flex-none px-4 py-2 border rounded-lg bg-white hover:bg-gray-100 disabled:opacity-50 transition-colors font-medium text-gray-700"
                    >
                        Anterior
                    </button>
                    <button 
                        @click="filters.page++" 
                        :disabled="filters.page >= pagination.last_page || loading"
                        class="flex-1 sm:flex-none px-4 py-2 border rounded-lg bg-white hover:bg-gray-100 disabled:opacity-50 transition-colors font-medium text-gray-700"
                    >
                        Siguiente
                    </button>
                </div>
            </div>

            <div v-else-if="loading" class="p-4 bg-gray-50 flex items-center justify-between animate-pulse">
                <div class="h-4 w-32 bg-gray-200 rounded-full"></div>
                <div class="flex gap-2">
                    <div class="h-9 w-20 bg-gray-200 rounded-lg"></div>
                    <div class="h-9 w-20 bg-gray-200 rounded-lg"></div>
                </div>
            </div>

            <div v-else-if="pagination.total === 0" class="p-8 text-center bg-gray-50">
                <p class="text-gray-500 text-sm italic">No se encontraron productos con los filtros aplicados.</p>
            </div>
        </div>
    </div>
</template>