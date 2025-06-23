<template>
  <div class="print-offer-container">
    <h2>Štampanje ponude</h2>
    <p>ID ponude: {{ offerId }}</p>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const offerId = route.params.id;
</script>

<style scoped>
.print-offer-container {
  max-width: 1800px;
  margin: 2rem auto;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

h2 {
  color: #035aca;
}
</style>
