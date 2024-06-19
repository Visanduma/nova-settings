<?php

namespace Visanduma\NovaSettings\Http\Controllers;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Panel;
use Laravel\Nova\ResolvesFields;
use Visanduma\NovaSettings\NovaSettings;

class SettingsController
{
    use ResolvesFields;

    public function index($section = null)
    {

        $sections = NovaSettings::keyByUri();

        if ($sections->isEmpty()) {
            return inertia('Empty');
        }

        if (! $section) {
            return redirect()->to(Nova::url('/nova-settings/'.$sections->first()->uriKey()));
        }

        $activeSection = $sections->get($section) ?? $sections->first();

        $menu = $sections->map(function ($sec) {
            $object = new $sec;

            return MenuSection::make($object->label())
                ->path('/nova-settings/'.$object->uriKey())
                ->icon($object->icon)
                ->jsonSerialize();
        });

        return inertia('NovaSettings', [
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
        $page = NovaSettings::findSection($section);

        $request->validate($page->getValidationRules($request));

        return $page->store($request);
    }

    public function getSectionData($section = null)
    {
        $request = resolve(NovaRequest::class);

        $sections = NovaSettings::keyByUri();

        $activeSection = $sections->get($section) ?? $sections->first();

        $fields = FieldCollection::make($activeSection->fields());

        // fields modifications
        $fields->map(fn ($field) => $this->setFieldHandlers($field));

        $currentSettings = $activeSection->getSettings($request);

        $fieldsWithNoPanel = $fields->filter(fn ($el) => $el->component != 'panel');

        $panels = $fields->filter(fn ($el) => $el->component == 'panel');

        if ($fieldsWithNoPanel->isNotEmpty()) {
            $defaultPanel = new Panel($activeSection->label(), $fieldsWithNoPanel);
            $panels = $panels->prepend($defaultPanel);
        }

        $panels
            ->transform(fn ($panel) => $this->panelJsonSerializeWithFields($panel, $currentSettings))
            ->toArray();

        return response()->json([
            'panels' => $panels,
        ]);
    }

    private function setFieldHandlers(Field|Panel $field)
    {

        if ($field->component() == 'file-field') {
            // set default thumbnail path
            $field->thumbnail(fn ($value, $disk) => Storage::disk($disk)->url($value));
        }

        return $field;
    }

    private function panelJsonSerializeWithFields(Panel $panel, $values)
    {
        return array_merge($panel->jsonSerialize(), [
            'fields' => $this->resolvePanel($panel, $values),
        ]);
    }

    private function resolvePanel(Panel $panel, $values)
    {
        return collect($panel->data)
            ->each(function (Field $field) use ($values) {
                $value = $values[$field->attribute] ?? '';

                if ($field->component == 'date-field') {
                    $value = Date::parse($value);
                }
                $field->resolve($this->makeFakeResource($field->attribute, $value));
            })
            ->toArray();
    }
}
