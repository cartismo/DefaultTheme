<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

const props = defineProps({
    settings: Object,
    customer: Object,
    recentOrders: Array,
    stats: Object,
    currency: Object,
});

const page = usePage();
const trans = computed(() => page.props.translations?.store?.account || {});

const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const menuItems = computed(() => [
    { name: trans.value.dashboard || 'Табло', href: '/account', icon: 'home', current: true },
    { name: trans.value.orders || 'Поръчки', href: '/account/orders', icon: 'shopping-bag' },
    { name: trans.value.addresses || 'Адреси', href: '/account/addresses', icon: 'location' },
    { name: trans.value.profile || 'Профил', href: '/account/profile', icon: 'user' },
    { name: trans.value.password || 'Парола', href: '/account/password', icon: 'lock' },
]);
</script>

<template>
    <StorefrontLayout title="Моят акаунт">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Моят акаунт</h1>

                <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Sidebar -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-24">
                            <div class="text-center mb-4 pb-4 border-b border-gray-200">
                                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-white text-2xl font-bold" :style="{ backgroundColor: primaryColor }">
                                    {{ customer.name?.charAt(0).toUpperCase() }}
                                </div>
                                <h3 class="font-semibold text-gray-900 mt-3">{{ customer.name }}</h3>
                                <p class="text-sm text-gray-500">{{ customer.email }}</p>
                            </div>

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

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    class="w-full flex items-center px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50"
                                >
                                    Изход
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9 mt-8 lg:mt-0">
                        <!-- Welcome -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                Добре дошли, {{ customer.first_name || customer.name }}!
                            </h2>
                            <p class="text-gray-600">
                                От вашия акаунт можете да управлявате поръчките, адресите и профила си.
                            </p>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <p class="text-sm text-gray-500 mb-1">{{ trans.total_orders || 'Общо поръчки' }}</p>
                                <p class="text-3xl font-bold" :style="{ color: primaryColor }">{{ stats.total_orders }}</p>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <p class="text-sm text-gray-500 mb-1">{{ trans.total_spent || 'Общо похарчено' }}</p>
                                <p class="text-3xl font-bold" :style="{ color: primaryColor }">{{ stats.formatted_total_spent }}</p>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">Последни поръчки</h3>
                                <Link href="/account/orders" class="text-sm font-medium" :style="{ color: primaryColor }">
                                    Виж всички
                                </Link>
                            </div>

                            <div v-if="recentOrders.length > 0" class="divide-y divide-gray-200">
                                <div v-for="order in recentOrders" :key="order.id" class="p-4 flex items-center justify-between hover:bg-gray-50">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ order.created_at }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium" :style="{ color: primaryColor }">{{ order.formatted_total }}</p>
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                            :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }"
                                        >
                                            {{ order.status?.name || 'Изчаква' }}
                                        </span>
                                    </div>
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
