<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    settings: Object,
    products: Object,
    filters: Object,
    currentFilters: Object,
});

// View mode (grid/list)
const viewMode = ref('grid');

// Filters panel (mobile)
const showFilters = ref(false);

// Local filter state
const localFilters = ref({ ...props.currentFilters });

// Apply filters
const applyFilters = () => {
    const params = {};

    if (localFilters.value.category) params.category = localFilters.value.category;
    if (localFilters.value.manufacturer) params.manufacturer = localFilters.value.manufacturer;
    if (localFilters.value.min_price) params.min_price = localFilters.value.min_price;
    if (localFilters.value.max_price) params.max_price = localFilters.value.max_price;
    if (localFilters.value.in_stock) params.in_stock = 1;
    if (localFilters.value.sale) params.sale = 1;
    if (localFilters.value.sort) params.sort = localFilters.value.sort;
    if (localFilters.value.q) params.q = localFilters.value.q;

    router.get('/products', params, { preserveState: true });
    showFilters.value = false;
};

// Reset filters
const resetFilters = () => {
    localFilters.value = {
        category: null,
        manufacturer: null,
        min_price: null,
        max_price: null,
        in_stock: false,
        sale: false,
        sort: 'newest',
        q: null,
    };
    router.get('/products');
    showFilters.value = false;
};

// Change sort
const changeSort = (sort) => {
    localFilters.value.sort = sort;
    applyFilters();
};

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Has active filters
const hasActiveFilters = computed(() => {
    return props.currentFilters.category ||
        props.currentFilters.manufacturer ||
        props.currentFilters.min_price ||
        props.currentFilters.max_price ||
        props.currentFilters.in_stock ||
        props.currentFilters.sale ||
        props.currentFilters.q;
});
</script>

<template>
    <StorefrontLayout title="Всички продукти">
        <div class="bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <h1 class="text-3xl font-bold text-gray-900">Всички продукти</h1>
                    <p class="text-gray-600 mt-2">{{ products.total }} продукта</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="flex gap-8">
                    <!-- Sidebar Filters (Desktop) -->
                    <div class="hidden lg:block w-64 flex-shrink-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-semibold text-gray-900">Филтри</h3>
                                <button
                                    v-if="hasActiveFilters"
                                    @click="resetFilters"
                                    class="text-sm text-red-600 hover:text-red-700"
                                >
                                    Изчисти
                                </button>
                            </div>

                            <!-- Categories -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Категории</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <label
                                        v-for="category in filters.categories"
                                        :key="category.id"
                                        class="flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="category.id"
                                            v-model="localFilters.category"
                                            @change="applyFilters"
                                            class="rounded-full border-gray-300 text-indigo-600"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">{{ category.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Manufacturers -->
                            <div v-if="filters.manufacturers?.length > 0" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Производител</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <label
                                        v-for="manufacturer in filters.manufacturers"
                                        :key="manufacturer.id"
                                        class="flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="manufacturer.id"
                                            v-model="localFilters.manufacturer"
                                            @change="applyFilters"
                                            class="rounded-full border-gray-300 text-indigo-600"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">{{ manufacturer.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Цена</h4>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="localFilters.min_price"
                                        type="number"
                                        placeholder="От"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                    />
                                    <span class="text-gray-400">-</span>
                                    <input
                                        v-model="localFilters.max_price"
                                        type="number"
                                        placeholder="До"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                    />
                                </div>
                                <button
                                    @click="applyFilters"
                                    class="mt-2 w-full px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                                >
                                    Приложи
                                </button>
                            </div>

                            <!-- Availability -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Наличност</h4>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="localFilters.in_stock"
                                        @change="applyFilters"
                                        class="rounded border-gray-300 text-indigo-600"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Само налични</span>
                                </label>
                                <label class="flex items-center cursor-pointer mt-2">
                                    <input
                                        type="checkbox"
                                        v-model="localFilters.sale"
                                        @change="applyFilters"
                                        class="rounded border-gray-300 text-indigo-600"
                                    />
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
                                <!-- Mobile Filter Button -->
                                <button
                                    @click="showFilters = true"
                                    class="lg:hidden flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Филтри
                                </button>

                                <!-- Sort -->
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 hidden sm:inline">Сортирай:</span>
                                    <select
                                        v-model="localFilters.sort"
                                        @change="applyFilters"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm bg-white"
                                    >
                                        <option v-for="option in filters.sortOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- View Toggle -->
                                <div class="hidden sm:flex items-center border border-gray-300 rounded-lg">
                                    <button
                                        @click="viewMode = 'grid'"
                                        class="p-2 transition-colors"
                                        :class="viewMode === 'grid' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="viewMode = 'list'"
                                        class="p-2 transition-colors"
                                        :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Active Filters Tags -->
                        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-6">
                            <span
                                v-if="currentFilters.q"
                                class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
                            >
                                "{{ currentFilters.q }}"
                                <button @click="localFilters.q = null; applyFilters()" class="ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                            <span
                                v-if="currentFilters.in_stock"
                                class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm"
                            >
                                Налични
                                <button @click="localFilters.in_stock = false; applyFilters()" class="ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                            <span
                                v-if="currentFilters.sale"
                                class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm"
                            >
                                С намаление
                                <button @click="localFilters.sale = false; applyFilters()" class="ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </div>

                        <!-- Products Grid -->
                        <div v-if="products.data?.length > 0">
                            <div
                                :class="viewMode === 'grid'
                                    ? 'grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6'
                                    : 'space-y-4'"
                            >
                                <ProductCard
                                    v-for="product in products.data"
                                    :key="product.id"
                                    :product="product"
                                />
                            </div>

                            <!-- Pagination -->
                            <div class="mt-8">
                                <Pagination :links="products.links" />
                            </div>
                        </div>

                        <!-- No Products -->
                        <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Няма намерени продукти</h3>
                            <p class="text-gray-500 mb-6">Опитай с други филтри или разгледай всички продукти</p>
                            <button
                                @click="resetFilters"
                                class="px-6 py-2 text-white rounded-lg"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                Изчисти филтрите
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Filters Modal -->
        <Teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showFilters" class="fixed inset-0 bg-black/50 z-50 lg:hidden" @click="showFilters = false" />
            </transition>

            <transition
                enter-active-class="transition-transform duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div v-if="showFilters" class="fixed right-0 top-0 h-full w-full max-w-sm bg-white z-50 flex flex-col lg:hidden">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">Филтри</h3>
                        <button @click="showFilters = false" class="p-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Filters Content -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <!-- Same filter content as desktop -->
                        <!-- Categories -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Категории</h4>
                            <div class="space-y-2">
                                <label v-for="category in filters.categories" :key="category.id" class="flex items-center cursor-pointer">
                                    <input type="radio" :value="category.id" v-model="localFilters.category" class="rounded-full border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">{{ category.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Manufacturers -->
                        <div v-if="filters.manufacturers?.length > 0" class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Производител</h4>
                            <div class="space-y-2">
                                <label v-for="manufacturer in filters.manufacturers" :key="manufacturer.id" class="flex items-center cursor-pointer">
                                    <input type="radio" :value="manufacturer.id" v-model="localFilters.manufacturer" class="rounded-full border-gray-300 text-indigo-600" />
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
                        </div>

                        <!-- Availability -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Наличност</h4>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="localFilters.in_stock" class="rounded border-gray-300 text-indigo-600" />
                                <span class="ml-2 text-sm text-gray-600">Само налични</span>
                            </label>
                            <label class="flex items-center cursor-pointer mt-2">
                                <input type="checkbox" v-model="localFilters.sale" class="rounded border-gray-300 text-indigo-600" />
                                <span class="ml-2 text-sm text-gray-600">Само с намаление</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
                        <button @click="resetFilters" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium">
                            Изчисти
                        </button>
                        <button @click="applyFilters" class="flex-1 px-4 py-3 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                            Приложи
                        </button>
                    </div>
                </div>
            </transition>
        </Teleport>
    </StorefrontLayout>
</template>
