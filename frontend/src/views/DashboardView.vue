<template>
  <div class="home-container">
    <div class="header">
      <LogoutView></LogoutView>   
    </div> 
        <div class="center-stack">
          <div class="logo-wrapper">
            <img src="@/assets/logoHome.png" alt="logo" class="logo-img" />
          </div>

          <div class="options-wrapper">
            <div>
              <img src="@/assets/ponuda.png" alt="Klijenti" class="ponuda-img" @click="clientsPage" />
              <h4 class="label-ponuda">Klijenti</h4>
            </div>
            <div>
              <img src="@/assets/ponuda.png" alt="ponuda" class="ponuda-img" @click="offersPage" />
              <h4 class="label-ponuda">Ponude</h4>
            </div>
          </div>
        </div>

    
  </div>
</template>


<script setup>
import {useRouter} from 'vue-router';
import LogoutView from '@/components/LogoutView.vue';
import logoutIcon from '@/assets/logoutIcon.png';


const router = useRouter();

const clientsPage = async () => {
  router.push('/clients');
}

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
  gap: 2rem;
}

.logo-wrapper {
  display: flex;
  justify-content: center;
}

.logo-img {
  height: 100px;
}

.options-wrapper {
  display: flex;
  gap: 2rem;
}

.options-wrapper > div {
  display: flex;
  flex-direction: column;
  align-items: center;
}

@media (max-width: 768px) {
  .options-wrapper {
    flex-direction: column;
  }
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

@media (max-width: 768px) {
  .center-stack {
    flex-direction: column;
    top: 60%;
  }
}
</style>
