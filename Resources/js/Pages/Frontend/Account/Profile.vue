<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

const props = defineProps({
    settings: Object,
    customer: Object,
});

const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const form = useForm({
    first_name: props.customer.first_name || '',
    last_name: props.customer.last_name || '',
    phone: props.customer.phone || '',
});

const isSubmitting = ref(false);
const successMessage = ref('');

const submit = () => {
    isSubmitting.value = true;
    successMessage.value = '';

    form.put('/account/profile', {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Профилът е обновен успешно';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const menuItems = [
    { name: 'Табло', href: '/account', icon: 'home' },
    { name: 'Поръчки', href: '/account/orders', icon: 'shopping-bag' },
    { name: 'Адреси', href: '/account/addresses', icon: 'location' },
    { name: 'Профил', href: '/account/profile', icon: 'user', current: true },
    { name: 'Парола', href: '/account/password', icon: 'lock' },
];
</script>

<template>
    <StorefrontLayout title="Профил">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Настройки на профила</h1>

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
                        <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-6">Лична информация</h2>

                            <!-- Success Message -->
                            <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                                {{ successMessage }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Име</label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="form.errors.first_name ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Фамилия</label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="form.errors.last_name ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                                </div>

                                <!-- Email (readonly) -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Имейл</label>
                                    <input
                                        :value="customer.email"
                                        type="email"
                                        disabled
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">Имейлът не може да бъде променен</p>
                                </div>

                                <!-- Phone -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="form.errors.phone ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing || isSubmitting"
                                    class="px-6 py-3 text-white rounded-lg font-medium disabled:opacity-50"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    {{ isSubmitting ? 'Запазване...' : 'Запази промените' }}
                                </button>
                            </div>
                        </form>

                        <!-- Change Password Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Промяна на парола</h2>
                            <p class="text-gray-500 mb-4">За да промените паролата си, използвайте линка по-долу</p>
                            <Link href="/account/password" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                Промени парола
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
