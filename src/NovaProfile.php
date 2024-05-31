<?php

namespace Visanduma\NovaProfile;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Symfony\Component\Finder\Finder;

class NovaProfile extends Tool
{
    private static array $sections = [];

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-profile', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-profile', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Settings')
            ->path('/nova-profile')
            ->icon('cog');
    }

    public static function register(array $sections)
    {
        static::$sections = $sections;
    }

    public static function autoRegister()
    {
        $namespace = app()->getNamespace();

        $directory = config('nonfig.settings_path');

        $resources = [];

        foreach ((new Finder())->in($directory)->files() as $resource) {
            $resource = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($resource->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($resource, ProfilePage::class)
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

    public static function findSection($name): ProfilePage
    {
        return static::keyByUri()[$name] ?? null;
    }
}
