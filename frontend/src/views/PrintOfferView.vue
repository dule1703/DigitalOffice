<template>
  <div class="header">
      <LogoutView></LogoutView>
  </div>
  <div class="print-container">
    <h1 class="title">Ponuda br. {{ offerStore.offerNumber }}</h1>
    <p class="client-info">Klijent: {{ offerStore.searchQuery }}</p>

    <div v-for="(model, index) in allModels" :key="index" class="model-card">
      <h2>Model {{ index + 1 }}: {{ model.model_name }}</h2>
      <div class="equipment">
        <p><strong>Osnovna oprema:</strong></p>
        <ul>
          <li v-for="item in getBasicEquipment(model, 'EXT')" :key="item.basic_eq_item_id">
            Eksterijer: {{ item.basic_equip_name }}
          </li>
          <li v-for="item in getBasicEquipment(model, 'ENT')" :key="item.basic_eq_item_id">
            Enterijer: {{ item.basic_equip_name }}
          </li>
          <li v-for="item in getBasicEquipment(model, 'BFK')" :key="item.basic_eq_item_id">
            Bezbednost i funkcionalnost: {{ item.basic_equip_name }}
          </li>
        </ul>
      </div>

      <ul class="model-info">
        <li><strong>Paket:</strong> {{ model.package_name }}</li>
        <li><strong>Motor:</strong> {{ model.engine_id }}</li>
        <li><strong>Broj automobila:</strong> {{ model.car_quantity }}</li>
        <li v-if="model.color"><strong>Boja:</strong> {{ model.color }}</li>
        <li v-if="model.wheels"><strong>Felne:</strong> {{ model.wheels }}</li>
        <li v-if="model.interior"><strong>Enterijer:</strong> {{ model.interior }}</li>
      </ul>

      <div v-if="getAccessories(model).length" class="equipment">
        <p><strong>Dodatna oprema:</strong></p>
        <ul>
          <li v-for="(acc, i) in getAccessories(model)" :key="i">{{ acc.accessories_name }} ({{ acc.accessories_price }} €)</li>
        </ul>
      </div>

      <p class="price">Cena ovog modela (x{{ model.car_quantity }}): <strong>{{ getModelTotal(model).toFixed(2) }} €</strong></p>
    </div>

    <div class="totals">
      <p><strong>Ukupna cena (bez PDV-a):</strong> {{ offerStore.totalOfferWithoutVAT.toFixed(2) }} €</p>
      <p><strong>Ukupna cena (sa PDV-om):</strong> {{ offerStore.totalOfferWithVAT.toFixed(2) }} €</p>
    </div>

    <div class="note-section" v-if="offerStore.note">
      <p><strong>Napomena:</strong> {{ offerStore.note }}</p>
    </div>

    <div class="print-btn">
      <button @click="handlePrint">Štampaj</button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useOfferStore } from '@/stores/offerStore';
import { useModelStore } from '@/stores/modelStore';
import LogoutView from '@/components/LogoutView.vue';

const route = useRoute();
const offerStore = useOfferStore();
const modelStore = useModelStore();
const offerId = route.params.id;
const API_URL = import.meta.env.VITE_API_URL;

onMounted(async () => {
  await modelStore.fetchModels(API_URL);
  await modelStore.fetchBasicEquipment(API_URL);
  await offerStore.fetchOffer(offerId);
});

const allModels = computed(() => {
  return [
    {
      model_id: offerStore.selectedModelId,
      model_name: modelStore.models.find(m => m.id === offerStore.selectedModelId)?.model_name || '',
      package_name: offerStore.selectedPackage,
      package_code: offerStore.selectedPackageId,
      car_quantity: offerStore.carQuantity,
      engine_id: offerStore.selectedEngine,
      color: offerStore.selectedColor,
      wheels: offerStore.selectedWheel,
      interior: offerStore.selectedInterior,
      accessories: offerStore.selectedAccessories,
    },
    ...offerStore.savedModels
  ];
});

const getAccessories = (model) => {
  const all = [...(model.accessories || [])];
  if (model.color) all.push(model.color);
  if (model.wheels) all.push(model.wheels);
  if (model.interior) all.push(model.interior);

  return all.map(name => {
    const item = modelStore.models.find(m =>
      m.id === model.model_id &&
      m.package_name === model.package_name &&
      m.accessories_name === name
    );
    return item || { accessories_name: name, accessories_price: '0.00' };
  });
};

const getModelTotal = (model) => {
  const base = Number(
    modelStore.models.find(m =>
      m.id === model.model_id && m.package_name === model.package_name
    )?.model_price || 0
  );

  const accSum = getAccessories(model).reduce((sum, item) => sum + Number(item.accessories_price || 0), 0);
  return (base + accSum) * model.car_quantity;
};

const getBasicEquipment = (model, type) => {
  return modelStore.basicEquipment.filter(i =>
    i.model_id === model.model_id &&
    i.equip_package_id === model.package_code &&
    i.basic_equip_type_id === type
  );
};

function handlePrint() {
  window.print();
}
</script>

<style scoped>
.print-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px;
  font-family: 'Arial', sans-serif;
  color: #333;
  background-color: white;
}

.title {
  text-align: center;
  margin-bottom: 20px;
}

.client-info {
  font-size: 18px;
  margin-bottom: 30px;
}

.model-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 30px;
  padding: 20px;
  background-color: #f9f9f9;
}

.model-info {
  list-style: none;
  padding: 0;
  margin-bottom: 15px;
}

.model-info li {
  margin-bottom: 6px;
}

.equipment {
  margin-top: 10px;
}

.price {
  margin-top: 10px;
  font-size: 18px;
  font-weight: bold;
  color: #0073e6;
}

.totals {
  margin-top: 30px;
  font-size: 20px;
  font-weight: bold;
  border-top: 2px solid #ccc;
  padding-top: 20px;
}

.note-section {
  margin-top: 20px;
  font-style: italic;
}

.print-btn {
  text-align: center;
  margin-top: 40px;
}

.print-btn button {
  background-color: #035aca;
  color: white;
  padding: 10px 30px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.print-btn button:hover {
  background-color: #023c8a;
}

@media print {
  .print-btn {
    display: none;
  }
}
</style>