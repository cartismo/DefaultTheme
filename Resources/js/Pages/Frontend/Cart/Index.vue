<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { useCurrency } from '@/Composables/useCurrency';
import axios from 'axios';

const props = defineProps({
    settings: Object,
    cart: Object,
});

const { t } = useThemeTranslations();
const { formatPrice } = useCurrency();

// Local cart state for optimistic updates
const localCart = ref({ ...props.cart });
const isUpdating = ref({});
const isRemoving = ref({});

// Computed
const isEmpty = computed(() => !localCart.value.items?.length);
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Update quantity
const updateQuantity = async (cartKey, newQuantity) => {
    if (newQuantity < 1) return;

    isUpdating.value[cartKey] = true;

    try {
        const response = await axios.post('/cart/update', {
            cart_key: cartKey,
            quantity: newQuantity,
        });

        if (response.data.success) {
            localCart.value = response.data.cart;
        }
    } catch (error) {
        // silent
    } finally {
        isUpdating.value[cartKey] = false;
    }
};

// Remove item
const removeItem = async (cartKey) => {
    isRemoving.value[cartKey] = true;

    try {
        const response = await axios.delete(`/cart/remove/${cartKey}`);

        if (response.data.success) {
            localCart.value = response.data.cart;
        }
    } catch (error) {
        // silent
    } finally {
        isRemoving.value[cartKey] = false;
    }
};

// Clear cart
const clearCart = async () => {
    if (!confirm(t('cart.clear_confirm'))) return;

    try {
        const response = await axios.post('/cart/clear');

        if (response.data.success) {
            localCart.value = response.data.cart;
        }
    } catch (error) {
        // silent
    }
};
</script>

<template>
    <StorefrontLayout :title="t('cart.title')">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                    <Link href="/" class="hover:text-gray-700">{{ t('home') }}</Link>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">{{ t('cart.title') }}</span>
                </nav>

                <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('cart.title') }}</h1>

                <!-- Empty Cart -->
                <div v-if="isEmpty" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('cart.empty') }}</h2>
                    <p class="text-gray-500 mb-6">{{ t('cart.empty_text') }}</p>
                    <Link href="/products" class="inline-flex items-center px-6 py-3 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ t('cart.browse_products') }}
                    </Link>
                </div>

                <!-- Cart Content -->
                <div v-else class="lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-8">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h2 class="font-semibold text-gray-900">{{ t('cart.items_count', { count: localCart.totals.items_count }) }}</h2>
                                <button @click="clearCart" class="text-sm text-red-600 hover:text-red-700">
                                    {{ t('cart.clear_cart') }}
                                </button>
                            </div>

                            <!-- Items -->
                            <div class="divide-y divide-gray-200">
                                <div v-for="item in localCart.items" :key="item.cart_key" class="p-6 flex gap-4" :class="{ 'opacity-50': isRemoving[item.cart_key] }">
                                    <!-- Image -->
                                    <Link :href="`/product/${item.slug}`" class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </Link>

                                    <!-- Details -->
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/product/${item.slug}`" class="font-medium text-gray-900 hover:text-gray-700 line-clamp-2">
                                            {{ item.name }}
                                        </Link>

                                        <p v-if="item.options_text" class="text-sm text-gray-500 mt-1">{{ item.options_text }}</p>

                                        <p v-if="item.sku" class="text-sm text-gray-400 mt-1">SKU: {{ item.sku }}</p>

                                        <!-- Price -->
                                        <div class="mt-2">
                                            <span class="font-semibold" :style="{ color: primaryColor }">{{ formatPrice(item.price) }}</span>
                                            <span v-if="item.original_price > item.price" class="ml-2 text-sm text-gray-400 line-through">
                                                {{ formatPrice(item.original_price) }}
                                            </span>
                                        </div>

                                        <!-- Mobile: Quantity & Remove -->
                                        <div class="mt-4 flex items-center justify-between sm:hidden">
                                            <div class="flex items-center border border-gray-300 rounded-lg">
                                                <button
                                                    @click="updateQuantity(item.cart_key, item.quantity - 1)"
                                                    :disabled="item.quantity <= 1 || isUpdating[item.cart_key]"
                                                    class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                <span class="px-4 py-2 font-medium">{{ item.quantity }}</span>
                                                <button
                                                    @click="updateQuantity(item.cart_key, item.quantity + 1)"
                                                    :disabled="item.quantity >= item.max_quantity || isUpdating[item.cart_key]"
                                                    class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <button @click="removeItem(item.cart_key)" :disabled="isRemoving[item.cart_key]" class="text-red-600 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Desktop: Quantity -->
                                    <div class="hidden sm:flex items-center border border-gray-300 rounded-lg h-fit">
                                        <button
                                            @click="updateQuantity(item.cart_key, item.quantity - 1)"
                                            :disabled="item.quantity <= 1 || isUpdating[item.cart_key]"
                                            class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="px-4 py-2 font-medium min-w-[3rem] text-center">{{ item.quantity }}</span>
                                        <button
                                            @click="updateQuantity(item.cart_key, item.quantity + 1)"
                                            :disabled="item.quantity >= item.max_quantity || isUpdating[item.cart_key]"
                                            class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Desktop: Total & Remove -->
                                    <div class="hidden sm:flex flex-col items-end gap-2">
                                        <span class="font-semibold text-lg" :style="{ color: primaryColor }">{{ formatPrice(item.total) }}</span>
                                        <button @click="removeItem(item.cart_key)" :disabled="isRemoving[item.cart_key]" class="text-sm text-red-600 hover:text-red-700">
                                            {{ t('cart.remove') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Continue Shopping -->
                        <Link href="/products" class="inline-flex items-center mt-6 text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ t('cart.continue_shopping') }}
                        </Link>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-4 mt-8 lg:mt-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('cart.order_summary') }}</h2>

                            <div class="space-y-3">
                                <div class="flex justify-between text-gray-600">
                                    <span>{{ t('cart.subtotal') }}</span>
                                    <span>{{ formatPrice(localCart.totals.subtotal) }}</span>
                                </div>

                                <div v-if="localCart.totals.discount > 0" class="flex justify-between text-green-600">
                                    <span>{{ t('cart.discount') }}</span>
                                    <span>-{{ formatPrice(localCart.totals.discount) }}</span>
                                </div>

                                <div v-if="localCart.totals.shipping > 0" class="flex justify-between text-gray-600">
                                    <span>{{ t('cart.shipping') }}</span>
                                    <span>{{ formatPrice(localCart.totals.shipping) }}</span>
                                </div>

                                <div v-else class="flex justify-between text-gray-600">
                                    <span>{{ t('cart.shipping') }}</span>
                                    <span class="text-green-600">{{ t('cart.calculated_at_checkout') }}</span>
                                </div>

                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <div class="flex justify-between text-lg font-semibold text-gray-900">
                                        <span>{{ t('cart.total') }}</span>
                                        <span :style="{ color: primaryColor }">{{ formatPrice(localCart.totals.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <Link
                                href="/checkout"
                                class="w-full mt-6 flex items-center justify-center px-6 py-3 text-white rounded-lg font-medium transition-colors"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                                {{ t('cart.proceed_checkout') }}
                            </Link>

                            <!-- Coupon Code -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('cart.coupon_label') }}</label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        :placeholder="t('cart.enter_code')"
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:border-transparent"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                    />
                                    <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                                        {{ t('cart.apply_coupon') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Trust Badges -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-center text-sm text-gray-600">
                                    <div>
                                        <svg class="w-8 h-8 mx-auto mb-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        {{ t('cart.secure_payment') }}
                                    </div>
                                    <div>
                                        <svg class="w-8 h-8 mx-auto mb-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        {{ t('cart.various_methods') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>