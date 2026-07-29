import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Request interceptor to append authorization token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors globally (401, 403, 422)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      // Clear local auth storage
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      
      // Redirect to login using dynamic router import to prevent circular dependency
      import('../router').then((routerModule) => {
        routerModule.default.push('/login')
      })
    } else if (status === 403) {
      import('../router').then((routerModule) => {
        routerModule.default.push('/403')
      })
    }

    // Always reject so validation/other errors can be caught locally
    return Promise.reject(error)
  }
)

export default api
