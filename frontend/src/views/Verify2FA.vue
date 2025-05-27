<template>
  <div class="verify-2fa">
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

  try {
    const response = await fetch('http://localhost/DigitalOffice/backend/public/verify-2fa.php', {
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

/**/
</script>



<style scoped>
.verify-2fa {
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
.success {
  color: green;
}
</style>