export interface DashboardResponse {
  dashboardFinancial: {
    total_semua: number
    total_sudah_dibayar: number
    total_belum_dibayar: number
    jumlah_semua: number
    jumlah_sudah_dibayar: number
    jumlah_belum_dibayar: number
  }
  dashboardStatus: {
    penawaran: {
      draft: number
      submitted: number
      approved: number
      rejected: number
    }
    purchasing_order: {
      menunggu_upload: number
      sudah_upload: number
    }
    invoice: {
      belum_dibayar: number
      sudah_dibayar: number
    }
    faktur_pajak: {
      menunggu_upload: number
      belum_dibayar: number
      sudah_dibayar: number
    }
  }
  dashboardTax: {
    total_semua: number
    total_sudah_dibayar: number
    total_belum_dibayar: number
    jumlah_semua: number
    jumlah_sudah_dibayar: number
    jumlah_belum_dibayar: number
  }
  dashboardNotaToko: {
    total_semua: number
    total_sudah_dibayar: number
    total_belum_dibayar: number
    jumlah_semua: number
    jumlah_sudah_dibayar: number
    jumlah_belum_dibayar: number
  }
  dashboardTransactions: Array<{
    sort_date: string
    invoice: {
      id: number
      nomor: string
      tanggal: string
      total: number
      payment_status: string | null
      payment_date: string | null
    } | null
    penawaran: PenawaranSummary
    faktur_pajak: {
      id: number
      dokumen_path: string | null
      dokumen_name: string | null
      payment_status: string | null
      payment_date: string | null
    } | null
  }>
}

export interface BootstrapResponse {
  user: {
    id: number
    name: string
    email: string
    role: string | null
  }
  company: {
    id: number | null
    name: string | null
    address: string | null
    logo: string | null
    settings: CompanySettings | null
  }
  dashboard: DashboardResponse
  lookups: {
    customers: any[]
    mitras: any[]
  }
  siteProfile: {
    clients: SiteClient[]
    products: SiteProduct[]
  }
}

export interface CompanySettings {
  company_name?: string | null
  home_seo_title?: string | null
  home_seo_description?: string | null
  home_seo_keywords?: string | null
  tagline?: string | null
  hero_title?: string | null
  hero_description?: string | null
  profile_title?: string | null
  profile_description?: string | null
  vision_title?: string | null
  vision_description?: string | null
  mission_items?: string[] | null
  about_title?: string | null
  about_description?: string | null
  about_slug?: string | null
  about_seo_title?: string | null
  about_seo_description?: string | null
  about_seo_keywords?: string | null
  clients_slug?: string | null
  clients_seo_title?: string | null
  clients_seo_description?: string | null
  clients_seo_keywords?: string | null
  products_slug?: string | null
  products_seo_title?: string | null
  products_seo_description?: string | null
  products_seo_keywords?: string | null
  contact_email?: string | null
  contact_phone?: string | null
  contact_whatsapp?: string | null
  company_address?: string | null
  logo_image_path?: string | null
  hero_image_path?: string | null
  about_image_path?: string | null
}

export interface SiteClient {
  id: number
  name: string
  sector: string | null
  description: string | null
  image_path: string | null
  sort_order: number
}

export interface SiteProduct {
  id: number
  name: string
  description: string | null
  features: string[] | null
  image_path: string | null
  sort_order: number
}

export interface ApiListResponse<T> {
  data: T[]
}

export interface CustomerLookup {
  id: number
  nama: string
  alamat: string | null
  no_hp: string | null
  email: string | null
}

export interface MitraLookup {
  id: number
  nama: string
  email: string | null
  alamat: string | null
  nomor_penawaran: string | null
  nomor_invoice: string | null
  nomor_surat_jalan: string | null
  nomor_berita_acara: string | null
}

export interface PenawaranItem {
  id?: number
  nama: string
  rincian: string | null
  qty: number
  satuan: 'month' | 'pcs' | 'item' | 'unit'
  unit_price: number
  amount: number
}

export interface PenawaranSummary {
  id: number
  company_id: number
  mitra_id: number | null
  user_id: number | null
  nomor: string
  tanggal: string
  customer_nama: string
  to_company: string | null
  to_address: string | null
  jenis_kontrak: 'kontrak' | 'satuan'
  signature_role: 'Direktur' | 'Manager' | 'Sales'
  keterangan: string | null
  subtotal: number
  tax_percent: number
  tax_amount: number
  total: number
  status: 'draft' | 'submitted' | 'approved' | 'rejected'
  invoice_date: string | null
  invoice_number: string | null
  invoice_sequence: number | null
  approved_by: number | null
  approved_at: string | null
  mitra?: MitraLookup | null
  latest_invoice?: any | null
  purchasing_order?: any | null
}

export interface PenawaranDetail extends PenawaranSummary {
  company?: {
    id: number
    name: string
    address: string | null
    logo: string | null
  } | null
  user?: {
    id: number
    name: string
    email: string
  } | null
  approver?: {
    id: number
    name: string
    email: string
  } | null
  items: PenawaranItem[]
  invoices?: any[] | null
}

export interface PenawaranMetaResponse {
  nomor_preview: string
  to_company_options: string[]
  customers: CustomerLookup[]
  mitras: MitraLookup[]
  defaults: {
    tanggal: string
    tax_percent: number
    status: 'draft'
    jenis_kontrak: 'kontrak' | 'satuan'
    signature_role: 'Direktur' | 'Manager' | 'Sales'
    keterangan: string
  }
  options: {
    jenis_kontrak: Array<'kontrak' | 'satuan'>
    signature_role: Array<'Direktur' | 'Manager' | 'Sales'>
    satuan: Array<'month' | 'pcs' | 'item' | 'unit'>
    status: Array<'draft' | 'submitted' | 'approved' | 'rejected'>
  }
}

export interface PenawaranFormItem {
  nama: string
  rincian: string
  qty: string
  satuan: 'month' | 'pcs' | 'item' | 'unit'
  unit_price: string
}

export interface PenawaranFormState {
  mitra_id: string
  tanggal: string
  to_company: string
  to_address: string
  jenis_kontrak: 'kontrak' | 'satuan'
  signature_role: 'Direktur' | 'Manager' | 'Sales' | ''
  keterangan: string
  tax_percent: string
  status: 'draft' | 'submitted' | 'approved' | 'rejected'
  items: PenawaranFormItem[]
}

export interface PenawaranSubmitPayload {
  mitra_id?: number | null
  tanggal: string
  to_company: string
  to_address: string | null
  jenis_kontrak: 'kontrak' | 'satuan'
  signature_role: 'Direktur' | 'Manager' | 'Sales'
  keterangan: string | null
  tax_percent: number
  status: 'draft' | 'submitted' | 'approved' | 'rejected'
  items: Array<{
    nama: string
    rincian: string | null
    qty: number
    satuan: 'month' | 'pcs' | 'item' | 'unit'
    unit_price: number
  }>
}

export interface InvoiceSummary {
  id: number
  penawaran_id: number
  purchasing_order_id: number | null
  nomor: string
  tanggal: string
  sequence: number | null
  total: number
  payment_status: string | null
  payment_date: string | null
  created_by: number | null
  penawaran?: {
    id: number
    company_id: number
    nomor: string
    tanggal: string
    customer_nama: string
    to_company: string | null
    jenis_kontrak: 'kontrak' | 'satuan'
    status: 'draft' | 'submitted' | 'approved' | 'rejected'
    subtotal: number
    tax_percent: number
    tax_amount: number
    total: number
  } | null
  purchasing_order?: {
    id: number
    dokumen_path: string | null
    dokumen_name: string | null
    nomor_po: string | null
    tanggal_po: string | null
  } | null
  faktur_pajak?: {
    id: number
    dokumen_path: string | null
    dokumen_name: string | null
    payment_status: string | null
    payment_date: string | null
  } | null
  surat_jalan?: {
    id: number
    nomor: string | null
    tanggal: string | null
    pemberi_nama: string | null
    pemberi_jabatan: string | null
    penerima_nama: string | null
    penerima_hp: string | null
  } | null
  berita_acara?: {
    id: number
    nomor: string | null
    tanggal: string | null
    perihal: string | null
    keterangan_akhir: string | null
  } | null
}

export interface InvoiceDetail extends InvoiceSummary {
  penawaran?: InvoiceSummary['penawaran'] & {
    items?: PenawaranItem[] | null
    mitra?: MitraLookup | null
  } | null
}

export interface InvoiceDatePayload {
  tanggal: string
}

export interface InvoicePaymentPayload {
  payment_date: string
}

export interface SuratJalanSummary {
  id: number
  invoice_id: number
  nomor: string
  tanggal: string
  pemberi_nama: string | null
  pemberi_jabatan: string | null
  pemberi_alamat: string | null
  penerima_nama: string | null
  penerima_hp: string | null
  kota_tanggal_manual: string | null
  created_by: number | null
  invoice?: {
    id: number
    nomor: string
    tanggal: string
    total: number
    penawaran?: {
      id: number
      nomor: string
      customer_nama: string
      to_company: string | null
      status: 'draft' | 'submitted' | 'approved' | 'rejected'
      total: number
      mitra?: MitraLookup | null
    } | null
    purchasing_order?: {
      id: number
      nomor_po: string | null
      tanggal_po: string | null
      dokumen_name: string | null
    } | null
    faktur_pajak?: {
      id: number
      dokumen_name: string | null
      payment_status: string | null
      payment_date: string | null
    } | null
    berita_acara?: {
      id: number
      nomor: string | null
      tanggal: string | null
    } | null
  } | null
}

export interface SuratJalanDetail extends SuratJalanSummary {
  invoice?: NonNullable<SuratJalanSummary['invoice']> & {
    penawaran?: NonNullable<SuratJalanSummary['invoice']>['penawaran'] & {
      items?: PenawaranItem[] | null
      company?: {
        id: number
        name: string
        address: string | null
        logo: string | null
      } | null
      user?: {
        id: number
        name: string
        email: string
      } | null
      approver?: {
        id: number
        name: string
        email: string
      } | null
    } | null
  } | null
}

export interface BeritaAcaraSummary {
  id: number
  invoice_id: number
  nomor: string
  tanggal: string
  perihal: string | null
  keterangan_akhir: string | null
  kota_tanggal_manual: string | null
  created_by: number | null
  invoice?: {
    id: number
    nomor: string
    tanggal: string
    total: number
    penawaran?: {
      id: number
      nomor: string
      customer_nama: string
      to_company: string | null
      status: 'draft' | 'submitted' | 'approved' | 'rejected'
      total: number
      mitra?: MitraLookup | null
    } | null
    purchasing_order?: {
      id: number
      nomor_po: string | null
      tanggal_po: string | null
      dokumen_name: string | null
    } | null
    surat_jalan?: {
      id: number
      nomor: string | null
      tanggal: string | null
    } | null
  } | null
}

export interface BeritaAcaraDetail extends BeritaAcaraSummary {
  invoice?: NonNullable<BeritaAcaraSummary['invoice']> & {
    penawaran?: NonNullable<BeritaAcaraSummary['invoice']>['penawaran'] & {
      items?: PenawaranItem[] | null
      company?: {
        id: number
        name: string
        address: string | null
        logo: string | null
      } | null
      user?: {
        id: number
        name: string
        email: string
      } | null
      approver?: {
        id: number
        name: string
        email: string
      } | null
    } | null
  } | null
}
