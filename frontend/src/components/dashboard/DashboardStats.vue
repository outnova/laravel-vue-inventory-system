<script setup>
defineProps({
    animatedStats: Object,
    stats: Object,
    formatCurrency: Function
})
</script>

<template>
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
</template>