<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';

const props = defineProps({
    settings: Object,
    products: Object,
    query: String,
});

const { t } = useThemeTranslations();
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Search form
const searchQuery = ref(props.query || '');

const performSearch = () => {
    if (searchQuery.value.length >= 2) {
        router.get('/search', { q: searchQuery.value }, { preserveState: true });
    }
};
</script>

<template>
    <StorefrontLayout :title="t('search.results_for', { query })">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Search Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ t('search.results_title') }}</h1>

                    <!-- Search Form -->
                    <form @submit.prevent="performSearch" class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="t('search.placeholder')"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                        />
                        <button
                            type="submit"
                            class="px-6 py-3 text-white rounded-lg font-medium"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>

                    <p v-if="query" class="text-gray-500 mt-4">
                        <span v-if="products?.total > 0">
                            {{ t('search.found_count', { count: products.total }) }} "<strong>{{ query }}</strong>"
                        </span>
                        <span v-else>
                            {{ t('search.no_results', { query }) }}
                        </span>
                    </p>
                </div>

                <!-- Results -->
                <div v-if="products?.data?.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                    </div>

                    <div class="mt-8">
                        <Pagination :links="products.links" />
                    </div>
                </div>

                <!-- No Results -->
                <div v-else-if="query" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('search.no_results_title') }}</h3>
                    <p class="text-gray-500 mb-6">{{ t('search.no_results_text') }}</p>

                    <div class="max-w-md mx-auto text-left bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ t('search.tips_title') }}</p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>- {{ t('search.tip_spelling') }}</li>
                            <li>- {{ t('search.tip_general') }}</li>
                            <li>- {{ t('search.tip_keywords') }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Empty Search -->
                <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('search.enter_search') }}</h3>
                    <p class="text-gray-500">{{ t('search.min_chars_search') }}</p>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>