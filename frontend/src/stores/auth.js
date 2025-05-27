import { defineStore } from 'pinia';
import { ref, watchEffect } from 'vue';

// Eksportujemo decodeJWT kako bi bila dostupna u drugim fajlovima
export function decodeJWT(token) {
  try {
    const payload = token.split('.')[1];
    const decoded = JSON.parse(atob(payload));
    return decoded;
  } catch (err) {
    return null;
  }
}

export const useAuthStore = defineStore('auth', () => {
  const rawToken = localStorage.getItem('token');
  const token = ref(rawToken && rawToken !== 'null' ? rawToken : null);

  const rawUserId = localStorage.getItem('user_id');
  const user_id = ref(rawUserId !== null ? Number(rawUserId) : null);

  const is2FAverified = ref(localStorage.getItem('2fa_verified') === 'true');

  const isAuthenticated = ref(false);

  // Reaktivno praćenje tokena i isteka
  watchEffect(() => {
    if (!token.value || user_id.value === null) {
      isAuthenticated.value = false;
     // console.log('🔒 Token ili user_id nedostaje, isAuthenticated postavljen na false');
      return;
    }
    const payload = decodeJWT(token.value);
    if (!payload || !payload.exp) {
      isAuthenticated.value = false;
     // console.log('🔒 Nevažeći payload ili exp, isAuthenticated postavljen na false');
      return;
    }
    const now = Math.floor(Date.now() / 1000);
    isAuthenticated.value = payload.exp > now;
    //console.log(`🔐 Token istek: ${payload.exp}, sada: ${now}, isAuthenticated: ${isAuthenticated.value}`);
  });

  const setUser = (uid, jwt) => {
    user_id.value = uid;
    token.value = jwt;
    localStorage.setItem('user_id', uid);
    localStorage.setItem('token', jwt);
    localStorage.setItem('2fa_verified', 'true');
    is2FAverified.value = true;
  };

  const logout = () => {
    user_id.value = null;
    token.value = null;
    is2FAverified.value = false;
    localStorage.removeItem('user_id');
    localStorage.removeItem('token');
    localStorage.removeItem('2fa_verified');
  };

  return {
    user_id,
    token,
    is2FAverified,
    isAuthenticated,
    setUser,
    logout,
  };
});