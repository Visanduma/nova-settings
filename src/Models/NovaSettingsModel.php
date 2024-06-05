<?php

namespace Visanduma\NovaSettings\Models;

use Illuminate\Database\Eloquent\Model;

class NovaSettingsModel extends Model
{
    protected $table = 'advance_nova_settings';

    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo('owner');
    }
}
