<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../../Components/ProductCard.vue';

const props = defineProps({
    settings: Object,
    product: Object,
    relatedProducts: Array,
    breadcrumb: Array,
});

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Gallery
const selectedImage = ref(0);
const allImages = computed(() => {
    const images = [];
    if (props.product.image_url) {
        images.push({
            id: 0,
            url: props.product.image_url,
            urls: { large: props.product.image_url },
        });
    }
    if (props.product.images?.length) {
        images.push(...props.product.images);
    }
    return images;
});

// Quantity
const quantity = ref(props.product.min_quantity || 1);

const increaseQuantity = () => {
    quantity.value++;
};

const decreaseQuantity = () => {
    if (quantity.value > (props.product.min_quantity || 1)) {
        quantity.value--;
    }
};

// Selected options
const selectedOptions = ref({});

// Initialize options with first value
props.product.options?.forEach(option => {
    if (option.values?.length > 0) {
        selectedOptions.value[option.id] = option.values[0].id;
    }
});

// Calculate total price with options
const totalPrice = computed(() => {
    let price = props.product.final_price;

    Object.entries(selectedOptions.value).forEach(([optionId, valueId]) => {
        const option = props.product.options?.find(o => o.id == optionId);
        const value = option?.values?.find(v => v.id == valueId);

        if (value && value.price) {
            if (value.price_prefix === '+') {
                price += value.price;
            } else if (value.price_prefix === '-') {
                price -= value.price;
            }
        }
    });

    return price * quantity.value;
});

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

const page = usePage();

// Add to cart
const addToCart = async () => {
    try {
        const response = await axios.post('/cart/add', {
            product_id: props.product.id,
            quantity: quantity.value,
            options: selectedOptions.value,
        });

        if (response.data.success && response.data.cart) {
            // Update shared cart state
            page.props.cart = response.data.cart;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

// Add to wishlist
const addToWishlist = async () => {
    try {
        await axios.post('/wishlist/add', {
            product_id: props.product.id,
        });
    } catch (error) {
        console.error('Error adding to wishlist:', error);
    }
};

// Active tab
const activeTab = ref('description');

// Zoom modal
const showZoom = ref(false);
</script>

<template>
    <StorefrontLayout :title="product.name">
        <div class="bg-gray-50 min-h-screen">
            <!-- Breadcrumb -->
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 py-4">
                    <nav class="flex items-center space-x-2 text-sm text-gray-500">
                        <Link href="/" class="hover:text-gray-700">Начало</Link>
                        <span>/</span>
                        <Link href="/products" class="hover:text-gray-700">Продукти</Link>
                        <template v-for="item in breadcrumb" :key="item.id">
                            <span>/</span>
                            <Link :href="`/category/${item.slug}`" class="hover:text-gray-700">
                                {{ item.name }}
                            </Link>
                        </template>
                        <span>/</span>
                        <span class="text-gray-900">{{ product.name }}</span>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-8">
                <!-- Product Main Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="grid lg:grid-cols-2 gap-8 p-6 lg:p-8">
                        <!-- Gallery -->
                        <div class="space-y-4">
                            <!-- Main Image -->
                            <div
                                class="relative aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-zoom-in"
                                @click="showZoom = true"
                            >
                                <img
                                    v-if="allImages[selectedImage]"
                                    :src="allImages[selectedImage].urls?.large || allImages[selectedImage].url"
                                    :alt="product.name"
                                    class="w-full h-full object-contain"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>

                                <!-- Badges -->
                                <div class="absolute top-4 left-4 flex flex-col gap-2">
                                    <span v-if="product.discount_percent" class="px-3 py-1 text-sm font-bold text-white bg-red-500 rounded-lg">
                                        -{{ product.discount_percent }}%
                                    </span>
                                    <span v-if="product.tag" class="px-3 py-1 text-sm font-bold text-white rounded-lg" :style="{ backgroundColor: primaryColor }">
                                        {{ product.tag }}
                                    </span>
                                </div>

                                <!-- Zoom icon -->
                                <div class="absolute bottom-4 right-4 p-2 bg-white/80 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Thumbnails -->
                            <div v-if="allImages.length > 1" class="flex gap-3 overflow-x-auto pb-2">
                                <button
                                    v-for="(image, index) in allImages"
                                    :key="image.id"
                                    @click="selectedImage = index"
                                    class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all"
                                    :class="selectedImage === index ? 'border-indigo-500' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <img :src="image.url" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-cover" />
                                </button>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="space-y-6">
                            <!-- Header -->
                            <div>
                                <div v-if="product.manufacturer" class="text-sm text-gray-500 mb-1">
                                    <Link :href="`/products?manufacturer=${product.manufacturer.id}`" class="hover:text-indigo-600">
                                        {{ product.manufacturer.name }}
                                    </Link>
                                </div>
                                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ product.name }}</h1>

                                <!-- Rating -->
                                <div v-if="product.average_rating > 0" class="flex items-center gap-2 mt-3">
                                    <div class="flex">
                                        <svg
                                            v-for="i in 5"
                                            :key="i"
                                            class="w-5 h-5"
                                            :class="i <= product.average_rating ? 'text-yellow-400' : 'text-gray-300'"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">({{ product.reviews_count }} отзива)</span>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="flex items-baseline gap-3">
                                <span class="text-3xl font-bold" :style="{ color: primaryColor }">
                                    {{ formatPrice(product.final_price) }}
                                </span>
                                <span v-if="product.discount_percent" class="text-xl text-gray-400 line-through">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>

                            <!-- Stock Status -->
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="product.in_stock"
                                    class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    На склад
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ product.stock_status?.name || 'Изчерпан' }}
                                </span>
                            </div>

                            <!-- Short Description -->
                            <p v-if="product.description" class="text-gray-600 line-clamp-3">
                                {{ product.description.replace(/<[^>]*>/g, '').substring(0, 200) }}...
                            </p>

                            <!-- Options -->
                            <div v-if="product.options?.length > 0" class="space-y-4">
                                <div v-for="option in product.options" :key="option.id">
                                    <label class="block text-sm font-medium text-gray-900 mb-2">
                                        {{ option.name }}
                                        <span v-if="option.is_required" class="text-red-500">*</span>
                                    </label>

                                    <!-- Select type -->
                                    <select
                                        v-if="option.type === 'select' || !option.type"
                                        v-model="selectedOptions[option.id]"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option v-for="value in option.values" :key="value.id" :value="value.id">
                                            {{ value.name }}
                                            <template v-if="value.price">
                                                ({{ value.price_prefix }}{{ formatPrice(value.price) }})
                                            </template>
                                        </option>
                                    </select>

                                    <!-- Radio/Color type -->
                                    <div v-else-if="option.type === 'radio'" class="flex flex-wrap gap-2">
                                        <label
                                            v-for="value in option.values"
                                            :key="value.id"
                                            class="relative cursor-pointer"
                                        >
                                            <input
                                                type="radio"
                                                :name="`option_${option.id}`"
                                                :value="value.id"
                                                v-model="selectedOptions[option.id]"
                                                class="sr-only peer"
                                            />
                                            <span class="px-4 py-2 border border-gray-300 rounded-lg text-sm peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 hover:border-gray-400 transition-colors">
                                                {{ value.name }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity & Add to Cart -->
                            <div class="flex items-center gap-4">
                                <!-- Quantity -->
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button
                                        @click="decreaseQuantity"
                                        class="px-4 py-3 text-gray-600 hover:bg-gray-100 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <input
                                        v-model="quantity"
                                        type="number"
                                        :min="product.min_quantity || 1"
                                        class="w-16 text-center border-x border-gray-300 py-3 focus:outline-none"
                                    />
                                    <button
                                        @click="increaseQuantity"
                                        class="px-4 py-3 text-gray-600 hover:bg-gray-100 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Add to Cart -->
                                <button
                                    @click="addToCart"
                                    :disabled="!product.in_stock"
                                    class="flex-1 flex items-center justify-center gap-2 px-6 py-3 text-white font-semibold rounded-lg transition-all hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :style="{ backgroundColor: product.in_stock ? primaryColor : '#9CA3AF' }"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>{{ formatPrice(totalPrice) }}</span>
                                </button>

                                <!-- Wishlist -->
                                <button
                                    @click="addToWishlist"
                                    class="p-3 border border-gray-300 rounded-lg text-gray-600 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Product Meta -->
                            <div class="pt-6 border-t border-gray-200 space-y-2 text-sm">
                                <div v-if="product.sku" class="flex">
                                    <span class="w-24 text-gray-500">SKU:</span>
                                    <span class="text-gray-900 font-mono">{{ product.sku }}</span>
                                </div>
                                <div v-if="product.category">
                                    <span class="w-24 text-gray-500">Категория:</span>
                                    <Link :href="`/category/${product.category.slug}`" class="text-indigo-600 hover:underline">
                                        {{ product.category.name }}
                                    </Link>
                                </div>
                                <div v-if="product.weight">
                                    <span class="w-24 text-gray-500">Тегло:</span>
                                    <span class="text-gray-900">{{ product.weight }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details Tabs -->
                <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'description'"
                                class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                                :class="activeTab === 'description'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Описание
                            </button>
                            <button
                                v-if="product.attributes?.length > 0"
                                @click="activeTab = 'specifications'"
                                class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                                :class="activeTab === 'specifications'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Характеристики
                            </button>
                            <button
                                @click="activeTab = 'reviews'"
                                class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                                :class="activeTab === 'reviews'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Отзиви ({{ product.reviews_count }})
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6 lg:p-8">
                        <!-- Description -->
                        <div v-show="activeTab === 'description'">
                            <div v-if="product.description" class="prose max-w-none" v-html="product.description" />
                            <p v-else class="text-gray-500">Няма налично описание за този продукт.</p>
                        </div>

                        <!-- Specifications -->
                        <div v-show="activeTab === 'specifications'">
                            <div v-if="product.attributes?.length > 0" class="space-y-6">
                                <div v-for="group in product.attributes" :key="group.group">
                                    <h3 class="font-semibold text-gray-900 mb-3">{{ group.group }}</h3>
                                    <table class="w-full">
                                        <tbody>
                                            <tr v-for="(attr, index) in group.items" :key="index" :class="index % 2 === 0 ? 'bg-gray-50' : ''">
                                                <td class="py-2 px-4 text-gray-600 w-1/3">{{ attr.name }}</td>
                                                <td class="py-2 px-4 text-gray-900">{{ attr.value }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p v-else class="text-gray-500">Няма налични характеристики.</p>
                        </div>

                        <!-- Reviews -->
                        <div v-show="activeTab === 'reviews'">
                            <p class="text-gray-500">Няма отзиви за този продукт все още.</p>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts?.length > 0" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Подобни продукти</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                        <ProductCard v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" :product="relatedProduct" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Zoom Modal -->
        <Teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showZoom" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4" @click="showZoom = false">
                    <button class="absolute top-4 right-4 p-2 text-white hover:bg-white/20 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img
                        v-if="allImages[selectedImage]"
                        :src="allImages[selectedImage].urls?.large || allImages[selectedImage].url"
                        :alt="product.name"
                        class="max-h-[90vh] max-w-[90vw] object-contain"
                        @click.stop
                    />
                </div>
            </transition>
        </Teleport>
    </StorefrontLayout>
</template>

<style>
.prose img {
    @apply rounded-lg;
}
.prose h2 {
    @apply text-xl font-bold mt-6 mb-3;
}
.prose h3 {
    @apply text-lg font-semibold mt-4 mb-2;
}
.prose p {
    @apply mb-4;
}
.prose ul {
    @apply list-disc list-inside mb-4;
}
.prose ol {
    @apply list-decimal list-inside mb-4;
}
</style>
