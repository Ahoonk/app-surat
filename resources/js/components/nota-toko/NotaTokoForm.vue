<template>
  <div class="panel p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div>
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ eyebrow }}</p>
        <h1 class="section-title mt-2">{{ title }}</h1>
        <p class="mt-3 text-sm text-slate-600">{{ description }}</p>
      </div>

      <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Nomor Nota</div>
        <div class="mt-1 font-semibold text-slate-900">{{ nomorPreview }}</div>
      </div>
    </div>

    <div v-if="firstError" class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ firstError }}
    </div>

    <form class="mt-6 space-y-6" @submit.prevent="submit">
      <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal</span>
          <input v-model="form.tanggal" type="date" class="input" />
          <p v-if="form.errors.tanggal" class="mt-2 text-xs text-red-600">{{ form.errors.tanggal }}</p>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Customer</span>
          <select v-model="form.customer_nama" class="input">
            <option value="">Pilih customer</option>
            <option v-for="customer in customers" :key="customer.id" :value="customer.nama">
              {{ customer.nama }}
            </option>
          </select>
          <p v-if="form.errors.customer_nama" class="mt-2 text-xs text-red-600">{{ form.errors.customer_nama }}</p>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Alamat</span>
          <textarea v-model="form.alamat" rows="3" class="input"></textarea>
          <p v-if="form.errors.alamat" class="mt-2 text-xs text-red-600">{{ form.errors.alamat }}</p>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Email Customer</span>
          <input v-model="form.customer_email" type="email" class="input" />
          <p v-if="form.errors.customer_email" class="mt-2 text-xs text-red-600">{{ form.errors.customer_email }}</p>
        </label>
      </div>

      <section>
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Item Nota</h2>
            <p class="text-sm text-slate-500">Tambahkan item sebanyak yang dibutuhkan.</p>
          </div>
          <button type="button" class="button-primary" @click="addItem">+ Tambah Item</button>
        </div>

        <div class="space-y-4">
          <div v-for="(item, index) in form.items" :key="index" class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-slate-800">Detail Item</p>
                <p class="text-xs text-slate-500">Isi nama, qty, satuan, dan harga satuan.</p>
              </div>
              <button type="button" class="button-ghost text-red-600" :disabled="form.items.length === 1" @click="removeItem(index)">
                Hapus
              </button>
            </div>

            <div class="grid gap-4 xl:grid-cols-12">
              <label class="block xl:col-span-4">
                <span class="mb-2 block text-sm font-medium text-slate-700">Item</span>
                <input v-model="item.nama" type="text" class="input" />
                <p v-if="form.errors[`items.${index}.nama`]" class="mt-2 text-xs text-red-600">
                  {{ form.errors[`items.${index}.nama`] }}
                </p>
              </label>

              <label class="block xl:col-span-2">
                <span class="mb-2 block text-sm font-medium text-slate-700">Qty</span>
                <input v-model="item.qty" type="number" step="0.01" min="0.01" class="input" />
                <p v-if="form.errors[`items.${index}.qty`]" class="mt-2 text-xs text-red-600">
                  {{ form.errors[`items.${index}.qty`] }}
                </p>
              </label>

              <label class="block xl:col-span-2">
                <span class="mb-2 block text-sm font-medium text-slate-700">Satuan</span>
                <select v-model="item.satuan" class="input">
                  <option value="month">month</option>
                  <option value="pcs">pcs</option>
                  <option value="item">item</option>
                  <option value="unit">unit</option>
                </select>
                <p v-if="form.errors[`items.${index}.satuan`]" class="mt-2 text-xs text-red-600">
                  {{ form.errors[`items.${index}.satuan`] }}
                </p>
              </label>

              <label class="block xl:col-span-2">
                <span class="mb-2 block text-sm font-medium text-slate-700">Unit Price</span>
                <input v-model="item.unit_price" type="number" step="0.01" min="0" class="input" />
                <p v-if="form.errors[`items.${index}.unit_price`]" class="mt-2 text-xs text-red-600">
                  {{ form.errors[`items.${index}.unit_price`] }}
                </p>
              </label>

              <div class="xl:col-span-2">
                <span class="mb-2 block text-sm font-medium text-slate-700">Amount</span>
                <div class="input flex items-center bg-slate-100">{{ formatCurrency(amount(item)) }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="grid gap-4 md:grid-cols-[1fr_320px]">
        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Keterangan</span>
          <textarea v-model="form.keterangan" rows="4" class="input"></textarea>
          <p v-if="form.errors.keterangan" class="mt-2 text-xs text-red-600">{{ form.errors.keterangan }}</p>
        </label>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm">
          <div class="flex items-center justify-between gap-4">
            <span class="text-slate-500">Subtotal</span>
            <span class="font-semibold text-slate-900">{{ formatCurrency(subtotal) }}</span>
          </div>
          <div class="mt-2 flex items-center justify-between gap-4 border-t border-slate-200 pt-2">
            <span class="text-slate-700">Total</span>
            <span class="font-semibold text-slate-900">{{ formatCurrency(total) }}</span>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap justify-end gap-3">
        <a :href="backHref" class="button-ghost">{{ cancelLabel }}</a>
        <button type="submit" class="button-primary" :disabled="form.processing">
          {{ form.processing ? processingLabel : submitLabel }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { formatCurrency } from '../../utils/format'

const props = defineProps({
  action: {
    type: String,
    required: true,
  },
  method: {
    type: String,
    default: 'post',
  },
  title: {
    type: String,
    required: true,
  },
  eyebrow: {
    type: String,
    default: 'Nota Toko',
  },
  description: {
    type: String,
    default: 'Kelola nota toko dengan form Vue yang tetap tersambung ke alur backend.',
  },
  nomorPreview: {
    type: String,
    required: true,
  },
  customers: {
    type: Array,
    default: () => [],
  },
  backHref: {
    type: String,
    default: '/nota-toko',
  },
  cancelLabel: {
    type: String,
    default: 'Batal',
  },
  submitLabel: {
    type: String,
    default: 'Simpan',
  },
  processingLabel: {
    type: String,
    default: 'Menyimpan...',
  },
  notaToko: {
    type: Object,
    default: null,
  },
})

const initialItems = props.notaToko?.items?.length
  ? props.notaToko.items.map((item) => ({
      nama: item.nama ?? '',
      qty: item.qty ?? 1,
      satuan: item.satuan ?? 'pcs',
      unit_price: item.unit_price ?? 0,
    }))
  : [{ nama: '', qty: 1, satuan: 'pcs', unit_price: 0 }]

const form = useForm({
  tanggal: props.notaToko?.tanggal ?? new Date().toISOString().slice(0, 10),
  customer_nama: props.notaToko?.customer_nama ?? '',
  customer_email: props.notaToko?.customer_email ?? '',
  alamat: props.notaToko?.alamat ?? '',
  keterangan: props.notaToko?.keterangan ?? '',
  items: initialItems,
})

const initialCustomerSync = ref(true)
const customerByName = computed(() => {
  return props.customers.find((customer) => customer.nama === form.customer_nama) ?? null
})

watch(
  customerByName,
  (customer) => {
    if (!customer) return

    if (initialCustomerSync.value && props.notaToko) {
      initialCustomerSync.value = false
      return
    }

    form.alamat = customer.alamat ?? form.alamat
    form.customer_email = customer.email ?? form.customer_email
    initialCustomerSync.value = false
  },
  { immediate: true }
)

const subtotal = computed(() =>
  form.items.reduce((sum, item) => sum + amount(item), 0)
)

const total = computed(() => subtotal.value)
const firstError = computed(() => Object.values(form.errors)[0] ?? '')

function amount(item) {
  const qty = Number(item.qty ?? 0)
  const price = Number(item.unit_price ?? 0)
  return qty * price
}

function addItem() {
  form.items.push({
    nama: '',
    qty: 1,
    satuan: 'pcs',
    unit_price: 0,
  })
}

function removeItem(index) {
  if (form.items.length === 1) {
    return
  }

  form.items.splice(index, 1)
}

function submit() {
  if (props.method.toLowerCase() === 'put') {
    form.put(props.action, {
      preserveScroll: true,
    })
    return
  }

  form.post(props.action, {
    preserveScroll: true,
  })
}
</script>
