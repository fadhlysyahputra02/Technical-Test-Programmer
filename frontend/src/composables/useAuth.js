import { storeToRefs } from 'pinia'
import { useAuthStore } from '../stores/auth'

export function useAuth() {
  const authStore = useAuthStore()
  const { user, token, loading, error, isAuthenticated, role, permissions } = storeToRefs(authStore)

  return {
    // Reactive States & Getters
    user,
    token,
    loading,
    error,
    isAuthenticated,
    role,
    permissions,

    // Store Actions
    login: authStore.login,
    logout: authStore.logout,
    register: authStore.register,
    fetchMe: authStore.fetchMe,
    hasRole: authStore.hasRole,
    hasPermission: authStore.hasPermission
  }
}
