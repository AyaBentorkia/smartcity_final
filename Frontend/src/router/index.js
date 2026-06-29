import { createRouter, createWebHistory } from 'vue-router'

// ✅ Seules les pages publiques chargent immédiatement
import Home           from '../pages/Home.vue'
import Login          from '../pages/Login.vue'
import GoogleCallback from '../pages/GoogleCallback.vue'
import ForgotPassword from '../pages/ForgotPassword.vue'
import ResetPassword  from '../pages/ResetPassword.vue'
import AppLayout      from '../layouts/AppLayout.vue'

// ✅ Pages dashboard en lazy loading (chargées seulement quand visitées)
const Dashboard             = () => import('../pages/Dashboard/Dashboard.vue')
const IncidentPage          = () => import('../pages/Incidents/IncidentPage.vue')
const AgentsPage            = () => import('../pages/Users/AgentsPage.vue')
const AssignmentsPage       = () => import('../pages/Assignments/AssignmentsPage.vue')
const ProfilePage           = () => import('../pages/Profile/ProfilePage.vue')
const MunicipalityPage      = () => import('../pages/Municipalities/MunicipalityPage.vue')
const UsersPage             = () => import('../pages/Users/UsersPage.vue')
const Zones                 = () => import('../pages/Zones/Zones.vue')
const MunicipalitiesListPage = () => import('../pages/Municipalities/MunicipalitiesListPage.vue')
const ServicesPage          = () => import('../pages/Services(Categories)/ServicesPage.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                     component: Home },
    { path: '/login',                component: Login },
    { path: '/auth/google/callback', name: 'GoogleCallback', component: GoogleCallback, meta: { requiresAuth: false } },
    { path: '/forgot-password',      name: 'ForgotPassword', component: ForgotPassword, meta: { requiresAuth: false } },
    { path: '/reset-password',       name: 'ResetPassword',  component: ResetPassword,  meta: { requiresAuth: false } },

    {
      path: '/',
      component: AppLayout,
      children: [
        { path: '/Dashboard',      component: Dashboard },
        { path: '/Incidents',      component: IncidentPage },
        { path: '/agents',         component: AgentsPage },
        { path: '/users',          component: UsersPage },
        { path: '/assignments',    component: AssignmentsPage },
        { path: '/profile',        component: ProfilePage },
        { path: '/municipality',   component: MunicipalityPage },
        { path: '/municipalities', component: MunicipalitiesListPage },
        { path: '/zones',          component: Zones },
        { path: '/Services',       component: ServicesPage },
      ],
    },
  ],
})

export default router