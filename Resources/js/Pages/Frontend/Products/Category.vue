<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { FunnelIcon, Squares2X2Icon, Bars3Icon, FaceFrownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    category: Object,
    products: Object,
    filters: Object,
    breadcrumb: Array,
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

// View mode
const viewMode = ref(listingSettings.value.default_view || 'grid');
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
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

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
                        <Link href="/" class="hover:text-gray-700">{{ t('home') }}</Link>
                        <span>/</span>
                        <Link href="/products" class="hover:text-gray-700">{{ t('products') }}</Link>
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
                            <p class="text-gray-500 mt-2">{{ t('listing.product_count', { count: products.total }) }}</p>
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
                <div class="flex gap-8" :class="sidebarPosition === 'right' ? 'flex-row-reverse' : ''">
                    <!-- Sidebar Filters -->
                    <div v-if="showFiltersPanel && sidebarPosition !== 'none'" class="hidden lg:block w-64 flex-shrink-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-semibold text-gray-900">{{ t('listing.filters') }}</h3>
                                <button v-if="hasActiveFilters" @click="resetFilters" class="text-sm text-red-600 hover:text-red-700">
                                    {{ t('clear') }}
                                </button>
                            </div>

                            <!-- Manufacturers -->
                            <div v-if="filters.manufacturers?.length > 0" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.manufacturers') }}</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <label v-for="manufacturer in filters.manufacturers" :key="manufacturer.id" class="flex items-center cursor-pointer">
                                        <input type="radio" :value="manufacturer.id" v-model="localFilters.manufacturer" @change="applyFilters" class="rounded-full border-gray-300 text-indigo-600" />
                                        <span class="ml-2 text-sm text-gray-600">{{ manufacturer.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div v-if="!catalogMode" class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.price_range') }}</h4>
                                <div class="flex items-center gap-2">
                                    <input v-model="localFilters.min_price" type="number" :placeholder="t('listing.from_placeholder')" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                                    <span class="text-gray-400">-</span>
                                    <input v-model="localFilters.max_price" type="number" :placeholder="t('listing.to_placeholder')" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" />
                                </div>
                                <button @click="applyFilters" class="mt-2 w-full px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                    {{ t('apply') }}
                                </button>
                            </div>

                            <!-- Availability -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ t('listing.availability') }}</h4>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="localFilters.in_stock" @change="applyFilters" class="rounded border-gray-300 text-indigo-600" />
                                    <span class="ml-2 text-sm text-gray-600">{{ t('listing.in_stock_only') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer mt-2">
                                    <input type="checkbox" v-model="localFilters.sale" @change="applyFilters" class="rounded border-gray-300 text-indigo-600" />
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
                                <button v-if="showFiltersPanel" @click="showFilters = true" class="lg:hidden flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <FunnelIcon class="w-5 h-5 mr-2" />
                                    {{ t('listing.filters') }}
                                </button>

                                <div v-if="showSorting" class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 hidden sm:inline">{{ t('listing.sort_label') }}</span>
                                    <select v-model="localFilters.sort" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                                        <option v-for="option in filters.sortOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="hidden sm:flex items-center border border-gray-300 rounded-lg">
                                    <button @click="viewMode = 'grid'" class="p-2 transition-colors" :class="viewMode === 'grid' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'">
                                        <Squares2X2Icon class="w-5 h-5" />
                                    </button>
                                    <button @click="viewMode = 'list'" class="p-2 transition-colors" :class="viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600'">
                                        <Bars3Icon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div v-if="products.data?.length > 0">
                            <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6' : 'space-y-4'">
                                <ProductCard v-for="product in products.data" :key="product.id" :product="product" :show-wishlist="showWishlistCards" :show-quick-view="showQuickViewCards" :show-compare="showCompare" />
                            </div>

                            <div class="mt-8">
                                <Pagination :links="products.links" />
                            </div>
                        </div>

                        <!-- No Products -->
                        <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                            <FaceFrownIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('listing.no_products_category') }}</h3>
                            <p class="text-gray-500 mb-6">{{ t('listing.no_products_category_text') }}</p>
                            <Link href="/products" class="px-6 py-2 text-white rounded-lg" :style="{ backgroundColor: primaryColor }">
                                {{ t('listing.see_all_products') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>