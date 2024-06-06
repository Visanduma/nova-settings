<?php

namespace Visanduma\NovaSettings;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Symfony\Component\Finder\Finder;
use Visanduma\NovaSettings\Models\NovaSettingsModel;

class NovaSettings extends Tool
{
    private static array $sections = [];

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-settings', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-settings', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make(__('Settings'))
            ->path('/nova-settings')
            ->icon('cog');
    }

    public static function register(array $sections)
    {
        static::$sections = $sections;
    }

    public static function autoRegister()
    {
        $namespace = app()->getNamespace();

        $directory = config('nova-settings.settings_path');

        $resources = [];

        foreach ((new Finder())->in($directory)->files() as $resource) {
            $resource = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($resource->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($resource, NovaSettingsMum::class)
            ) {
                $resources[] = $resource;
            }

            $resources[] = $resource;
        }

        static::register(
            collect($resources)->sort()->all()
        );
    }

    public static function getSections(): array
    {
        return static::$sections;
    }

    public static function keyByUri(): Collection
    {
        return collect(static::$sections)
            ->mapWithKeys(fn ($el) => [(new $el)->uriKey() => (new $el)]);
    }

    public static function findSection($name): NovaSettingsMum
    {
        return static::keyByUri()[$name] ?? null;
    }

    public static function get($key, $default = null)
    {
        $key = str($key)->trim('.');

        if ($key->contains('.')) {
            return Auth::user()->novaSettings()->where('key', $key)->first()?->value ?? $default;
        } else {
            $results = Auth::user()->novaSettings()->where('key', 'LIKE', "$key.%")
                ->get()
                ->map(function ($el) {
                    $el['key'] = str($el->key)->after('.')->toString();

                    return $el;
                })
                ->pluck('value', 'key')
                ->toArray();

            return ! empty($results) ? $results : $default;
        }
    }

    public static function global($key, $default = null)
    {
        $key = str($key)->trim('.');

        if ($key->contains('.')) {
            return NovaSettingsModel::where('key', $key)
                ->whereNull('owner_type')
                ->first()?->value ?? $default;
        } else {
            $results = NovaSettingsModel::where('key', 'LIKE', "$key.%")
                ->whereNull('owner_type')
                ->get()
                ->map(function ($el) {
                    $el['key'] = str($el->key)->after('.')->toString();

                    return $el;
                })
                ->pluck('value', 'key')
                ->toArray();

            return ! empty($results) ? $results : $default;

        }

    }
}
