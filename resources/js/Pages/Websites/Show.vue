<template>
  <AppLayout :title="website.name">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ website.name }}
      </h2>
    </template>

    <!-- Alerts Section -->
    <div class="fixed top-4 right-4 z-50 space-y-4">
      <div v-for="(alert, index) in alerts" :key="index">
        <Alert
          :type="alert.type"
          :message="alert.message"
          :title="alert.title"
          :duration="alert.duration"
        />
      </div>
    </div>

    <!-- Display website details -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
      <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <p><strong>Full URL:</strong> {{ website.full_url }}</p>
        <p><strong>Renewal Date:</strong> {{ website.renewal_date ? formatDate(website.renewal_date) : 'N/A' }}</p>
      </div>

      <!-- Date-Time Picker Form -->
      <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Select Time Range</h3>
        <form @submit.prevent="fetchStatusData">
          <div class="flex items-center space-x-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
              <input
                type="datetime-local"
                v-model="startTimeRef"
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
              <input
                type="datetime-local"
                v-model="endTimeRef"
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              />
            </div>
            <div class="pt-6">
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white font-semibold rounded-md"
              >
                Submit
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Status Codes Table -->
      <DataTable
        title="Recent Status Codes"
        :columns="statusColumns"
        :rows="statusData"
        :loading="loading"
        :errorMessage="errorMessage"
      />

      <!-- Plugins Section -->
      <DataTable
        title="Installed Plugins"
        :columns="pluginColumns"
        :rows="plugins"
        :loading="loadingPlugins"
        :errorMessage="pluginError"
        :refresh="refreshPlugins"
      >
        <!-- Custom Slot for 'is_active' Column -->
        <template v-slot:is_active="{ row }">
          <label class="inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              :checked="row.is_active"
              @change="togglePlugin(row)"
              class="sr-only peer"
            />
            <div
              class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                     peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full 
                     peer dark:bg-gray-700 peer-checked:bg-blue-600
                     peer-checked:after:translate-x-full peer-checked:after:border-white 
                     after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                     after:bg-white after:border-gray-300 after:border after:rounded-full 
                     after:h-5 after:w-5 after:transition-all dark:border-gray-600"
            ></div>
          </label>
        </template>

        <!-- Actions Slot -->
        <template v-slot:actions="{ row }">
          <button
            v-if="row.is_outdated"
            @click="updatePlugin(row.slug)"
            class="px-2 py-1 bg-yellow-500 text-white rounded-md"
          >
            Update
          </button>
          <button
            @click="deletePlugin(row.slug)"
            class="px-2 py-1 bg-gray-500 text-white rounded-md"
          >
            Delete
          </button>
        </template>
      </DataTable>

      <!-- Themes Section -->
      <DataTable
        title="Installed Themes"
        :columns="themeColumns"
        :rows="themes"
        :loading="loadingThemes"
        :errorMessage="themeError"
        :refresh="refreshThemes"
      >
        <template v-slot:actions="{ row }">
          <button
            @click="deleteTheme(row.slug)"
            class="px-2 py-1 bg-gray-500 text-white rounded-md"
          >
            Delete
          </button>
        </template>
      </DataTable>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Alert from '@/Components/Alert.vue';

const props = defineProps({
  website: Object,
  statusData: Array,
  startTime: String,
  endTime: String,
  plugins: Array,
  themes: Array,
});

const startTimeRef = ref(props.startTime);
const endTimeRef = ref(props.endTime);
const loading = ref(false);
const loadingPlugins = ref(false);
const loadingThemes = ref(false);
const errorMessage = ref('');
const pluginError = ref(null);
const themeError = ref(null);
const alerts = ref([]);

const plugins = computed(() => props.plugins ?? []);
const themes = computed(() => props.themes ?? []);
const statusData = computed(() => props.statusData);

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

// Columns for DataTable
const statusColumns = [
  { label: 'Time (24hr)', field: 'time' },
  { label: 'UNIX Time', field: 'unix_time' },
  { label: 'Status Code', field: 'status_code' },
];

const pluginColumns = [
  { label: 'Name', field: 'name' },
  { label: 'Version', field: 'version' },
  { label: 'Active', field: 'is_active' }, // Toggle switch will be here
  { label: 'Outdated', field: 'is_outdated' },
  { label: 'Actions', field: 'actions' },
];

const themeColumns = [
  { label: 'Name', field: 'name' },
  { label: 'Version', field: 'version' },
  { label: 'Actions', field: 'actions' },
];

// Fetch status data
const fetchStatusData = async () => {
  try {
    loading.value = true;
    errorMessage.value = '';
    await router.get(
      route('websites.show', props.website.id),
      {
        start_time: startTimeRef.value,
        end_time: endTimeRef.value,
      },
      {
        preserveState: true,
        only: ['statusData', 'startTime', 'endTime'],
        preserveScroll: true,
      }
    );
  } catch (error) {
    errorMessage.value = 'Failed to fetch status data.';
  } finally {
    loading.value = false;
  }
};

// Plugin actions
const disablePlugin = async (slug) => {
  loadingPlugins.value = true;
  try {
    await router.post(
      route('plugins.disable', { website: props.website.id, slug }),
      {},
      {
        preserveScroll: true,
        only: ['plugins'],
      }
    );
    refreshPlugins();
    alerts.value.push({
      type: 'success',
      title: 'Success!',
      message: 'Plugin disabled successfully.',
      duration: 5000,
    });
  } catch (error) {
    pluginError.value = 'Failed to disable plugin.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to disable plugin.',
      duration: 5000,
    });
  } finally {
    loadingPlugins.value = false;
  }
};

const enablePlugin = async (slug) => {
  loadingPlugins.value = true;
  try {
    await router.post(
      route('plugins.enable', { website: props.website.id, slug }),
      {},
      {
        preserveScroll: true,
        only: ['plugins'],
      }
    );
    refreshPlugins();
    alerts.value.push({
      type: 'success',
      title: 'Success!',
      message: 'Plugin enabled successfully.',
      duration: 5000,
    });
  } catch (error) {
    pluginError.value = 'Failed to enable plugin.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to enable plugin.',
      duration: 5000,
    });
  } finally {
    loadingPlugins.value = false;
  }
};

const togglePlugin = async (row) => {
  if (row.is_active) {
    // Plugin is currently active, so disable it
    await disablePlugin(row.slug);
  } else {
    // Plugin is currently inactive, so enable it
    await enablePlugin(row.slug);
  }
};

const updatePlugin = async (slug) => {
  loadingPlugins.value = true;
  try {
    await router.post(
      route('plugins.update', { website: props.website.id, slug }),
      {},
      {
        preserveScroll: true,
        only: ['plugins'],
      }
    );
    refreshPlugins();
    alerts.value.push({
      type: 'success',
      title: 'Success!',
      message: 'Plugin updated successfully.',
      duration: 5000,
    });
  } catch (error) {
    pluginError.value = 'Failed to update plugin.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to update plugin.',
      duration: 5000,
    });
  } finally {
    loadingPlugins.value = false;
  }
};

const deletePlugin = async (slug) => {
  loadingPlugins.value = true;
  try {
    await router.delete(
      route('plugins.delete', { website: props.website.id, slug }),
      {},
      {
        preserveScroll: true,
        only: ['plugins'],
      }
    );
    refreshPlugins();
    alerts.value.push({
      type: 'success',
      title: 'Success!',
      message: 'Plugin deleted successfully.',
      duration: 5000,
    });
  } catch (error) {
    pluginError.value = 'Failed to delete plugin.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to delete plugin.',
      duration: 5000,
    });
  } finally {
    loadingPlugins.value = false;
  }
};

// Theme actions
const deleteTheme = async (slug) => {
  loadingThemes.value = true;
  try {
    await router.delete(
      route('themes.delete', { website: props.website.id, slug }),
      {},
      {
        preserveScroll: true,
        only: ['themes'],
      }
    );
    refreshThemes();
    alerts.value.push({
      type: 'success',
      title: 'Success!',
      message: 'Theme deleted successfully.',
      duration: 5000,
    });
  } catch (error) {
    themeError.value = 'Failed to delete theme.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to delete theme.',
      duration: 5000,
    });
  } finally {
    loadingThemes.value = false;
  }
};

// Refresh plugins
const refreshPlugins = async () => {
  loadingPlugins.value = true;
  pluginError.value = null;
  try {
    await router.reload({
      only: ['plugins'],
      preserveScroll: true,
    });
  } catch (error) {
    pluginError.value = 'Failed to fetch plugins.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to fetch plugins.',
      duration: 5000,
    });
  } finally {
    loadingPlugins.value = false;
  }
};

// Refresh themes
const refreshThemes = async () => {
  loadingThemes.value = true;
  themeError.value = null;
  try {
    await router.reload({
      only: ['themes'],
      preserveScroll: true,
    });
  } catch (error) {
    themeError.value = 'Failed to fetch themes.';
    alerts.value.push({
      type: 'error',
      title: 'Error!',
      message: 'Failed to fetch themes.',
      duration: 5000,
    });
  } finally {
    loadingThemes.value = false;
  }
};
</script>
