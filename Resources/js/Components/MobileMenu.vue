<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    categories: Array,
    menus: Object,
    customer: Object,
});

const emit = defineEmits(['close']);

// Expanded categories
const expandedCategories = ref([]);

const toggleCategory = (categoryId) => {
    const index = expandedCategories.value.indexOf(categoryId);
    if (index > -1) {
        expandedCategories.value.splice(index, 1);
    } else {
        expandedCategories.value.push(categoryId);
    }
};

const isExpanded = (categoryId) => expandedCategories.value.includes(categoryId);

// Root categories for menu
const menuCategories = props.categories?.filter(c => !c.parent_id && c.show_in_menu) || [];

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
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-300"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="show"
                class="fixed left-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 flex flex-col"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200">
                    <span class="text-lg font-bold text-gray-900">Меню</span>
                    <button
                        @click="$emit('close')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Menu Content -->
                <div class="flex-1 overflow-y-auto">
                    <!-- User Section -->
                    <div class="px-4 py-4 border-b border-gray-200">
                        <div v-if="customer" class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-lg font-bold text-indigo-600">
                                    {{ customer.name?.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ customer.name }}</p>
                                <p class="text-sm text-gray-500">{{ customer.email }}</p>
                            </div>
                        </div>
                        <div v-else class="flex space-x-3">
                            <Link
                                href="/login"
                                @click="$emit('close')"
                                class="flex-1 px-4 py-2 text-center bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors"
                            >
                                Вход
                            </Link>
                            <Link
                                href="/register"
                                @click="$emit('close')"
                                class="flex-1 px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                            >
                                Регистрация
                            </Link>
                        </div>
                    </div>

                    <!-- Main Navigation -->
                    <nav class="px-4 py-4">
                        <Link
                            href="/"
                            @click="$emit('close')"
                            class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Начало
                        </Link>

                        <Link
                            href="/products"
                            @click="$emit('close')"
                            class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Всички продукти
                        </Link>

                        <!-- Categories -->
                        <div class="mt-4">
                            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                Категории
                            </h3>
                            <div class="space-y-1">
                                <div v-for="category in menuCategories" :key="category.id">
                                    <div class="flex items-center">
                                        <Link
                                            :href="`/category/${category.slug}`"
                                            @click="$emit('close')"
                                            class="flex-1 flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                        >
                                            <img
                                                v-if="category.image_url"
                                                :src="category.image_url"
                                                :alt="category.name"
                                                class="w-8 h-8 rounded object-cover mr-3"
                                            />
                                            <span v-else class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                                </svg>
                                            </span>
                                            {{ category.name }}
                                        </Link>
                                        <button
                                            v-if="category.children?.length"
                                            @click="toggleCategory(category.id)"
                                            class="p-2 text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                class="w-5 h-5 transition-transform"
                                                :class="{ 'rotate-180': isExpanded(category.id) }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Subcategories -->
                                    <div
                                        v-if="category.children?.length && isExpanded(category.id)"
                                        class="ml-11 mt-1 space-y-1"
                                    >
                                        <Link
                                            v-for="child in category.children"
                                            :key="child.id"
                                            :href="`/category/${child.slug}`"
                                            @click="$emit('close')"
                                            class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                        >
                                            {{ child.name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Links -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <Link
                                href="/blog"
                                @click="$emit('close')"
                                class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                                Блог
                            </Link>

                            <Link
                                href="/contact"
                                @click="$emit('close')"
                                class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Контакти
                            </Link>
                        </div>
                    </nav>
                </div>

                <!-- Footer -->
                <div v-if="customer" class="border-t border-gray-200 px-4 py-4">
                    <Link
                        href="/account"
                        @click="$emit('close')"
                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Моят акаунт
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        @click="$emit('close')"
                        class="w-full flex items-center px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Изход
                    </Link>
                </div>
            </div>
        </transition>
    </Teleport>
</template>
