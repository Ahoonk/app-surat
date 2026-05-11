export function useApi() {
  const config = useRuntimeConfig()
  const requestHeaders = process.server ? useRequestHeaders(['cookie']) : {}

  return async function apiFetch<T>(path: string, options: any = {}) {
    return await $fetch<T>(path, {
      baseURL: config.public.apiBase,
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        ...requestHeaders,
        ...(options.headers ?? {}),
      },
      ...options,
    })
  }
}
