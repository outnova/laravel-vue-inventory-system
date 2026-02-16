<script setup>
import { ref, onMounted, watch } from 'vue'
import axiosClient from '../api/axios'

//State
const products = ref([])
const categories = ref([])
const loading = ref(false)
const pagination = ref({})

//Reactive filters
const filters = ref({
    search: '',
    category_id: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1
})

// Methods

const fetchProducts = async () => {
    loading.value = true
    try {
        const { data } = await axiosClient.get('/products', { params: filters.value })
        products.value = data.data
        pagination.value = data.meta
    } catch (error) {
        alert("Error al cargar productos")
    } finally {
        loading.value = false
    }
}

const fetchCategories = async () => {
    try {
        const { data } = await axiosClient.get('/categories')
        categories.value = data.data
    } catch(e) { console.error("Error al traer categorias") }
}

//Listen filter changes to reload the table
watch(() => [filters.value.search, filters.value.category_id, filters.value.sort_by, filters.value.sort_order], () => {
    filters.value.page = 1
    fetchProducts()
}, { deep: true })

//Page watch
watch(
    () => filters.value.page, 
    () => {
        fetchProducts();
    }
)

onMounted(() => {
    fetchProducts()
    fetchCategories()
})
</script>

<template>
  <div class="p-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Inventario de Productos</h1>
        
        <div class="flex flex-1 max-w-md gap-2">
            <input 
                v-model.lazy="filters.search"
                type="text" 
                placeholder="Buscar por nombre o SKU..." 
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <select 
                v-model="filters.category_id"
                class="px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Todas las categorías</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>
        </div>
    </div>

    <div class="relative bg-white rounded-xl shadow overflow-hidden">
        <div v-if="loading" class="absolute top-0 left-0 w-full h-1 bg-blue-500 animate-pulse"></div>
        
        <table :class="{ 'opacity-50 pointer-events-none': loading }" class="w-full text-left border-collapse transition-opacity duration-300">
            <thead class="bg-gray-50 border-b">
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
                    <td class="p-4 font-semibold">${{ product.price.toFixed(2) }}</td>
                    <td class="p-4 text-center">
                        <span :class="[
                            'px-3 py-1 rounded-full text-xs font-bold',
                            product.stock < 5 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-green-100 text-green-600'
                        ]">
                            {{ product.stock }} unidades
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 font-medium">Editar</button>
                        <button class="text-red-600 hover:text-red-800 font-medium">Borrar</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="p-4 bg-gray-50 border-t flex items-center justify-between">
            <span class="text-sm text-gray-600">
                Mostrando página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            
            <div class="flex gap-2">
                <button 
                    @click="filters.page--" 
                    :disabled="filters.page <= 1"
                    class="px-4 py-2 border rounded-lg bg-white hover:bg-gray-100 disabled:opacity-50"
                >
                    Anterior
                </button>
                <button 
                    @click="filters.page++" 
                    :disabled="filters.page >= pagination.last_page"
                    class="px-4 py-2 border rounded-lg bg-white hover:bg-gray-100 disabled:opacity-50"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
  </div>
</template>