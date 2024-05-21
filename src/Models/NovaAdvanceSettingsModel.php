<?php

namespace Visanduma\NovaProfile\Models;

use Illuminate\Database\Eloquent\Model;

class NovaAdvanceSettingsModel extends Model
{
    protected $table = 'advance_nova_settings';

    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo('owner');
    }

    public static function getGlobalSettings($key)
    {
        return self::where('key', 'LIKE', "$key.%")
            ->get()
            ->map(function ($el) {
                $el['key'] = str($el->key)->after('.')->toString();

                return $el;
            })
            ->pluck('value', 'key')
            ->toArray();
    }
}
