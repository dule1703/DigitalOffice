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

      <button class="create-button" type="submit">Kreiraj klijenta</button>
      <div v-if="message" :class="['status-message', messageType]">
        {{ message }}
      </div><br>

    </form>
  </div>
</template>

<script setup>
import LogoutView from '@/components/LogoutView.vue';
import { ref } from 'vue';

const API_URL = import.meta.env.VITE_API_URL;

const clientData = ref({
  c_name: '',
  tax_number: '',
  identification_number: '',
  address: '',
  zip_code: '',
  city: '',
  country: '',
});

const message = ref('');
const messageType = ref(''); // 'success' ili 'error'

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
]


const resetForm = () => {
  for (const key in clientData.value) {
    clientData.value[key] = '';
  }
};

const getFieldLabel = (fieldName) => {
  const found = fields.find((f) => f.name === fieldName);
  return found ? found.label : fieldName;
};

const regexRules = {
  c_name: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s\-.]+$/, // slova latinica i ćirilica, razmaci i -
  tax_number: /^\d{11}$/, // tačno 11 cifara
  identification_number: /^\d{13}$/, // tačno 13 cifara
  address: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ0-9\s-]+$/, // slova ćirilica/latinica, cifre, razmaci i -
  zip_code: /^\d{5}$/, // tačno 5 cifara
  city: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s-]+$/, // kao za ime
  country: /^[A-Za-zА-Яа-яĆČĐŠŽćčđšžЉЊЋЂЈЏљњћђјџ\s-]+$/, // kao za ime
};


const handleSubmit = async () => {
  const cleaned = {};

  // Trimuj sve vrednosti da ne prođu praznine
  for (const key in clientData.value) {
    cleaned[key] = clientData.value[key].trim();
  }

  // Proveri da li je TAČNO jedno od PIB ili JMBG uneto
  const hasPIB = cleaned.tax_number?.trim() !== '';
  const hasJMBG = cleaned.identification_number?.trim() !== '';


  if ((hasPIB && hasJMBG) || (!hasPIB && !hasJMBG)) {
    message.value = 'Unesite tačno jedno: ili JMBG ili PIB.';
    messageType.value = 'error';
    setTimeout(() => (message.value = ''), 3000);
    return;
  }

  // Validacija po regEx pravilima
  for (const [field, pattern] of Object.entries(regexRules)) {
    // Preskoči PIB ili JMBG koji nije popunjen jer je već provereno da je tačno jedan
    if ((field === 'tax_number' && !hasPIB) || (field === 'identification_number' && !hasJMBG)) {
      continue;
    }

    // Proveri da polje nije prazno
    if (!cleaned[field]) {
      message.value = `Polje "${getFieldLabel(field)}" ne sme biti prazno.`;
      messageType.value = 'error';
      setTimeout(() => (message.value = ''), 3000);
      return;
    }

    // Proveri da polje odgovara regex pravilu
    if (!pattern.test(cleaned[field])) {
      message.value = `Neispravan format za polje "${getFieldLabel(field)}".`;
      messageType.value = 'error';
      setTimeout(() => (message.value = ''), 3000);
      return;
    }
  }

  try {
    const res = await fetch(`${API_URL}new-client.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(cleaned),
    });

    const result = await res.json();

    message.value = result.message;
    messageType.value = result.status === 'success' ? 'success' : 'error';

    if (result.status === 'success') {
           resetForm();
    }

    setTimeout(() => {
      message.value = '';
    }, 3000);
  } catch (error) {
    message.value = 'Neuspešna komunikacija sa serverom.';
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
.create-button,
.start-configurator {
  padding: 0.7rem;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}
.create-button {
  background-color: #035aca;
  color: white;
}
.start-configurator {
  background-color: #ddd;
  color: #333;
  margin-top: 1rem;
}
.start-configurator:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Poruka */
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
