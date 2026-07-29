import { defineStore } from 'pinia'
import api from '../utils/api'

export const useApplicationStore = defineStore('application', {
  state: () => ({
    list: [],
    detail: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  }),

  actions: {
    async fetchApplications(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/api/applications', { params: filters })
        this.list = response.data.data
        if (response.data.meta) {
          this.pagination = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total
          }
        }
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengambil daftar permohonan.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchDetail(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/api/applications/${id}`)
        this.detail = response.data.data
        return response.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengambil detail permohonan.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createApplication(projectId) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/api/applications', { project_id: projectId })
        return response.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal membuat permohonan baru.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async submitApplication(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post(`/api/applications/${id}/submit`)
        if (this.detail && this.detail.id === id) {
          this.detail = response.data.data
        }
        return response.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengajukan permohonan.'
        throw err
      } finally {
        this.loading = false
      }
    }
  }
})
