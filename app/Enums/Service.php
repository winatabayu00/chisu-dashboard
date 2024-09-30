<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use Illuminate\Support\Str;

enum Service: string
{
    use Options;

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
     * @return array
     */
    public static function allowMonthlyGrouping(): array
    {
        return [
            self::KUNJUNGAN_ANC_6->value,
            self::PERSALINAN_DI_FASILITAS_KESEHATAN->value,
            self::KUNJUNGAN_NIFAS_LENGKAP->value,
            self::IMUNISASI_DASAR_LENGKAP->value,
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP->value,
        ];
    }

    /**
     * @return string|null
     */
    public function tableMaps(): ?string
    {
        return match ($this) {
            self::KUNJUNGAN_ANC_6 => 'dbEkohortAnc', // K6
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
            self::SKRINING_ANEMIA => 'dbEkohortAnc', //soon
            self::KONSUMSI_TABLET_TABAH_DARAH => null,
        };
    }

    /**
     *
     * @param string $code id atau nama kecamatan/puskesmas
     * @param string $type ['kecamatan', 'kelurahan' or 'puskesmas']
     */
    public function subDistricts($district = null, $subdistrict = null, $puskesmas = null) : array
    {
        if (!empty($puskesmas)) {
            // TODO: daftar desa/kelurahan per puskesmas petakan dari array constant
            return [];
        }
        // jika kelurahan maka kembalikan saja array dengan 1 element
        elseif (!empty($district)) {
            if (!empty($subdistrict)) {
                return [$subdistrict];
            }else {
                // TODO: daftar desa/kelurahan per kecamatan petakan dari array constant
                return [];
            }
        }

        return [];
    }

    public function districtColumn(): ?string
    {
        return match ($this) {
            self::KUNJUNGAN_ANC_6 => 'Nama Kecamatan', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'Nama Kecamatan',
            self::KUNJUNGAN_NIFAS_LENGKAP => 'Nama Kecamatan',
            self::IMUNISASI_DASAR_LENGKAP => 'kecamatan',
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => 'kecamatan',
            self::SKRINING_HIPOTIROID_KONGENITAL => null, // Hanya untuk Rumah Sakit
            self::ASI_EKSKLUSIF => 'Kec',// 6 bulan sejak bayi lahir
            self::KUNJUNGAN_NEONATAL_LENGKAP => null,
            self::PEMBERIAN_VITAMIN_A => 'Kec',
            self::LAYANAN_TUMBUH_KEMBANG => null,
            self::IMUNISASI_LANJUTAN_LENGKAP => 'Kecamatan',
            self::SKRINING_KESEHATAN => null, // Hanya untuk Puskesmas
            self::SKRINING_ANEMIA => 'Nama Kecamatan', //soon
            self::KONSUMSI_TABLET_TABAH_DARAH => null,

        };
    }

    public function subDistrictColumn(): ?string
    {
        return match ($this) {
            self::KUNJUNGAN_ANC_6 => 'Nama Desa', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'Nama Desa',
            self::KUNJUNGAN_NIFAS_LENGKAP => 'Nama Desa',
            self::IMUNISASI_DASAR_LENGKAP => 'Kelurahan atau Desa',
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => 'Kelurahan atau Desa',
            self::SKRINING_HIPOTIROID_KONGENITAL => 'NAMA FASYANKES', // Hanya untuk Rumah Sakit
            self::ASI_EKSKLUSIF => 'Desa/Kel',// 6 bulan sejak bayi lahir
            self::KUNJUNGAN_NEONATAL_LENGKAP => null,
            self::PEMBERIAN_VITAMIN_A => 'Desa/Kel',
            self::LAYANAN_TUMBUH_KEMBANG => null,
            self::IMUNISASI_LANJUTAN_LENGKAP => 'Kelurahan atau Desa',
            self::SKRINING_KESEHATAN => 'Puskesmas', // Hanya untuk Puskesmas
            self::SKRINING_ANEMIA => 'Nama Desa', //soon
            self::KONSUMSI_TABLET_TABAH_DARAH => null,

        };
    }

    /**
     * KLASTER 3
     * dbSiptm == skrining ysia produktif
     * */

    public function dateColumn()
    {
        return match ($this) {
            self::KUNJUNGAN_ANC_6 => 'Tanggal Anc', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'Tgl Persalinan',
            self::KUNJUNGAN_NIFAS_LENGKAP => 'Tanggal Pnc',
            self::IMUNISASI_DASAR_LENGKAP => 'waktu',
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => 'waktu',
            self::SKRINING_HIPOTIROID_KONGENITAL => null, //'TANGGAL DAN JAM PENGAMBILAN SPESIMEN',
            self::ASI_EKSKLUSIF => null,// 6 bulan sejak bayi lahir
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
     * @return string|null
     */
    public function namaLembaga(): ?string
    {
        return match ($this) {
            self::KUNJUNGAN_ANC_6 => 'Nama Lembaga', // K6
            self::PERSALINAN_DI_FASILITAS_KESEHATAN => 'Nama Lembaga Kesehatan',
            self::KUNJUNGAN_NEONATAL_LENGKAP => null,
            self::KUNJUNGAN_NIFAS_LENGKAP => 'Nama Lembaga Kesehatan',
            self::SKRINING_HIPOTIROID_KONGENITAL => 'NAMA FASYANKES',
            self::ASI_EKSKLUSIF => 'Pukesmas',
            self::IMUNISASI_DASAR_LENGKAP => null,
            self::IMUNISASI_LANJUTAN_BADUTA_LENGKAP => null,
            self::PEMBERIAN_VITAMIN_A => 'Pukesmas',
            self::LAYANAN_TUMBUH_KEMBANG => null,
            self::IMUNISASI_LANJUTAN_LENGKAP => 'Pukesmas',
            self::SKRINING_KESEHATAN => 'puskesmas',
            self::SKRINING_ANEMIA => 'Nama Lembaga', //soon
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
