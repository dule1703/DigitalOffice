<template>
  <div class="home-container">
    <div class="header">
      <!-- Logout dugme gore desno -->
      <LogoutView>
        <!-- <template #logo>
          <img :src="fullLogo" alt="full-logo"  />
        </template> -->
        <template #icon>
          <img :src="logoutIcon" alt="Logout" width="24" height="24" />
        </template>
      </LogoutView>

      <!-- Centralni blok: logo, ikonica, labela -->
      <div class="center-stack">
        <img src="@/assets/logoHome.png" alt="logo" class="logo-img" />
        <img src="@/assets/ponuda.png" alt="ponuda" class="ponuda-img" @click="offersPage" />
        <h4 class="label-ponuda">Ponude</h4>
      </div>
    </div>
  </div>
</template>


<script setup>
import {useRouter} from 'vue-router';
import LogoutView from '@/components/LogoutView.vue';
import logoutIcon from '@/assets/logoutIcon.png';


const router = useRouter();

const offersPage = async() => {
  router.push('/offers')
}
</script>

<style scoped>
.home-container {
  width: 100%;
  height: 100vh;
  background-image: url('../assets/homeBg.webp');
  background-size: cover;
  background-position: left;
  background-repeat: no-repeat;
  position: relative;
}

.center-stack {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px; /* prostor između logo, ikonice i labele */
}

.logo-img {
  height: 100px;
  position: relative;
  top: -100px;
}

.ponuda-img {  
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  border-radius: 110px; /* opciono, ako želiš blago zaobljene ivice */
  transition: box-shadow 0.3s ease;
}

.label-ponuda {
  color: #ffffff;
  font-size: 1.2rem;
  font-weight: 500;
  text-shadow: 2px 1px 1px rgba(3, 90, 202, 0.9);
}


</style>
