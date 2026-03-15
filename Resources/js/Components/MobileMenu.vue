<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useThemeTranslations } from '../Composables/useThemeTranslations';
import {
    XMarkIcon,
    HomeIcon,
    CubeIcon,
    Squares2X2Icon,
    ChevronDownIcon,
    NewspaperIcon,
    EnvelopeIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    categories: Array,
    menus: Object,
    customer: Object,
});

const emit = defineEmits(['close']);
const { t } = useThemeTranslations();

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

const menuCategories = props.categories?.filter(c => !c.parent_id && c.show_in_menu) || [];

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
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200">
                    <span class="text-lg font-bold text-gray-900">{{ t('mobile.menu') }}</span>
                    <button
                        @click="$emit('close')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
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
                                {{ t('mobile.login') }}
                            </Link>
                            <Link
                                href="/register"
                                @click="$emit('close')"
                                class="flex-1 px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                            >
                                {{ t('mobile.register') }}
                            </Link>
                        </div>
                    </div>

                    <nav class="px-4 py-4">
                        <Link
                            href="/"
                            @click="$emit('close')"
                            class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <HomeIcon class="w-5 h-5 mr-3" />
                            {{ t('mobile.home') }}
                        </Link>

                        <Link
                            href="/products"
                            @click="$emit('close')"
                            class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <CubeIcon class="w-5 h-5 mr-3" />
                            {{ t('mobile.all_products') }}
                        </Link>

                        <div class="mt-4">
                            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                {{ t('mobile.categories') }}
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
                                                <Squares2X2Icon class="w-4 h-4 text-gray-400" />
                                            </span>
                                            {{ category.name }}
                                        </Link>
                                        <button
                                            v-if="category.children?.length"
                                            @click="toggleCategory(category.id)"
                                            class="p-2 text-gray-400 hover:text-gray-600"
                                        >
                                            <ChevronDownIcon
                                                class="w-5 h-5 transition-transform"
                                                :class="{ 'rotate-180': isExpanded(category.id) }"
                                            />
                                        </button>
                                    </div>

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

                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <Link
                                href="/blog"
                                @click="$emit('close')"
                                class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <NewspaperIcon class="w-5 h-5 mr-3" />
                                {{ t('mobile.blog') }}
                            </Link>

                            <Link
                                href="/contact"
                                @click="$emit('close')"
                                class="flex items-center px-3 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <EnvelopeIcon class="w-5 h-5 mr-3" />
                                {{ t('mobile.contact') }}
                            </Link>
                        </div>
                    </nav>
                </div>

                <div v-if="customer" class="border-t border-gray-200 px-4 py-4">
                    <Link
                        href="/account"
                        @click="$emit('close')"
                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <UserCircleIcon class="w-5 h-5 mr-3" />
                        {{ t('mobile.my_account') }}
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        @click="$emit('close')"
                        class="w-full flex items-center px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3" />
                        {{ t('mobile.logout') }}
                    </Link>
                </div>
            </div>
        </transition>
    </Teleport>
</template>