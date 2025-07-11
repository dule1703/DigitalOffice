<template>
  <div class="header">
    <LogoutView />
  </div>
  <OfferForm mode="edit" :offer-id="offerId" />
</template>

<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import OfferForm from '@/components/OfferForm.vue';
import { useClientStore } from '@/stores/clientStore';
import { useModelStore } from '@/stores/modelStore';
import { useOfferStore } from '@/stores/offerStore';

const route = useRoute();
const offerId = route.params.id;
const clientStore = useClientStore();
const modelStore = useModelStore();
const offerStore = useOfferStore();
const API_URL = import.meta.env.VITE_API_URL;

onMounted(async () => {
  try {
    await clientStore.fetchClients(API_URL);
    await modelStore.fetchModels(API_URL);
    await modelStore.fetchBasicEquipment(API_URL);
    await offerStore.fetchOffer(offerId);
  } catch (err) {
    console.error('Greška pri učitavanju podataka:', err);
    offerStore.errorMessage = 'Došlo je do greške pri učitavanju podataka.';
    setTimeout(() => (offerStore.errorMessage = ''), 4000);
  }
});
</script>