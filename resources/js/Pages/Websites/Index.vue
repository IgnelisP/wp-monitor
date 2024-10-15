<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import WebsiteItem from '@/Components/WebsiteItem.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue'; 

defineProps({
  websites: Array
});

const addNewWebsite = () => {
  window.location.href = '/websites/create'; // Redirect to the page for adding a new website
};
</script>

<template>
  <AppLayout title="My Websites">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        My Websites
      </h2>
    </template>

    <!-- Add New Website Button aligned to the right -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="flex justify-end my-4">
        <PrimaryButton @click="addNewWebsite">Add New Website</PrimaryButton>
      </div>

      <!-- Websites List -->
      <div v-if="websites.length > 0">
        <ul>
          <WebsiteItem v-for="website in websites" :key="website.id" :website="website" class="mb-4" />
        </ul>
      </div>

      <!-- Show a message if no websites are found -->
      <div v-else>
        <p>No websites found.</p>
      </div>
    </div>
  </AppLayout>
</template>
