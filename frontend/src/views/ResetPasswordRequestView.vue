<template>
  <div class="reset-container">
    <div class="reset-box">
      <h2>Resetuj lozinku</h2>
      <form @submit.prevent="handleRequest">
        <input
          v-model="email"
          type="email"
          required
          placeholder="Unesite vaš email"
        />
        <button type="submit" :disabled="loading">
          {{ loading ? 'Slanje...' : 'Pošalji link za reset' }}
        </button>
      </form>
      <p v-if="message" class="success">{{ message }}</p>
      <p v-if="error" class="error">{{ error }}</p>
      <router-link to="/login" class="back-link">Nazad na prijavu</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const handleRequest = async () => {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const response = await fetch(import.meta.env.VITE_API_URL + 'request-reset.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value })
    })

    const result = await response.json()

    if (result.status === 'success') {
      message.value = 'Link za reset lozinke je poslat na email.'
    } else {
      error.value = result.message || 'Došlo je do greške.'
    }
  } catch (err) {
    error.value = 'Greška u komunikaciji sa serverom.'
  } finally {
    loading.value = false
  }
}
</script>
<style scoped>
.reset-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #035aca;
  padding: 20px;
}

.reset-box {
  background-color: #ffffff;
  padding: 40px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 400px;
  width: 100%;
}

h2 {
  color: #035aca;
  margin-bottom: 20px;
  font-size: 22px;
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

.success {
  color: #388e3c;
  margin-top: 10px;
}

.error {
  color: #d32f2f;
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
