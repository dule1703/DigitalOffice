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

  return { clients, fetchClients };
});
