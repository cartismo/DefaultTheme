<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { useCurrency } from '@/Composables/useCurrency';
import { useConfirmDialog } from '@/Composables/useConfirmDialog.js';
import axios from 'axios';
import {
    BookOpenIcon,
    ShoppingCartIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    PhotoIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    ShieldCheckIcon,
    CreditCardIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    cart: Object,
});

const { t } = useThemeTranslations();
const { formatPrice } = useCurrency();
const { confirm } = useConfirmDialog();

// Local cart state for optimistic updates
const localCart = ref({ ...props.cart });
const isUpdating = ref({});
const isRemoving = ref({});

// Computed
const isEmpty = computed(() => !localCart.value.items?.length);
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

// Catalog mode
const catalogMode = computed(() => usePage().props.store?.catalog_mode === true);

// Cart settings
const cartSettings = computed(() => props.settings?.cart || {});
const showCartTotals = computed(() => cartSettings.value.show_cart_totals !== false);

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
    const confirmed = await confirm({
        title: t('cart.clear_title'),
        message: t('cart.clear_confirm'),
        variant: 'warning',
    });
    if (!confirmed) return;

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

                <!-- Catalog Mode - cart disabled -->
                <div v-if="catalogMode" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <BookOpenIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ t('cart.catalog_mode_title') }}</h3>
                    <p class="text-gray-500 mb-6">{{ t('cart.catalog_mode_text') }}</p>
                    <Link href="/products" class="px-6 py-2 text-white rounded-lg" :style="{ backgroundColor: primaryColor }">
                        {{ t('cart.browse_products') }}
                    </Link>
                </div>

                <!-- Empty Cart -->
                <div v-else-if="isEmpty" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                    <ShoppingCartIcon class="w-24 h-24 mx-auto text-gray-300 mb-6" />
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('cart.empty') }}</h2>
                    <p class="text-gray-500 mb-6">{{ t('cart.empty_text') }}</p>
                    <Link href="/products" class="inline-flex items-center px-6 py-3 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                        <ArrowLeftIcon class="w-5 h-5 mr-2" />
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
                                            <PhotoIcon class="w-10 h-10 text-gray-300" />
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
                                                    <MinusIcon class="w-4 h-4" />
                                                </button>
                                                <span class="px-4 py-2 font-medium">{{ item.quantity }}</span>
                                                <button
                                                    @click="updateQuantity(item.cart_key, item.quantity + 1)"
                                                    :disabled="item.quantity >= item.max_quantity || isUpdating[item.cart_key]"
                                                    class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                                >
                                                    <PlusIcon class="w-4 h-4" />
                                                </button>
                                            </div>

                                            <button @click="removeItem(item.cart_key)" :disabled="isRemoving[item.cart_key]" class="text-red-600 hover:text-red-700">
                                                <TrashIcon class="w-5 h-5" />
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
                                            <MinusIcon class="w-4 h-4" />
                                        </button>
                                        <span class="px-4 py-2 font-medium min-w-[3rem] text-center">{{ item.quantity }}</span>
                                        <button
                                            @click="updateQuantity(item.cart_key, item.quantity + 1)"
                                            :disabled="item.quantity >= item.max_quantity || isUpdating[item.cart_key]"
                                            class="p-2 hover:bg-gray-100 disabled:opacity-50"
                                        >
                                            <PlusIcon class="w-4 h-4" />
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
                            <ArrowLeftIcon class="w-5 h-5 mr-2" />
                            {{ t('cart.continue_shopping') }}
                        </Link>
                    </div>

                    <!-- Order Summary -->
                    <div v-if="showCartTotals" class="lg:col-span-4 mt-8 lg:mt-0">
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
                                <ArrowRightIcon class="w-5 h-5 mr-2" />
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
                                        <ShieldCheckIcon class="w-8 h-8 mx-auto mb-1 text-green-600" />
                                        {{ t('cart.secure_payment') }}
                                    </div>
                                    <div>
                                        <CreditCardIcon class="w-8 h-8 mx-auto mb-1 text-green-600" />
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