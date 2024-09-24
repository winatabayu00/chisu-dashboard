<?php

namespace App\Enums;

enum Gender: string
{
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
