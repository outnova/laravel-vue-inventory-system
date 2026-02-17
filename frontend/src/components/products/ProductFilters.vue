<script setup>
const props = defineProps({
    modelValue: Object,
    categories: Array
})

const emit = defineEmits(['update:modelValue'])

const updateFilter = (key, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value
    })
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input 
            :value="modelValue.search"
            @input="updateFilter('search', $event.target.value)"
            type="text" 
            placeholder="Buscar por nombre o SKU..." 
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
        />
        
        <select 
            :value="modelValue.category_id"
            @change="updateFilter('category_id', $event.target.value)"
            class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="">Todas las categorías</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <select 
            :value="modelValue.sort_order"
            @change="updateFilter('sort_order', $event.target.value)"
            class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="desc">Más recientes primero</option>
            <option value="asc">Más antiguos primero</option>
        </select>
    </div>
</template>