<template>
  <div class="verify-2fa-container">
    <div class="verify-2fa-box">
      <h1>Verify 2FA</h1>
      <p>Please enter your two-factor authentication code.</p>
      <form @submit.prevent="verifyCode">
        <input
          type="text"
          v-model="code"
          placeholder="Enter 2FA code"
          maxlength="6"
        />
        <button type="submit" :disabled="isButtonDisabled">
          {{ isLoading ? 'Verifying...' : 'Verify' }}
        </button>
      </form>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="success">{{ successMessage }}</p>
      <router-link to="/login" class="back-link">Nazad na prijavu</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const code = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const router = useRouter();
const auth = useAuthStore();

const isButtonDisabled = computed(() => isLoading.value || !code.value || code.value.length !== 6);

const verifyCode = async () => {
  if (!code.value || code.value.length !== 6) {
    errorMessage.value = 'Unesite ispravan 6-cifreni kod.';
    return;
  }

const stored = sessionStorage.getItem('pending_user_id');
if (stored === null) {
  errorMessage.value = 'Korisnički ID nije dostupan. Prijavite se ponovo.';
  return;
}
const pendingUserId = Number(stored);


  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  const API_URL = import.meta.env.VITE_API_URL;
  try {
    const response = await fetch(`${API_URL}verify-2fa.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${auth.token}`, // možeš i izostaviti ako se ne koristi ovde
      },
      body: JSON.stringify({
        code: code.value,
        user_id: pendingUserId,
      }),
    });

    const result = await response.json();

    if (result.status === 'success') {
      sessionStorage.removeItem('pending_user_id');
      auth.setUser(result.user_id, result.token);
      successMessage.value = '2FA uspešno verifikovan!';

      setTimeout(() => {
        console.log('➡ Preusmeravam na /dashboard');
        router.push('/dashboard');
      }, 100);
    } else {
      errorMessage.value = result.message || 'Neispravan kod.';
    }

  } catch (err) {
    errorMessage.value = 'Greška pri povezivanju sa serverom.';
  } finally {
    isLoading.value = false;
  }
};

</script>

<style scoped>
.verify-2fa-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #035aca;
  padding: 20px;
}

.verify-2fa-box {
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
  margin-bottom: 10px;
  font-size: 24px;
}

p {
  color: #333;
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border: 2px solid #00aeef;
  border-radius: 6px;
  margin-bottom: 20px;
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