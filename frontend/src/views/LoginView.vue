<template>
  <div class="login">
    <h1>Login</h1>
    <p>Please enter your credentials.</p>
    <form @submit.prevent="login">
      <input v-model="email" placeholder="Email" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <button type="submit" :disabled="isLoading">
        {{ isLoading ? 'Logging in...' : 'Login' }}
      </button>
    </form>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
  <router-link to="/register" class="register-link">Registruj se</router-link>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const email = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const router = useRouter();
const auth = useAuthStore();

const login = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = 'Unesite korisničko ime i lozinku.';
    return;
  }

  // 🚫 Resetuj stare podatke iz localStorage i store-a
  localStorage.removeItem('token');
  localStorage.removeItem('user_id');
  localStorage.removeItem('2fa_verified');
  auth.logout();

  isLoading.value = true;
  errorMessage.value = '';
  const API_URL = import.meta.env.VITE_API_URL;
  console.log("API_URL:", import.meta.env.VITE_API_URL);

  try {
    const response = await fetch(`${API_URL}/login.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });

    const result = await response.json();

    if (result.status === 'success') {
      if (result['2fa_verified'] && result.token) {
        // ✅ Direktan login jer je korisnik već verifikovan
        auth.setUser(result.user_id, result.token);
        router.push('/dashboard');
      } else {
        // ⏳ Čeka se verifikacija koda
        sessionStorage.setItem('pending_user_id', result.user_id);
        router.push('/verify-2fa');
      }
    } else {
      errorMessage.value = result.message || 'Pogrešno korisničko ime ili lozinka.';
    }
  } catch (err) {
    errorMessage.value = 'Greška pri povezivanju sa serverom.';
  } finally {
    isLoading.value = false;
  }
};
</script>


<style scoped>
.login {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  text-align: center;
}
input {
  padding: 8px;
  margin: 10px 0;
  width: 100%;
  box-sizing: border-box;
}
button {
  padding: 10px 20px;
  background-color: #42b983;
  color: white;
  border: none;
  cursor: pointer;
}
button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}
.error {
  color: red;
}

.register-link {
  display: inline-block;
  margin-top: 15px;
  color: #42b983;
  text-decoration: underline;
  cursor: pointer;
}

</style>