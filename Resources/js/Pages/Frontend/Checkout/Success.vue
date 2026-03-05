<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    settings: Object,
    order: Object,
});

const { t } = useThemeTranslations();
const { formatPrice } = useCurrency();
const page = usePage();
const store = computed(() => page.props.store || {});
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');
</script>

<template>
    <StorefrontLayout :title="t('checkout_success.title')">
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

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ t('checkout_success.thank_you') }}</h1>
                    <p class="text-gray-600 mb-6">{{ t('checkout_success.order_accepted') }}</p>

                    <!-- Order Info -->
                    <div class="bg-gray-50 rounded-xl p-6 text-left mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ t('checkout_success.order_number') }}</p>
                                <p class="font-semibold text-gray-900">{{ order.invoice_no }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ t('checkout_success.date') }}</p>
                                <p class="font-semibold text-gray-900">{{ order.created_at }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ t('checkout_success.shipping_method') }}</p>
                                <p class="font-semibold text-gray-900">{{ order.shipping_method }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ t('checkout_success.payment_method') }}</p>
                                <p class="font-semibold text-gray-900">{{ order.payment_method }}</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">{{ t('checkout_success.total') }}</span>
                            <span class="text-xl font-bold" :style="{ color: primaryColor }">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-gray-50 rounded-xl p-6 text-left mb-6">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ t('checkout_success.shipping_address') }}</h3>
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
                            {{ t('checkout_success.view_orders') }}
                        </Link>
                        <Link
                            href="/products"
                            class="px-6 py-3 text-white rounded-xl font-medium transition-colors"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            {{ t('checkout_success.continue_shopping') }}
                        </Link>
                    </div>
                </div>

                <!-- Help Section -->
                <div v-if="store.phone || store.email" class="mt-8 text-center text-gray-600">
                    <p class="mb-2">{{ t('checkout_success.need_help') }}</p>
                    <p>
                        {{ t('checkout_success.contact_us') }}
                        <a v-if="store.phone" :href="'tel:' + store.phone" class="font-medium" :style="{ color: primaryColor }">{{ store.phone }}</a>
                        <template v-if="store.phone && store.email"> {{ t('checkout_success.or') }} </template>
                        <a v-if="store.email" :href="'mailto:' + store.email" class="font-medium" :style="{ color: primaryColor }">{{ store.email }}</a>
                    </p>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>