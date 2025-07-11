import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useClientStore = defineStore('client', () => {
  const clients = ref([]);
  const loaded = ref(false);

  const fetchClients = async (API_URL) => {
    if (loaded.value) return; //koristi ako je učitano ranije

    try {
      const res = await fetch(`${API_URL}get-clients.php`);
      const result = await res.json();
      if (result.status === 'success' && result.data?.data) {
        clients.value = result.data.data;
        loaded.value = true;
      }
    } catch (err) {
      console.error('Greška u fetchClients:', err);
    }
  };


  // Nova metoda za forsirano osvežavanje
  const refreshClients = async (API_URL) => {
    loaded.value = false; // Resetujemo loaded da bismo forsirali ponovno učitavanje
    await fetchClients(API_URL);
  };

  return { clients, fetchClients, refreshClients, loaded };
});
