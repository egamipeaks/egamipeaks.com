<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Visibility: string implements HasLabel
{
    case Draft = 'draft';
    case Public = 'public';
    case Unlisted = 'unlisted';

    public function getLabel(): ?string
    {
        return match ($this) {
            Visibility::Draft => 'Draft',
            Visibility::Public => 'Public',
            Visibility::Unlisted => 'Unlisted',
        };
    }
}
