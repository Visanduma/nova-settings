<?php

namespace Visanduma\NovaProfile;

use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Visanduma\NovaProfile\Models\NovaAdvanceSettingsModel;

abstract class ProfilePage
{
    protected bool $global = false;

    public function fields()
    {
        return [];
    }

    public function label()
    {
        return str(get_called_class())
            ->afterLast('\\')
            ->headline()
            ->toString();
    }

    public function uriKey(): string
    {
        return str($this->label())->slug()->toString();
    }

    public function getValidationRules(NovaRequest $request): array
    {
        return collect($this->fields())
            ->reduce(function (array $all, $field) use ($request) {

                return [...$all, ...$field->getRules($request)];

            }, []);
    }

    public function store(NovaRequest $request)
    {

        $data = $this->transformInputs($request->all());

        // prepare data with section prefix
        $data = collect($data)->mapWithKeys(function ($value, $key) {
            return [
                $this->uriKey().'.'.$key => $value,
            ];
        });

        if ($this->global) {
            // save global settings
            return $this->saveGlobalSettings($data, $request);
        } else {
            // save settings for current auth user
            return $this->saveUserSettings($data, $request);
        }

        return response('', 422);
    }

    protected function transformInputs(array $inputs): array
    {
        return $inputs;
    }

    protected function saveUserSettings(Collection $data, NovaRequest $request)
    {
        $data->each(function ($value, $key) use ($request) {
            $request->user()->advanceSettings()->updateOrCreate(
                [
                    'key' => $key,
                ], [
                    'value' => $value,
                ]
            );
        });

        return response('', 204);
    }

    protected function saveGlobalSettings(Collection $data, NovaRequest $request)
    {
        $data->each(function ($value, $key) {
            NovaAdvanceSettingsModel::updateOrCreate(
                [
                    'key' => $key,
                ], [
                    'value' => $value,
                ]
            );
        });

        return response('', 204);
    }

    public function getSettings(NovaRequest $request)
    {
        if ($this->global) {
            return NovaAdvanceSettingsModel::getGlobalSettings($this->uriKey());
        } else {
            return $request->user()->getAdvanceSettingsBySection($this->uriKey());
        }
    }
}
