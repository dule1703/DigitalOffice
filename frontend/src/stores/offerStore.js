import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useClientStore } from '@/stores/clientStore';
import { useModelStore } from '@/stores/modelStore';
import { useRouter } from 'vue-router';



export const useOfferStore = defineStore('offer', () => {
  const API_URL = import.meta.env.VITE_API_URL;
  const storeClient = useClientStore();
  const storeModel = useModelStore();
  const router = useRouter();

  // State
  const searchQuery = ref('');
  const selectedClientId = ref('');
  const dropdownVisible = ref(false);
  const clientLocked = ref(false);
  const showModal = ref(false);
  const successMessage = ref('');
  const errorMessage = ref('');
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
  const offerId = ref(null);
  const PDV_RATE = 0.18;
  const offerNumber = ref('');

  // Actions
  const selectClient = (client) => {
    if (clientLocked.value) return;
    searchQuery.value = client.c_name;
    selectedClientId.value = client.id;
    dropdownVisible.value = false;
  };


    // Metoda za osvežavanje klijenata
  const refreshClients = () => {
    console.log('Pokretanje osvežavanja klijenata');
    storeClient.refreshClients(API_URL);
  };

  const hideDropdown = () => {
    setTimeout(() => (dropdownVisible.value = false), 100);
  };

  const saveCurrentModel = () => {
    if (!selectedModelId.value || !selectedPackage.value || !selectedEngine.value) {
      errorMessage.value = 'Morate izabrati model, paket opreme i motor pre snimanja modela.';
      setTimeout(() => (errorMessage.value = ''), 2500);
      return;
    }

    if (!clientLocked.value && selectedClientId.value) {
      clientLocked.value = true;
    }

    savedModels.value.push({
      model_id: selectedModelId.value,
      model_name: storeModel.models.find(m => m.id === selectedModelId.value)?.model_name || selectedModelId.value,
      package_name: selectedPackage.value,
      package_code: selectedPackageId.value,
      car_quantity: carQuantity.value,
      engine_id: selectedEngine.value,
      color: selectedColor.value,
      wheels: selectedWheel.value,
      interior: selectedInterior.value,
      accessories: [...selectedAccessories.value],
    });

    resetModelForm();
  };

  const removeSavedModel = (index) => {
    savedModels.value.splice(index, 1);
    if (savedModels.value.length === 0) {
      clientLocked.value = false;
      selectedClientId.value = '';
      searchQuery.value = '';
    }
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

  const resetForm = () => {
    resetModelForm();
    savedModels.value = [];
    note.value = '';
    clientLocked.value = false;
    selectedClientId.value = '';
    searchQuery.value = '';
    offerId.value = null;
  };

    const fetchOffer = async (id) => {
    try {
        const res = await fetch(`${API_URL}get-offer.php?id=${id}`);
        const result = await res.json();       
        if (result.status === 'success') {
        const offer = result.data;
        selectedClientId.value = offer.client_id || '';
        searchQuery.value = offer.client_name || '';
        clientLocked.value = true;

        const models = JSON.parse(offer.model_data || '[]');
        if (models.length > 0) {
            const firstValidModel = models.find(model => model.model_id && model.model_id.trim());
            if (firstValidModel) {
            selectedModelId.value = firstValidModel.model_id || '';
            selectedPackage.value = firstValidModel.package_name || '';
            selectedPackageId.value = firstValidModel.package_code || '';
            selectedEngine.value = firstValidModel.engine_id || '';
            carQuantity.value = firstValidModel.car_quantity || 1;
            selectedAccessories.value = firstValidModel.accessories || [];
            selectedColor.value = firstValidModel.color || '';
            selectedWheel.value = firstValidModel.wheels || '';
            selectedInterior.value = firstValidModel.interior || '';
            }
            savedModels.value = models.filter(model => model !== firstValidModel && model.model_id && model.model_id.trim()).map(model => ({
            model_id: model.model_id,
            model_name: storeModel.models.find(m => m.id === model.model_id)?.model_name || model.model_name || model.model_id,
            package_name: model.package_name,
            package_code: model.package_code,
            car_quantity: model.car_quantity,
            engine_id: model.engine_id,
            color: model.color,
            wheels: model.wheels,
            interior: model.interior,
            accessories: [...model.accessories],
            }));
        }

        note.value = offer.note || '';
        offerId.value = offer.id || id; // Provera oba izvora      
        offerNumber.value = offer.number || '';  
        } else {
        errorMessage.value = result.message || 'Došlo je do greške pri učitavanju ponude.';
        setTimeout(() => (errorMessage.value = ''), 3000);
        }
    } catch (err) {
        console.error('Greška pri učitavanju ponude:', err);
        errorMessage.value = 'Došlo je do greške pri učitavanju ponude.';
        setTimeout(() => (errorMessage.value = ''), 3000);
    }
    };

    const saveOffer = async () => {
    const payload = {
        client_id: selectedClientId.value,
        total_without_vat: totalOfferWithoutVAT.value,
        note: note.value,
        model_data: JSON.stringify([{
        model_id: selectedModelId.value,
        model_name: storeModel.models.find(m => m.id === selectedModelId.value)?.model_name || selectedModelId.value,
        package_name: selectedPackage.value,
        package_code: selectedPackageId.value,
        car_quantity: carQuantity.value,
        engine_id: selectedEngine.value,
        color: selectedColor.value,
        wheels: selectedWheel.value,
        interior: selectedInterior.value,
        accessories: [...selectedAccessories.value],
        }, ...savedModels.value]),
    };

    if (offerId.value) {
        payload.id = offerId.value;
    }

    try {
        const res = await fetch(`${API_URL}${offerId.value ? 'edit-offer.php' : 'new-offer.php'}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        });
        const result = await res.json();
        if (result.status === 'success') {
        successMessage.value = result.message;
        errorMessage.value = '';
        showModal.value = false;
        setTimeout(() => {           
            successMessage.value = '';
            resetForm();           
            router.push('/offers');
        }, 4000);
        
        } else {
        errorMessage.value = result.message;
        successMessage.value = '';
        showModal.value = false;
        setTimeout(() => (errorMessage.value = ''), 4000);
        }
    } catch (err) {
        console.error('Greška pri slanju:', err);
        errorMessage.value = 'Došlo je do greške.';
        showModal.value = false;
        setTimeout(() => (errorMessage.value = ''), 4000);
    }
    };

  // Computed
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
      const valid = item.id === selectedModelId.value && item.package_name === selectedPackage.value && item.naziv_stavke === 'ODO';
      const nameValid = item.accessories_name?.trim();
      const unique = !seen.has(item.accessories_name);
      if (valid && nameValid && unique) {
        seen.add(item.accessories_name);
        return true;
      }
      return false;
    });
  });

  const createAccessoryFilter = (sifra) => computed(() => {
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

  const modelBasePrice = computed(() => {
    const model = storeModel.models.find(m =>
      m.id === selectedModelId.value && m.package_name === selectedPackage.value
    );
    return Number(model?.model_price || 0);
  });

  const additionalSelected = computed(() => [
    selectedColor.value,
    selectedWheel.value,
    selectedInterior.value,
  ].filter(Boolean));

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
      const base = Number(
        storeModel.models.find(m =>
          m.id === model.model_id && m.package_name === model.package_name
        )?.model_price || 0
      );

      const accessories = [
        ...(model.accessories || []),
        model.color,
        model.wheels,
        model.interior,
      ].filter(Boolean);

      const accSum = accessories.reduce((aSum, acc) => {
        const item = storeModel.models.find(m =>
          m.id === model.model_id &&
          m.package_name === model.package_name &&
          m.accessories_name === acc
        );
        return aSum + (item ? Number(item.accessories_price || 0) : 0);
      }, 0);

      return sum + (base + accSum) * model.car_quantity;
    }, 0);
  });

  const savedModelsTotalWithVAT = computed(() => savedModelsTotal.value * (1 + PDV_RATE));
  const totalOfferWithoutVAT = computed(() => savedModelsTotal.value + currentModelTotal.value);
  const totalOfferWithVAT = computed(() => totalOfferWithoutVAT.value * (1 + PDV_RATE));

  return {
    searchQuery,
    selectedClientId,
    dropdownVisible,
    clientLocked,
    showModal,
    successMessage,
    errorMessage,
    selectedModelId,
    selectedPackage,
    selectedPackageId,
    selectedEngine,
    carQuantity,
    selectedAccessories,
    selectedColor,
    selectedWheel,
    selectedInterior,
    note,
    savedModels,
    offerId,
    selectClient,
    hideDropdown,
    saveCurrentModel,
    removeSavedModel,
    resetModelForm,
    resetForm,
    fetchOffer,
    saveOffer,
    filteredClients,
    uniqueModels,
    filteredPackages,
    filteredEngines,
    filteredAccessories,
    filteredColors,
    filteredWheels,
    filteredInterior,
    extEquipment,
    entEquipment,
    bfkEquipment,
    modelBasePrice,
    accessoriesTotal,
    currentModelTotal,
    currentModelTotalWithVAT,
    savedModelsTotal,
    savedModelsTotalWithVAT,
    totalOfferWithoutVAT,
    totalOfferWithVAT,
    offerNumber,
    refreshClients
  };
});