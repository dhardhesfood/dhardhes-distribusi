<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WahaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('WAHA RAW', $data);

        // =========================
        // VALIDASI EVENT
        // =========================
        if (($data['event'] ?? null) !== 'message') {
            return response()->json(['status' => 'ignored_event']);
        }

        $payload = $data['payload'] ?? [];
        // =========================
// PARSING PESAN
// =========================
$message = $payload['body'] ?? null;
$hasMedia = $payload['hasMedia'] ?? false;

// handle kalau kirim gambar/video
if (!$message && $hasMedia) {
    $message = '[MEDIA]';
}

// normalize text
$messageLower = strtolower($message ?? '');

// =========================
// FILTER IKLAN
// =========================
$isFromAds = false;

// PRIORITAS 1: kode FB-
if (strpos($messageLower, 'fb-') !== false) {
    $isFromAds = true;
}

// PRIORITAS 2: hallo
elseif (strpos($messageLower, 'hallo') !== false) {
    $isFromAds = true;
}

// =========================
// JIKA BUKAN DARI IKLAN → SKIP
// =========================
if (!$isFromAds) {
    Log::info('CHAT BUKAN IKLAN - SKIP', [
        'message' => $message
    ]);

    return response()->json(['status' => 'not_ads']);
}

        $isFromMe = $payload['fromMe'] ?? true;

        if ($isFromMe) {
            return response()->json(['status' => 'ignored']);
        }

        // =========================
        // AMBIL NOMOR
        // =========================
        $phoneRaw = $payload['from'] ?? null;

        if (!$phoneRaw) {
            Log::warning('NO PHONE - SKIPPED', $data);
            return response()->json(['status' => 'no_phone']);
        }

        // bersihin nomor WAHA
        $phone = explode('@', $phoneRaw)[0];
        $phone = preg_replace('/\D/', '', $phone);

        $today = now()->toDateString();

        // =========================
        // CEK DUPLIKAT
        // =========================
        $exists = DB::table('waha_chat_logs')
            ->where('phone', $phone)
            ->whereDate('report_date', $today)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'duplicate']);
        }

        // =========================
        // SIMPAN LOG
        // =========================
        DB::table('waha_chat_logs')->insert([
            'phone' => $phone,
            'report_date' => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =========================
        // UPDATE ADS
        // =========================
        $ads = DB::table('ads_reports')
            ->whereDate('report_date', $today)
            ->first();

        if ($ads) {
            DB::table('ads_reports')
                ->whereDate('report_date', $today)
                ->increment('real_chat');

            Log::info('REAL CHAT +1 (UPDATE)', ['phone' => $phone]);
        } else {
            DB::table('ads_reports')->insert([
                'report_date' => $today,
                'budget' => 0,
                'tayangan_konten' => 0,
                'klik_tautan' => 0,
                'hasil' => 0,
                'real_chat' => 1,
                'closing' => 0,
                'platform' => 'auto',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('REAL CHAT +1 (INSERT)', ['phone' => $phone]);
        }

        return response()->json([
            'status' => 'counted'
        ]);
    }
}