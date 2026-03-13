<script setup>
import { ref, computed, provide } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import Footer from '../Components/Footer.vue';
import MiniCart from '../Components/MiniCart.vue';
import MobileMenu from '../Components/MobileMenu.vue';
import SearchModal from '../Components/SearchModal.vue';
import ThemeSeoHead from '../Components/ThemeSeoHead.vue';

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
const currentCurrency = computed(() => page.props.currentCurrency || { code: 'EUR', symbol: '€' });
const languages = computed(() => page.props.languages || []);
const currentLanguage = computed(() => page.props.currentLanguage || { code: 'en', name: 'English' });
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
const primaryColor = computed(() => settings.value?.colors?.primary || '#4334db');
const secondaryColor = computed(() => settings.value?.colors?.secondary || '#5d5bf1');
const accentColor = computed(() => settings.value?.colors?.accent || '#F59E0B');
const successColor = computed(() => settings.value?.colors?.success || '#10B981');
const warningColor = computed(() => settings.value?.colors?.warning || '#F59E0B');
const dangerColor = computed(() => settings.value?.colors?.danger || '#F43F5E');
const infoColor = computed(() => settings.value?.colors?.info || '#3B82F6');

// Provide colors
provide('primaryColor', primaryColor);
provide('secondaryColor', secondaryColor);
provide('accentColor', accentColor);

// Layout settings
const layoutSettings = computed(() => settings.value?.layout || {});
const containerWidth = computed(() => layoutSettings.value.container_width || 'default');
const headerStyle = computed(() => layoutSettings.value.header_style || 'default');
const footerStyle = computed(() => layoutSettings.value.footer_style || 'default');

// Container width CSS value
const containerMaxWidth = computed(() => {
    const widths = { default: '80rem', wide: '90rem', full: '100%' };
    return widths[containerWidth.value] || '80rem';
});

// Typography settings
const fontFamily = computed(() => settings.value?.typography?.font_family || 'Inter');
const fontSizeBase = computed(() => settings.value?.typography?.font_size_base || '16px');
const headingFontFamily = computed(() => settings.value?.typography?.heading_font_family || 'Inter');

provide('containerWidth', containerWidth);
provide('headerStyle', headerStyle);
provide('footerStyle', footerStyle);

// Catalog mode - from store settings (hides prices, cart, and checkout)
const catalogMode = computed(() => store.value?.catalog_mode === true);
provide('catalogMode', catalogMode);

// Cart settings
const cartSettings = computed(() => settings.value?.cart || {});
const showMiniCartEnabled = computed(() => !catalogMode.value && cartSettings.value.show_mini_cart !== false);

// Cart methods
const openMiniCart = () => {
    if (showMiniCartEnabled.value) {
        showMiniCart.value = true;
    } else {
        // Navigate to cart page when mini cart is disabled
        window.location.href = '/cart';
    }
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
    <ThemeSeoHead :fallback-title="pageTitle" />

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
            v-if="showMiniCartEnabled"
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
    --color-success: v-bind(successColor);
    --color-warning: v-bind(warningColor);
    --color-danger: v-bind(dangerColor);
    --color-info: v-bind(infoColor);
    --container-max-width: v-bind(containerMaxWidth);
    --font-family: v-bind(fontFamily);
    --font-size-base: v-bind(fontSizeBase);
    --heading-font-family: v-bind(headingFontFamily);
}

/* Apply container width to all max-w-7xl containers */
.max-w-7xl {
    max-width: var(--container-max-width) !important;
}

/* Typography from theme settings */
body {
    font-family: var(--font-family), system-ui, sans-serif;
    font-size: var(--font-size-base);
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--heading-font-family), system-ui, sans-serif;
}
</style>
