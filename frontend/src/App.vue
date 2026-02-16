<script setup>
import ProductList from './components/ProductList.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm p-4 mb-4">
      <div class="container mx-auto font-bold text-xl text-blue-600 italic">
        INVENTORY PRO
      </div>
    </nav>
    <main class="container mx-auto">
      <ProductList />
    </main>
  </div>
</template>