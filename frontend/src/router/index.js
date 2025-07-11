import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore, decodeJWT } from '@/stores/auth';
import DashboardView from '@/views/DashboardView.vue';
import Verify2FAView from '@/views/Verify2FA.vue';
import LoginView from '@/views/LoginView.vue';
import RegisterView from '@/views/RegisterView.vue';
import ResetPasswordRequestView from '@/views/ResetPasswordRequestView.vue';
import SubmitNewPasswordView from '@/views/SubmitNewPasswordView.vue';
import OffersView from '@/views/OffersView.vue';
import CreateOfferView from '@/views/CreateOfferView.vue';
import EditOfferView from '@/views/EditOfferView.vue';
import PrintOfferView from '@/views/PrintOfferView.vue';
import ClientsView from '@/views/ClientsView.vue';
import CreateClientView from '@/views/CreateClientView.vue';
import EditClientView from '@/views/EditClientView.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
  },
  {
    path: '/verify-2fa',
    name: 'Verify2FA',
    component: Verify2FAView
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterView,
  },
  {
    path: '/',
    redirect: '/login',
  },
  { path: '/reset-password-request', 
    name: 'ResetRequest',
    component: ResetPasswordRequestView 
  },
  { path: '/reset-password', 
    name: 'ResetPassword',
    component: SubmitNewPasswordView 
  },
  {
    path: '/offers',
    name: 'Offers',
    component: OffersView,
    meta: { requiresAuth: true },
  }, 
  {
    path: '/create-offer',
    name: 'CreateOffer',
    component: CreateOfferView,
    meta: {requiresAuth: true}
  },
  {
    path: '/edit-offer/:id',
    name: 'EditOffer',
    component: EditOfferView,
    meta: {requiresAuth: true}
  },
  {
    path: '/print-offer/:id',
    name: 'PrintOffer',
    component: PrintOfferView,
    meta: {requiresAuth: true}
  },
  {
    path: '/clients',
    name: 'Clients',
    component: ClientsView,
    meta: {requiresAuth: true}
  },
  {
    path: '/create-client',
    name: 'CreateClient',
    component: CreateClientView,
    meta: {requiresAuth: true}
  },
  {
    path: '/edit-client/:id',
    name: 'EditClient',
    component: EditClientView,
    meta: {requiresAuth: true}
  }


];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  const isAuth = auth.isAuthenticated;

  // console.log('🔐 Auth check → token:', auth.token, 'user_id:', auth.user_id, 'isAuth:', isAuth);

  if (to.meta.requiresAuth && !isAuth) {
    auth.logout();
    return next({ name: 'Login' });
  }

  // Dodatna provera isteka tokena
  if (auth.token) {
    const payload = decodeJWT(auth.token);
    if (payload && payload.exp) {
      const now = Math.floor(Date.now() / 1000);
      if (payload.exp <= now) {
        auth.logout();
        return next({ name: 'Login' });
      }
    }
  }

  if (to.name === 'Verify2FA' && auth.is2FAverified) {
    return next({ name: 'Dashboard' });
  }

  next();
});

export default router;
