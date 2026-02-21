<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReleaseType: string implements HasLabel
{
    case Album = 'album';
    case Single = 'single';
    case EP = 'ep';

    public function getLabel(): ?string
    {
        return match ($this) {
            ReleaseType::Album => 'Album',
            ReleaseType::Single => 'Single',
            ReleaseType::EP => 'EP',
        };
    }
}
