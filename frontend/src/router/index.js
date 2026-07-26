import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../components/HomePage.vue'
import LoginView from '../views/LoginView.vue'
import CustomerRegisterView from '../views/CustomerRegisterView.vue'
import ProviderRegisterView from '../views/ProviderRegisterView.vue'
import CustomerDashboardLayout from '../layouts/CustomerDashboardLayout.vue'
import ProviderDashboardLayout from '../layouts/ProviderDashboardLayout.vue'
import CustomerOverviewView from '../views/customer/CustomerOverviewView.vue'
import ServicesBrowseView from '../views/customer/ServicesBrowseView.vue'
import CustomerBookView from '../views/customer/CustomerBookView.vue'
import CustomerBookingsView from '../views/customer/CustomerBookingsView.vue'
import CustomerBookingDetailView from '../views/customer/CustomerBookingDetailView.vue'
import ProviderOverviewView from '../views/provider/ProviderOverviewView.vue'
import ProviderRequestsView from '../views/provider/ProviderRequestsView.vue'
import ProviderJobsView from '../views/provider/ProviderJobsView.vue'
import ProviderBookingDetailView from '../views/provider/ProviderBookingDetailView.vue'
import ProfileSettingsView from '../views/account/ProfileSettingsView.vue'
import ChangePasswordView from '../views/account/ChangePasswordView.vue'
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
  { path: '/dashboard', redirect: '/dashboard/customer' },
  {
    path: '/dashboard/customer',
    component: CustomerDashboardLayout,
    meta: { requiresAuth: true, role: 'customer' },
    children: [
      {
        path: '',
        name: 'dashboard-customer',
        component: CustomerOverviewView,
        meta: { title: 'Overview', subtitle: 'Book services and track your requests' },
      },
      {
        path: 'services',
        name: 'customer-services',
        component: ServicesBrowseView,
        meta: { title: 'Book a Service', subtitle: 'Choose a service to start booking' },
      },
      {
        path: 'book',
        name: 'customer-book',
        component: CustomerBookView,
        meta: { title: 'New Booking', subtitle: 'Fill in details to confirm your request' },
      },
      {
        path: 'bookings',
        name: 'customer-bookings',
        component: CustomerBookingsView,
        meta: { title: 'My Bookings', subtitle: 'Filter and manage your booking list' },
      },
      {
        path: 'bookings/:id',
        name: 'customer-booking-detail',
        component: CustomerBookingDetailView,
        meta: { title: 'Booking Details', subtitle: 'Track progress with your provider', match: '/dashboard/customer/bookings' },
      },
      {
        path: 'settings/profile',
        name: 'customer-profile-settings',
        component: ProfileSettingsView,
        meta: { title: 'Profile settings', subtitle: 'Update your account details' },
      },
      {
        path: 'settings/password',
        name: 'customer-change-password',
        component: ChangePasswordView,
        meta: { title: 'Change password', subtitle: 'Keep your account secure' },
      },
      {
        path: 'support/complaint',
        name: 'customer-complaint',
        component: ComplaintFormView,
        meta: { title: 'Complaint', subtitle: 'Report an issue with a booking' },
      },
      {
        path: 'support/refund',
        name: 'customer-refund',
        component: RefundFormView,
        meta: { title: 'Refund', subtitle: 'Request a refund for a booking' },
      },
      {
        path: 'support/insurance',
        name: 'customer-insurance',
        component: InsuranceFormView,
        meta: { title: 'Insurance', subtitle: 'Submit an insurance claim' },
      },
    ],
  },
  {
    path: '/dashboard/provider',
    component: ProviderDashboardLayout,
    meta: { requiresAuth: true, role: 'provider' },
    children: [
      {
        path: '',
        name: 'dashboard-provider',
        component: ProviderOverviewView,
        meta: { title: 'Overview', subtitle: 'Requests, jobs, and profile' },
      },
      {
        path: 'requests',
        name: 'provider-requests',
        component: ProviderRequestsView,
        meta: { title: 'Booking Requests', subtitle: 'Accept open customer requests' },
      },
      {
        path: 'requests/:id',
        name: 'provider-request-detail',
        component: ProviderBookingDetailView,
        meta: { title: 'Request Details', subtitle: 'Review and accept this booking' },
      },
      {
        path: 'jobs',
        name: 'provider-jobs',
        component: ProviderJobsView,
        meta: { title: 'My Jobs', subtitle: 'Start, complete, or cancel assigned work' },
      },
      {
        path: 'jobs/:id',
        name: 'provider-job-detail',
        component: ProviderBookingDetailView,
        meta: { title: 'Job Details', subtitle: 'Manage your assigned booking' },
      },
      {
        path: 'profile',
        redirect: '/dashboard/provider/settings/profile',
      },
      {
        path: 'settings/profile',
        name: 'provider-profile-settings',
        component: ProfileSettingsView,
        meta: { title: 'Profile settings', subtitle: 'Update your professional details' },
      },
      {
        path: 'settings/password',
        name: 'provider-change-password',
        component: ChangePasswordView,
        meta: { title: 'Change password', subtitle: 'Keep your account secure' },
      },
      {
        path: 'support/complaint',
        name: 'provider-complaint',
        component: ComplaintFormView,
        meta: { title: 'Complaint', subtitle: 'Report an issue with a booking' },
      },
      {
        path: 'support/insurance',
        name: 'provider-insurance',
        component: InsuranceFormView,
        meta: { title: 'Insurance', subtitle: 'Submit an insurance claim' },
      },
      {
        path: 'support/company',
        name: 'provider-company',
        component: CompanyFormView,
        meta: { title: 'Company Signup', subtitle: 'Onboard your business on Taskora' },
      },
    ],
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

  // Keep support/forms inside the app when signed in
  if (isAuthenticated.value) {
    const role = state.user?.role === 'provider' ? 'provider' : 'customer'
    const formRedirects = {
      '/forms/feedback': role === 'customer'
        ? '/dashboard/customer/bookings'
        : '/dashboard/provider/jobs',
      '/forms/complaint': `/dashboard/${role}/support/complaint`,
      '/forms/refund': role === 'customer'
        ? '/dashboard/customer/support/refund'
        : '/dashboard/provider',
      '/forms/insurance': `/dashboard/${role}/support/insurance`,
      '/forms/company': role === 'provider'
        ? '/dashboard/provider/support/company'
        : '/dashboard/customer',
      '/forms/booking': role === 'customer'
        ? '/dashboard/customer/book'
        : '/dashboard/provider/requests',
    }
    if (formRedirects[to.path]) {
      return formRedirects[to.path]
    }
  }

  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)
  const role = to.matched.map((r) => r.meta.role).find(Boolean)
  const guestOnly = to.matched.some((r) => r.meta.guestOnly)

  if (requiresAuth && !isAuthenticated.value) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (role && state.user?.role && role !== state.user.role) {
    return dashboardPathFor(state.user)
  }

  if (guestOnly && isAuthenticated.value) {
    return dashboardPathFor(state.user)
  }

  return true
})

export default router
