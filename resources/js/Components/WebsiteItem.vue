<template>
    <li class="p-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg mb-4 flex flex-col lg:flex-row items-center lg:space-x-4">
      <div class="flex items-center flex-1 min-w-0 lg:justify-start w-full lg:w-auto">
        <!-- Status Indicator -->
        <span class="flex h-4 w-4 relative mr-4">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
        </span>
  
        <!-- Website Name and URL -->
        <div class="truncate">
          <strong class="block text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">{{ website.name }}</strong>
          <a :href="website.full_url" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-600 truncate">
            {{ website.full_url }}
          </a>
        </div>
      </div>
  
      <!-- WP Version, Outdated Plugins, Outdated Themes -->
      <div class="flex justify-center flex-grow items-center space-x-6">
        <!-- WP Version Box -->
        <div class="flex items-center p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
          <div class="text-sm">
            <p class="font-semibold text-gray-800 dark:text-gray-200">WP Version</p>
            <p class="text-gray-600 dark:text-gray-400">{{ website.wordpress_version || 'Unknown' }}</p>
          </div>
        </div>
  
        <!-- Outdated Plugins Box -->
        <div class="flex items-center p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
          <div class="text-sm">
            <p class="font-semibold text-gray-800 dark:text-gray-200">Outdated Plugins</p>
            <p class="text-gray-600 dark:text-gray-400">{{ website.outdated_plugins || 0 }}</p>
          </div>
        </div>
  
        <!-- Outdated Themes Box -->
        <div class="flex items-center p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
          <div class="text-sm">
            <p class="font-semibold text-gray-800 dark:text-gray-200">Outdated Themes</p>
            <p class="text-gray-600 dark:text-gray-400">{{ website.outdated_themes || 0 }}</p>
          </div>
        </div>
      </div>
  
      <!-- Manage Button -->
      <div class="flex-shrink-0 lg:ml-auto">
    <Link :href="`/websites/${website.id}`">
      <PrimaryButton>Manage</PrimaryButton>
    </Link>
  </div>
    </li>
</template>

<script setup>
import { defineProps } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  website: Object,
});
</script>

<style scoped>
/* Additional styling if needed */
</style>