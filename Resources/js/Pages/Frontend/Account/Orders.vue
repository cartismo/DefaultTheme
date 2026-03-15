<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { useCurrency } from '@/Composables/useCurrency';
import { ShoppingBagIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    orders: Object,
});

const { t } = useThemeTranslations();
const { formatPrice } = useCurrency();
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

const menuItems = computed(() => [
    { name: t('account.dashboard'), href: '/account' },
    { name: t('account.orders'), href: '/account/orders', current: true },
    { name: t('account.addresses'), href: '/account/addresses' },
    { name: t('account.profile'), href: '/account/profile' },
    { name: t('account.password'), href: '/account/password' },
]);
</script>

<template>
    <StorefrontLayout :title="t('account.my_orders')">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('account.my_orders') }}</h1>

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
                                <h2 class="font-semibold text-gray-900">{{ t('account.order_history') }}</h2>
                            </div>

                            <div v-if="orders.data?.length > 0">
                                <!-- Desktop Table -->
                                <div class="hidden md:block overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('account.order_number') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('account.order_date') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('account.order_status') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('account.order_items') }}</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ t('account.order_total') }}</th>
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
                                                        {{ order.status?.name || t('account.pending') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-gray-500">{{ order.items_count }}</td>
                                                <td class="px-6 py-4 text-right font-medium" :style="{ color: primaryColor }">
                                                    {{ formatPrice(order.total) }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <Link :href="`/account/orders/${order.id}`" class="text-sm font-medium" :style="{ color: primaryColor }">
                                                        {{ t('account.details') }}
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
                                                {{ order.status?.name || t('account.pending') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                                            <span>{{ order.created_at }}</span>
                                            <span>{{ t('account.items_count', { count: order.items_count }) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold" :style="{ color: primaryColor }">{{ formatPrice(order.total) }}</span>
                                            <Link :href="`/account/orders/${order.id}`" class="text-sm font-medium" :style="{ color: primaryColor }">
                                                {{ t('account.see_details') }}
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 border-t border-gray-200">
                                    <Pagination :links="orders.links" />
                                </div>
                            </div>

                            <div v-else class="p-8 text-center">
                                <ShoppingBagIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                                <p class="text-gray-500 mb-4">{{ t('account.no_orders') }}</p>
                                <Link href="/products" class="inline-flex items-center px-4 py-2 text-white rounded-lg font-medium" :style="{ backgroundColor: primaryColor }">
                                    {{ t('account.start_shopping') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>