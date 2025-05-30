<template>
  <div class="register">
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
.register {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  text-align: center;
}
input {
  padding: 8px;
  margin: 10px 0;
  width: 100%;
}
button {
  padding: 10px 20px;
  background-color: #42b983;
  color: white;
  border: none;
  cursor: pointer;
}
button:disabled {
  background-color: #ccc;
}
.error {
  color: red;
}
.success {
  color: green;
}
.back-link {
  display: block;
  margin-top: 15px;
  color: #42b983;
  text-decoration: underline;
}
</style>
