<template>
  <form class="space-y-6" @submit.prevent="onSubmit">
    <section class="panel overflow-hidden">
      <div class="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">
            {{ mode === 'create' ? 'Buat Data' : 'Ubah Data' }}
          </p>
          <h2 class="section-title mt-3">
            {{ mode === 'create' ? 'Surat Penawaran Baru' : 'Edit Surat Penawaran' }}
          </h2>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Form ini mengikuti alur lama, tapi sekarang sudah siap dipakai dari frontend Nuxt.
            Nomor surat, customer, dan total item tetap dihitung dari backend PT ASKARYA.
          </p>
        </div>

        <div class="rounded-[22px] bg-gradient-to-br from-slate-950 to-cyan-900 p-5 text-white">
          <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Nomor Surat</p>
          <h3 class="mt-3 text-2xl font-semibold">{{ displayNomor }}</h3>
          <p v-if="selectedMitra" class="mt-2 text-sm leading-6 text-slate-200/80">
            Mengikuti nomor penawaran milik mitra: {{ selectedMitra.nama }}
          </p>
          <p v-else class="mt-2 text-sm leading-6 text-slate-200/80">
            Nomor akan dibuat otomatis saat disimpan.
          </p>
        </div>
      </div>
    </section>

    <section v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ errorMessage }}
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal</span>
        <input v-model="form.tanggal" type="date" class="input" required />
      </label>

      <label v-if="mode === 'create'" class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Mitra</span>
        <select v-model="form.mitra_id" class="input">
          <option value="">Tanpa mitra</option>
          <option v-for="mitra in mitras" :key="mitra.id" :value="String(mitra.id)">
            {{ mitra.nama }}
          </option>
        </select>
        <p class="mt-2 text-xs text-slate-500">
          Jika mitra dipilih dan punya nomor penawaran, nomor surat akan mengikuti nomor tersebut.
        </p>
      </label>

      <div v-else class="block rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-sm font-medium text-slate-700">Mitra</p>
        <p class="mt-2 text-sm text-slate-600">{{ selectedMitra?.nama ?? 'Tanpa mitra' }}</p>
        <p class="mt-1 text-xs text-slate-500">Mitra diubah dari backend lama dan hanya dibaca di frontend ini.</p>
      </div>

      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Customer</span>
        <input
          v-model="form.to_company"
          list="penawaran-customer-options"
          type="text"
          class="input"
          placeholder="Ketik nama customer atau pilih dari daftar"
          required
          @input="handleCompanyInput"
        />
        <datalist id="penawaran-customer-options">
          <option v-for="option in customerOptions" :key="option" :value="option" />
        </datalist>
        <p class="mt-2 text-xs text-slate-500">
          Bisa pilih customer master atau ketik manual jika belum ada di master data.
        </p>
      </label>

      <label class="block md:col-span-2">
        <span class="mb-2 block text-sm font-medium text-slate-700">Alamat</span>
        <textarea
          v-model="form.to_address"
          class="input min-h-[92px]"
          placeholder="Alamat customer"
          @input="handleAddressInput"
        />
        <p class="mt-2 text-xs text-slate-500">
          Jika customer cocok dengan master data, alamat akan terisi otomatis.
        </p>
      </label>

      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Jenis Kontrak</span>
        <select v-model="form.jenis_kontrak" class="input" required>
          <option v-for="option in meta.options.jenis_kontrak" :key="option" :value="option">
            {{ option === 'kontrak' ? 'Kontrak' : 'Satuan' }}
          </option>
        </select>
      </label>

      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Status</span>
        <select v-model="form.status" class="input" required>
          <option v-for="option in statusOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </label>

      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Signature Role</span>
        <select v-model="form.signature_role" class="input" required>
          <option value="">Pilih jabatan</option>
          <option v-for="option in meta.options.signature_role" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </label>

      <label class="block">
        <span class="mb-2 block text-sm font-medium text-slate-700">Pajak (%)</span>
        <input v-model="form.tax_percent" type="number" min="0" max="100" step="0.01" class="input" required />
      </label>
    </section>

    <section class="panel p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">Item Penawaran</h3>
          <p class="mt-1 text-sm text-slate-500">Tambah item sebanyak yang diperlukan. Total dihitung otomatis.</p>
        </div>
        <button type="button" class="button-primary" @click="addItem">
          + Tambah Item
        </button>
      </div>

      <div class="mt-6 space-y-4">
        <article v-for="(item, index) in form.items" :key="index" class="rounded-2xl border border-slate-200 bg-white p-4">
          <div class="mb-4 flex items-start justify-between gap-3">
            <div>
              <p class="text-sm font-semibold text-slate-800">Detail Item {{ index + 1 }}</p>
              <p class="text-xs text-slate-500">Isi nama, rincian, qty, satuan, dan harga satuannya.</p>
            </div>
            <button type="button" class="button-ghost px-3 py-2 text-sm text-red-600" :disabled="form.items.length === 1" @click="removeItem(index)">
              Hapus
            </button>
          </div>

          <div class="grid gap-3 xl:grid-cols-12">
            <label class="block xl:col-span-4">
              <span class="mb-1 block text-sm font-medium text-slate-700">Item</span>
              <input v-model="item.nama" type="text" class="input" required placeholder="Nama item" />
            </label>

            <label class="block xl:col-span-4">
              <span class="mb-1 block text-sm font-medium text-slate-700">Rincian</span>
              <textarea v-model="item.rincian" class="input min-h-[44px]" placeholder="Rincian item" />
            </label>

            <label class="block sm:col-span-1 xl:col-span-1">
              <span class="mb-1 block text-sm font-medium text-slate-700">Qty</span>
              <input v-model="item.qty" type="number" step="0.01" min="0.01" class="input" required />
            </label>

            <label class="block sm:col-span-1 xl:col-span-1">
              <span class="mb-1 block text-sm font-medium text-slate-700">Satuan</span>
              <select v-model="item.satuan" class="input" required>
                <option v-for="option in meta.options.satuan" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
            </label>

            <label class="block xl:col-span-1">
              <span class="mb-1 block text-sm font-medium text-slate-700">Unit Price</span>
              <input v-model="item.unit_price" type="number" step="0.01" min="0" class="input" required />
            </label>

            <div class="block xl:col-span-1">
              <span class="mb-1 block text-sm font-medium text-slate-700">Amount</span>
              <div class="flex min-h-[44px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800">
                {{ formatCurrency(itemAmount(item)) }}
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
      <label class="block panel p-6">
        <span class="mb-2 block text-sm font-medium text-slate-700">Keterangan</span>
        <textarea
          v-model="form.keterangan"
          class="input min-h-[180px]"
          placeholder="Catatan tambahan untuk surat penawaran"
        />
      </label>

      <div class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Ringkasan Nilai</h3>
        <div class="mt-5 space-y-3">
          <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
            <span class="text-sm text-slate-500">Subtotal</span>
            <span class="font-semibold text-slate-900">{{ formatCurrency(subtotal) }}</span>
          </div>
          <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
            <span class="text-sm text-slate-500">Pajak</span>
            <span class="font-semibold text-slate-900">{{ formatCurrency(taxAmount) }}</span>
          </div>
          <div class="flex items-center justify-between rounded-2xl bg-cyan-50 px-4 py-4">
            <span class="text-sm font-medium text-cyan-800">Total</span>
            <span class="text-xl font-semibold text-cyan-950">{{ formatCurrency(total) }}</span>
          </div>
        </div>
      </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <NuxtLink :to="cancelTo" class="button-ghost justify-center">
        Batal
      </NuxtLink>
      <button type="submit" class="button-primary" :disabled="busy">
        {{ busy ? 'Menyimpan...' : submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { formatCurrency } from '~/utils/format'
import type {
  MitraLookup,
  PenawaranDetail,
  PenawaranFormItem,
  PenawaranFormState,
  PenawaranMetaResponse,
  PenawaranSubmitPayload,
  CustomerLookup,
} from '~/types/api'

const props = withDefaults(defineProps<{
  mode: 'create' | 'edit'
  meta: PenawaranMetaResponse
  customers: CustomerLookup[]
  mitras: MitraLookup[]
  initialValues?: PenawaranDetail | null
  busy?: boolean
  errorMessage?: string
  submitLabel?: string
  cancelTo?: string
}>(), {
  busy: false,
  errorMessage: '',
  submitLabel: '',
  cancelTo: '/penawaran',
})

const emit = defineEmits<{
  submit: [payload: PenawaranSubmitPayload]
}>()

const defaultStatus = props.mode === 'create' ? props.meta.defaults.status : 'draft'

function createItem(item: Partial<PenawaranFormItem> = {}): PenawaranFormItem {
  return {
    nama: item.nama ?? '',
    rincian: item.rincian ?? '',
    qty: item.qty ?? '1',
    satuan: item.satuan ?? 'pcs',
    unit_price: item.unit_price ?? '0',
  }
}

function createState(values?: PenawaranDetail | null): PenawaranFormState {
  return {
    mitra_id: values?.mitra_id ? String(values.mitra_id) : '',
    tanggal: values?.tanggal ?? props.meta.defaults.tanggal,
    to_company: values?.to_company ?? values?.customer_nama ?? '',
    to_address: values?.to_address ?? '',
    jenis_kontrak: values?.jenis_kontrak ?? props.meta.defaults.jenis_kontrak,
    signature_role: values?.signature_role ?? props.meta.defaults.signature_role,
    keterangan: values?.keterangan ?? props.meta.defaults.keterangan,
    tax_percent: String(values?.tax_percent ?? props.meta.defaults.tax_percent),
    status: values?.status ?? defaultStatus,
    items: values?.items?.length
      ? values.items.map((item) => createItem({
        nama: item.nama,
        rincian: item.rincian ?? '',
        qty: String(item.qty),
        satuan: item.satuan,
        unit_price: String(item.unit_price),
      }))
      : [createItem()],
  }
}

const form = reactive<PenawaranFormState>(createState(props.initialValues ?? null))
const addressTouched = ref(false)

const customerOptions = computed(() => {
  return Array.from(new Set([
    ...props.meta.to_company_options,
    ...props.customers.map((customer) => customer.nama),
  ]))
})

const selectedCustomer = computed(() => {
  return props.customers.find((customer) => customer.nama === form.to_company) ?? null
})

const selectedMitra = computed(() => {
  if (!form.mitra_id) {
    return null
  }

  return props.mitras.find((mitra) => String(mitra.id) === form.mitra_id) ?? null
})

const displayNomor = computed(() => {
  if (props.mode === 'edit') {
    return props.initialValues?.nomor ?? '-'
  }

  return selectedMitra.value?.nomor_penawaran || props.meta.nomor_preview || '-'
})

const submitLabel = computed(() => {
  if (props.submitLabel) {
    return props.submitLabel
  }

  return props.mode === 'create' ? 'Simpan Penawaran' : 'Simpan Perubahan'
})

const statusOptions = computed(() => {
  if (props.mode === 'create') {
    return props.meta.options.status.filter((option) => option === 'draft' || option === 'submitted')
  }

  return props.meta.options.status
})

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + itemAmount(item), 0)
})

const taxAmount = computed(() => {
  const taxPercent = toNumber(form.tax_percent)
  return subtotal.value * (taxPercent / 100)
})

const total = computed(() => subtotal.value + taxAmount.value)

function toNumber(value: string | number | null | undefined): number {
  return Number(value ?? 0) || 0
}

function itemAmount(item: PenawaranFormItem): number {
  return toNumber(item.qty) * toNumber(item.unit_price)
}

function applyState(values: PenawaranDetail | null | undefined) {
  const nextState = createState(values ?? null)
  form.mitra_id = nextState.mitra_id
  form.tanggal = nextState.tanggal
  form.to_company = nextState.to_company
  form.to_address = nextState.to_address
  form.jenis_kontrak = nextState.jenis_kontrak
  form.signature_role = nextState.signature_role
  form.keterangan = nextState.keterangan
  form.tax_percent = nextState.tax_percent
  form.status = nextState.status
  form.items.splice(0, form.items.length, ...nextState.items)
  addressTouched.value = Boolean(form.to_address)
  syncAddressFromCustomer(!form.to_address)
}

function syncAddressFromCustomer(force = false) {
  const customer = selectedCustomer.value

  if (!customer) {
    return
  }

  const customerAddress = customer.alamat ?? ''

  if (!customerAddress && !force) {
    return
  }

  if (force || !addressTouched.value || !form.to_address) {
    form.to_address = customerAddress
  }
}

function handleCompanyInput() {
  syncAddressFromCustomer(false)
}

function handleAddressInput() {
  addressTouched.value = true
}

function addItem() {
  form.items.push(createItem())
}

function removeItem(index: number) {
  if (form.items.length === 1) {
    return
  }

  form.items.splice(index, 1)
}

function onSubmit() {
  emit('submit', {
    mitra_id: form.mitra_id ? Number(form.mitra_id) : null,
    tanggal: form.tanggal,
    to_company: form.to_company.trim(),
    to_address: form.to_address.trim() || null,
    jenis_kontrak: form.jenis_kontrak,
    signature_role: form.signature_role || 'Direktur',
    keterangan: form.keterangan.trim() || null,
    tax_percent: toNumber(form.tax_percent),
    status: form.status,
    items: form.items.map((item) => ({
      nama: item.nama.trim(),
      rincian: item.rincian.trim() || null,
      qty: toNumber(item.qty),
      satuan: item.satuan,
      unit_price: toNumber(item.unit_price),
    })),
  })
}

watch(
  () => props.initialValues,
  (value) => {
    applyState(value ?? null)
  },
  { immediate: true, deep: true },
)
</script>
