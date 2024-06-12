<?php

namespace Visanduma\NovaSettings;

use Visanduma\NovaSettings\Models\NovaSettingsModel;

trait HasNovaSettings
{
    public function novaSettings()
    {
        return $this->morphMany(NovaSettingsModel::class, 'owner');
    }

    public function getNovaSettings($key)
    {
        return $this->novaSettings()->where('key', $key)->first()?->value;
    }
}
