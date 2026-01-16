<script setup>
import { ref, watch, computed, inject } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

// Get theme colors
const primaryColor = inject('primaryColor', computed(() => '#4F46E5'));

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const searchInput = ref(null);

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Debounced search
const performSearch = debounce(async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;

    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        searchResults.value = data.products || [];
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}, 300);

// Watch search query
watch(searchQuery, () => {
    performSearch();
});

// Focus input when modal opens
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            searchInput.value?.focus();
        }, 100);
        window.addEventListener('keydown', handleKeydown);
    } else {
        document.body.style.overflow = '';
        searchQuery.value = '';
        searchResults.value = [];
        window.removeEventListener('keydown', handleKeydown);
    }
});

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

// Submit search
const submitSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value });
        emit('close');
    }
};

// Click result
const clickResult = () => {
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 bg-black/60 z-50"
                @click="$emit('close')"
            />
        </transition>

        <!-- Modal -->
        <transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="show"
                class="fixed inset-x-4 top-20 md:inset-x-auto md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-2xl bg-white rounded-2xl shadow-2xl z-50 overflow-hidden"
            >
                <!-- Search Input -->
                <form @submit.prevent="submitSearch" class="flex items-center border-b border-gray-200">
                    <svg class="w-5 h-5 ml-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        ref="searchInput"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Търсене на продукти..."
                        class="flex-1 px-4 py-4 text-lg border-none focus:outline-none focus:ring-0"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="searchQuery = ''"
                        class="p-2 mr-2 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="p-2 mr-2 text-gray-400 hover:text-gray-600"
                    >
                        <span class="text-xs px-2 py-1 bg-gray-100 rounded">ESC</span>
                    </button>
                </form>

                <!-- Results -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Loading -->
                    <div v-if="isSearching" class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-8 w-8" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Results List -->
                    <div v-else-if="searchResults.length > 0" class="py-2">
                        <Link
                            v-for="product in searchResults"
                            :key="product.id"
                            :href="`/product/${product.slug}`"
                            @click="clickResult"
                            class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition-colors"
                        >
                            <div class="w-14 h-14 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                                <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold" :style="{ color: primaryColor }">{{ formatPrice(product.final_price) }}</p>
                                <p v-if="product.final_price < product.price" class="text-sm text-gray-400 line-through">
                                    {{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </Link>

                        <!-- View All Results -->
                        <div class="px-4 py-3 border-t border-gray-100">
                            <button
                                @click="submitSearch"
                                class="w-full py-2 text-center font-medium hover:opacity-80 transition-opacity"
                                :style="{ color: primaryColor }"
                            >
                                Виж всички резултати за "{{ searchQuery }}"
                            </button>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div v-else-if="searchQuery.length >= 2" class="py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-500">Няма намерени продукти за "{{ searchQuery }}"</p>
                    </div>

                    <!-- Initial State -->
                    <div v-else class="py-8 px-4">
                        <p class="text-sm text-gray-500 text-center">Въведете поне 2 символа за търсене</p>

                        <!-- Popular Searches -->
                        <div class="mt-6">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Популярни търсения</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="term in ['Телефони', 'Лаптопи', 'Слушалки', 'Часовници']"
                                    :key="term"
                                    @click="searchQuery = term"
                                    class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors"
                                >
                                    {{ term }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>
