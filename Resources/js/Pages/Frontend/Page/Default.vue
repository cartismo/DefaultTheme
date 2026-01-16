<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

defineOptions({ layout: StorefrontLayout });

const props = defineProps({
    settings: Object,
    page: Object,
});

const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');
</script>

<template>
    <Head>
        <title>{{ page.meta_title || page.title }}</title>
        <meta v-if="page.meta_description" name="description" :content="page.meta_description" />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Hero Section with Image -->
        <div v-if="page.image" class="relative h-64 md:h-80 bg-gray-900">
            <img
                :src="page.image"
                :alt="page.title"
                class="w-full h-full object-cover opacity-60"
            />
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">
                        {{ page.title }}
                    </h1>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Title (if no image) -->
            <div v-if="!page.image" class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                    {{ page.title }}
                </h1>
                <div v-if="page.published_at" class="mt-3 text-gray-500">
                    {{ page.published_at }}
                </div>
            </div>

            <!-- Page Content -->
            <div class="bg-white rounded-2xl shadow-sm p-6 md:p-10">
                <article
                    class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-indigo-600 prose-strong:text-gray-900 prose-ul:text-gray-600 prose-ol:text-gray-600"
                    v-html="page.content"
                ></article>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Customize prose link color with primary color */
:deep(.prose a) {
    color: v-bind(primaryColor);
}

:deep(.prose a:hover) {
    opacity: 0.8;
}

/* Better spacing for lists */
:deep(.prose ul),
:deep(.prose ol) {
    margin-top: 1em;
    margin-bottom: 1em;
}

:deep(.prose li) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}

/* Tables */
:deep(.prose table) {
    width: 100%;
    border-collapse: collapse;
}

:deep(.prose th),
:deep(.prose td) {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

:deep(.prose th) {
    background-color: #f9fafb;
    font-weight: 600;
}

/* Images in content */
:deep(.prose img) {
    border-radius: 0.75rem;
    margin: 2rem auto;
}

/* Blockquotes */
:deep(.prose blockquote) {
    border-left-color: v-bind(primaryColor);
    background-color: #f9fafb;
    padding: 1rem 1.5rem;
    border-radius: 0 0.5rem 0.5rem 0;
}
</style>