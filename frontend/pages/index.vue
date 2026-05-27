<template>
  <div />
</template>

<script setup lang="ts">
const { ensure } = useSession()

try {
  await ensure()
  await navigateTo('/dashboard')
} catch {
  await navigateTo('/login')
}
</script>
