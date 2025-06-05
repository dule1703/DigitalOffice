<template>
    <div class="offers-container">
        <div class="header">
            <LogoutView>
                <template #logo>
                    <img :src="fullLogo" alt="full-logo"  />
                </template> 
                <template #icon>
                    <img :src="logoutIcon" alt="Logout" width="24" height="24" />
                </template>
            </LogoutView>
        </div>
        <div class="title">
            <p>Izaberite ponudu</p>
        </div>
    </div>

</template>

<script setup>
import { useRouter } from 'vue-router';
import LogoutView from '@/components/LogoutView.vue';
import fullLogo from '@/assets/full-logo.png'
import logoutIcon from '@/assets/logoutIcon.png'


const router = useRouter();


</script>

<style scoped>
.title p {
    font-size: 1.9rem;
    color: #3c4551;
    font-weight: 600;
    text-align: center;
}
</style>