import { defineStore } from 'pinia'
import api from '../utils/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    role: (state) => {
      if (!state.user) return null
      return state.user.roles && state.user.roles.length > 0 ? state.user.roles[0] : null
    },
    permissions: (state) => state.user?.permissions || [],
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/api/login', credentials)
        const { token, data } = response.data
        
        this.token = token
        this.user = data
        
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(data))
        
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/api/register', userData)
        const { token, data } = response.data
        
        this.token = token
        this.user = data
        
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(data))
        
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Registrasi gagal.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await api.post('/api/logout')
      } catch (err) {
        console.error('Logout failed on backend:', err)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        this.loading = false
      }
    },

    async fetchMe() {
      if (!this.token) return
      this.loading = true
      try {
        const response = await api.get('/api/me')
        const user = response.data.data
        this.user = user
        localStorage.setItem('user', JSON.stringify(user))
      } catch (err) {
        console.error('fetchMe failed:', err)
        // Clean local session if backend returned error (expired token)
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        throw err
      } finally {
        this.loading = false
      }
    },

    hasRole(roleName) {
      return this.role === roleName
    },

    hasPermission(permissionName) {
      return this.permissions.includes(permissionName)
    }
  }
})
