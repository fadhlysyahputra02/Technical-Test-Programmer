import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  // Static Routes
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/403',
    name: 'forbidden',
    component: () => import('../views/Forbidden.vue')
  },

  // Guest Only Routes
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/Register.vue'),
    meta: { guest: true }
  },

  // Auth Base Routes
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { auth: true }
  },

  // Applicant Only Routes
  {
    path: '/projects',
    name: 'projects.index',
    component: () => import('../views/projects/ProjectList.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/projects/create',
    name: 'projects.create',
    component: () => import('../views/projects/ProjectCreate.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/projects/:id/edit',
    name: 'projects.edit',
    component: () => import('../views/projects/ProjectEdit.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/applications',
    name: 'applications.index',
    component: () => import('../views/applications/ApplicationList.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/applications/create',
    name: 'applications.create',
    component: () => import('../views/applications/ApplicationCreate.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/applications/:id',
    name: 'applications.show',
    component: () => import('../views/applications/ApplicationDetail.vue'),
    meta: { auth: true, role: 'applicant' }
  },
  {
    path: '/applications/:id/edit',
    name: 'applications.edit',
    component: () => import('../views/applications/ApplicationEdit.vue'),
    meta: { auth: true, role: 'applicant' }
  },

  // Reviewer Only Routes
  {
    path: '/reviewer/applications',
    name: 'reviewer.applications.index',
    component: () => import('../views/reviewer/ReviewerList.vue'),
    meta: { auth: true, role: 'reviewer' }
  },
  {
    path: '/reviewer/applications/:id',
    name: 'reviewer.applications.show',
    component: () => import('../views/reviewer/ReviewerDetail.vue'),
    meta: { auth: true, role: 'reviewer' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Route guard to check authentication status and roles
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // 1. Fetch current user if token exists but user info isn't in memory (e.g. Page refresh)
  if (authStore.token && !authStore.user && !authStore.loading) {
    try {
      await authStore.fetchMe()
    } catch (e) {
      // Invalid or expired token
      authStore.logout()
      return next({ name: 'login' })
    }
  }

  // 2. Redirect to /login if route requires auth but user is not authenticated
  if (to.meta.auth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }

  // 3. Redirect to /dashboard if route is guest-only but user is authenticated
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  // 4. Redirect to /403 if route requires a specific role and user role does not match
  if (to.meta.role && authStore.user && authStore.role !== to.meta.role) {
    return next({ name: 'forbidden' })
  }

  next()
})

export default router
