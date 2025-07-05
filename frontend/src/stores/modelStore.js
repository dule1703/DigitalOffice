import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useModelStore = defineStore('model', () => {
    const models = ref([]);
    const basicEquipment = ref([]);
    const loaded = ref(false);
    const basicLoaded = ref(false);

    const fetchModels = async(API_URL) => {
        if(loaded.value) return;

        try{
            const res = await fetch(`${API_URL}get-models.php`);
            const result = await res.json();

            if(result.status === 'success' && result.data) {
                models.value = result.data;               
                loaded.value = true;
            }
        }catch(err) {
            console.err('Greška u fetchModels', err);
        }     
    };

    const fetchBasicEquipment = async (API_URL) => {
        if (basicLoaded.value) return;
        try {
        const res = await fetch(`${API_URL}get-basic-equip.php`);
        const result = await res.json();
        if (result.status === 'success' && result.data) {
            basicEquipment.value = result.data;            
            basicLoaded.value = true;
        }
        } catch (err) {
        console.error('Greška u fetchBasicEquipment:', err);
        }
    };

    return { models, basicEquipment, fetchModels, fetchBasicEquipment };
});