<template>
  <div class="header">
    <LogoutView />
  </div>
  <OfferForm mode="create" />
</template>

<script setup>
import OfferForm from '@/components/OfferForm.vue';
import { useClientStore } from '@/stores/clientStore';
import { useModelStore } from '@/stores/modelStore';
import { useOfferStore } from '@/stores/offerStore';
import { onMounted } from 'vue';

const clientStore = useClientStore();
const modelStore = useModelStore();
const offerStore = useOfferStore();
const API_URL = import.meta.env.VITE_API_URL;

onMounted(async () => {
  try {
    await clientStore.fetchClients(API_URL);
    await modelStore.fetchModels(API_URL);
    await modelStore.fetchBasicEquipment(API_URL);
    offerStore.resetForm(); // Ensure form is reset for new offer
  } catch (err) {
    console.error('Greška pri učitavanju podataka:', err);
    offerStore.errorMessage = 'Došlo je do greške pri učitavanju podataka.';
    setTimeout(() => (offerStore.errorMessage = ''), 4000);
  }
});
</script>