<template>
  <DefaultLayout>
    <Head :title="`Ubah ${notaToko.nomor}`" />
    <NotaTokoForm
      :action="`/nota-toko/${notaToko.id}`"
      method="put"
      :nota-toko="notaToko"
      title="Ubah Nota Toko"
      eyebrow="Nota Toko"
      description="Perbarui data nota, customer, atau item sesuai kebutuhan."
      :customers="customers"
      :nomor-preview="notaToko.nomor"
      back-href="/nota-toko"
      cancel-label="Batal"
      submit-label="Simpan Perubahan"
      processing-label="Menyimpan..."
    />
  </DefaultLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import NotaTokoForm from '../../components/nota-toko/NotaTokoForm.vue'

defineProps({
  notaToko: {
    type: Object,
    required: true,
  },
  customers: {
    type: Array,
    default: () => [],
  },
})
</script>
