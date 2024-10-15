<template>
    <AppLayout :title="website.name">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          {{ website.name }}
        </h2>
      </template>
  
      <!-- Display website details -->
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
          <p><strong>Full URL:</strong> {{ website.full_url }}</p>
          <p><strong>Renewal Date:</strong> {{ website.renewal_date ? formatDate(website.renewal_date) : 'N/A' }}</p>
        </div>
  
        <!-- Display status data -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4">Recent Status Codes</h3>
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Time (24hr)</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">UNIX Time</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status Code</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(item, index) in statusData" :key="index">
                <td class="px-6 py-2 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(item.time) }}</td>
                <td class="px-6 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.unix_time }}</td>
                <td class="px-6 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.status_code }}</td>
              </tr>
              <tr v-if="statusData.length === 0">
                <td class="px-6 py-2 text-sm text-gray-900 dark:text-gray-100" colspan="3">No data available.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps } from 'vue';

defineProps({
  website: Object,
  statusData: Array,
});

const formatDate = (date) => {
  return new Date(date).toLocaleString(); // Format to 24-hour format
};
</script>