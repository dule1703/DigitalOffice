<template>
  <div class="register-container">
    <div class="register-box">
      <h1>Registracija</h1>
      <form @submit.prevent="register">
        <input v-model="name" placeholder="Ime i prezime" required />
        <input v-model="email" placeholder="Email" type="email" required />
        <input v-model="password" type="password" placeholder="Lozinka" required />
        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Registrujem...' : 'Registruj se' }}
        </button>
      </form>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="success">{{ successMessage }}</p>
      <router-link to="/login" class="back-link">Nazad na prijavu</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const name = ref('');
const email = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const router = useRouter();

const register = async () => {
  if (!name.value || !email.value || !password.value) {
    errorMessage.value = 'Popunite sva polja.';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  const API_URL = import.meta.env.VITE_API_URL;
  try {
    const response = await fetch(`${API_URL}register.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value,
        email: email.value,
        password: password.value
      }),
    });

    const result = await response.json();

    if (result.status === 'success') {
      successMessage.value = 'Uspešno ste se registrovali!';
      setTimeout(() => router.push('/login'), 1500);
    } else {
      errorMessage.value = result.message || 'Greška pri registraciji.';
    }
  } catch (err) {
    errorMessage.value = 'Greška pri povezivanju sa serverom.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.register-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #035aca;
  padding: 20px;
}

.register-box {
  background-color: #ffffff;
  padding: 40px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 400px;
  width: 100%;
}

h1 {
  color: #035aca;
  margin-bottom: 20px;
  font-size: 24px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

input {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border: 2px solid #00aeef;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.3s;
}

input:focus {
  border-color: #035aca;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #00aeef;
  color: #ffffff;
  font-size: 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:disabled {
  background-color: #b3dff3;
  cursor: not-allowed;
}

button:hover:not(:disabled) {
  background-color: #028ac2;
}

.error {
  color: #d32f2f;
  margin-top: 10px;
}

.success {
  color: #388e3c;
  margin-top: 10px;
}

.back-link {
  display: block;
  margin-top: 20px;
  color: #00aeef;
  text-decoration: none;
  font-size: 14px;
}

.back-link:hover {
  text-decoration: underline;
}
</style>