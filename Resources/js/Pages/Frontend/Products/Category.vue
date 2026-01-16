<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    settings: Object,
    category: Object,
    products: Object,
    filters: Object,
    breadcrumb: Array,
    currentFilters: Object,
});

// View mode
const viewMode = ref('grid');
const showFilters = ref(false);

// Local filter state
const localFilters = ref({ ...props.currentFilters });

// Apply filters
const applyFilters = () => {
    const params = {};

    if (localFilters.value.manufacturer) params.manufacturer = localFilters.value.manufacturer;
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.in_stock) params.in_stock = 1;
    if (localFilters.value.sale) params.sale = 1;
    if (localFilters.value.sort) params.sort = localFilters.value.sort;

    router.get(`/category/${props.category.slug}`, params, { preserveState: true });
    showFilters.value = false;
};

// Reset filters
const resetFilters = () => {
    localFilters.value = {
        manufacturer: null,
        min_price: null,
        max_price: null,
        in_stock: false,
        sale: false,
        sort: 'newest',
    };
    router.get(`/category/${props.category.slug}`);
    showFilters.value = false;
};

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Has active filters
const hasActiveFilters = computed(() => {
    return props.currentFilters.manufacturer ||
        props.currentFilters.min_price ||
        props.currentFilters.max_price ||
        props.currentFilters.in_stock ||
        props.currentFilters.sale;
});
</script>

<template>
    <StorefrontLayout :title="category.name">
        <div class="bg-gray-50 min-h-screen">
            <!-- Category Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                        <Link href="/" class="hover:text-gray-700">Начало</Link>
                        <span>/</span>
                        <Link href="/products" class="hover:text-gray-700">Продукти</Link>
                        <template v-for="(item, index) in breadcrumb" :key="item.id">
                            <span>/</span>
                            <Link
                                v-if="index < breadcrumb.length - 1"
                                :href="`/category/${item.slug}`"
                                class="hover:text-gray-700"
                            >
                                {{ item.name }}
                            </Link>
                            <span v-else class="text-gray-900 font-medium">{{ item.name }}</span>
                        </template>
                    </nav>

                    <div class="flex items-start gap-6">
                        <!-- Category Image -->
                        <div v-if="category.image_url" class="hidden sm:block w-24 h-24 rounded-xl overflow-hidden flex-shrink-0">
                            <img :src="category.image_url" :alt="category.name" class="w-full h-full object-cover" />
                        </div>

                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ category.name }}</h1>
                            <p v-if="category.description" class="text-gray-600 mt-2 max-w-2xl">{{ category.description }}</p>
                            <p class="text-gray-500 mt-2">{{ products.total }} продукта</p>
                        </div>
                    </div>

                    <!-- Subcategories -->
                    <div v-if="category.children?.length > 0" class="mt-6 flex flex-wrap gap-2">
                        <Link
                            v-for="child in category.children"
                            :key="child.id"
                            :href="`/category/${child.slug}`"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors"
                        >
                            {{ child.name }}
                        </Link>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="flex gap-8">
                    <!-- Sidebar Filters -->
                    <div class="hidden lg:block w-64 flex-shrink-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-semibold text-gray-900">Филтри</h3>
                                <button v-if="hasActiveFilters" @click="resetFilters" class="text-sm text-red-600 hover:text-red-700">
                                    Изчисти
                                </button>
                            </div>

                            <!-- Manufacturers -->
                            <div v-if="filters.manufacturers?.length > 0" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Производител</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <label v-for="manufacturer in filters.manufacturers" :key="manufacturer.id" class="flex items-center cursor-pointer">
                                        <input type="radio" :value="manufacturer.id" v-model="localFilters.manufacturer" @change="applyFilters" class="rounded-full border-gray-300 text-indigo-600" />
                                        <span class="ml-2 text-sm text-gray-600">{{ manufacturer.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Цена</h4>
                                <div class="flex items-center gap-2">
                                    <input v-model="localFilters.min_price" type="number" placeholder="От" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                                    <span class="text-gray-400">-</span>
                                    <input v-model="localFilters.max_price" type="number" placeholder="До" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                                </div>
                                <button @click="applyFilters" class="mt-2 w-full px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                    Приложи
                                </button>
                            </div>

                            <!-- Availability -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Наличност</h4>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="localFilters.in_stock" @change="applyFilters" class="rounded border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">Само налични</span>
                                </label>
                                <label class="flex items-center cursor-pointer mt-2">
                                    <input type="checkbox" v-model="localFilters.sale" @change="applyFilters" class="rounded border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">Само с намаление</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <button @click="showFilters = true" class="lg:hidden flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Филтри
                                </button>

                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 hidden sm:inline">Сортирай:</span>
                                    <select v-model="localFilters.sort" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                                        <option v-for="option in filters.sortOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="hidden sm:flex items-center border border-gray-300 rounded-lg">
                                    <button @click="viewMode = 'grid'" class="p-2 transition-colors" :class="viewMode === 'grid' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </button>
                                    <button @click="viewMode = 'list'" class="p-2 transition-colors" :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div v-if="products.data?.length > 0">
                            <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6' : 'space-y-4'">
                                <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                            </div>

                            <div class="mt-8">
                                <Pagination :links="products.links" />
                            </div>
                        </div>

                        <!-- No Products -->
                        <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Няма продукти в тази категория</h3>
                            <p class="text-gray-500 mb-6">Разгледай другите ни категории</p>
                            <Link href="/products" class="px-6 py-2 text-white rounded-lg" :style="{ backgroundColor: primaryColor }">
                                Виж всички продукти
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
