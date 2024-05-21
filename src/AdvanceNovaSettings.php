<?php

namespace Visanduma\NovaProfile;

use Visanduma\NovaProfile\Models\NovaAdvanceSettingsModel;

trait AdvanceNovaSettings
{
    public function advanceSettings()
    {
        return $this->morphMany(NovaAdvanceSettingsModel::class, 'owner');
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
