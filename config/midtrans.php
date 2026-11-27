<?php
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-LLJzk45RqhZdTT.3FuEAeTLF');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-2nF5qfW-nClIsoWN');
define('MIDTRANS_IS_PRODUCTION', false);
define('VOTING_HARGA_PER_SUARA', 3500);

function midtrans_snap_js_url(): string
{
    return MIDTRANS_IS_PRODUCTION
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
}

function midtrans_snap_api_url(): string
{
    return MIDTRANS_IS_PRODUCTION
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
}

function midtrans_create_snap_token(array $payload): array
{
    if (
        MIDTRANS_SERVER_KEY === '' ||
        strpos(MIDTRANS_SERVER_KEY, 'GANTI_') !== false ||
        strpos(MIDTRANS_SERVER_KEY, 'xxxx') !== false
    ) {
        return [
            'ok' => false,
            'message' => 'MIDTRANS_SERVER_KEY belum diisi dengan key asli dari dashboard Midtrans.',
        ];
    }

    $ch = curl_init(midtrans_snap_api_url());
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    $rawResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($rawResponse === false || $curlError !== '') {
        return [
            'ok' => false,
            'message' => 'Koneksi cURL ke Midtrans gagal: ' . $curlError,
        ];
    }

    $decoded = json_decode($rawResponse, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'message' => 'Respons Midtrans tidak valid (HTTP ' . $httpCode . ').',
        ];
    }

    if ($httpCode >= 400 || empty($decoded['token'])) {
        $detail = '';
        if (!empty($decoded['error_messages']) && is_array($decoded['error_messages'])) {
            $detail = implode('; ', $decoded['error_messages']);
        } elseif (!empty($decoded['status_message'])) {
            $detail = $decoded['status_message'];
        }
        return [
            'ok' => false,
            'message' => 'Ditolak Midtrans (HTTP ' . $httpCode . ')' . ($detail !== '' ? ': ' . $detail : '.'),
        ];
    }

    return [
        'ok' => true,
        'data' => $decoded,
    ];
}