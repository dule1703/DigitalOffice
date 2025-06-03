<template>
  <div class="home-container">
    <div class="header">
      <LogoutView>
        <template #icon>
            <img :src="logoutIcon" alt="Logout" width="24" height="24" />
        </template>
      </LogoutView>
    </div>
  </div>
</template>

<script setup>
import LogoutView from '@/components/LogoutView.vue'
import logoutIcon from '@/assets/logoutIcon.png'
</script>

<style scoped>
.home-container {
  width: 100%;
  height: 100vh;
  background-image: url('../assets/homeBg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
}

.header {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 100;
}
</style>
