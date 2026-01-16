<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

const props = defineProps({
    settings: Object,
    order: Object,
});

const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};
</script>

<template>
    <StorefrontLayout title="Успешна поръчка">
        <div class="bg-gray-50 min-h-screen py-12">
            <div class="max-w-2xl mx-auto px-4">
                <!-- Success Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '20' }">
                        <svg class="w-10 h-10" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Благодарим за поръчката!</h1>
                    <p class="text-gray-600 mb-6">Вашата поръчка беше приета успешно. Ще получите имейл с потвърждение.</p>

                    <!-- Order Info -->
                    <div class="bg-gray-50 rounded-xl p-6 text-left mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Номер на поръчка</p>
                                <p class="font-semibold text-gray-900">{{ order.invoice_no }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Дата</p>
                                <p class="font-semibold text-gray-900">{{ order.created_at }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Метод на доставка</p>
                                <p class="font-semibold text-gray-900">{{ order.shipping_method }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Метод на плащане</p>
                                <p class="font-semibold text-gray-900">{{ order.payment_method }}</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Общо:</span>
                            <span class="text-xl font-bold" :style="{ color: primaryColor }">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-gray-50 rounded-xl p-6 text-left mb-6">
                        <h3 class="font-semibold text-gray-900 mb-2">Адрес за доставка</h3>
                        <p class="text-gray-600">{{ order.shipping_address.name }}</p>
                        <p class="text-gray-600">{{ order.shipping_address.address }}</p>
                        <p class="text-gray-600">{{ order.shipping_address.city }}, {{ order.shipping_address.postcode }}</p>
                        <p class="text-gray-600">{{ order.shipping_address.country }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            href="/account/orders"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
                        >
                            Виж поръчките
                        </Link>
                        <Link
                            href="/products"
                            class="px-6 py-3 text-white rounded-xl font-medium transition-colors"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            Продължи пазаруването
                        </Link>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-8 text-center text-gray-600">
                    <p class="mb-2">Имате въпроси относно поръчката?</p>
                    <p>
                        Свържете се с нас на
                        <a href="tel:+359888123456" class="font-medium" :style="{ color: primaryColor }">+359 888 123 456</a>
                        или
                        <a href="mailto:support@store.com" class="font-medium" :style="{ color: primaryColor }">support@store.com</a>
                    </p>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
