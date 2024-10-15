<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  plans: Object, // Expect plans to be passed from the backend
});

const form = useForm({
  name: '',
  url: '',
  plan: '',
  paymentMethodId: '',
});

let stripe = ref(null);
let elements = ref(null);
let card = ref(null);

// Load Stripe on component mount
onMounted(async () => {
  const stripeKey = import.meta.env.VITE_STRIPE_KEY;
  stripe.value = await loadStripe(stripeKey);
  elements.value = stripe.value.elements();
  
  const style = {
    base: {
      color: '#30313d',
      fontFamily: '"Inter", sans-serif',
      fontSize: '16px',
      '::placeholder': {
        color: '#9ca3af',
      },
      backgroundColor: '#f9fafb', // same as other input fields
      padding: '12px',  // Adjust padding to match
      border: '1px solid #d1d5db', // Adjust border to match other fields
      borderRadius: '0.375rem', // To match Tailwind's rounded-md
    },
    invalid: {
      color: '#dc2626', // Red color for invalid input
    },
  };

  card.value = elements.value.create('card', { style });
  card.value.mount('#card-element');
});

// Submit form and create payment method
const submit = async () => {
  form.processing = true;

  // Create Stripe payment method
  const { error, paymentMethod } = await stripe.value.createPaymentMethod({
    type: 'card',
    card: card.value,
  });

  if (error) {
    form.errors = { card: [error.message] };
    form.processing = false;
  } else {
    form.paymentMethodId = paymentMethod.id;
    form.post(route('websites.store')); // Send form data to the store method
  }
};
</script>

<template>
    <AppLayout title="Add a New Website">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Add a New Website
        </h2>
      </template>
  
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stripe Form with consistent padding and layout -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 mt-8">
          <form @submit.prevent="submit">
            <!-- Website Name Input -->
            <div class="mb-4">
              <InputLabel for="name" value="Website Name" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>
  
            <!-- Website URL Input -->
            <div class="mb-4">
              <InputLabel for="url" value="Website URL" />
              <TextInput
                id="url"
                v-model="form.url"
                type="url"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.url" class="mt-2" />
            </div>
  
            <!-- Plan Selection Dropdown -->
            <div class="mb-4">
              <InputLabel for="plan" value="Select Plan" />
              <select 
                v-model="form.plan" 
                id="plan"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full"
                required
              >
                <optgroup v-for="(plan, planKey) in props.plans" :label="plan.name" :key="planKey">
                  <option v-for="(stripePrice, priceKey) in plan.stripe_prices" :value="stripePrice.price_id" :key="priceKey">
                    {{ plan.name }} - {{ priceKey.replace('_', ' ') }} (${{ stripePrice.price }})
                  </option>
                </optgroup>
              </select>
              <InputError :message="form.errors.plan" class="mt-2" />
            </div>
  
            <!-- Stripe Card Element -->
            <div class="mb-6">
  <InputLabel for="card-element" value="Credit or Debit Card" />
  <div id="card-element" class="mt-1 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm p-2"></div>
  <InputError :message="form.errors.card" class="mt-2" />
</div>
  
            <!-- Submit Button -->
            <div class="flex justify-end">
              <PrimaryButton :disabled="form.processing" class="w-full lg:w-auto">
                Add Website
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>
  </template>