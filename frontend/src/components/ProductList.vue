<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
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

const toast = ref({
    show: false,
    message: '',
    type: 'success'
})

const formatCurrency = (value) => {
    const number = parseFloat(value) || 0

    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number)
}

const stats = ref({
    total_value: 0,
    low_stock: 0,
    total_products: 0,
    top_category: 'Cargando...'
});

const animatedStats = ref({
    total_value: 0,
    low_stock: 0,
    total_products: 0
})

// Methods

const fetchProducts = async () => {
    loading.value = true
    try {
        const { data } = await axiosClient.get('/products', { params: filters.value })
        products.value = data.data
        pagination.value = data.meta
    } catch (error) {
        showToast("Error al cargar los productos", "error")
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
    loading.value = true
    try {
        if (isEditing.value) {
            await axiosClient.put(`/products/${form.value.id}`, form.value)
        } else {
            await axiosClient.post(`/products`, form.value)
        }

        showModal.value = false
        await nextTick()

        const successMessage = isEditing.value ? "Producto actualizado" : "Producto creado con éxito"
        resetForm()

        await fetchProducts() //Reload table
        await fetchStats()
        
        showToast(successMessage, "success")

    } catch (error) {
        // Show technical errors
        console.error("Error completo de Axios:", {
            status: error.response?.status,
            data: error.response?.data,
            message: error.message
        })

        const status = error.response?.status
        const serverErrors = error.response?.data?.errors

        if(status === 422 && serverErrors) {
            const firstError = Object.values(serverErrors[0][0])
            showToast(firstError, "error")
        } else if(status === 404) {
            //Error 404
            showToast("El producto ya no existe en el servidor", "error")
        } else {
            //Error 500 or similar
            showToast("No pudimos conectar con el servidor. Intenta más tarde.", "error")
        }
    } finally {
        loading.value = false
    }
}

const deleteProduct = async (id) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        loading.value = true
        try {
            await axiosClient.delete(`/products/${id}`)
            await fetchProducts() //Reload list
            await fetchStats()
            showToast('Producto eliminado correctamente', 'success')
        } catch (error) {
            showToast('No se pudo eliminar el producto', 'error')
        } finally {
            loading.value = false
        }
    }
}

const showToast = (message, type = 'success') => {
    toast.value = { show: true, message, type }

    //Automatic hide in 3 seconds..
    setTimeout(() => {
        toast.value.show = false
    }, 3000)
}

const fetchStats = async () => {
    try {
        const { data } = await axiosClient.get('/dashboard-stats');

        animateNumber('total_products', data.total_products)
        animateNumber('low_stock', data.low_stock)
        animateNumber('total_value', data.total_value)

        //stats.value = data
        stats.value.top_category = data.top_category
    } catch(error) {
        console.error("Error al cargar estadisticas:", error)
    }
}

const animateNumber = (key, target) => {
    const duration = 1000
    const startValue = animatedStats.value[key]
    const startTime = performance.now()

    const update = (currentTime) => {
        const elapsed = currentTime - startTime
        const progress = Math.min(elapsed / duration, 1)

        const easeOutQuad = (t) => t * (2 - t)

        const currentValue = startValue + (target - startValue) * easeOutQuad(progress)
        animatedStats.value[key] = currentValue

        if (progress < 1) {
            requestAnimationFrame(update)
        } else {
            animatedStats.value[key] = target
        }
    }

    requestAnimationFrame(update)
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
        fetchProducts()
    }
)

onMounted(() => {
    fetchProducts()
    fetchCategories()
    fetchStats()
})
</script>

<style>
@keyframes progress-loop {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.animate-progress-loop {
  animation: progress-loop 1.5s infinite linear;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>

<template>
    <div class="p-6">
        <div class="mb-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Inventario de Productos</h1>
                    <p class="text-sm text-gray-500">Gestiona stock y precios en tiempo real</p>
                </div>
                
                <button 
                    @click="showModal = true; isEditing = false; resetForm()"
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 transition shadow-md"
                >
                    <span class="text-xl">+</span> Nuevo Producto
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl font-bold">
                        📦
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Productos</p>
                        <p class="text-2xl font-black text-gray-800">{{ Math.floor(animatedStats.total_products) }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl font-bold">
                        💰
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Valor Capital</p>
                        <p class="text-2xl font-black text-gray-800">$
                            {{ formatCurrency(animatedStats.total_value) }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center text-xl font-bold">
                        ⚠️
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Stock Crítico</p>
                        <p class="text-2xl font-black text-red-600">{{ Math.floor(animatedStats.low_stock) }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl font-bold">
                        🏆
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Categoría Líder</p>
                        <p class="text-md font-black text-gray-800 truncate w-32">{{ stats.top_category }}</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input 
                    v-model.lazy="filters.search"
                    type="text" 
                    placeholder="Buscar por nombre o SKU..." 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                />
                
                <select 
                    v-model="filters.category_id"
                    class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Todas las categorías</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>

                <select 
                    v-model="filters.sort_order"
                    class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="desc">Más recientes primero</option>
                    <option value="asc">Más antiguos primero</option>
                </select>
            </div>
        </div>

        <div class="relative bg-white rounded-xl shadow overflow-hidden border border-gray-100">
    
        <div 
        v-if="loading" 
        class="absolute top-0 left-0 w-full h-1 z-20 overflow-hidden"
        >
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
        <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 overflow-y-auto">
        
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full my-auto overflow-hidden flex flex-col">
                
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
                </div>

                <form @submit.prevent="saveProduct" class="p-6 overflow-y-auto max-h-[70vh] space-y-4">
                    
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
                            @click="showModal = false" 
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
    </div>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="toast.show" 
            class="fixed top-5 right-5 z-[100] max-w-sm w-full shadow-2xl rounded-2xl pointer-events-auto overflow-hidden border"
            :class="toast.type === 'success' ? 'bg-white border-green-100' : 'bg-white border-red-100'">
            <div class="p-4 flex items-center gap-3">
                <div v-if="toast.type === 'success'" class="flex-shrink-0 w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                    <span class="text-xl">✓</span>
                </div>
                <div v-else class="flex-shrink-0 w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                    <span class="text-xl">✕</span>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900">
                        {{ toast.type === 'success' ? '¡Logrado!' : 'Hubo un problema' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ toast.message }}
                    </p>
                </div>
                
                <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600 px-2 text-xl font-light">
                    ×
                </button>
            </div>
            <div class="h-1 bg-gray-100 w-full">
                <div class="h-full bg-blue-500 transition-all duration-[3000ms] ease-linear"
                    :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
                    :style="{ width: toast.show ? '100%' : '0%' }"></div>
            </div>
        </div>
    </Transition>
</template>