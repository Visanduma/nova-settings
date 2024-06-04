<?php

use Visanduma\NovaSettings\Models\NovaSettingsModel;

if (! function_exists('nova_settings')) {
    function nova_settings($key, $default = null)
    {
        return NovaSettingsModel::findByKey($key) ?? $default;
    }
}

if (! function_exists('nova_settings_global')) {
    function nova_settings_global($key, $default = null)
    {
        $value = NovaSettingsModel::getGlobalSettings($key);

        return ! empty($value) ? $value : $default;
    }
}
