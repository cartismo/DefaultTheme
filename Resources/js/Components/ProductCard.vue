<script setup>
import { computed, inject } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    showQuickView: {
        type: Boolean,
        default: true,
    },
    showWishlist: {
        type: Boolean,
        default: true,
    },
    showCompare: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['quick-view', 'add-to-wishlist', 'add-to-compare']);

// Inject theme colors
const primaryColor = inject('primaryColor', computed(() => '#4F46E5'));

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Check if product has discount
const hasDiscount = computed(() => {
    return props.product.final_price < props.product.price;
});

// Discount percentage
const discountPercent = computed(() => {
    if (!hasDiscount.value || !props.product.price) return 0;
    return Math.round(((props.product.price - props.product.final_price) / props.product.price) * 100);
});

// Product tag (badge)
const productTag = computed(() => {
    return props.product.tag || null;
});

// In stock
const inStock = computed(() => {
    return props.product.in_stock !== false;
});

const page = usePage();

// Add to cart
const addToCart = async () => {
    try {
        const response = await axios.post('/cart/add', {
            product_id: props.product.id,
            quantity: 1,
        });

        if (response.data.success && response.data.cart) {
            // Update shared cart state
            page.props.cart = response.data.cart;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

// Product URL
const productUrl = computed(() => {
    return `/product/${props.product.slug || props.product.id}`;
});

// Rating (if available)
const rating = computed(() => props.product.average_rating || 0);
const reviewCount = computed(() => props.product.reviews_count || 0);
</script>

<template>
    <div class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all duration-300">
        <!-- Image Container -->
        <div class="relative aspect-square overflow-hidden bg-gray-100">
            <Link :href="productUrl">
                <img
                    v-if="product.image_url"
                    :src="product.image_url"
                    :alt="product.name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </Link>

            <!-- Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-2">
                <!-- Discount Badge -->
                <span
                    v-if="hasDiscount"
                    class="px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-lg"
                >
                    -{{ discountPercent }}%
                </span>

                <!-- Custom Tag -->
                <span
                    v-if="productTag"
                    class="px-2 py-1 text-xs font-bold text-white rounded-lg"
                    :style="{ backgroundColor: primaryColor }"
                >
                    {{ productTag }}
                </span>

                <!-- Out of Stock -->
                <span
                    v-if="!inStock"
                    class="px-2 py-1 text-xs font-bold text-white bg-gray-500 rounded-lg"
                >
                    Изчерпан
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <!-- Wishlist -->
                <button
                    v-if="showWishlist"
                    @click.prevent="$emit('add-to-wishlist', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Добави в любими"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>

                <!-- Quick View -->
                <button
                    v-if="showQuickView"
                    @click.prevent="$emit('quick-view', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-indigo-500 hover:bg-indigo-50 transition-colors"
                    title="Бърз преглед"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                <!-- Compare -->
                <button
                    v-if="showCompare"
                    @click.prevent="$emit('add-to-compare', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-green-500 hover:bg-green-50 transition-colors"
                    title="Сравни"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Category -->
            <p v-if="product.category?.name" class="text-xs text-gray-500 mb-1">
                {{ product.category.name }}
            </p>

            <!-- Name -->
            <Link :href="productUrl">
                <h3 class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-2 min-h-[2.5rem] transition-colors">
                    {{ product.name }}
                </h3>
            </Link>

            <!-- Rating -->
            <div v-if="rating > 0" class="flex items-center gap-1 mt-2">
                <div class="flex">
                    <svg
                        v-for="i in 5"
                        :key="i"
                        class="w-4 h-4"
                        :class="i <= rating ? 'text-yellow-400' : 'text-gray-300'"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <span class="text-xs text-gray-500">({{ reviewCount }})</span>
            </div>

            <!-- Price -->
            <div class="mt-3 flex items-center gap-2">
                <span class="text-lg font-bold" :style="{ color: primaryColor }">
                    {{ formatPrice(product.final_price) }}
                </span>
                <span v-if="hasDiscount" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(product.price) }}
                </span>
            </div>

            <!-- Add to Cart Button -->
            <button
                @click="addToCart"
                :disabled="!inStock"
                class="w-full mt-4 px-4 py-2.5 text-sm font-medium text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90"
                :style="{ backgroundColor: inStock ? primaryColor : '#9CA3AF' }"
            >
                <span v-if="inStock" class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Добави в количката
                </span>
                <span v-else>Изчерпан</span>
            </button>
        </div>
    </div>
</template>
