<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

const props = defineProps({
    settings: Object,
});

// Theme colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    newsletter: false,
    terms: false,
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <StorefrontLayout title="Регистрация">
        <Head title="Регистрация" />

        <div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-lg">
                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100">
                        <h1 class="text-2xl font-bold text-gray-900">Създайте акаунт</h1>
                        <p class="mt-2 text-gray-600">Регистрирайте се за да пазарувате по-лесно</p>
                    </div>

                    <!-- Form -->
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Name Row -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- First Name -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Име <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        autofocus
                                        placeholder="Иван"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                        :class="form.errors.first_name ? 'border-red-500' : ''"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1.5 text-sm text-red-600">
                                        {{ form.errors.first_name }}
                                    </p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Фамилия <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="last_name"
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        placeholder="Иванов"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                        :class="form.errors.last_name ? 'border-red-500' : ''"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1.5 text-sm text-red-600">
                                        {{ form.errors.last_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Имейл адрес <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.email ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Телефон
                                </label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    autocomplete="tel"
                                    placeholder="+359 888 123 456"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.phone ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.phone" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Парола <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Минимум 8 символа"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.password ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Password Confirmation -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Потвърдете паролата <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Повторете паролата"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                            </div>

                            <!-- Newsletter -->
                            <div class="flex items-start">
                                <input
                                    id="newsletter"
                                    v-model="form.newsletter"
                                    type="checkbox"
                                    class="w-4 h-4 mt-0.5 rounded border-gray-300 focus:ring-2"
                                    :style="{ color: primaryColor, '--tw-ring-color': primaryColor }"
                                />
                                <label for="newsletter" class="ml-2 text-sm text-gray-600">
                                    Искам да получавам новини и промоции по имейл
                                </label>
                            </div>

                            <!-- Terms -->
                            <div class="flex items-start">
                                <input
                                    id="terms"
                                    v-model="form.terms"
                                    type="checkbox"
                                    required
                                    class="w-4 h-4 mt-0.5 rounded border-gray-300 focus:ring-2"
                                    :class="form.errors.terms ? 'border-red-500' : ''"
                                    :style="{ color: primaryColor, '--tw-ring-color': primaryColor }"
                                />
                                <label for="terms" class="ml-2 text-sm text-gray-600">
                                    Съгласен съм с
                                    <a href="/page/terms" class="underline hover:no-underline" :style="{ color: primaryColor }">Общите условия</a>
                                    и
                                    <a href="/page/privacy" class="underline hover:no-underline" :style="{ color: primaryColor }">Политиката за поверителност</a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                            <p v-if="form.errors.terms" class="text-sm text-red-600">
                                {{ form.errors.terms }}
                            </p>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 px-4 text-white font-semibold rounded-lg transition-all hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Регистрация...' : 'Регистрация' }}
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-600">
                            Вече имате акаунт?
                            <Link
                                href="/login"
                                class="font-semibold hover:underline"
                                :style="{ color: primaryColor }"
                            >
                                Влезте тук
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
