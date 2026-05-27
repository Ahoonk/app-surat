import type { BootstrapResponse } from '~/types/api'

export function useSession() {
  const session = useState<BootstrapResponse | null>('session', () => null)
  const loading = useState<boolean>('session-loading', () => false)
  const apiFetch = useApi()

  async function refresh() {
    loading.value = true

    try {
      session.value = await apiFetch<BootstrapResponse>('/api/bootstrap')
      return session.value
    } finally {
      loading.value = false
    }
  }

  async function ensure() {
    if (session.value) {
      return session.value
    }

    return await refresh()
  }

  async function logout() {
    await apiFetch('/sanctum/csrf-cookie')
    await apiFetch('/logout', { method: 'POST' })
    session.value = null
  }

  return {
    session,
    loading,
    refresh,
    ensure,
    logout,
  }
}
