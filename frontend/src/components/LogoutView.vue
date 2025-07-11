<template>
<header class="no-print">
  <div class="header-wrapper">
    <div class="logo-wrapper" v-if = "!shouldHide">
      <img :src="fullLogo" alt="Logo" class="logo" />
    </div>
      <nav class="main-nav" v-if = "!shouldHide">
        <router-link to="/dashboard" class="nav-link">Home</router-link>
        <router-link to="/clients" class="nav-link">Klijenti</router-link>
        <router-link to="/offers" class="nav-link">Ponude</router-link>
      </nav>
    <div class="button-wrapper">
      <button class="logout-button" @click="handleLogout">
        <span class="text">Logout</span>
        <span class="icon">
          <img :src="logoutIcon" alt="logout" />
        </span>
      </button>
    </div>
  </div>

</header>

</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth' 
import fullLogo from '@/assets/full-logo.png';
import logoutIcon from '@/assets/logoutIcon.png';

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const hideOnViews = ['Dashboard']
const shouldHide = hideOnViews.includes(route.name)

const handleLogout = () => {
  auth.clearToken?.() 
  router.push('/login')
}
</script>

<style scoped>

header {
  width: 100%;
}

/* Gornji deo: logo i logout */
.header-wrapper {
  margin-left: auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;  
  flex-wrap: wrap;
}

.button-wrapper {
  margin-left: auto;
  display: flex;
  align-items: center;
}

.logout-button {
  background-color: #035aca;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

/* Navigacija ispod */
.main-nav {
  display: flex;
  justify-content: center;
  gap: 2rem;
  padding: 1rem 0;  
  flex-wrap: wrap;
  margin-left: auto;
}

.nav-link {
  text-decoration: none;
  color: #035aca;
  font-weight: 600;
  font-size: 1rem;
}

.nav-link:hover {
  color: #023f91;
}

/* Mobilna prilagodba */
@media (max-width: 768px) {
  .header-wrapper {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .logo-wrapper, .main-nav, .button-wrapper {
    margin: 0 auto;
  }

  .main-nav {
    flex-direction: column;
    align-items: center;
    gap: 1rem;   
  }

  .nav-link {
    font-size: 1.1rem;
  }
}

.logo img {
  display: flex;
  align-items: center;
  margin-right: 8px;
}

.icon {
  display: flex;
  align-items: center;
}

@media print {
  .no-print {
    display: none !important;
  }
}
</style>
