<template>
  <div class="header-wrapper">
    <div class="logo-wrapper">
      <span class="logo">
        <slot name="logo" />
      </span>
    </div>
    <div class="button-wrapper">
        <button class="logout-button" @click="handleLogout">
          <span class="text">Logout</span>
          <span class="icon">
            <slot name="icon" />
          </span>
        </button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth' 

const router = useRouter()
const auth = useAuthStore()

const handleLogout = () => {
  auth.clearToken?.() 
  router.push('/login')
}
</script>

<style scoped>

.header-wrapper {
  display: flex;
  justify-content: space-between; /* ↔ logo levo, dugme desno */
  align-items: center;
  width: 100%;
  padding: 20px;
}

.logout-button {
  display: flex;
  align-items: center;
  gap: 8px;

  background: #035aca;
  border: 2px solid white;
  border-radius: 10px;
  color: white;
  font-size: 18px;
  padding: 10px 16px;
  cursor: pointer;
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
</style>
