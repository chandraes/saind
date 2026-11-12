<?php

namespace App\Console\Commands;

use App\Support\RevocationList;
use Illuminate\Console\Command;
use InvalidArgumentException;

/** Tanda tangani daftar pencabutan ruang kerja (di komputer pemegang kunci utama). */
class MobileRevocationList extends Command
{
    protected $signature = 'mobile:revocation-list {source : file JSON sumber daftar} {--key= : path file kunci utama}';

    protected $description = 'Tanda tangani daftar pencabutan ruang kerja mobile dengan kunci utama DS Groups';

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        $key = (string) $this->option('key');
        try {
            if (! is_readable($source) || $key === '' || ! is_readable($key)) {
                throw new InvalidArgumentException('Isi file sumber dan --key yang dapat dibaca.');
            }
            $data = json_decode((string) file_get_contents($source), true);
            $this->line(RevocationList::sign((string) file_get_contents($key), is_array($data) ? $data : []));
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
