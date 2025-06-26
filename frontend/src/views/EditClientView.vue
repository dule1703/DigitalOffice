<template>
  <div class="header">
    <LogoutView />
  </div>

  <div class="form-container">
    <form @submit.prevent="handleSubmit" class="client-form">
      <div class="form-group" v-for="(field, index) in fields" :key="index">
        <label :for="field.name">{{ field.label }}</label>
        <input
          :id="field.name"
          v-model="clientData[field.name]"
          type="text"
          :placeholder="field.placeholder"
          :required="field.name !== 'tax_number' && field.name !== 'identification_number'"
        />
      </div>

      <button class="create-button" type="submit">Ažuriraj klijenta</button>

      <div v-if="message" :class="['status-message', messageType]">
        {{ message }}
      </div>
    </form>
  </div>
</template>

<script setup>
import LogoutView from '@/components/LogoutView.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const API_URL = import.meta.env.VITE_API_URL;

// ✅ Dobavi ID klijenta iz URL parametra
const clientId = route.params.id || '';
console.log('ID iz URL parametra:', clientId);

const clientData = ref({
  c_name: '',
  tax_number: '',
  identification_number: '',
  address: '',
  zip_code: '',
  city: '',
  country: ''
});

const message = ref('');
const messageType = ref('');

// Polja i validaciona pravila
const fields = [
  {
    name: 'c_name',
    label: 'Ime i prezime (Naziv firme za privatna lica)',
    placeholder: 'Samo slova, razmak, crtica (npr. Petar Petrović)'
  },
  {
    name: 'identification_number',
    label: 'JMBG',
    placeholder: 'Tačno 13 cifara (npr. 0101990123456)'
  },
  {
    name: 'tax_number',
    label: 'PIB (za privatna lica)',
    placeholder: 'Tačno 11 cifara (npr. 12345678901)'
  },
  {
    name: 'address',
    label: 'Adresa',
    placeholder: 'Slova, brojevi, razmaci i crtica (npr. Nemanjina 12)'
  },
  {
    name: 'zip_code',
    label: 'Poštanski kod',
    placeholder: 'Tačno 5 cifara (npr. 11000)'
  },
  {
    name: 'city',
    label: 'Grad',
    placeholder: 'Samo slova, razmak i crtica (npr. Novi Sad)'
  },
  {
    name: 'country',
    label: 'Zemlja',
    placeholder: 'Samo slova, razmak i crtica (npr. Srbija)'
  }
];

const getFieldLabel = (fieldName) => {
  const found = fields.find((f) => f.name === fieldName);
  return found ? found.label : fieldName;
};

const regexRules = {
  c_name: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s\-.]+$/,
  tax_number: /^\d{11}$/,
  identification_number: /^\d{13}$/,
  address: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ0-9\s-]+$/,
  zip_code: /^\d{5}$/,
  city: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s-]+$/,
  country: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s-]+$/
};

// ✅ Učitaj klijenta po ID-u prilikom montaže komponente
onMounted(async () => {
  if (!clientId) return;

  try {
    const res = await fetch(`${API_URL}get-client.php/?id=${clientId}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }      
    });

    const result = await res.json();
    const client = result?.data?.data || result?.data;

    if (result.status === 'success' && client) {
      for (const key in clientData.value) {
        clientData.value[key] = client[key] ?? '';
      }
    } else {
      message.value = result.message || 'Greška pri učitavanju klijenta.';
      messageType.value = 'error';
    }
  } catch (err) {
    message.value = 'Neuspešna komunikacija sa serverom.';
    messageType.value = 'error';
  }
});

// ✅ Ažuriraj klijenta
const handleSubmit = async () => {
  const cleaned = {};
  for (const key in clientData.value) {
    cleaned[key] = clientData.value[key].trim();
  }

  const hasPIB = cleaned.tax_number !== '';
  const hasJMBG = cleaned.identification_number !== '';

  if ((hasPIB && hasJMBG) || (!hasPIB && !hasJMBG)) {
    message.value = 'Unesite tačno jedno: ili JMBG ili PIB.';
    messageType.value = 'error';
    return setTimeout(() => (message.value = ''), 3000);
  }

  for (const [field, pattern] of Object.entries(regexRules)) {
    if ((field === 'tax_number' && !hasPIB) || (field === 'identification_number' && !hasJMBG)) continue;

    if (!cleaned[field]) {
      message.value = `Polje "${getFieldLabel(field)}" ne sme biti prazno.`;
      messageType.value = 'error';
      return setTimeout(() => (message.value = ''), 3000);
    }

    if (!pattern.test(cleaned[field])) {
      message.value = `Neispravan format za polje "${getFieldLabel(field)}".`;
      messageType.value = 'error';
      return setTimeout(() => (message.value = ''), 3000);
    }
  }

  try {
    cleaned.id = clientId;

    const res = await fetch(`${API_URL}edit-client.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cleaned)
    });

    const result = await res.json();
    message.value = result.message;
    messageType.value = result.status === 'success' ? 'success' : 'error';

    setTimeout(() => {
      message.value = '';
    }, 3000);
  } catch (error) {
    message.value = 'Greška pri komunikaciji sa serverom.';
    messageType.value = 'error';
    setTimeout(() => (message.value = ''), 3000);
  }
};
</script>


<style scoped>
.form-container {
  max-width: 600px;
  margin: 0 auto;
}
.client-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.form-group label {
  font-weight: bold;
}
.form-group input {
  padding: 0.5rem;
  border-radius: 20px;
  border: 1px solid #ccc;
  width: 100%;
}
.create-button {
  padding: 0.7rem;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  background-color: #035aca;
  color: white;
}
.status-message {
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  font-weight: bold;
  text-align: center;
}
.status-message.success {
  background-color: #d4edda;
  color: #155724;
}
.status-message.error {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
