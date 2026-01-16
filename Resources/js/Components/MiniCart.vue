<script setup>
import { ref, computed, watch, inject } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

// Get theme colors
const primaryColor = inject('primaryColor', computed(() => '#4F46E5'));

// Get cart from page props
const page = usePage();
const localCart = ref(page.props.cart || { items: [], totals: {} });

// Watch for page prop changes
watch(() => page.props.cart, (newCart) => {
    if (newCart) {
        localCart.value = newCart;
    }
}, { deep: true });

// Computed values
const items = computed(() => localCart.value.items || []);
const totals = computed(() => localCart.value.totals || {});
const count = computed(() => totals.value.items_count || 0);

// Loading states
const isUpdating = ref({});
const isRemoving = ref({});

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Update quantity
const updateQuantity = async (cartKey, quantity) => {
    if (quantity < 1) {
        removeItem(cartKey);
        return;
    }

    isUpdating.value[cartKey] = true;

    try {
        const response = await axios.post('/cart/update', {
            cart_key: cartKey,
            quantity: quantity,
        });

        if (response.data.success) {
            localCart.value = response.data.cart;
        }
    } catch (error) {
        console.error('Error updating cart:', error);
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
        console.error('Error removing item:', error);
    } finally {
        isRemoving.value[cartKey] = false;
    }
};

// Close on escape
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', handleEscape);
    } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', handleEscape);
    }
});

const handleEscape = (e) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 bg-black/50 z-50"
                @click="$emit('close')"
            />
        </transition>

        <!-- Sidebar -->
        <transition
            enter-active-class="transition-transform duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-300"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="show"
                class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">
                        Количка
                        <span v-if="count > 0" class="text-gray-500 font-normal">({{ count }} артикула)</span>
                    </h2>
                    <button
                        @click="$emit('close')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <!-- Empty Cart -->
                    <div v-if="items.length === 0" class="flex flex-col items-center justify-center h-full text-center">
                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-gray-500 mb-4">Количката е празна</p>
                        <button
                            @click="$emit('close')"
                            class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition-colors"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            Продължи пазаруването
                        </button>
                    </div>

                    <!-- Items List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="item in items"
                            :key="item.cart_key"
                            class="flex gap-4 p-3 bg-gray-50 rounded-xl"
                            :class="{ 'opacity-50': isRemoving[item.cart_key] }"
                        >
                            <!-- Product Image -->
                            <Link
                                :href="`/product/${item.slug}`"
                                @click="$emit('close')"
                                class="flex-shrink-0 w-20 h-20 bg-white rounded-lg overflow-hidden border border-gray-200"
                            >
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="item.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </Link>

                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <Link
                                    :href="`/product/${item.slug}`"
                                    @click="$emit('close')"
                                    class="font-medium text-gray-900 line-clamp-2 text-sm hover:opacity-80 transition-opacity"
                                >
                                    {{ item.name }}
                                </Link>

                                <!-- Options -->
                                <p v-if="item.options_text" class="text-xs text-gray-500 mt-1">
                                    {{ item.options_text }}
                                </p>

                                <!-- Price & Quantity -->
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button
                                            @click="updateQuantity(item.cart_key, item.quantity - 1)"
                                            :disabled="isUpdating[item.cart_key]"
                                            class="px-2 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="px-3 py-1 text-sm font-medium">{{ item.quantity }}</span>
                                        <button
                                            @click="updateQuantity(item.cart_key, item.quantity + 1)"
                                            :disabled="item.quantity >= item.max_quantity || isUpdating[item.cart_key]"
                                            class="px-2 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ formatPrice(item.total) }}</span>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button
                                @click="removeItem(item.cart_key)"
                                :disabled="isRemoving[item.cart_key]"
                                class="flex-shrink-0 p-1 text-gray-400 hover:text-red-600 transition-colors disabled:opacity-50"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="items.length > 0" class="border-t border-gray-200 px-6 py-4 space-y-4">
                    <!-- Totals -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Междинна сума</span>
                            <span class="font-medium">{{ formatPrice(totals.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-lg font-bold">
                            <span>Общо</span>
                            <span :style="{ color: primaryColor }">{{ formatPrice(totals.total) }}</span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="grid grid-cols-2 gap-3">
                        <Link
                            href="/cart"
                            @click="$emit('close')"
                            class="px-4 py-3 text-center border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
                        >
                            Виж количката
                        </Link>
                        <Link
                            href="/checkout"
                            @click="$emit('close')"
                            class="px-4 py-3 text-center text-white rounded-xl font-medium hover:opacity-90 transition-colors"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            Поръчай
                        </Link>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>
