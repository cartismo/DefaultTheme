<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';

const props = defineProps({
    settings: Object,
    customer: Object,
    recentOrders: Array,
    stats: Object,
    currency: Object,
});

const { t } = useThemeTranslations();
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

const menuItems = computed(() => [
    { name: t('account.dashboard'), href: '/account', current: true },
    { name: t('account.orders'), href: '/account/orders' },
    { name: t('account.addresses'), href: '/account/addresses' },
    { name: t('account.profile'), href: '/account/profile' },
    { name: t('account.password'), href: '/account/password' },
]);
</script>

<template>
    <StorefrontLayout :title="t('account.title')">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('account.title') }}</h1>

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
                                    {{ t('auth.logout') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9 mt-8 lg:mt-0">
                        <!-- Welcome -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                {{ t('account.welcome', { name: customer.first_name || customer.name }) }}
                            </h2>
                            <p class="text-gray-600">
                                {{ t('account.welcome_text') }}
                            </p>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <p class="text-sm text-gray-500 mb-1">{{ t('account.total_orders') }}</p>
                                <p class="text-3xl font-bold" :style="{ color: primaryColor }">{{ stats.total_orders }}</p>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <p class="text-sm text-gray-500 mb-1">{{ t('account.total_spent') }}</p>
                                <p class="text-3xl font-bold" :style="{ color: primaryColor }">{{ stats.formatted_total_spent }}</p>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">{{ t('account.recent_orders') }}</h3>
                                <Link href="/account/orders" class="text-sm font-medium" :style="{ color: primaryColor }">
                                    {{ t('see_all') }}
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
                                            {{ order.status?.name || t('account.pending') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="p-8 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
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