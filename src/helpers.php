<?php

use Visanduma\NovaProfile\Models\NovaAdvanceSettingsModel;

if (! function_exists('nonfig')) {
    function nonfig($key, $default = null)
    {
        return NovaAdvanceSettingsModel::findByKey($key) ?? $default;
    }
}

if (! function_exists('nonfig_global')) {
    function nonfig_global($key, $default = null)
    {
        $value = NovaAdvanceSettingsModel::getGlobalSettings($key);

        return ! empty($value) ? $value : $default;
    }
}
