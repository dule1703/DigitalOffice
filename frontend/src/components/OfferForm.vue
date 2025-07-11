<template>
  <div class="header">
    <LogoutView />
  </div>

  <div class="page-title">
    <h1>{{ mode === 'edit' ? 'Izmena ponude' : 'Konfigurator modela automobila' }}</h1>
  </div>

  <form class="form-wrapper" @submit.prevent="handleSubmit" novalidate>
    <div class="form-layout">
      <div class="form-left">
        <!-- Klijent -->
        <div class="form-group">
          <label for="client-search">Izaberite klijenta:</label>
          <input
            id="client-search"
            type="text"
            :value="offerStore.searchQuery"
            :readonly="offerStore.clientLocked"
            :class="['search-input', { locked: offerStore.clientLocked }]"
            placeholder="Pretraga klijenata..."
            @focus="offerStore.dropdownVisible = !offerStore.clientLocked"
            @input="updateSearchQuery($event)"
            @blur="offerStore.hideDropdown"
            autocomplete="off"
            required
          />
          <ul v-show="offerStore.dropdownVisible && offerStore.filteredClients.length" class="dropdown-list">
            <li
              v-for="client in offerStore.filteredClients"
              :key="client.id"
              @mousedown.prevent="offerStore.selectClient(client)"
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
            <select
              id="model-select"
              v-model="offerStore.selectedModelId"
              :required="isModelSelectionRequired"
              class="dropdown"
            >
              <option disabled value="">-- Izaberite model --</option>
              <option v-for="model in offerStore.uniqueModels" :key="model.id" :value="model.id">
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
              v-model.number="offerStore.carQuantity"
              min="1"
              max="10"
              step="1"
              :required="isModelSelectionRequired"
              class="search-input"
            />
          </div>
        </div>

        <!-- Paket opreme -->
        <div class="form-group">
          <label for="package-select">Paket opreme:</label>
          <select
            id="package-select"
            v-model="offerStore.selectedPackage"
            :disabled="!offerStore.selectedModelId"
            :required="isModelSelectionRequired"
            class="dropdown"
          >
            <option disabled value="">-- Izaberite paket --</option>
            <option v-for="pkg in offerStore.filteredPackages" :key="pkg.package_name" :value="pkg.package_name">
              {{ pkg.package_name }}
            </option>
          </select>
        </div>

        <!-- Motor -->
        <div class="form-group">
          <label for="engine-select">Motor:</label>
          <select
            id="engine-select"
            v-model="offerStore.selectedEngine"
            :disabled="!offerStore.selectedPackage"
            :required="isModelSelectionRequired"
            class="dropdown"
          >
            <option disabled value="">-- Izaberite motor --</option>
            <option v-for="motor in offerStore.filteredEngines" :key="motor.engine_id" :value="motor.engine_id">
              {{ motor.engine_id }}
            </option>
          </select>
        </div>

        <!-- Boje -->
        <div class="form-group" v-if="offerStore.filteredColors.length">
          <label for="color-select">Boja:</label>
          <select id="color-select" v-model="offerStore.selectedColor" class="dropdown">
            <option value="">-- Izaberite boju --</option>
            <option v-for="color in offerStore.filteredColors" :key="color.accessories_name" :value="color.accessories_name">
              {{ color.accessories_name }} ({{ color.accessories_price }} €)
            </option>
          </select>
        </div>

        <!-- Felne -->
        <div class="form-group" v-if="offerStore.filteredWheels.length">
          <label for="wheel-select">Felne:</label>
          <select id="wheel-select" v-model="offerStore.selectedWheel" class="dropdown">
            <option value="">-- Izaberite felne --</option>
            <option v-for="wheel in offerStore.filteredWheels" :key="wheel.accessories_name" :value="wheel.accessories_name">
              {{ wheel.accessories_name }} ({{ wheel.accessories_price }} €)
            </option>
          </select>
        </div>

        <!-- Enterijer -->
        <div class="form-group" v-if="offerStore.filteredInterior.length">
          <label for="interior-select">Enterijer:</label>
          <select id="interior-select" v-model="offerStore.selectedInterior" class="dropdown">
            <option value="">-- Izaberite enterijer --</option>
            <option v-for="interior in offerStore.filteredInterior" :key="interior.accessories_name" :value="interior.accessories_name">
              {{ interior.accessories_name }} ({{ interior.accessories_price }} €)
            </option>
          </select>
        </div>
      </div>

      <div class="form-right">
        <!-- Osnovna oprema -->
        <h4 class="oprema">OSNOVNA OPREMA</h4>
        <div class="form-group" v-if="offerStore.selectedModelId && offerStore.selectedPackage">
          <label><strong>Eksterijer:</strong></label>
          <ul v-if="offerStore.extEquipment.length">
            <li v-for="item in offerStore.extEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>

          <label><strong>Enterijer:</strong></label>
          <ul v-if="offerStore.entEquipment.length">
            <li v-for="item in offerStore.entEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>

          <label><strong>Bezbednost i funkcionalnost:</strong></label>
          <ul v-if="offerStore.bfkEquipment.length">
            <li v-for="item in offerStore.bfkEquipment" :key="item.basic_eq_item_id">
              {{ item.basic_equip_name }}
            </li>
          </ul>
        </div>

        <!-- Dodatna oprema -->
        <div class="form-group">
          <h4 class="oprema">DODATNA OPREMA</h4>
          <div class="accessory-list" v-if="offerStore.filteredAccessories.length">
            <div v-for="item in offerStore.filteredAccessories" :key="item.accessories_name" class="accessory-item">
              <label>
                <input type="checkbox" :value="item.accessories_name" v-model="offerStore.selectedAccessories" />
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
        v-model="offerStore.note"
        class="search-input"
        rows="3"
        placeholder="Unesite dodatne napomene vezane za ponudu..."
      ></textarea>
    </div>

    <!-- Trenutni model -->
    <div class="form-group total-summary">
      <p><strong>Trenutni model (bez PDV-a):</strong><span> {{ offerStore.currentModelTotal.toFixed(2) }} €</span></p>
      <p><strong>Trenutni model (sa PDV-om):</strong><span> {{ offerStore.currentModelTotalWithVAT.toFixed(2) }} €</span></p>
    </div>

    <!-- Snimljeni modeli -->
    <div class="form-group total-summary">
      <p><strong>Sabrana cena snimljenih modela (bez PDV-a):</strong><span> {{ offerStore.savedModelsTotal.toFixed(2) }} €</span></p>
      <p><strong>Sabrana cena snimljenih modela (sa PDV-om):</strong><span> {{ offerStore.savedModelsTotalWithVAT.toFixed(2) }} €</span></p>
    </div>

    <!-- Dugme za snimanje modela -->
    <div class="snimi-btn-wrapper">
      <button type="button" class="submit-button snimi-model-btn" @click="offerStore.saveCurrentModel">Snimi model</button>
    </div>

    <div v-if="offerStore.savedModels.length">
      <h3>Snimljeni modeli:</h3>
      <ul>
        <li v-for="(model, index) in offerStore.savedModels" :key="index">
          {{ model.model_name }} / {{ model.package_name }} / {{ model.engine_id }} / {{ model.car_quantity }} kom
          <button @click="offerStore.removeSavedModel(index)" class="remove-button">Obriši</button>
        </li>
      </ul>
    </div>

    <!-- Ukupna ponuda -->
    <div class="form-group total-summary">
      <p><strong>UKUPNA PONUDA (bez PDV-a):</strong><span> {{ offerStore.totalOfferWithoutVAT.toFixed(2) }} €</span></p>
      <p><strong>UKUPNA PONUDA (sa PDV-om):</strong><span> {{ offerStore.totalOfferWithVAT.toFixed(2) }} €</span></p>
    </div>

    <br />
    <p v-if="offerStore.successMessage" class="success-msg">{{ offerStore.successMessage }}</p>
    <p v-if="offerStore.errorMessage" class="error-msg">{{ offerStore.errorMessage }}</p>

    <br />
    <button type="button" class="submit-button main-button" @click="handleSubmit">{{ mode === 'edit' ? 'Ažuriraj ponudu' : 'Zaključi ponudu' }}</button>

    <!-- Modal potvrde -->
    <div v-if="offerStore.showModal" class="modal-overlay">
      <div class="modal">
        <p>Da li želite da sačuvate ovu ponudu?</p>
        <div class="modal-buttons">
          <button @click="offerStore.saveOffer" class="submit-button">Da, sačuvaj</button>
          <button @click="offerStore.showModal = false" class="cancel-button">Otkaži</button>
        </div>
      </div>
    </div>
  </form>
</template>

<script setup>
import { defineProps, computed, watch } from 'vue';
import LogoutView from '@/components/LogoutView.vue';
import { useOfferStore } from '@/stores/offerStore';
import { useModelStore } from '@/stores/modelStore';


defineProps({
  mode: {
    type: String,
    required: true,
    validator: value => ['create', 'edit'].includes(value),
  },
});

const offerStore = useOfferStore();
const modelStore = useModelStore();


// Dinamički određujemo da li su polja za model, paket, motor i broj automobila obavezna
const isModelSelectionRequired = computed(() => {
  return !!offerStore.selectedModelId || !!offerStore.selectedPackage || !!offerStore.selectedEngine;
});

// Ažuriranje searchQuery kada korisnik unosi tekst
const updateSearchQuery = (event) => {
  if (!offerStore.clientLocked) {
    offerStore.searchQuery = event.target.value;
    offerStore.dropdownVisible = true;
  }
};

watch(() => offerStore.selectedPackage, (newVal) => {
  const found = modelStore.models.find(m => m.package_name === newVal && m.id === offerStore.selectedModelId);
  offerStore.selectedPackageId = found?.sifra_paketa || '';
  offerStore.selectedAccessories = [];
  offerStore.selectedColor = '';
  offerStore.selectedWheel = '';
  offerStore.selectedInterior = '';
});

const handleSubmit = () => {
  // Provera da li postoji barem jedan model (trenutni u formi ili u savedModels)
  if (!offerStore.selectedClientId && !offerStore.searchQuery) {
    offerStore.errorMessage = 'Morate izabrati klijenta.';
    setTimeout(() => (offerStore.errorMessage = ''), 4000);
    return;
  }
  if (!offerStore.selectedModelId && offerStore.savedModels.length === 0) {
    offerStore.errorMessage = 'Morate snimiti barem jedan model.';
    setTimeout(() => (offerStore.errorMessage = ''), 4000);
    return;
  }

  // Logovanje stanja za debug
//   console.log('Stanje pre slanja:', {
//     clientId: offerStore.selectedClientId,
//     searchQuery: offerStore.searchQuery,
//     selectedModelId: offerStore.selectedModelId,
//     selectedPackage: offerStore.selectedPackage,
//     savedModels: offerStore.savedModels,
//     note: offerStore.note,
//     offerId: offerStore.offerId
//   });

  offerStore.showModal = true;
};
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

.remove-button {
  margin-left: 10px;
  padding: 4px 8px;
  background-color: #ff5555;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.remove-button:hover {
  background-color: #cc0000;
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