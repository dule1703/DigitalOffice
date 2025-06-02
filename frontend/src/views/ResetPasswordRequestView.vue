<template>
  <div class="max-w-md mx-auto mt-10 p-4 border rounded">
    <h2 class="text-xl font-semibold mb-4">Resetuj lozinku</h2>
    <form @submit.prevent="handleRequest">
      <input v-model="email" type="email" required placeholder="Unesite vaš email"
             class="w-full p-2 border rounded mb-4" />
      <button type="submit" :disabled="loading"
              class="w-full bg-blue-600 text-white py-2 rounded">
        {{ loading ? 'Slanje...' : 'Pošalji link za reset' }}
      </button>
    </form>
    <p v-if="message" class="mt-4 text-green-600">{{ message }}</p>
    <p v-if="error" class="mt-4 text-red-600">{{ error }}</p>
     <router-link to="/login" class="back-link">Nazad na prijavu</router-link>
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
.back-link {
  display: block;
  margin-top: 15px;
  color: #42b983;
  text-decoration: underline;
}
</style>
