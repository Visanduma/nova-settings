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

    public function getNovaSettingsBySection($section)
    {
        return $this->novaSettings()
            ->where('key', 'LIKE', "$section.%")
            ->get()
            ->map(function ($el) {
                $el['key'] = str($el->key)->after('.')->toString();

                return $el;
            })
            ->pluck('value', 'key')
            ->toArray();
    }
}
