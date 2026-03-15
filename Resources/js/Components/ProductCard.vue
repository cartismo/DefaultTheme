<script setup>
import { computed, inject } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useCurrency } from '@/Composables/useCurrency';
import { useThemeTranslations } from '../Composables/useThemeTranslations';
import { PhotoIcon, HeartIcon, EyeIcon, ChartBarSquareIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

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

const primaryColor = inject('primaryColor', computed(() => '#4334db'));
const catalogMode = inject('catalogMode', computed(() => false));
const { formatPrice } = useCurrency();
const { t } = useThemeTranslations();

const hasDiscount = computed(() => props.product.final_price < props.product.price);

const discountPercent = computed(() => {
    if (!hasDiscount.value || !props.product.price) return 0;
    return Math.round(((props.product.price - props.product.final_price) / props.product.price) * 100);
});

const productTag = computed(() => props.product.tag || null);
const inStock = computed(() => props.product.in_stock !== false);

const page = usePage();

const addToCart = async () => {
    try {
        const response = await axios.post('/cart/add', {
            product_id: props.product.id,
            quantity: 1,
        });

        if (response.data.success && response.data.cart) {
            page.props.cart = response.data.cart;
        }
    } catch {
        // silent
    }
};

const productUrl = computed(() => `/product/${props.product.slug || props.product.id}`);
const rating = computed(() => props.product.average_rating || 0);
const reviewCount = computed(() => props.product.reviews_count || 0);
</script>

<template>
    <div class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all duration-300">
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
                    <PhotoIcon class="w-16 h-16" />
                </div>
            </Link>

            <div class="absolute top-3 left-3 flex flex-col gap-2">
                <span v-if="hasDiscount && !catalogMode" class="px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-lg">
                    -{{ discountPercent }}%
                </span>
                <span
                    v-if="productTag"
                    class="px-2 py-1 text-xs font-bold text-white rounded-lg"
                    :style="{ backgroundColor: primaryColor }"
                >
                    {{ productTag }}
                </span>
                <span v-if="!inStock" class="px-2 py-1 text-xs font-bold text-white bg-gray-500 rounded-lg">
                    {{ t('product.out_of_stock') }}
                </span>
            </div>

            <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <button
                    v-if="showWishlist"
                    @click.prevent="$emit('add-to-wishlist', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 transition-colors"
                    :title="t('product.add_to_wishlist')"
                >
                    <HeartIcon class="w-5 h-5" />
                </button>

                <button
                    v-if="showQuickView"
                    @click.prevent="$emit('quick-view', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-indigo-500 hover:bg-indigo-50 transition-colors"
                    :title="t('product.quick_view')"
                >
                    <EyeIcon class="w-5 h-5" />
                </button>

                <button
                    v-if="showCompare"
                    @click.prevent="$emit('add-to-compare', product)"
                    class="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-green-500 hover:bg-green-50 transition-colors"
                    :title="t('product.compare')"
                >
                    <ChartBarSquareIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div class="p-4">
            <p v-if="product.category?.name" class="text-xs text-gray-500 mb-1">
                {{ product.category.name }}
            </p>

            <Link :href="productUrl">
                <h3 class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-2 min-h-[2.5rem] transition-colors">
                    {{ product.name }}
                </h3>
            </Link>

            <div v-if="rating > 0" class="flex items-center gap-1 mt-2">
                <div class="flex">
                    <StarIcon
                        v-for="i in 5"
                        :key="i"
                        class="w-4 h-4"
                        :class="i <= rating ? 'text-yellow-400' : 'text-gray-300'"
                    />
                </div>
                <span class="text-xs text-gray-500">({{ reviewCount }})</span>
            </div>

            <div v-if="!catalogMode" class="mt-3 flex items-center gap-2">
                <span class="text-lg font-bold" :style="{ color: primaryColor }">
                    {{ formatPrice(product.final_price) }}
                </span>
                <span v-if="hasDiscount" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(product.price) }}
                </span>
            </div>

            <button
                v-if="!catalogMode"
                @click="addToCart"
                :disabled="!inStock"
                class="w-full mt-4 px-4 py-2.5 text-sm font-medium text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90"
                :style="{ backgroundColor: inStock ? primaryColor : '#9CA3AF' }"
            >
                <span v-if="inStock" class="flex items-center justify-center gap-2">
                    <ShoppingCartIcon class="w-5 h-5" />
                    {{ t('product.add_to_cart') }}
                </span>
                <span v-else>{{ t('product.out_of_stock') }}</span>
            </button>
        </div>
    </div>
</template>