<?php
if (!defined('HUTCHMOOT')) die();

/**
 * Upload a file to Cloudflare R2 via the S3-compatible API (AWS Signature V4).
 * Returns the public URL on success, throws RuntimeException on failure.
 */
function r2_upload(string $local_path, string $r2_key, string $content_type): string {
    $account_id = R2_ACCOUNT_ID;
    $access_key = R2_ACCESS_KEY;
    $secret_key = R2_SECRET_KEY;
    $bucket     = R2_BUCKET;
    $host       = "{$account_id}.r2.cloudflarestorage.com";
    $region     = 'auto';

    $body         = file_get_contents($local_path);
    $payload_hash = hash('sha256', $body);
    $datetime     = gmdate('Ymd\THis\Z');
    $date         = substr($datetime, 0, 8);

    $canonical_headers = "content-type:{$content_type}\nhost:{$host}\nx-amz-content-sha256:{$payload_hash}\nx-amz-date:{$datetime}\n";
    $signed_headers    = 'content-type;host;x-amz-content-sha256;x-amz-date';

    $canonical_request = implode("\n", [
        'PUT',
        '/' . rawurlencode($bucket) . '/' . implode('/', array_map('rawurlencode', explode('/', $r2_key))),
        '',
        $canonical_headers,
        $signed_headers,
        $payload_hash,
    ]);

    $credential_scope = "{$date}/{$region}/s3/aws4_request";
    $string_to_sign   = implode("\n", [
        'AWS4-HMAC-SHA256',
        $datetime,
        $credential_scope,
        hash('sha256', $canonical_request),
    ]);

    $signing_key = hash_hmac('sha256', 'aws4_request',
        hash_hmac('sha256', 's3',
            hash_hmac('sha256', $region,
                hash_hmac('sha256', $date, 'AWS4' . $secret_key, true),
            true),
        true),
    true);

    $signature     = hash_hmac('sha256', $string_to_sign, $signing_key);
    $authorization = "AWS4-HMAC-SHA256 Credential={$access_key}/{$credential_scope}, SignedHeaders={$signed_headers}, Signature={$signature}";

    $url = "https://{$host}/{$bucket}/{$r2_key}";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS    => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER    => [
            "Authorization: {$authorization}",
            "Content-Type: {$content_type}",
            "Content-Length: " . strlen($body),
            "x-amz-content-sha256: {$payload_hash}",
            "x-amz-date: {$datetime}",
            "Host: {$host}",
        ],
    ]);

    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err || $code >= 300) {
        throw new RuntimeException("R2 upload failed (HTTP {$code}): {$err} {$response}");
    }

    return rtrim(R2_PUBLIC_URL, '/') . '/' . $r2_key;
}

function r2_content_type(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return match($ext) {
        'mp3'  => 'audio/mpeg',
        'm4a'  => 'audio/mp4',
        'aac'  => 'audio/aac',
        '3gp'  => 'audio/3gpp',
        'amr'  => 'audio/amr',
        'ogg'  => 'audio/ogg',
        'wav'  => 'audio/wav',
        'webm' => 'audio/webm',
        'jpg', 'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        default => 'application/octet-stream',
    };
}
