<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Manifest ruang kerja bertanda tangan Ed25519 milik DS Groups:
 * "<base64url(payload JSON)>.<base64url(tanda tangan atas bagian pertama)>".
 * Aplikasi mobile hanya mengirim password/token ke origin yang tercantum di
 * manifest sah, sehingga server palsu tidak dapat mengaku sebagai ruang kerja.
 */
class WorkspaceManifest
{
    private const B64 = SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

    /** @return array{secret: string, public: string} kunci rahasia base64url dan kunci publik base64 standar */
    public static function generateKeyPair(): array
    {
        $pair = sodium_crypto_sign_keypair();

        return ['secret' => sodium_bin2base64(sodium_crypto_sign_secretkey($pair), self::B64),
            'public' => base64_encode(sodium_crypto_sign_publickey($pair))];
    }

    public static function sign(string $secretKey, string $workspaceId, string $origin, ?int $issuedAt = null): string
    {
        if (! Str::isUuid($workspaceId)) {
            throw new InvalidArgumentException('Workspace ID harus UUID.');
        }

        return self::signPayload($secretKey, ['v' => 1, 'workspace_id' => strtolower($workspaceId),
            'origin' => self::origin($origin), 'iat' => $issuedAt ?? time()]);
    }

    /** Amplop "<base64url(JSON)>.<base64url(tanda tangan)>" untuk manifest dan dokumen trust lain. */
    public static function signPayload(string $secretKey, array $payload): string
    {
        try {
            $secret = sodium_base642bin(trim($secretKey), self::B64);
        } catch (\SodiumException) {
            $secret = '';
        }
        if (strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new InvalidArgumentException('Kunci rahasia tidak valid.');
        }
        $encoded = sodium_bin2base64(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), self::B64);

        return $encoded.'.'.sodium_bin2base64(sodium_crypto_sign_detached($encoded, $secret), self::B64);
    }

    /**
     * Isi amplop tanpa memeriksa tanda tangan (server tidak menyimpan kunci
     * publik); aplikasi mobile yang memverifikasi.
     *
     * @return array<string, mixed>
     */
    public static function decode(string $document): array
    {
        $parts = explode('.', trim($document));
        try {
            $data = count($parts) === 2 ? json_decode(sodium_base642bin($parts[0], self::B64), true) : null;
        } catch (\SodiumException) {
            $data = null;
        }
        if (! is_array($data) || ($data['v'] ?? null) !== 1) {
            throw new InvalidArgumentException('Format dokumen bertanda tangan tidak dikenali.');
        }

        return $data;
    }

    /**
     * Isi manifest tanpa memeriksa tanda tangan; dipakai untuk mencegah salah pasang di server.
     *
     * @return array{v: int, workspace_id: string, origin: string}
     */
    public static function payload(string $manifest): array
    {
        $data = self::decode($manifest);
        if (! is_string($data['workspace_id'] ?? null) || ! is_string($data['origin'] ?? null)) {
            throw new InvalidArgumentException('Format manifest tidak dikenali.');
        }

        return $data;
    }

    /** Origin HTTPS persis: skema, host huruf kecil, port hanya bila bukan 443, tanpa path. */
    public static function origin(string $url): string
    {
        $parts = parse_url(rtrim(trim($url), '/'));
        if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['path']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Origin harus berupa alamat HTTPS utama, tanpa path.');
        }
        $port = isset($parts['port']) && $parts['port'] !== 443 ? ':'.$parts['port'] : '';

        return 'https://'.strtolower($parts['host']).$port;
    }
}
