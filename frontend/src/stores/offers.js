// import { defineStore } from 'pinia';
// import { ref } from 'vue';

// export const useOfferStore = defineStore('offers', () => {
//   const offer = ref(null);

//   const fetchOfferById = async (id) => {
//     try {
//       const res = await fetch(`${import.meta.env.VITE_API_URL}get-offer.php?id=${id}`);
//       const data = await res.json();
//       if (data.status === 'success') {
//         offer.value = data.data;
//       } else {
//         offer.value = null;
//       }
//     } catch (err) {
//       console.error('Greška u fetchOfferById:', err);
//       offer.value = null;
//     }
//   };

//   return { offer, fetchOfferById };
// });
