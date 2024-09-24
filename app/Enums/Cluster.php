<?php

namespace App\Enums;

enum Cluster: string
{
    case CLUSTER_2 = 'cluster_2';
    case CLUSTER_3 = 'cluster_3';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::CLUSTER_2 => 'Kluster 2',
            self::CLUSTER_3 => 'Kluster 3',
        };
    }
}
