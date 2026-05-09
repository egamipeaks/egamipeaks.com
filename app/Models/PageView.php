<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageView extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_PAGE_VIEW = 'page_view';

    public const EVENT_TRACK_PLAY = 'track_play';

    protected $guarded = [];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
