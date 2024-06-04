<?php

namespace Visanduma\NovaSettings;

use Visanduma\NovaSettings\Models\NovaSettingsModel;

trait HasNovaSettings
{
    public function advanceSettings()
    {
        return $this->morphMany(NovaSettingsModel::class, 'owner');
    }

    public function getAdvanceSettings($key)
    {
        return $this->advanceSettings()->where('key', $key)->first()?->value;
    }

    public function getAdvanceSettingsBySection($section)
    {
        return $this->advanceSettings()
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
