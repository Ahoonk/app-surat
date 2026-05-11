export default defineNuxtRouteMiddleware(async (to) => {
  const { ensure } = useSession()

  try {
    await ensure()
  } catch {
    return navigateTo({
      path: '/login',
      query: to.fullPath === '/login' ? {} : { redirect: to.fullPath },
    })
  }
})
