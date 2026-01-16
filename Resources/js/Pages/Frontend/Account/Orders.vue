<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    settings: Object,
    orders: Object,
});

const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

const menuItems = [
    { name: 'Табло', href: '/account', icon: 'home' },
    { name: 'Поръчки', href: '/account/orders', icon: 'shopping-bag', current: true },
    { name: 'Адреси', href: '/account/addresses', icon: 'location' },
    { name: 'Профил', href: '/account/profile', icon: 'user' },
    { name: 'Парола', href: '/account/password', icon: 'lock' },
];
</script>

<template>
    <StorefrontLayout title="Моите поръчки">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Моите поръчки</h1>

                <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-24">
                            <nav class="space-y-1">
                                <Link
                                    v-for="item in menuItems"
                                    :key="item.name"
                                    :href="item.href"
                                    class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="item.current ? 'text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    :style="item.current ? { backgroundColor: primaryColor } : {}"
                                >
                                    {{ item.name }}
                                </Link>
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9 mt-8 lg:mt-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="font-semibold text-gray-900">История на поръчките</h2>
                            </div>

                            <div v-if="orders.data?.length > 0">
                                <!-- Desktop Table -->
                                <div class="hidden md:block overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Поръчка</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Артикули</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Сума</th>
                                                <th class="px-6 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 font-medium text-gray-900">{{ order.order_number }}</td>
                                                <td class="px-6 py-4 text-gray-500">{{ order.created_at }}</td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                                        :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }"
                                                    >
                                                        {{ order.status?.name || 'Изчаква' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-gray-500">{{ order.items_count }}</td>
                                                <td class="px-6 py-4 text-right font-medium" :style="{ color: primaryColor }">
                                                    {{ formatPrice(order.total) }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <Link :href="`/account/orders/${order.id}`" class="text-sm font-medium" :style="{ color: primaryColor }">
                                                        Детайли
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile Cards -->
                                <div class="md:hidden divide-y divide-gray-200">
                                    <div v-for="order in orders.data" :key="order.id" class="p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-medium text-gray-900">{{ order.order_number }}</span>
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                                :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }"
                                            >
                                                {{ order.status?.name || 'Изчаква' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                                            <span>{{ order.created_at }}</span>
                                            <span>{{ order.items_count }} артикула</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold" :style="{ color: primaryColor }">{{ formatPrice(order.total) }}</span>
                                            <Link :href="`/account/orders/${order.id}`" class="text-sm font-medium" :style="{ color: primaryColor }">
                                                Виж детайли
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 border-t border-gray-200">
                                    <Pagination :links="orders.links" />
                                </div>
                            </div>

                            <div v-else class="p-8 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <p class="text-gray-500 mb-4">Все още нямате поръчки</p>
                                <Link href="/products" class="inline-flex items-center px-4 py-2 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                                    Започни да пазаруваш
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
