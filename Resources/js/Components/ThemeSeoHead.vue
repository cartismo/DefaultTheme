<script setup>
/**
 * ThemeSeoHead — renders full SEO meta tags from controller's seo prop.
 *
 * Reads $page.props.seo (set by SeoMetaService in controllers) and renders:
 *   - title, description, keywords, robots
 *   - Open Graph tags (og:title, og:description, og:type, og:url, og:image, og:site_name, og:locale)
 *   - Twitter Card tags
 *   - Canonical link
 *   - Hreflang alternate links
 *   - JSON-LD structured data (via DOM script)
 *
 * Falls back to fallbackTitle prop when no seo data is available.
 */
import { Head, usePage } from '@inertiajs/vue3';
import { computed, watchEffect, onUnmounted } from 'vue';

const props = defineProps({
    fallbackTitle: { type: String, default: '' },
});

const page = usePage();

// Per-page SEO data from controller (SeoMetaService)
const seo = computed(() => page.props.seo || {});
const og = computed(() => seo.value.og || {});
const twitter = computed(() => seo.value.twitter || {});

// Title: prefer seo.title, fall back to prop
const title = computed(() => seo.value.title || props.fallbackTitle);

// Hreflangs from HandleInertiaRequests middleware
const hreflangs = computed(() => page.props.hreflangs || []);

// Alternate OG locales (exclude current)
const alternateOgLocales = computed(() => {
    const alts = og.value.locale_alternate;
    return Array.isArray(alts) ? alts : [];
});

// JSON-LD structured data via DOM (reliable script tag injection)
// On initial load, reuses the server-rendered blade script to avoid duplicates.
// On SPA navigation, updates the same element with new data.
let scriptEl = null;

watchEffect(() => {
    const schemas = seo.value.schema;

    if (!schemas || !Array.isArray(schemas) || schemas.length === 0) {
        if (scriptEl) {
            scriptEl.remove();
            scriptEl = null;
        }
        return;
    }

    const jsonLd = schemas.length === 1
        ? { '@context': 'https://schema.org', ...schemas[0] }
        : { '@context': 'https://schema.org', '@graph': schemas };

    if (typeof document === 'undefined') return;

    if (!scriptEl) {
        // Try to find existing blade-rendered JSON-LD script to avoid duplicates
        scriptEl = document.querySelector('script[data-theme-seo]');

        if (scriptEl) {
            // Mark it so we own it from now on
            scriptEl.setAttribute('data-theme-seo', '');
        } else {
            scriptEl = document.createElement('script');
            scriptEl.type = 'application/ld+json';
            scriptEl.setAttribute('data-theme-seo', '');
            document.head.appendChild(scriptEl);
        }
    }

    scriptEl.textContent = JSON.stringify(jsonLd);
});

onUnmounted(() => {
    if (scriptEl) {
        scriptEl.remove();
        scriptEl = null;
    }
});
</script>

<template>
    <Head :title="title">
        <!-- Meta Description -->
        <meta v-if="seo.description" name="description" :content="seo.description" />

        <!-- Meta Keywords -->
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />

        <!-- Robots -->
        <meta v-if="seo.robots" name="robots" :content="seo.robots" />

        <!-- Canonical -->
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />

        <!-- Open Graph -->
        <meta v-if="og.title" property="og:title" :content="og.title" />
        <meta v-if="og.description" property="og:description" :content="og.description" />
        <meta v-if="og.type" property="og:type" :content="og.type" />
        <meta v-if="og.url" property="og:url" :content="og.url" />
        <meta v-if="og.site_name" property="og:site_name" :content="og.site_name" />
        <meta v-if="og.image" property="og:image" :content="og.image" />
        <meta v-if="og.locale" head-key="og-locale" property="og:locale" :content="og.locale" />
        <meta
            v-for="alt in alternateOgLocales"
            :key="alt"
            :head-key="`og-locale-alt-${alt}`"
            property="og:locale:alternate"
            :content="alt"
        />

        <!-- Twitter Card -->
        <meta v-if="twitter.card" name="twitter:card" :content="twitter.card" />
        <meta v-if="twitter.title" name="twitter:title" :content="twitter.title" />
        <meta v-if="twitter.description" name="twitter:description" :content="twitter.description" />
        <meta v-if="twitter.image" name="twitter:image" :content="twitter.image" />

        <!-- Hreflang Alternates -->
        <link
            v-for="hl in hreflangs"
            :key="hl.code"
            :head-key="`hreflang-${hl.code}`"
            rel="alternate"
            :hreflang="hl.code"
            :href="hl.url"
        />
    </Head>
</template>