<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum Service: string
{
    case KUNJUNGAN_ANC_6 = 'KUNJUNGAN_ANC_6';
    case PERSALINAN_DI_FASILITAS_KESEHATAN = 'persalinan_di_fasilitas_kesehatan';
    case KUNJUNGAN_NIFAS_LENGKAP = 'kunjungan_nifas_lengkap';
    case KUNJUNGAN_NEONATAL_LENGKAP = 'kunjungan_neonatal_lengkap';
    case SKRINING_HIPOTIROID_KONGENITAL = 'skrining_hipotiroid_kongenial';
    case ASI_EKSKLUSIF = 'asi_ekslusif';
    case IMUNISASI_DASAR_LENGKAP = 'imunisasi_dasar_lengkap';
    case IMUNISASI_LANJUTAN_BADUTA_LENGKAP = 'imunisasi_lanjutan_baduta_lengkap';
    case PEMBERIAN_VITAMIN_A = 'pemberian_vitamin_a';
    case LAYANAN_TUMBUH_KEMBANG = 'layanan_tumbuh_kembang';
    case IMUNISASI_LANJUTAN_LENGKAP = 'imunisasi_lanjutan_lengkap';
    case SKRINING_KESEHATAN = 'skrining_kesehatan';
    case SKRINING_ANEMIA = 'skrining_anemia';
    case KONSUMSI_TABLET_TABAH_DARAH = 'konsumsi_tablet_tabel_darah';

    public function tableMaps()
    {
        return match ($this){
            self::KUNJUNGAN_ANC_6 => 'fbEkohortAnc', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'dbEkohortPersalinan',
            self::KUNJUNGAN_NIFAS_LENGKAP => 'dbEkohortPnc',
            self::KUNJUNGAN_NEONATAL_LENGKAP => null,
            self::SKRINING_HIPOTIROID_KONGENITAL => 'dbShk',
            self::ASI_EKSKLUSIF => 'dbasi',
            self::IMUNISASI_DASAR_LENGKAP => 'idasar',
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => 'baduta',
            self::PEMBERIAN_VITAMIN_A => 'dbvita',
            self::LAYANAN_TUMBUH_KEMBANG => null,
            self::IMUNISASI_LANJUTAN_LENGKAP => 'dbbias',
            self::SKRINING_KESEHATAN => 'fbpd',
            self::SKRINING_ANEMIA => 'fbEkohortAnc', //soon
            self::KONSUMSI_TABLET_TABAH_DARAH => null,
        };
    }

    /**
     * KLASTER 3
     * dbSiptm == skrining ysia produktif
     * */

    public function dateColumn()
    {
        return match ($this){
            self::KUNJUNGAN_ANC_6 => 'Tanggal anc', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'Tgl Persalinan',
            self::KUNJUNGAN_NIFAS_LENGKAP => 'Tanggal Pnc',
            self::SKRINING_HIPOTIROID_KONGENITAL => 'TANGGAL DAN JAM PENGAMBILAN SPESIMEN',
            self::ASI_EKSKLUSIF => 'dbasi',// 6 bulan sejak bayi lahir
            self::IMUNISASI_DASAR_LENGKAP => 'waktu',
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => 'waktu',
            self::KUNJUNGAN_NEONATAL_LENGKAP => null,
            self::PEMBERIAN_VITAMIN_A => null,
            self::LAYANAN_TUMBUH_KEMBANG => null,
            self::IMUNISASI_LANJUTAN_LENGKAP => null,
            self::SKRINING_KESEHATAN => null,
            self::SKRINING_ANEMIA => null, //soon
            self::KONSUMSI_TABLET_TABAH_DARAH => null,

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
