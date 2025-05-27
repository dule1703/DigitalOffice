<template>
  <RouterView />
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

let inactivityTimer = null;

// 🕓 Token proverava svakih 30 sekundi
const startTokenExpiryCheck = () => {
  setInterval(() => {
    const token = auth.token;
    if (token) {
      try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        if (payload && payload.exp) {
          const now = Math.floor(Date.now() / 1000);
          const remaining = payload.exp - now;
          console.log(`📆 Token ističe za ${remaining} sekundi`);
          if (remaining <= 0) {
            console.log('⛔ JWT token istekao. Logging out...');
            auth.logout();
            router.push('/login').catch(() => {});
          }
        }
      } catch (e) {
        console.warn('⚠️ Nevalidan token format');
        auth.logout();
        router.push('/login').catch(() => {});
      }
    }
  }, 30 * 1000);
};


// 😴 Logout nakon 5 minuta neaktivnosti
const handleInactivity = () => {
  console.log('😴 Neaktivnost 5 min. Logout.');
  auth.logout();
  router.push('/login').catch(() => {});
};

// 🎯 Reset pri aktivnosti
const resetInactivityTimer = () => {
  clearTimeout(inactivityTimer);
  if (auth.isAuthenticated) {
    inactivityTimer = setTimeout(handleInactivity, 5 * 60 * 1000);
  }
};

onMounted(() => {
  startTokenExpiryCheck();
  ['mousemove', 'keydown', 'scroll', 'click'].forEach(event =>
    window.addEventListener(event, resetInactivityTimer)
  );
  resetInactivityTimer();
});

onUnmounted(() => {
  clearTimeout(inactivityTimer);
  ['mousemove', 'keydown', 'scroll', 'click'].forEach(event =>
    window.removeEventListener(event, resetInactivityTimer)
  );
});
</script>


<style scoped>

</style>
