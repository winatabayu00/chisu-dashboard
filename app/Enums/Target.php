<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum Target: string
{
    case IBU_HAMIL = 'ibu_hamil';
    case IBU_BERSALIN = 'ibu_bersalin';
    case BAYI_BARU_LAHIR = 'bayi_baru_lahir';
    case BAYI = 'bayi';
    case BALITA = 'balita';
    case ANAK_PRASEKOLAH = 'anak_prasekolah';
    case ANAK_USIA_SEKOLAH = 'anak_usia_sekolah';
    case REMAJA = 'remaja';
    case USIA_DEWASA = 'usia_dewasa';
    case LANSIA = 'lansia';

    /**
     * @return string
     */
    public function label(): string
    {
        return ucwords(Str::replace('_', ' ', $this->value));
    }
}
