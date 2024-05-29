<?php

namespace Visanduma\NovaProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        $key = str($key)->trim('.');

        if ($key->contains('.')) {
            return self::where('key', $key)
                ->whereNull('owner_type')
                ->first()?->value;
        } else {
            return self::where('key', 'LIKE', "$key.%")
                ->whereNull('owner_type')
                ->get()
                ->map(function ($el) {
                    $el['key'] = str($el->key)->after('.')->toString();

                    return $el;
                })
                ->pluck('value', 'key')
                ->toArray();
        }

    }

    public static function findByKey($key)
    {
        $key = str($key)->trim('.');

        if ($key->contains('.')) {
            return Auth::user()->advanceSettings()->where('key', $key)->first()?->value;
        } else {
            return Auth::user()->advanceSettings()->where('key', 'LIKE', "$key.%")
                ->get()
                ->map(function ($el) {
                    $el['key'] = str($el->key)->after('.')->toString();

                    return $el;
                })
                ->pluck('value', 'key')
                ->toArray();
        }
    }
}
