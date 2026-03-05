/*
 * Copyright (c) LemonDev Ltd. (ЛемънДев ООД)
 * Email: info@lemondev.co
 * https://cartismo.com
 */

import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for accessing theme translation strings.
 * Reads from page.props.themeTranslations shared by DefaultThemeServiceProvider.
 *
 * Usage:
 *   const { t, tc } = useThemeTranslations();
 *   t('cart.title')           => "Количка"
 *   t('search.found_count', { count: 5 }) => "5 продукта намерени"
 *   tc('footer')              => { newsletter_title: '...', ... }
 */
export function useThemeTranslations() {
    const page = usePage();
    const translations = computed(() => page.props.themeTranslations || {});

    /**
     * Get a translation by dot-notated key with optional replacements.
     * @param {string} key - e.g. 'cart.title' or 'home'
     * @param {Object} replacements - e.g. { count: 5, query: 'test' }
     * @returns {string}
     */
    const t = (key, replacements = {}) => {
        const keys = key.split('.');
        let value = translations.value;

        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return key; // Return the key itself as fallback
            }
        }

        if (typeof value !== 'string') {
            return key;
        }

        // Replace :placeholder patterns
        return Object.entries(replacements).reduce(
            (str, [placeholder, val]) => str.replace(`:${placeholder}`, val),
            value,
        );
    };

    /**
     * Get a translation section (object) by key.
     * @param {string} key - e.g. 'footer' or 'cart'
     * @returns {Object}
     */
    const tc = (key) => {
        const keys = key.split('.');
        let value = translations.value;

        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return {};
            }
        }

        return typeof value === 'object' ? value : {};
    };

    return { t, tc, translations };
}

export default useThemeTranslations;