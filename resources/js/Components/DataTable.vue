<template>
    <div class="table-container mt-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
      <h3 class="text-xl font-semibold mb-6 flex items-center text-gray-800 dark:text-gray-200">
        {{ title }}
        <!-- Optional Refresh Button with Local SVG Icon -->
        <button v-if="refresh" @click="refresh" class="ml-auto p-2 bg-gray-500 hover:bg-gray-600 transition text-white rounded-full shadow-md">
          <img src="/icons/refresh.svg" alt="Refresh" class="h-5 w-5"/>
        </button>
      </h3>
  
      <!-- Loading State -->
      <div v-if="loading" class="mt-4 flex justify-center">
        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0H4z"></path>
        </svg>
        <span class="ml-2 text-gray-700 dark:text-gray-300">Loading...</span>
      </div>
  
      <!-- Error State -->
      <div v-if="errorMessage" class="text-red-500 text-center font-medium mt-4">{{ errorMessage }}</div>
  
      <!-- Table Content -->
      <div v-if="!loading && !errorMessage" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th v-for="(column, index) in columns" :key="index" class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                {{ column.label }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Data Rows -->
            <tr v-for="(row, rowIndex) in rows" :key="rowIndex" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <td v-for="(column, colIndex) in columns" :key="colIndex" class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                <!-- Render based on column's field or slot -->
                <slot :name="column.field" :row="row">{{ row[column.field] }}</slot>
              </td>
            </tr>
            <!-- Empty State -->
            <tr v-if="rows.length === 0">
              <td :colspan="columns.length" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                No data available.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
    </div>
  </template>
  
  <script>
  export default {
    props: {
      title: {
        type: String,
        required: true
      },
      columns: {
        type: Array,
        required: true
      },
      rows: {
        type: Array,
        required: true
      },
      loading: {
        type: Boolean,
        default: false
      },
      errorMessage: {
        type: String,
        default: ''
      },
      refresh: {
        type: Function,
        default: null
      }
    }
  };
  </script>
  
  <style scoped>
  .table-container {
    width: 100%;
  }
  
  table {
    border-collapse: separate;
    border-spacing: 0;
  }
  
  th:first-child,
  td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
  }
  
  th:last-child,
  td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
  }
  
  </style>
  