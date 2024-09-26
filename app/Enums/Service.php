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

    /**
     * @return string
     */
    public function label(): string
    {
        return ucwords(Str::replace('_', ' ', $this->value));
    }
}
