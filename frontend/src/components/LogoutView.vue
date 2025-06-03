<template>
  <button class="logout-button" @click="handleLogout">
    <span class="text">Logout</span>
    <span class="icon">
      <slot name="icon" />
    </span>
  </button>
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
.logout-button {
  display: flex;
  align-items: center;
  gap: 8px;

  background: transparent;
  border: 1px solid white;
  border-radius: 4px;
  color: white;
  font-size: 18px;
  padding: 10px 16px;
  cursor: pointer;
}

.logout-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.icon {
  display: flex;
  align-items: center;
}
</style>
