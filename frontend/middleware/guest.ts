export default defineNuxtRouteMiddleware(async () => {
  const { ensure } = useSession()

  try {
    await ensure()
    return navigateTo('/dashboard')
  } catch {
    return
  }
})
