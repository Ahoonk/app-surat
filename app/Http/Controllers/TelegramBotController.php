<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\NotaToko;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return response()->json(['ok' => true]);
        }

        $message = $request->input('message') ?? $request->input('edited_message');
        if (! $message) {
            return response()->json(['ok' => true]);
        }

        $chatId = data_get($message, 'chat.id');
        if (! $this->isAllowedChat($chatId)) {
            return response()->json(['ok' => true]);
        }

        $text = trim((string) data_get($message, 'text', ''));
        if ($text === '' || stripos($text, '/nota') !== 0) {
            return response()->json(['ok' => true]);
        }

        $parsed = $this->parseNotaCommand($text);
        if (isset($parsed['error'])) {
            $this->sendMessage($chatId, $parsed['error']);
            return response()->json(['ok' => true]);
        }

        $companyId = (int) config('services.telegram.company_id');
        $userId = (int) config('services.telegram.user_id');
        if ($companyId <= 0 || $userId <= 0) {
            $this->sendMessage($chatId, 'Konfigurasi telegram belum lengkap (COMPANY_ID / USER_ID).');
            return response()->json(['ok' => true]);
        }

        $customer = Customer::where('company_id', $companyId)
            ->where('nama', $parsed['customer'])
            ->first();

        if (! $customer) {
            $this->sendMessage($chatId, 'Customer tidak ditemukan di master data.');
            return response()->json(['ok' => true]);
        }

        if (! $customer->email) {
            $this->sendMessage($chatId, 'Email customer belum diisi di master data.');
            return response()->json(['ok' => true]);
        }

        $items = $parsed['items'];
        $subtotal = 0;
        $itemRows = [];

        foreach ($items as $item) {
            $amount = $item['qty'] * $item['unit_price'];
            $subtotal += $amount;
            $itemRows[] = [
                'nama' => $item['nama'],
                'qty' => $item['qty'],
                'satuan' => $item['satuan'],
                'unit_price' => $item['unit_price'],
                'amount' => $amount,
            ];
        }

        $notaToko = DB::transaction(function () use ($companyId, $userId, $customer, $parsed, $subtotal, $itemRows) {
            $notaToko = NotaToko::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'nomor' => $this->generateNomor($companyId),
                'tanggal' => $parsed['tanggal'] ?? now()->toDateString(),
                'customer_nama' => $customer->nama,
                'customer_email' => $customer->email,
                'alamat' => $customer->alamat,
                'keterangan' => $parsed['keterangan'],
                'subtotal' => $subtotal,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
            ]);

            $notaToko->items()->createMany($itemRows);

            return $notaToko;
        });

        $notaToko->load('items');
        $fileName = 'nota-toko-' . str_replace('/', '-', $notaToko->nomor) . '.pdf';
        $pdf = $this->makeNotaTokoPdf($notaToko);
        $pdfData = $pdf->output();

        $this->sendDocument($chatId, $fileName, $pdfData, 'Nota Toko ' . $notaToko->nomor);

        return response()->json(['ok' => true]);
    }

    private function parseNotaCommand(string $text): array
    {
        $lines = preg_split("/\\r\\n|\\r|\\n/", $text);
        $customer = null;
        $keterangan = null;
        $tanggal = null;
        $items = [];

        foreach ($lines as $index => $rawLine) {
            $line = trim($rawLine);
            if ($line === '' || $index === 0) {
                continue;
            }

            if (stripos($line, 'customer:') === 0) {
                $customer = trim(substr($line, 9));
                continue;
            }

            if (stripos($line, 'keterangan:') === 0) {
                $keterangan = trim(substr($line, 11));
                continue;
            }

            if (stripos($line, 'tanggal:') === 0) {
                $rawTanggal = trim(substr($line, 8));
                try {
                    $tanggal = Carbon::parse($rawTanggal)->toDateString();
                } catch (\Throwable $e) {
                    return ['error' => 'Format tanggal tidak valid. Gunakan: YYYY-MM-DD'];
                }
                continue;
            }

            if (stripos($line, 'item:') === 0) {
                $payload = trim(substr($line, 5));
                $parts = array_map('trim', explode('|', $payload));
                if (count($parts) < 4) {
                    return ['error' => 'Format item salah. Gunakan: item: Nama | Qty | Satuan | Harga'];
                }

                $qty = $this->parseNumber($parts[1]);
                $unitPrice = $this->parseNumber($parts[3]);
                $satuan = strtolower($parts[2]);
                $allowed = ['month', 'pcs', 'item', 'unit'];

                if ($qty <= 0 || $unitPrice < 0 || ! in_array($satuan, $allowed, true)) {
                    return ['error' => 'Data item tidak valid. Satuan harus: month, pcs, item, unit.'];
                }

                $items[] = [
                    'nama' => $parts[0],
                    'qty' => $qty,
                    'satuan' => $satuan,
                    'unit_price' => $unitPrice,
                ];
            }
        }

        if (! $customer) {
            return ['error' => 'Customer belum diisi. Gunakan: customer: Nama Customer'];
        }

        if (empty($items)) {
            return ['error' => 'Minimal harus ada 1 item. Gunakan: item: Nama | Qty | Satuan | Harga'];
        }

        return [
            'customer' => $customer,
            'keterangan' => $keterangan,
            'tanggal' => $tanggal,
            'items' => $items,
        ];
    }

    private function parseNumber(string $value): float
    {
        $clean = preg_replace('/[^0-9,.-]/', '', $value);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    private function generateNomor(int $companyId): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $next = NotaToko::where('company_id', $companyId)
            ->whereYear('tanggal', $year)
            ->count() + 1;

        return sprintf('NT/%s/%s/%04d', $year, $month, $next);
    }

    private function makeNotaTokoPdf(NotaToko $notaToko)
    {
        $pdfWidthPt = 210 * 2.83465;
        $pdfHeightPt = 150 * 2.83465;

        return Pdf::loadView('nota-toko.pdf', compact('notaToko'))
            ->setPaper([0, 0, $pdfWidthPt, $pdfHeightPt], 'portrait');
    }

    private function isAllowedChat(?int $chatId): bool
    {
        if (! $chatId) {
            return false;
        }

        $allowed = config('services.telegram.allowed_chat_ids');
        if (empty($allowed)) {
            return false;
        }

        $ids = array_filter(array_map('trim', explode(',', $allowed)));
        return in_array((string) $chatId, $ids, true);
    }

    private function sendMessage(int $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    private function sendDocument(int $chatId, string $fileName, string $binary, string $caption = ''): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }

        Http::attach('document', $binary, $fileName)
            ->post("https://api.telegram.org/bot{$token}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => $caption,
            ]);
    }
}
