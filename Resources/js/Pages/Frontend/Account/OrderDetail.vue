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
    <StorefrontLayout :title="`Поръчка ${order.order_number}`">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Back Link -->
                <Link href="/account/orders" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-6">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад към поръчки
                </Link>

                <!-- Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Поръчка {{ order.order_number }}</h1>
                            <p class="text-gray-500 mt-1">{{ order.created_at }}</p>
                        </div>
                        <span
                            class="inline-block px-4 py-2 text-sm font-medium rounded-full"
                            :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }"
                        >
                            {{ order.status?.name || 'Изчаква' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="font-semibold text-gray-900">Продукти</h2>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="item in order.items" :key="item.id" class="p-4 flex gap-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900">{{ item.name }}</p>
                                        <p v-if="item.sku" class="text-sm text-gray-500">SKU: {{ item.sku }}</p>
                                        <p v-if="item.options?.length" class="text-sm text-gray-500">
                                            {{ item.options.map(o => `${o.name}: ${o.value}`).join(', ') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">{{ formatPrice(item.unit_price) }}</p>
                                        <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order History -->
                        <div v-if="order.history?.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="font-semibold text-gray-900">История</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div v-for="(event, index) in order.history" :key="index" class="flex gap-4">
                                        <div class="w-3 h-3 rounded-full mt-1.5 flex-shrink-0" :style="{ backgroundColor: event.color || '#9CA3AF' }"></div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ event.status }}</p>
                                            <p v-if="event.comment" class="text-sm text-gray-600">{{ event.comment }}</p>
                                            <p class="text-sm text-gray-400">{{ event.created_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Totals -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Обобщение</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Междинна сума</span>
                                    <span class="text-gray-900">{{ formatPrice(order.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Доставка</span>
                                    <span class="text-gray-900">{{ formatPrice(order.shipping_total) }}</span>
                                </div>
                                <div v-if="order.tax_total > 0" class="flex justify-between">
                                    <span class="text-gray-500">ДДС</span>
                                    <span class="text-gray-900">{{ formatPrice(order.tax_total) }}</span>
                                </div>
                                <div v-if="order.discount_total > 0" class="flex justify-between text-green-600">
                                    <span>Отстъпка</span>
                                    <span>-{{ formatPrice(order.discount_total) }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span class="text-gray-900">Общо</span>
                                    <span :style="{ color: primaryColor }">{{ formatPrice(order.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Адрес за доставка</h3>
                            <div class="text-sm text-gray-600">
                                <p class="font-medium text-gray-900">{{ order.shipping_address.name }}</p>
                                <p>{{ order.shipping_address.address }}</p>
                                <p>{{ order.shipping_address.city }}, {{ order.shipping_address.postcode }}</p>
                                <p>{{ order.shipping_address.country }}</p>
                                <p v-if="order.shipping_address.phone" class="mt-2">{{ order.shipping_address.phone }}</p>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Адрес за фактуриране</h3>
                            <div class="text-sm text-gray-600">
                                <p class="font-medium text-gray-900">{{ order.billing_address.name }}</p>
                                <p>{{ order.billing_address.address }}</p>
                                <p>{{ order.billing_address.city }}, {{ order.billing_address.postcode }}</p>
                                <p>{{ order.billing_address.country }}</p>
                                <p v-if="order.billing_address.email" class="mt-2">{{ order.billing_address.email }}</p>
                            </div>
                        </div>

                        <!-- Payment & Shipping Methods -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Метод на доставка</h4>
                                <p class="text-gray-900">{{ order.shipping_method }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Метод на плащане</h4>
                                <p class="text-gray-900">{{ order.payment_method }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
