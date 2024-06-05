<?php

use Visanduma\NovaSettings\NovaSettings;

if (! function_exists('nova_settings')) {
    function nova_settings($key, $default = null)
    {
        return NovaSettings::get($key, $default);
    }
}

if (! function_exists('nova_settings_global')) {
    function nova_settings_global($key, $default = null)
    {
        $value = NovaSettings::global($key, $default);

        return ! empty($value) ? $value : $default;
    }
}
