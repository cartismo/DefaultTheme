<script setup>
import { ref, computed, provide } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import Footer from '../Components/Footer.vue';
import MiniCart from '../Components/MiniCart.vue';
import MobileMenu from '../Components/MobileMenu.vue';
import SearchModal from '../Components/SearchModal.vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();

// Theme settings from middleware
const settings = computed(() => page.props.themeSettings || {});
const store = computed(() => page.props.store || {});
const categories = computed(() => page.props.categories || []);
const cart = computed(() => page.props.cart || { items: [], total: 0, count: 0 });
const customer = computed(() => page.props.auth?.customer || null);
const currencies = computed(() => page.props.currencies || []);
const currentCurrency = computed(() => page.props.currentCurrency || { code: 'BGN', symbol: 'лв.' });
const languages = computed(() => page.props.languages || []);
const currentLanguage = computed(() => page.props.currentLanguage || { code: 'bg', name: 'Български' });
const menus = computed(() => page.props.menus || {});

// UI State
const showMiniCart = ref(false);
const showMobileMenu = ref(false);
const showSearchModal = ref(false);

// Provide theme data to child components
provide('settings', settings);
provide('store', store);
provide('cart', cart);
provide('customer', customer);
provide('currencies', currencies);
provide('currentCurrency', currentCurrency);
provide('languages', languages);
provide('currentLanguage', currentLanguage);
provide('categories', categories);
provide('menus', menus);

// Colors from settings
const primaryColor = computed(() => settings.value?.colors?.primary || '#4F46E5');
const secondaryColor = computed(() => settings.value?.colors?.secondary || '#6B7280');
const accentColor = computed(() => settings.value?.colors?.accent || '#F59E0B');

// Provide colors
provide('primaryColor', primaryColor);
provide('secondaryColor', secondaryColor);
provide('accentColor', accentColor);

// Cart methods
const openMiniCart = () => {
    showMiniCart.value = true;
};

const closeMiniCart = () => {
    showMiniCart.value = false;
};

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
};

const openSearch = () => {
    showSearchModal.value = true;
};

const closeSearch = () => {
    showSearchModal.value = false;
};

// Provide methods
provide('openMiniCart', openMiniCart);
provide('closeMiniCart', closeMiniCart);
provide('toggleMobileMenu', toggleMobileMenu);
provide('openSearch', openSearch);
provide('closeSearch', closeSearch);

// Page title
const pageTitle = computed(() => {
    const storeName = store.value?.name || 'Cartismo';
    return props.title ? `${props.title} | ${storeName}` : storeName;
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <Header
            :settings="settings"
            :store="store"
            :cart="cart"
            :customer="customer"
            :categories="categories"
            :menus="menus"
            @toggle-mobile-menu="toggleMobileMenu"
            @open-search="openSearch"
            @open-cart="openMiniCart"
        />

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <Footer
            :settings="settings"
            :store="store"
            :menus="menus"
        />

        <!-- Mini Cart Sidebar -->
        <MiniCart
            :show="showMiniCart"
            :cart="cart"
            @close="closeMiniCart"
        />

        <!-- Mobile Menu -->
        <MobileMenu
            :show="showMobileMenu"
            :categories="categories"
            :menus="menus"
            :customer="customer"
            @close="showMobileMenu = false"
        />

        <!-- Search Modal -->
        <SearchModal
            :show="showSearchModal"
            @close="closeSearch"
        />
    </div>
</template>

<style>
/* CSS Variables from theme settings */
:root {
    --color-primary: v-bind(primaryColor);
    --color-secondary: v-bind(secondaryColor);
    --color-accent: v-bind(accentColor);
}
</style>
