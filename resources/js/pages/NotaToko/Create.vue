<template>
  <DefaultLayout>
    <Head title="Buat Nota Toko" />
    <NotaTokoForm
      action="/nota-toko"
      method="post"
      title="Buat Nota Toko"
      eyebrow="Nota Toko"
      description="Isi data customer, item, dan keterangan lalu simpan ke backend Laravel."
      :customers="customers"
      :nomor-preview="nomorPreview"
      back-href="/nota-toko"
      cancel-label="Batal"
      submit-label="Simpan"
      processing-label="Menyimpan..."
    />
  </DefaultLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import NotaTokoForm from '../../components/nota-toko/NotaTokoForm.vue'

defineProps({
  nomorPreview: {
    type: String,
    required: true,
  },
  customers: {
    type: Array,
    default: () => [],
  },
})
</script>
