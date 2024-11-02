<template>
    <div v-if="visible" :class="alertClasses" role="alert">
      <div class="flex items-center">
        <svg
          class="flex-shrink-0 inline w-4 h-4 mr-3"
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <!-- Use different icons based on the alert type -->
          <path
            v-if="type === 'success'"
            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-3.293-3.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
          />
          <path
            v-else-if="type === 'error'"
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 8a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
            clip-rule="evenodd"
          />
          <!-- Add other icons as needed -->
        </svg>
        <div>
          <span class="font-medium">{{ title }}</span> {{ message }}
        </div>
      </div>
      <!-- Optional Close Button -->
      <button @click="visible = false" class="ml-4 text-xl font-bold">&times;</button>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      type: {
        type: String,
        default: 'info',
      },
      message: {
        type: String,
        required: true,
      },
      title: {
        type: String,
        default: '',
      },
      duration: {
        type: Number,
        default: 5000, // milliseconds
      },
    },
    data() {
      return {
        visible: true,
      };
    },
    computed: {
      alertClasses() {
        return {
          'w-96 mb-4 p-4 text-sm rounded-lg shadow-lg flex items-start': true,
          'text-blue-800 bg-blue-50 dark:bg-gray-800 dark:text-blue-400':
            this.type === 'info',
          'text-red-800 bg-red-50 dark:bg-gray-800 dark:text-red-400':
            this.type === 'error',
          'text-green-800 bg-green-50 dark:bg-gray-800 dark:text-green-400':
            this.type === 'success',
          'text-yellow-800 bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300':
            this.type === 'warning',
          'text-gray-800 bg-gray-50 dark:bg-gray-800 dark:text-gray-300':
            this.type === 'dark',
        };
      },
    },
    mounted() {
      if (this.duration > 0) {
        setTimeout(() => {
          this.visible = false;
        }, this.duration);
      }
    },
  };
  </script>
  