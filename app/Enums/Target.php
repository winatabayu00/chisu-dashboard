<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum Target: string
{
    use Options;
    case IBU_HAMIL = 'ibu_hamil';
    case IBU_BERSALIN = 'ibu_bersalin';
    case BAYI_BARU_LAHIR = 'bayi_baru_lahir';
    case BAYI_DIBAWAH_6_BULAN = 'bayi_dibawah_6_bulan';
    case BAYI = 'bayi';
    case ANAK_USIA_12_SAMPAI_23_BULAN = 'anak_usia_12_sampai_23_bulan';
    case BALITA = 'balita';
    case ANAK_USIA_SEKOLAH = 'anak_usia_sekolah';
    case REMAJA_PUTRI = 'usia_dewasa';

    /**
     * @return array|Collection
     */
    public function serviceLists(): array|Collection
    {
        return match ($this) {
            self::IBU_HAMIL => [Service::KUNJUNGAN_ANC_6, Service::PERSALINAN_DI_FASILITAS_KESEHATAN],
            self::IBU_BERSALIN => [Service::KUNJUNGAN_NIFAS_LENGKAP],
            self::BAYI_BARU_LAHIR => [Service::KUNJUNGAN_NEONATAL_LENGKAP, Service::SKRINING_HIPOTIROID_KONGENITAL],
            self::BAYI_DIBAWAH_6_BULAN => [Service::ASI_EKSKLUSIF],
            self::BAYI => [Service::IMUNISASI_DASAR_LENGKAP],
            self::ANAK_USIA_12_SAMPAI_23_BULAN => [Service::IMUNISASI_LANJUTAN_BADUTA_LENGKAP],
            self::BALITA => [Service::PEMBERIAN_VITAMIN_A, Service::LAYANAN_TUMBUH_KEMBANG],
            self::ANAK_USIA_SEKOLAH => [Service::IMUNISASI_LANJUTAN_LENGKAP, Service::SKRINING_KESEHATAN],
            self::REMAJA_PUTRI => [Service::SKRINING_ANEMIA, Service::KONSUMSI_TABLET_TABAH_DARAH],
        };
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return ucwords(Str::replace('_', ' ', $this->value));
    }
}
