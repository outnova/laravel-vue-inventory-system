<script setup>
import { ref, onMounted, watch } from 'vue'
import axiosClient from '../api/axios'

//State
const products = ref([])
const categories = ref([])
const loading = ref(false)
const pagination = ref({})
const showModal = ref(false)
const isEditing = ref(false)

//Reactive filters
const filters = ref({
    search: '',
    category_id: '',
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1
})

const form = ref({
    id: null,
    name: '',
    sku: '',
    price: 0,
    stock: 0,
    category_id: ''
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

const resetForm = () => {
    form.value = { id: null, name: '', sku: '', price: 0, stock: 0, category_id: ''}
    isEditing.value = false
}

const saveProduct = async () => {
    try {
        if (isEditing.value) {
            await axiosClient.put(`/products/${form.value.id}`, form.value)
        } else {
            await axiosClient.post(`/products`, form.value)
        }
        showModal.value = false
        resetForm()
        fetchProducts() //Reload table
    } catch (error) {
        // Imprime el error completo para investigar en la consola
        console.error("Error completo de Axios:", error);

        // Verificamos si hay respuesta del servidor antes de leerla
        const serverErrors = error.response?.data?.errors;
        
        if (serverErrors) {
            alert("Errores de validación: " + JSON.stringify(serverErrors));
        } else {
            alert("Error crítico del servidor (500). Revisa los logs de Laravel.");
        }
    }
}

const deleteProduct = async (id) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return

    try {
        await axiosClient.delete(`/products/${id}`)
        fetchProducts() //Reload list
    } catch (error) {
        alert("No se pudo eliminar el producto")
    }
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
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Inventario de Productos</h1>
                <p class="text-sm text-gray-500">Gestiona el stock y precios de tu catálogo</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button 
                    @click="showModal = true; isEditing = false; resetForm()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 transition shadow-lg shadow-blue-200"
                >
                    <span class="text-xl">+</span> Nuevo
                </button>
            </div>
        </div>
        
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
                    <td class="p-4 text-right space-x-3">
                        <button 
                            @click="showModal = true; isEditing = true; form = { ...product }"
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm transition"
                        >
                            Editar
                        </button>
                        
                        <button 
                            @click="deleteProduct(product.id)"
                            class="text-red-600 hover:text-red-800 font-medium text-sm transition"
                        >
                            Eliminar
                        </button>
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
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
            
            <form @submit.prevent="saveProduct" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input v-model="form.name" type="text" class="w-full border rounded-lg p-2 mt-1" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input v-model="form.sku" type="text" class="w-full border rounded-lg p-2 mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                        <select v-model="form.category_id" class="w-full border rounded-lg p-2 mt-1" required>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio</label>
                        <input v-model="form.price" type="number" step="0.01" class="w-full border rounded-lg p-2 mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input v-model="form.stock" type="number" class="w-full border rounded-lg p-2 mt-1" required>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
  </div>
</template>