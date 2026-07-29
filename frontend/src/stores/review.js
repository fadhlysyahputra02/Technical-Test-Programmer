import { defineStore } from 'pinia'
import api from '../utils/api'

export const useReviewStore = defineStore('review', {
  state: () => ({
    list: [],
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
    async fetchApplicationsForReview(filters = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/api/reviewer/applications', { params: filters })
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
        this.error = err.response?.data?.message || 'Gagal mengambil permohonan masuk.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async submitReview(applicationId, reviewData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post(`/api/applications/${applicationId}/reviews`, reviewData)
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal memproses penilaian.'
        throw err
      } finally {
        this.loading = false
      }
    }
  }
})
