<?php

namespace App\Enums;

use ArchTech\Enums\Options;

enum Gender: string
{
    use Options;
    case MALE = 'male';
    case FEMALE = 'female';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Laki Laki',
            self::FEMALE => 'Perempuan',
        };
    }
}
