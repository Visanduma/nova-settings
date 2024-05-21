<?php

namespace Visanduma\NovaProfile\Http\Controllers;

use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Panel;
use Laravel\Nova\ResolvesFields;
use Visanduma\NovaProfile\NovaProfile;

class TestController
{
    use ResolvesFields;

    public function index($section = null)
    {

        $request = resolve(NovaRequest::class);

        $sections = NovaProfile::getSections();
        $sections = NovaProfile::keyByUri();

        $activeSection = $sections->get($section) ?? $sections->first();

        $fields = FieldCollection::make($activeSection->fields());

        $curentSettings = $activeSection->getSettings($request);

        $fields = $fields->each(function ($field) use ($activeSection, $curentSettings) {
            $field->panel = $activeSection->label();
            $field->resolve($this->makeFakeResource($field->attribute, $curentSettings[$field->attribute] ?? ''));
        });

        $panel = new Panel($activeSection->label());

        $menu = $sections->map(function ($sec) {
            $object = new $sec;

            return MenuSection::make($object->label())
                ->path('/nova-profile/'.$object->uriKey())
                ->icon($object->icon)
                ->jsonSerialize();
        });

        return inertia('Test', [
            'fields' => $fields->map(fn ($f) => $f->jsonSerialize())->toArray(),
            'panel' => $panel->jsonSerialize(),
            'menus' => $menu->values()->toArray(),
            'section' => $activeSection->uriKey(),
        ]);

    }

    protected function makeFakeResource(string $fieldName, $fieldValue)
    {
        $fakeResource = new \Laravel\Nova\Support\Fluent;
        $fakeResource->{$fieldName} = $fieldValue;

        return $fakeResource;
    }

    public function store($section, NovaRequest $request)
    {
        // nofig('system.address');

        $page = NovaProfile::findSection($section);

        $request->validate($page->getValidationRules($request));

        return $page->store($request);
    }
}
