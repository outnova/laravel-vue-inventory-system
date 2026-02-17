<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import axiosClient from '../api/axios'
import DashboardStats from '../components/dashboard/DashboardStats.vue'
import ProductFilters from '../components/products/ProductFilters.vue'
import ProductModal from '../components/products/ProductModal.vue'
import ProductTable from '../components/products/ProductTable.vue'
import Toast from '../components/common/Toast.vue'
import { formatCurrency } from '../utils/formatters'

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

const openEditModal = (product) => {
    isEditing.value = true
    form.value = {...product }
    showModal.value = true
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
            <DashboardStats
                :animated-stats="animatedStats"
                :stats="stats"
                :format-currency="formatCurrency"
            />
            <ProductFilters
                v-model="filters"
                :categories="categories"
            />
            <ProductTable 
                :products="products" 
                :loading="loading" 
                :pagination="pagination" 
                :filters="filters"
                :format-currency="formatCurrency"
                @edit="openEditModal" 
                @delete="deleteProduct" 
            />
        </div>
        <ProductModal
            :show-modal="showModal"
            :is-editing="isEditing"
            :form="form"
            :categories="categories"
            :loading="loading"
            @close="showModal = false"
            @save="saveProduct"
        />
    </div>
    <Toast
        :show="toast.show"
        :message="toast.message"
        :type="toast.type"
        @close="toast.show = false"
    />
</template>