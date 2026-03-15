<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { FunnelIcon, Squares2X2Icon, Bars3Icon, XMarkIcon, FaceFrownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    products: Object,
    filters: Object,
    currentFilters: Object,
});

const { t } = useThemeTranslations();

// Catalog mode
const catalogMode = computed(() => usePage().props.store?.catalog_mode === true);

// Product listing settings
const listingSettings = computed(() => props.settings?.product_listing || {});
const layoutSettings = computed(() => props.settings?.layout || {});
const showFiltersPanel = computed(() => listingSettings.value.show_filters !== false);
const showSorting = computed(() => listingSettings.value.show_sorting !== false);
const showCompare = computed(() => listingSettings.value.show_compare !== false);
const showWishlistCards = computed(() => listingSettings.value.show_wishlist !== false);
const showQuickViewCards = computed(() => listingSettings.value.show_quick_view !== false);
const sidebarPosition = computed(() => layoutSettings.value.sidebar_position || 'left');

// View mode (grid/list)
const viewMode = ref(listingSettings.value.default_view || 'grid');

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
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

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
    <StorefrontLayout :title="t('all_products')">
        <div class="bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <h1 class="text-3xl font-bold text-gray-900">{{ t('all_products') }}</h1>
                    <p class="text-gray-600 mt-2">{{ t('listing.product_count', { count: products.total }) }}</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="flex gap-8" :class="sidebarPosition === 'right' ? 'flex-row-reverse' : ''">
                    <!-- Sidebar Filters (Desktop) -->
                    <div v-if="showFiltersPanel && sidebarPosition !== 'none'" class="hidden lg:block w-64 flex-shrink-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-semibold text-gray-900">{{ t('listing.filters') }}</h3>
                                <button
                                    v-if="hasActiveFilters"
                                    @click="resetFilters"
                                    class="text-sm text-red-600 hover:text-red-700"
                                >
                                    {{ t('clear') }}
                                </button>
                            </div>

                            <!-- Categories -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('categories') }}</h4>
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
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.manufacturers') }}</h4>
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
                            <div v-if="!catalogMode" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.price_range') }}</h4>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="localFilters.min_price"
                                        type="number"
                                        :placeholder="t('listing.from_placeholder')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                    />
                                    <span class="text-gray-400">-</span>
                                    <input
                                        v-model="localFilters.max_price"
                                        type="number"
                                        :placeholder="t('listing.to_placeholder')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                    />
                                </div>
                                <button
                                    @click="applyFilters"
                                    class="mt-2 w-full px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                                >
                                    {{ t('apply') }}
                                </button>
                            </div>

                            <!-- Availability -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.availability') }}</h4>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="localFilters.in_stock"
                                        @change="applyFilters"
                                        class="rounded border-gray-300 text-indigo-600"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">{{ t('listing.in_stock_only') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer mt-2">
                                    <input
                                        type="checkbox"
                                        v-model="localFilters.sale"
                                        @change="applyFilters"
                                        class="rounded border-gray-300 text-indigo-600"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">{{ t('listing.on_sale_only') }}</span>
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
                                    v-if="showFiltersPanel"
                                    @click="showFilters = true"
                                    class="lg:hidden flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                                >
                                    <FunnelIcon class="w-5 h-5 mr-2" />
                                    {{ t('listing.filters') }}
                                </button>

                                <!-- Sort -->
                                <div v-if="showSorting" class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 hidden sm:inline">{{ t('listing.sort_label') }}</span>
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
                                        <Squares2X2Icon class="w-5 h-5" />
                                    </button>
                                    <button
                                        @click="viewMode = 'list'"
                                        class="p-2 transition-colors"
                                        :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'"
                                    >
                                        <Bars3Icon class="w-5 h-5" />
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
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </span>
                            <span
                                v-if="currentFilters.in_stock"
                                class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm"
                            >
                                {{ t('listing.in_stock_tag') }}
                                <button @click="localFilters.in_stock = false; applyFilters()" class="ml-2">
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </span>
                            <span
                                v-if="currentFilters.sale"
                                class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm"
                            >
                                {{ t('listing.on_sale_tag') }}
                                <button @click="localFilters.sale = false; applyFilters()" class="ml-2">
                                    <XMarkIcon class="w-4 h-4" />
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
                                    :show-wishlist="showWishlistCards"
                                    :show-quick-view="showQuickViewCards"
                                    :show-compare="showCompare"
                                />
                            </div>

                            <!-- Pagination -->
                            <div class="mt-8">
                                <Pagination :links="products.links" />
                            </div>
                        </div>

                        <!-- No Products -->
                        <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                            <FaceFrownIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('listing.no_products') }}</h3>
                            <p class="text-gray-500 mb-6">{{ t('listing.no_products_text') }}</p>
                            <button
                                @click="resetFilters"
                                class="px-6 py-2 text-white rounded-lg"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                {{ t('listing.clear_all_filters') }}
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
                        <h3 class="font-semibold text-gray-900">{{ t('listing.filters') }}</h3>
                        <button @click="showFilters = false" class="p-2 text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Filters Content -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <!-- Categories -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('categories') }}</h4>
                            <div class="space-y-2">
                                <label v-for="category in filters.categories" :key="category.id" class="flex items-center cursor-pointer">
                                    <input type="radio" :value="category.id" v-model="localFilters.category" class="rounded-full border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">{{ category.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Manufacturers -->
                        <div v-if="filters.manufacturers?.length > 0" class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.manufacturers') }}</h4>
                            <div class="space-y-2">
                                <label v-for="manufacturer in filters.manufacturers" :key="manufacturer.id" class="flex items-center cursor-pointer">
                                    <input type="radio" :value="manufacturer.id" v-model="localFilters.manufacturer" class="rounded-full border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">{{ manufacturer.name }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.price_range') }}</h4>
                            <div class="flex items-center gap-2">
                                <input v-model="localFilters.min_price" type="number" :placeholder="t('listing.from_placeholder')" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                                <span class="text-gray-400">-</span>
                                <input v-model="localFilters.max_price" type="number" :placeholder="t('listing.to_placeholder')" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                            </div>
                        </div>

                        <!-- Availability -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.availability') }}</h4>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="localFilters.in_stock" class="rounded border-gray-300 text-indigo-600" />
                                <span class="ml-2 text-sm text-gray-600">{{ t('listing.in_stock_only') }}</span>
                            </label>
                            <label class="flex items-center cursor-pointer mt-2">
                                <input type="checkbox" v-model="localFilters.sale" class="rounded border-gray-300 text-indigo-600" />
                                <span class="ml-2 text-sm text-gray-600">{{ t('listing.on_sale_only') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
                        <button @click="resetFilters" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium">
                            {{ t('clear') }}
                        </button>
                        <button @click="applyFilters" class="flex-1 px-4 py-3 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                            {{ t('apply') }}
                        </button>
                    </div>
                </div>
            </transition>
        </Teleport>
    </StorefrontLayout>
</template>