<template>
  <div class="header">
    <LogoutView />
  </div>

  <div class="page-title">
    <h1>Konfigurator modela automobila</h1>
  </div>

  <form class="form-wrapper" @submit.prevent="handleSubmit">
    <div class="form-layout">
      <div class="form-left">
        <!-- Klijent -->
        <div class="form-group">
          <label for="client-search">Izaberite klijenta:</label>
          <input
            id="client-search"
            type="text"
            :value="searchQuery"
            :readonly="clientLocked"
            :class="['search-input', { locked: clientLocked }]"
            placeholder="Pretraga klijenata..."
            @focus="dropdownVisible = !clientLocked"
            @input="!clientLocked && (dropdownVisible = true)"
            @blur="hideDropdown"
            autocomplete="off"
            required
          />
          <ul v-show="dropdownVisible && filteredClients.length" class="dropdown-list">
            <li
              v-for="client in filteredClients"
              :key="client.id"
              @mousedown.prevent="selectClient(client)"
              class="dropdown-item"
            >
              {{ client.c_name }} / PIB: {{ client.tax_number || '—' }} | JMBG: {{ client.identification_number || '—' }}
            </li>
          </ul>
        </div>

        <div class="form-row">
          <!-- Model -->
          <div class="form-group">
            <label for="model-select">Model:</label>
            <select id="model-select" v-model="selectedModelId" required class="dropdown">
              <option disabled value="">-- Izaberite model --</option>
              <option v-for="model in uniqueModels" :key="model.id" :value="model.id">
                {{ model.model_name }}
              </option>
            </select>
          </div>

          <!-- Broj automobila -->
          <div class="form-group">
            <label for="car-quantity">Broj automobila:</label>
            <input
              id="car-quantity"
              type="number"
              v-model.number="carQuantity"
              min="1"
              max="10"
              step="1"
              required
              class="search-input"
            />
          </div>
        </div>

        <!-- Paket opreme -->
        <div class="form-group">
          <label for="package-select">Paket opreme:</label>
          <select
            id="package-select"
            v-model="selectedPackage"
            :disabled="!selectedModelId"
            class="dropdown"
            required
          >
            <option disabled value="">-- Izaberite paket --</option>
            <option v-for="pkg in filteredPackages" :key="pkg.package_name" :value="pkg.package_name">
              {{ pkg.package_name }}
            </option>
          </select>
        </div>

        <!-- Motor -->
        <div class="form-group">
          <label for="engine-select">Motor:</label>
          <select
            id="engine-select"
            v-model="selectedEngine"
            :disabled="!selectedPackage"
            class="dropdown"
            required
          >
            <option disabled value="">-- Izaberite motor --</option>
            <option v-for="motor in filteredEngines" :key="motor.engine_id" :value="motor.engine_id">
              {{ motor.engine_id }}
            </option>
          </select>
        </div>

        <!-- Boje -->
        <div class="form-group" v-if="filteredColors.length">
          <label for="color-select">Boja:</label>
          <select id="color-select" v-model="selectedColor" class="dropdown">
            <option value="">-- Izaberite boju --</option>
            <option v-for="color in filteredColors" :key="color.accessories_name" :value="color.accessories_name">
              {{ color.accessories_name }} ({{ color.accessories_price }} €)
            </option>
          </select>
        </div>

        <!-- Felne -->
        <div class="form-group" v-if="filteredWheels.length">
          <label for="wheel-select">Felne:</label>
          <select id="wheel-select" v-model="selectedWheel" class="dropdown">
            <option value="">-- Izaberite felne --</option>
            <option v-for="wheel in filteredWheels" :key="wheel.accessories_name" :value="wheel.accessories_name">
              {{ wheel.accessories_name }} ({{ wheel.accessories_price }} €)
            </option>
          </select>
        </div>

        <!-- Enterijer -->
        <div class="form-group" v-if="filteredInterior.length">
          <label for="interior-select">Enterijer:</label>
          <select id="interior-select" v-model="selectedInterior" class="dropdown">
            <option value="">-- Izaberite enterijer --</option>
            <option v-for="interior in filteredInterior" :key="interior.accessories_name" :value="interior.accessories_name">
              {{ interior.accessories_name }} ({{ interior.accessories_price }} €)
            </option>
          </select>
        </div>
      </div>

      <div class="form-right">
        <!-- Osnovna oprema -->
        <h4 class="oprema">OSNOVNA OPREMA</h4>
        <div class="form-group" v-if="selectedModelId && selectedPackage">
          <label><strong>Eksterijer:</strong></label>
          <ul v-if="extEquipment.length">
            <li v-for="item in extEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>

          <label><strong>Enterijer:</strong></label>
          <ul v-if="entEquipment.length">
            <li v-for="item in entEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>

          <label><strong>Bezbednost i funkcionalnost:</strong></label>
          <ul v-if="bfkEquipment.length">
            <li v-for="item in bfkEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>
        </div>

        <!-- Dodatna oprema -->
        <div class="form-group">
          <h4 class="oprema">DODATNA OPREMA</h4>
          <div class="accessory-list" v-if="filteredAccessories.length">
            <div v-for="item in filteredAccessories" :key="item.accessories_name" class="accessory-item">
              <label>
                <input type="checkbox" :value="item.accessories_name" v-model="selectedAccessories" />
                {{ item.accessories_name }} ({{ item.accessories_price }} €)
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Napomena -->
    <div class="form-group">
      <label for="note">Napomena:</label>
      <textarea
        id="note"
        v-model="note"
        class="search-input"
        rows="3"
        placeholder="Unesite dodatne napomene vezane za ponudu..."
      ></textarea>
    </div>

    <!-- Trenutni model -->
    <div class="form-group total-summary">
      <p><strong>Trenutni model (bez PDV-a):</strong><span> {{ currentModelTotal.toFixed(2) }} €</span></p>
      <p><strong>Trenutni model (sa PDV-om):</strong><span> {{ currentModelTotalWithVAT.toFixed(2) }} €</span></p>
    </div>

    <!-- Snimljeni modeli -->
    <div class="form-group total-summary">
      <p><strong>Sabrana cena snimljenih modela (bez PDV-a):</strong><span> {{ savedModelsTotal.toFixed(2) }} €</span></p>
      <p><strong>Sabrana cena snimljenih modela (sa PDV-om):</strong><span> {{ savedModelsTotalWithVAT.toFixed(2) }} €</span></p>
    </div>

    <!-- Dugme za snimanje modela -->
    <div class="snimi-btn-wrapper">
      <button type="button" class="submit-button snimi-model-btn" @click="saveCurrentModel">Snimi model</button>
    </div>

    <div v-if="savedModels.length">
      <h3>Snimljeni modeli:</h3>
      <ul>
        <li v-for="(model, index) in savedModels" :key="index">
          {{ model.model_name }} / {{ model.package_name }} / {{ model.engine_id }} / {{ model.car_quantity }} kom
        </li>
      </ul>
    </div>

    <!-- Ukupna ponuda -->
    <div class="form-group total-summary">
      <p><strong>UKUPNA PONUDA (bez PDV-a):</strong><span> {{ totalOfferWithoutVAT.toFixed(2) }} €</span></p>
      <p><strong>UKUPNA PONUDA (sa PDV-om):</strong><span> {{ totalOfferWithVAT.toFixed(2) }} €</span></p>
    </div>

    <br />
    <p v-if="successMessage" class="success-msg">{{ successMessage }}</p>
    <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

    <br />
    <button type="button" class="submit-button main-button" @click="handleSubmit">ZAKLJUČI PONUDU</button>

  </form>

  <!-- Modal potvrde -->
  <div v-if="showModal" class="modal-overlay">
    <div class="modal">
      <p>Da li želite da sačuvate ovu ponudu?</p>
      <div class="modal-buttons">
        <button @click="confirmSave" class="submit-button">Da, sačuvaj</button>
        <button @click="showModal = false" class="cancel-button">Otkaži</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import LogoutView from '@/components/LogoutView.vue';
import { useClientStore } from '@/stores/clientStore';
import { useModelStore } from '@/stores/modelStore';

const API_URL = import.meta.env.VITE_API_URL;
const storeClient = useClientStore();
const storeModel = useModelStore();

// UI i forme
const searchQuery = ref('');
const selectedClientId = ref('');
const dropdownVisible = ref(false);
const clientLocked = ref(false);
const showModal = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Model konfiguracija
const selectedModelId = ref('');
const selectedPackage = ref('');
const selectedPackageId = ref('');
const selectedEngine = ref('');
const carQuantity = ref(1);
const selectedAccessories = ref([]);
const selectedColor = ref('');
const selectedWheel = ref('');
const selectedInterior = ref('');
const note = ref('');
const savedModels = ref([]);

const PDV_RATE = 0.18;

const selectClient = (client) => {
  if (clientLocked.value) return;
  searchQuery.value = client.c_name;
  selectedClientId.value = client.id;
  dropdownVisible.value = false;
};

const hideDropdown = () => {
  setTimeout(() => (dropdownVisible.value = false), 100);
};

onMounted(async () => {
  try {
    await storeClient.fetchClients(API_URL);
    await storeModel.fetchModels(API_URL);
    await storeModel.fetchBasicEquipment(API_URL);
  } catch (err) {
    console.error('Greška pri učitavanju klijenata i modela:', err);
  }
});

watch(searchQuery, (newVal) => {
  if (clientLocked.value && !newVal) {
    const client = storeClient.clients.find(c => c.id === selectedClientId.value);
    if (client) {
      searchQuery.value = client.c_name;
    }
  }
});

// === FILTERI ===
const filteredClients = computed(() => {
  const q = searchQuery.value.toLowerCase();
  return storeClient.clients.filter(client => client.c_name.toLowerCase().includes(q));
});

const uniqueModels = computed(() => {
  const seen = new Set();
  return storeModel.models.filter(m => !seen.has(m.id) && seen.add(m.id));
});

const filteredPackages = computed(() => {
  if (!selectedModelId.value) return [];
  const seen = new Set();
  return storeModel.models.filter(m => {
    const valid = m.id === selectedModelId.value && m.package_name?.trim();
    const unique = !seen.has(m.package_name);
    if (valid && unique) {
      seen.add(m.package_name);
      return true;
    }
    return false;
  });
});

const filteredEngines = computed(() => {
  const seen = new Set();
  return storeModel.models.filter(m => {
    const valid = m.id === selectedModelId.value && m.package_name === selectedPackage.value;
    const unique = !seen.has(m.engine_id);
    if (valid && unique) {
      seen.add(m.engine_id);
      return true;
    }
    return false;
  });
});

const filteredAccessories = computed(() => {
  const seen = new Set();
  return storeModel.models.filter(item => {
    const valid = item.id === selectedModelId.value && item.package_name === selectedPackage.value;
    const nameValid = item.accessories_name?.trim();
    const unique = !seen.has(item.accessories_name);
    if (valid && nameValid && unique) {
      seen.add(item.accessories_name);
      return true;
    }
    return false;
  });
});

function createAccessoryFilter(sifra) {
  return computed(() => {
    const filtered = storeModel.models.filter(item =>
      item.id === selectedModelId.value &&
      item.sifra_paketa === selectedPackageId.value &&
      item.naziv_stavke === sifra
    );
    const seen = new Set();
    return filtered.filter(item => {
      const unique = !seen.has(item.accessories_name);
      if (item.accessories_name && unique) {
        seen.add(item.accessories_name);
        return true;
      }
      return false;
    });
  });
}

const filteredColors = createAccessoryFilter('BOJ');
const filteredWheels = createAccessoryFilter('FEL');
const filteredInterior = createAccessoryFilter('ENT');

const extEquipment = computed(() =>
  storeModel.basicEquipment.filter(i =>
    i.model_id === selectedModelId.value &&
    i.equip_package_id === selectedPackageId.value &&
    i.basic_equip_type_id === 'EXT')
);
const entEquipment = computed(() =>
  storeModel.basicEquipment.filter(i =>
    i.model_id === selectedModelId.value &&
    i.equip_package_id === selectedPackageId.value &&
    i.basic_equip_type_id === 'ENT')
);
const bfkEquipment = computed(() =>
  storeModel.basicEquipment.filter(i =>
    i.model_id === selectedModelId.value &&
    i.equip_package_id === selectedPackageId.value &&
    i.basic_equip_type_id === 'BFK')
);

// === CENE ===
const selectedModelName = computed(() => {
  const model = uniqueModels.value.find(m => m.id === selectedModelId.value);
  return model?.model_name || '';
});

const modelBasePrice = computed(() => {
  const model = storeModel.models.find(m =>
    m.id === selectedModelId.value && m.package_name === selectedPackage.value
  );
  return model?.model_price || 0;
});

const additionalSelected = computed(() => [selectedColor.value, selectedWheel.value, selectedInterior.value].filter(Boolean));

const accessoriesTotal = computed(() => {
  const all = [...selectedAccessories.value, ...additionalSelected.value];
  return all.reduce((sum, name) => {
    const item = storeModel.models.find(m =>
      m.id === selectedModelId.value &&
      m.package_name === selectedPackage.value &&
      m.accessories_name === name
    );
    return sum + (item ? Number(item.accessories_price || 0) : 0);
  }, 0);
});

const currentModelTotal = computed(() => (modelBasePrice.value + accessoriesTotal.value) * carQuantity.value);
const currentModelTotalWithVAT = computed(() => currentModelTotal.value * (1 + PDV_RATE));

const savedModelsTotal = computed(() => {
  return savedModels.value.reduce((sum, model) => {
    const base = storeModel.models.find(m =>
      m.id === model.model_id && m.package_name === model.package_name)?.model_price || 0;
    const accessories = [...(model.accessories || []), model.color, model.wheels, model.interior].filter(Boolean);
    const accSum = accessories.reduce((aSum, acc) => {
      const item = storeModel.models.find(m =>
        m.id === model.model_id && m.package_name === model.package_name && m.accessories_name === acc);
      return aSum + (item ? Number(item.accessories_price || 0) : 0);
    }, 0);
    return sum + ((base + accSum) * model.car_quantity);
  }, 0);
});

const savedModelsTotalWithVAT = computed(() => savedModelsTotal.value * (1 + PDV_RATE));
const totalOfferWithoutVAT = computed(() => savedModelsTotal.value + currentModelTotal.value);
const totalOfferWithVAT = computed(() => totalOfferWithoutVAT.value * (1 + PDV_RATE));

// === RADNJE ===
const saveCurrentModel = () => {
  if (!selectedEngine.value) {
    errorMessage.value = 'Morate izabrati motor pre snimanja modela.';
    setTimeout(() => errorMessage.value = '', 2500);
    return;
  }

  if (!clientLocked.value && selectedClientId.value) {
    clientLocked.value = true;
  }

  savedModels.value.push({
    model_id: selectedModelId.value,
    model_name: selectedModelName.value,
    package_name: selectedPackage.value,
    package_code: selectedPackageId.value,
    car_quantity: carQuantity.value,
    engine_id: selectedEngine.value,
    color: selectedColor.value,
    wheels: selectedWheel.value,
    interior: selectedInterior.value,
    accessories: selectedAccessories.value
  });
  resetModelForm();
};

const resetModelForm = () => {
  selectedModelId.value = '';
  selectedPackage.value = '';
  selectedPackageId.value = '';
  selectedEngine.value = '';
  selectedAccessories.value = [];
  selectedColor.value = '';
  selectedWheel.value = '';
  selectedInterior.value = '';
  carQuantity.value = 1;
};

const handleSubmit = () => {
  const isUnsavedModelPresent =
    selectedModelId.value || selectedPackage.value || selectedEngine.value;

  if (!selectedClientId.value || savedModels.value.length === 0) {
    errorMessage.value = 'Morate izabrati klijenta i snimiti barem jedan model.';
    setTimeout(() => errorMessage.value = '', 2500);
    return;
  }

  if (isUnsavedModelPresent) {
    errorMessage.value = 'Imate model koji nije snimljen. Molimo vas da ga snimite pre zaključenja ponude.';
    setTimeout(() => errorMessage.value = '', 3000);
    return;
  }

  showModal.value = true;
};



const confirmSave = async () => {
  showModal.value = false;
  const payload = {
    client_id: selectedClientId.value,
    total_without_vat: totalOfferWithoutVAT.value,
    note: note.value,
    model_data: JSON.stringify(savedModels.value)
  };

  try {
    const res = await fetch(`${API_URL}new-offer.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      successMessage.value = result.message;
      errorMessage.value = '';
      setTimeout(() => {
        successMessage.value = '';
        resetForm();
      }, 2500);
    } else {
      errorMessage.value = result.message;
      successMessage.value = '';
      setTimeout(() => (errorMessage.value = ''), 3000);
    }
  } catch (err) {
    console.error('Greška pri slanju:', err);
    alert('Došlo je do greške.');
  }
};

const resetForm = () => {
  resetModelForm();
  savedModels.value = [];
  note.value = '';
  clientLocked.value = false;
  selectedClientId.value = '';
  searchQuery.value = '';
};

watch(selectedPackage, (newVal) => {
  const found = storeModel.models.find(m => m.package_name === newVal && m.id === selectedModelId.value);
  selectedPackageId.value = found?.sifra_paketa || '';
  selectedAccessories.value = [];
  selectedColor.value = '';
  selectedWheel.value = '';
  selectedInterior.value = '';
});
</script>






<style scoped>
.page-title {
  text-align: center;
  margin-top: 20px;
  margin-bottom: 30px;
}

.page-title h1 {
  font-size: 26px;
  font-weight: bold;
  color: #1a1a1a;
}

.form-wrapper {
  max-width: 100%;
  margin: 0 auto;
  padding: 0 20px 50px 20px;
}

.form-group {
  position: relative;
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

.search-input,
select {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
  background-color: #fff;
  color: #333;
  appearance: none;
}

select:focus,
.search-input:focus {
  outline: none;
  border-color: #035aca;
  box-shadow: 0 0 3px rgba(3, 90, 202, 0.5);
}

.dropdown-list {
  position: absolute;
  z-index: 10;
  width: 100%;
  max-height: 220px;
  overflow-y: auto;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-top: 4px;
  list-style: none;
  padding: 0;
  top: 66px;
}

.dropdown-item {
  padding: 10px 12px;
  font-size: 16px;
  cursor: pointer;
  background-color: #fff;
  color: #333;
}

.dropdown-item:hover {
  background-color: #f0f0f0;
}

.submit-button {
  background-color: #035aca;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  margin-top: 20px;
  transition: background-color 0.3s, color 0.3s;
}

.submit-button:hover {
  background-color: white;
  color: #035aca;
  border: 1px solid #035aca;
}

input.locked {
  background-color: #f3f3f3;
  color: #666;
  cursor: not-allowed;
}


.form-row {
  display: flex;
  gap: 20px;
  justify-content: space-between;
}

.form-row .form-group {
  flex: 1;
}

.oprema {
  font-weight: 800;
}

select:disabled {
  background-color: #f5f5f5;
  color: #aaa;
}

.form-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  max-width: 1800px;
  margin: 0 auto;
}

.form-left {
  flex: 1 1 500px;
  min-width: 300px;
}

.form-right {
  flex: 1 1 400px;
  min-width: 300px;
}

.total-summary {
  background-color: #f5faff;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #cce3ff;
  margin-top: 20px;
}

.total-summary p {
  margin: 5px 0;
  font-size: 18px;
  font-weight: bold;
  color: #1a1a1a;
}

.total-summary p span {
  font-size: 22px;
  color: #035aca;
  padding-left: 10px;
  font-weight: 700;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99;
}

.modal {
  background: white;
  padding: 24px 32px;
  border-radius: 10px;
  text-align: center;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: space-around;
}

.cancel-button {
  background-color: #aaa;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
}

.cancel-button:hover {
  background-color: #888;
}

.success-msg {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  padding: 12px 16px;
  border-radius: 6px;
  margin-top: 20px;
  font-size: 16px;
  text-align: center;
  animation: fadeOut 2s ease-in-out 2s forwards;
}

.error-msg {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  padding: 12px 16px;
  border-radius: 6px;
  margin-top: 20px;
  font-size: 16px;
  text-align: center;
  animation: fadeOut 3s ease-in-out 3s forwards;
}

@keyframes fadeOut {
  to {
    opacity: 0;
    visibility: hidden;
    height: 0;
    margin: 0;
    padding: 0;
  }
}

.snimi-btn-wrapper {
   text-align: right;
}

.main-button {
  width: 50%;
  display: block;
  margin: 20px auto 0px auto; 
  background-color: #035aca;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
  font-weight: 800;
  letter-spacing: 2px;
}

@media (max-width: 768px) {
  .form-layout {
    flex-direction: column;
  }

  .form-left,
  .form-right {
    min-width: 100%;
  }
}
</style>
