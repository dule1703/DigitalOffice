<template>
      <LogoutView>
        <template #logo>
          <img :src="fullLogo" alt="full-logo"  />
        </template>
        <template #icon>
          <img :src="logoutIcon" alt="Logout" width="24" height="24" />
        </template>
      </LogoutView>
  <div class="edit-offer-container">
    <h2>Izmena ponude</h2>
    <p>ID ponude: {{ offerId }}</p>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const offerId = route.params.id;
</script>

<style scoped>
.edit-offer-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

h2 {
  color: #035aca;
}
</style>
