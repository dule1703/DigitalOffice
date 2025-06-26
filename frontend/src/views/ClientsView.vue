<template>
 <div class="clients-container">
    <div class="header">
      <LogoutView></LogoutView>
    </div>
    <div class="title">
      <p>Tabela klijenata</p>
    </div>

    <div class="controls">
      <div class="left-controls">
        <div class="custom-select-wrapper">
          <span>Broj klijenata po stranici:</span>
          <img
            :src="arrowIcon"
            alt="Strelica"
            class="dropdown-icon"
            @click="toggleDropdown"
          />
          <ul v-if="dropdownOpen" class="custom-select-options">
            <li v-for="n in [5, 10, 20]" :key="n" @click="selectPerPage(n)">
              {{ n }}
            </li>
          </ul>
        </div>

        <div class="custom-select-wrapper">
            <span>Novi klijent:</span>
            <img
            :src="newIcon"
            alt="Nova ponuda"
            class="dropdown-icon"
            @click="createClient"
            />
        </div>
       
      </div>
      <div class="right-controls">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Unesite pojam za pretragu..."
        />
      </div>
    </div>

    <div class="table-wrapper">
      <table class="clients-table">
        <thead>
          <tr>
            <th>RB</th>
            <th>Klijent</th>
            <th>JMBG</th>
            <th>PIB</th>
            <th>Adresa</th>
            <th>Poš. broj</th>
            <th>Grad</th>
            <th>Zemlja</th>
            <th>Datum</th>
            <th colspan="3">Akcije</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(client, index) in paginatedClients" :key="client.id">
            <td>{{ index + 1 }}</td>
            <td>{{ client.c_name }}</td>
            <td>{{ client.identification_number }}</td>
            <td>{{ client.tax_number }}</td>
            <td>{{ client.address }}</td>
            <td>{{ client.zip_code }}</td>
            <td>{{ client.city }}</td>
            <td>{{ client.country }}</td>
            <td>{{ formatDate(client.datum) }}</td>
            <td><img :src="editIcon" alt="Izmena ponude" class="action-icon" @click="editClient(client.id)"  /></td>
            <td><img :src="deleteIcon" alt="Brisaje ponude" class="action-icon" @click="openDeleteModal(client.id)"  /></td>            
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <button class="arrow-button" @click="prevPage" :disabled="currentPage === 1">
        <img :src="prevIcon" alt="Prethodna" />
      </button>

      <div v-for="(page, index) in paginationPages" :key="index">
        <button
          v-if="page === '...'"
          disabled
          class="ellipsis"
        >...</button>
        <button
          v-else
          @click="currentPage = page"
          :class="{ active: currentPage === page }"
          class="page-number"
        >{{ page }}</button>
      </div>

      <button class="arrow-button" @click="nextPage" :disabled="currentPage === totalPages">
        <img :src="nextIcon" alt="Sledeća" />
      </button>
    </div>

    <!-- Modal za potvrdu brisanja -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-content">
        <p>Da li ste sigurni da želite da obrišete ovu ponudu?</p>
        <div class="modal-actions">
          <button @click="confirmDelete">Potvrdi</button>
          <button @click="cancelDelete">Otkaži</button>
        </div>
      </div>
    </div>

  </div>

</template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import LogoutView from '@/components/LogoutView.vue';
import { debounce } from 'lodash';
import arrowIcon from '@/assets/arrow-down.png';
import newIcon from '@/assets/new-icon.png';
import prevIcon from '@/assets/prev-arrow.png'
import nextIcon from '@/assets/next-arrow.png'
import editIcon from '@/assets/edit-icon.png'
import deleteIcon from '@/assets/delete-icon.svg'


const API_URL = import.meta.env.VITE_API_URL;
const clients = ref([]);
const perPage = ref(10);
const searchQuery = ref('');
const currentPage = ref(1);
const router = useRouter();
const dropdownOpen = ref(false);
const showDeleteModal = ref(false);
const clientToDelete = ref(null);

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const selectPerPage = (value) => {
  perPage.value = value;
  dropdownOpen.value = false;
};

const fetchClients = async () => {
  try {
    const res = await fetch(`${API_URL}get-clients.php`);
    const result = await res.json();
    if (result.status === 'success' && result.data?.data) {
      clients.value = result.data.data;
    }
  } catch (error) {
    console.error('Greška prilikom učitavanja ponuda:', error);
  }
};

const searchClients = async (query) => {
  if (!query) {
    await fetchClients();
    return;
  }

  try {
    const res = await fetch(`${API_URL}search-clients.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ query }),
    });

    const result = await res.json();
    if (result.status === 'success' && Array.isArray(result.data)) {
      clients.value = result.data;
      currentPage.value = 1;
    } else {
      clients.value = [];
    }
  } catch (err) {
    console.error('Greška u pretrazi:', err);
  }
};

watch(
  searchQuery,
  debounce((newQuery) => {
    searchClients(newQuery.trim());
  }, 400)
);


// Paginacija
const totalPages = computed(() => Math.ceil(clients.value.length / perPage.value));

const paginatedClients = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return clients.value.slice(start, start + perPage.value);
});

// List of pages with `...`
const paginationPages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    for (let i = current - 1; i <= current + 1; i++) {
      if (i > 1 && i < total) pages.push(i);
    }
    if (current < total - 2) pages.push('...');
    pages.push(total);
  }

  return pages;
});

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const createClient = () => {
  router.push('/create-client');
};

const editClient = (id) => {
  router.push(`/edit-client/${id}`);
}

const printClient = (id) => {
  router.push(`/print-client/${id}`);
}

watch(perPage, () => {
  currentPage.value = 1;
});

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0'); // meseci su 0-indeksirani
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};



onMounted(fetchClients);

const openDeleteModal = (clientId) => {
  clientToDelete.value = clientId;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  clientToDelete.value = null;
  showDeleteModal.value = false;
};

// Delete client
const confirmDelete = async () => {
  if (!clientToDelete.value) return;

  try {
    const res = await fetch(`${API_URL}delete-client.php`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: clientToDelete.value }),
    });

    const result = await res.json();
    if (result.status === 'success') {
      clients.value = clients.value.filter(client => client.id !== clientToDelete.value);
    } else {
      alert(result.message);
    }
  } catch (e) {
    console.error('Greška:', e);
  } finally {
    cancelDelete();
  }
};
</script>
<style scoped>
.title p {
  font-size: 1.9rem;
  color: #3c4551;
  font-weight: 700;
  text-align: center;
  margin-bottom: 6rem;
}

.clients-container {
  max-width: 1920px;
  margin: auto;
  padding: 1rem;
}

.controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 1rem 0;
  flex-wrap: wrap;
}

.left-controls,
.right-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.right-controls > input {
  padding: 12px 20px;
  border-radius: 25px;
  border: 1px solid #035aca;
}

.table-wrapper {
  overflow-x: auto;
  width: 100%;
}

.clients-table {
  width: 100%;
  border-collapse: collapse;
}

.clients-table th,
.clients-table td {
  padding: 0.5rem;
  text-align: center;
}

.clients-table thead {  
  border-top: 5px solid #035aca;
  border-bottom: 5px solid #035aca;
}

.clients-table thead tr th {
  font-weight: 700;
}

.clients-table tbody tr {
  border-bottom: 5px solid #f7f7f7;
}

.action-icon {
  width: 37px;
  height: 37px;
  cursor: pointer;
}

/* Paginacija */
.pagination {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* Dugmad za broj stranice */
.pagination .page-number {
  border: 1px solid #007bff;
  border-radius: 25px;
  text-align: center;
  font-weight: bold;
  background-color: white;
  color: #007bff;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-weight: normal;
  font-size: 25px;
  cursor: pointer;
}

.pagination .page-number.active {
  background-color: #007bff;
  color: white;
}

/* Dugmad sa ikonama (prev/next) */
.pagination .arrow-button {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}

.pagination .arrow-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination .arrow-button img {
  width: 45px;
  height: 45px;
}

.pagination .ellipsis {
  border: none;
  background: none;
  color: #888;
  font-weight: bold;
  width: 25px;
  height: 25px;
  line-height: 25px;
  text-align: center;
}

/* Custom select */
.custom-select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.custom-select-wrapper > span:nth-child(1) {
  font-weight: 700;
}

.dropdown-icon {
  width: 45px;
  height: 45px;
  cursor: pointer;
}

.custom-select-options {
  position: absolute;
  top: 110%;
  right: 0;
  background: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  list-style: none;
  padding: 0.3rem 0;
  margin: 0;
  z-index: 10;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  min-width: 60px;
}

.custom-select-options li {
  padding: 0.4rem 1rem;
  cursor: pointer;
  text-align: right;
}

.custom-select-options li:hover {
  background-color: #f0f0f0;
}

.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  max-width: 400px;
  text-align: center;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}


/* Responsive tabela */
@media (max-width: 980px) {
  .table-wrapper {
    min-width: 980px;
  }
}


</style>