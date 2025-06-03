<template>
  <div class="new-password-container">
    <div class="new-password-box">
      <h2>Nova lozinka</h2>
      <form @submit.prevent="submitPassword">
        <input
          v-model="password"
          type="password"
          required
          placeholder="Nova lozinka"
        />
        <input
          v-model="confirmPassword"
          type="password"
          required
          placeholder="Ponovi lozinku"
        />
        <button type="submit" :disabled="loading">
          {{ loading ? 'Menjam...' : 'Postavi lozinku' }}
        </button>
      </form>

      <p v-if="message" class="success">{{ message }}</p>
      <p v-if="error" class="error">{{ error }}</p>
    </div>
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

<style scoped>
.new-password-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #035aca;
  padding: 20px;
}

.new-password-box {
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
</style>
