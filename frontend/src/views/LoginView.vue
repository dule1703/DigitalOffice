<template>
  <div class="login-container">
    <div class="login-background">
      <div class="login-card">
        <div class="logo">
          <span role="img" aria-label="Logo"><img src="../assets/logo.png" /></span>
        </div>        
        <p>Ulogujte se na vaš nalog</p>
        <form @submit.prevent="login" class="login-form">
          <input v-model="email" placeholder="Username" required />
          <input v-model="password" type="password" placeholder="Password" required />
          <button type="submit" :disabled="isLoading">
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </button>
        </form>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        <div class="links">
          <span class="register-button"><router-link to="/register" class="register-link">Registruj se</router-link></span>          
          <router-link to="/reset-password-request" class="reset-link">Zaboravljena lozinka?</router-link>
        </div>
      </div>
    </div>
  </div>
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

  //Resetuj stare podatke iz localStorage i store-a
  localStorage.removeItem('token');
  localStorage.removeItem('user_id');
  localStorage.removeItem('2fa_verified');
  auth.logout();

  isLoading.value = true;
  errorMessage.value = '';
  const API_URL = import.meta.env.VITE_API_URL; 

  try {
    const response = await fetch(`${API_URL}login.php`, {
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
        //Direktan login jer je korisnik već verifikovan
        auth.setUser(result.user_id, result.token);
        router.push('/dashboard');
      } else {
        // Čeka se verifikacija koda
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
.login-container {
  width: 100%;
  max-width: 1920px; 
  height: 100vh;
  background-image: url('../assets/loginBg.webp');
  background-size: cover;       
  background-position: center; 
  background-repeat: no-repeat;
  padding-top: 15%;
}

.login-background {
  text-align: center;
  color: white;
}

.login-card {
  background: #fff;
  padding: 2rem;
  border-radius: 10px;
  width: 100%;
  max-width: 400px;
  margin-left: 20%; 
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo {
  font-size: 2rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #1e3a8a;
}

p {
  margin-bottom: 1.5rem;
  color: #03316d;
  font-weight: 700;
}

.login-form {
  display: flex;
  flex-direction: column;
  width: 100%;
}

input {
  padding: 0.6rem;
  margin: 0.5rem 0;
  border: 1px solid #035aca;
  border-radius: 5px;
  font-size: 1rem;
}

button {
  padding: 0.75rem;
  background-color: #035aca;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  margin-top: 1rem;

}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error {
  color: red;
  margin-top: 1rem;
}

.links {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.register-button {
  border: 1px solid #035aca;
  border-radius: 20px;
}

.register-button:hover {
  background-color: #035aca;  
  cursor: pointer;
}

.register-link {
  color: #035aca;
  padding: 0 2rem;
}

.register-link:hover {
  color: #fff !important;
}

.register-button a:hover {  
  background-color: transparent;
}

.reset-link {
  color: #1e3a8a;
  text-decoration: underline;
  cursor: pointer;
}

@media (max-width: 1024px) {
  .login-card {
    margin-left: 5rem;
    padding: 2rem 3rem;
  }
}

@media (max-width: 768px) {
  .login-container {
    padding-top: 12rem;
  }

  .login-background {
    padding: 10px;
  }

  .login-card {
    margin: 0 auto;
    padding: 2rem 1rem;
  }
}

</style>