<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Daftar pencabutan ruang kerja mobile. Isi daftar ditandatangani kunci utama
 * DS Groups (offline); kesegarannya diperpanjang otomatis oleh "timestamp"
 * bertanda tangan kunci perpanjangan di repo trust (GitHub Actions).
 */
class RevocationList
{
    /**
     * Sumber JSON milik DS Groups: {"seq": 2, "revoked": [{"workspace_id": "...", "origin": "https://...", "issued_before": 1790000000, "reason": "..."}]}.
     * Setiap entri wajib punya workspace_id atau origin; semua field yang diisi harus cocok.
     */
    public static function sign(string $secretKey, array $source, ?int $issuedAt = null): string
    {
        $seq = $source['seq'] ?? null;
        if (! is_int($seq) || $seq < 1 || ! is_array($source['revoked'] ?? null) || ! array_is_list($source['revoked'])) {
            throw new InvalidArgumentException('Sumber daftar wajib berisi "seq" (bilangan bulat ≥ 1) dan "revoked" (array).');
        }
        $revoked = array_map(self::entry(...), $source['revoked']);

        return WorkspaceManifest::signPayload($secretKey, ['v' => 1, 'type' => 'revocations', 'seq' => $seq,
            'issued_at' => $issuedAt ?? time(), 'revoked' => $revoked]);
    }

    /** @return array<string, mixed> */
    private static function entry(mixed $entry): array
    {
        if (! is_array($entry) || array_diff(array_keys($entry), ['workspace_id', 'origin', 'issued_before', 'reason']) !== []) {
            throw new InvalidArgumentException('Entri hanya boleh berisi workspace_id, origin, issued_before, reason.');
        }
        $clean = [];
        if (isset($entry['workspace_id'])) {
            if (! is_string($entry['workspace_id']) || ! Str::isUuid($entry['workspace_id'])) {
                throw new InvalidArgumentException('workspace_id harus UUID.');
            }
            $clean['workspace_id'] = strtolower($entry['workspace_id']);
        }
        if (isset($entry['origin'])) {
            $clean['origin'] = WorkspaceManifest::origin((string) $entry['origin']);
        }
        if ($clean === []) {
            throw new InvalidArgumentException('Setiap entri wajib punya workspace_id atau origin.');
        }
        if (isset($entry['issued_before'])) {
            if (! is_int($entry['issued_before']) || $entry['issued_before'] < 1) {
                throw new InvalidArgumentException('issued_before harus Unix timestamp (detik).');
            }
            $clean['issued_before'] = $entry['issued_before'];
        }
        if (isset($entry['reason'])) {
            $clean['reason'] = mb_substr((string) $entry['reason'], 0, 200);
        }

        return $clean;
    }

    /**
     * Bundle {revocations, timestamp} dari repo trust; diperiksa bentuk dan
     * pasangannya (hash & seq), tanda tangan diverifikasi aplikasi.
     *
     * @return array{revocations: string, timestamp: string, seq: int}
     */
    public static function bundle(mixed $data): array
    {
        if (! is_array($data) || ! is_string($data['revocations'] ?? null) || ! is_string($data['timestamp'] ?? null)
            || strlen($data['revocations']) > 65536 || strlen($data['timestamp']) > 2048) {
            throw new InvalidArgumentException('Bundle trust tidak valid.');
        }
        $list = WorkspaceManifest::decode($data['revocations']);
        $stamp = WorkspaceManifest::decode($data['timestamp']);
        if (($list['type'] ?? null) !== 'revocations' || ($stamp['type'] ?? null) !== 'timestamp' || ! is_int($list['seq'] ?? null)
            || ($stamp['seq'] ?? null) !== $list['seq'] || ($stamp['sha256'] ?? null) !== hash('sha256', $data['revocations'])) {
            throw new InvalidArgumentException('Daftar pencabutan dan timestamp tidak berpasangan.');
        }

        return ['revocations' => $data['revocations'], 'timestamp' => $data['timestamp'], 'seq' => $list['seq']];
    }
}
