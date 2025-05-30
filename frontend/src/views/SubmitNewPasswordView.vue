<template>
  <div class="max-w-md mx-auto mt-10 p-4 border rounded">
    <h2 class="text-xl font-semibold mb-4">Nova lozinka</h2>
    <form @submit.prevent="submitPassword">
      <input v-model="password" type="password" required placeholder="Nova lozinka"
             class="w-full p-2 border rounded mb-4" />
      <input v-model="confirmPassword" type="password" required placeholder="Ponovi lozinku"
             class="w-full p-2 border rounded mb-4" />
      <button type="submit" :disabled="loading"
              class="w-full bg-green-600 text-white py-2 rounded">
        {{ loading ? 'Menjam...' : 'Postavi lozinku' }}
      </button>
    </form>
    <p v-if="message" class="mt-4 text-green-600">{{ message }}</p>
    <p v-if="error" class="mt-4 text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const token = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

onMounted(() => {
  token.value = route.query.token || ''
  if (!token.value) {
    error.value = 'Token nije prosleđen.'
  }
})

const submitPassword = async () => {
  if (password.value !== confirmPassword.value) {
    error.value = 'Lozinke se ne poklapaju.'
    return
  }

  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const response = await fetch(import.meta.env.VITE_API_URL + 'submit-new-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token: token.value,
        new_password: password.value
      })
    })

    const result = await response.json()

    if (result.status === 'success') {
      message.value = 'Lozinka je uspešno promenjena. Preusmeravanje...'
      setTimeout(() => router.push('/login'), 2000)
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
