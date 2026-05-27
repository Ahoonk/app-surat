# PT ASKARYA Frontend

Frontend terpisah untuk backend PT ASKARYA yang sudah ada.

## Jalankan

1. Pastikan backend PT ASKARYA berjalan, misalnya di `http://localhost:8000`.
2. Install dependency di folder ini.
3. Jalankan frontend di `http://localhost:3000`.

## Env

Gunakan:

```bash
NUXT_PUBLIC_API_BASE=http://localhost:8000
```

## Alur auth

- Login memakai cookie/session Sanctum.
- Frontend memanggil `/sanctum/csrf-cookie`, lalu `/login`.
- API read-only ada di `/api/bootstrap`, `/api/penawarans`, `/api/invoices`, `/api/customers`, dan `/api/mitras`.
