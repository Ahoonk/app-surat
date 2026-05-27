import axios from 'axios'

const api = axios.create({
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
})

export function extractApiMessage(error, fallback) {
    if (error?.response?.data?.message) {
        return error.response.data.message
    }

    const errors = error?.response?.data?.errors

    if (errors && typeof errors === 'object') {
        const firstError = Object.values(errors).flat()[0]

        if (typeof firstError === 'string') {
            return firstError
        }
    }

    return fallback
}

export default api
