import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../components/HomePage.vue'
import LoginView from '../views/LoginView.vue'
import CustomerRegisterView from '../views/CustomerRegisterView.vue'
import ProviderRegisterView from '../views/ProviderRegisterView.vue'
import CustomerDashboardView from '../views/CustomerDashboardView.vue'
import ProviderDashboardView from '../views/ProviderDashboardView.vue'
import CatalogueView from '../views/CatalogueView.vue'
import BookingFormView from '../views/forms/BookingFormView.vue'
import CompanyFormView from '../views/forms/CompanyFormView.vue'
import FeedbackFormView from '../views/forms/FeedbackFormView.vue'
import ComplaintFormView from '../views/forms/ComplaintFormView.vue'
import RefundFormView from '../views/forms/RefundFormView.vue'
import InsuranceFormView from '../views/forms/InsuranceFormView.vue'
import { useAuth, dashboardPathFor } from '../stores/auth'

const routes = [
  { path: '/', name: 'home', component: HomePage },
  { path: '/catalogue', name: 'catalogue', component: CatalogueView },
  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/register', redirect: '/register/customer' },
  { path: '/register/customer', name: 'register-customer', component: CustomerRegisterView, meta: { guestOnly: true } },
  { path: '/register/provider', name: 'register-provider', component: ProviderRegisterView, meta: { guestOnly: true } },
  { path: '/dashboard', redirect: (to) => {
    // resolved in beforeEach once auth is ready
    return '/dashboard/customer'
  }},
  {
    path: '/dashboard/customer',
    name: 'dashboard-customer',
    component: CustomerDashboardView,
    meta: { requiresAuth: true, role: 'customer' },
  },
  {
    path: '/dashboard/provider',
    name: 'dashboard-provider',
    component: ProviderDashboardView,
    meta: { requiresAuth: true, role: 'provider' },
  },
  { path: '/forms/booking', name: 'form-booking', component: BookingFormView },
  { path: '/forms/company', name: 'form-company', component: CompanyFormView },
  { path: '/forms/feedback', name: 'form-feedback', component: FeedbackFormView },
  { path: '/forms/complaint', name: 'form-complaint', component: ComplaintFormView },
  { path: '/forms/refund', name: 'form-refund', component: RefundFormView },
  { path: '/forms/insurance', name: 'form-insurance', component: InsuranceFormView },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const { init, isAuthenticated, state } = useAuth()
  await init()

  if (to.path === '/dashboard' && isAuthenticated.value) {
    return dashboardPathFor(state.user)
  }

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.role && state.user?.role && to.meta.role !== state.user.role) {
    return dashboardPathFor(state.user)
  }

  if (to.meta.guestOnly && isAuthenticated.value) {
    return dashboardPathFor(state.user)
  }

  return true
})

export default router
