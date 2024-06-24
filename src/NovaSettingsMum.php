<?php

namespace Visanduma\NovaSettings;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Http\Requests\NovaRequest;
use Visanduma\NovaSettings\Models\NovaSettingsModel;

abstract class NovaSettingsMum
{
    protected bool $global = false;

    public function fields(): array
    {
        return [];
    }

    public function label(): string
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

        return $this->getAllFieldsWithoutPanel()
            ->reduce(function (array $all, $field) use ($request) {

                return [...$all, ...$field->getRules($request)];

            }, []);
    }

    private function getAllFieldsWithoutPanel(): Collection
    {
        return collect($this->fields())
            ->map(function ($field) {
                if ($field->component == 'panel') {
                    return $field->data;
                }

                return $field;
            })->flatten(1);
    }

    public function store(NovaRequest $request): Response
    {

        $data = $this->transformInputs($request->all());

        // store files if exists
        $files = $request->allFiles();
        $filePaths = [];

        foreach ($files as $index => $file) {
            $filePaths[$index] = $this->uploadFile($file, $this->findField($index));
        }

        $data = array_merge($data, $filePaths);

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
            // save settings for model
            return $this->saveModelSettings($data, $request);
        }

        // call after hooks
        $this->afterSaved($request);

        return response('', 422);
    }

    protected function transformInputs(array $inputs): array
    {
        return $inputs;
    }

    /**
     * @return Illuminate\Contracts\Auth\Authenticatable | Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        return Auth::user();
    }

    private function saveModelSettings(Collection $data, NovaRequest $request)
    {
        throw_unless(
            in_array(HasNovaSettings::class, class_uses_recursive($this->getModel())),
            'Model should be use HasNovaSettings trait'
        );

        $data->each(function ($value, $key) {
            $this->getModel()
                ->novaSettings()
                ->updateOrCreate(
                    [
                        'key' => $key,
                    ], [
                        'value' => $value,
                    ]
                );
        });

        return response('', 204);
    }

    private function saveGlobalSettings(Collection $data, NovaRequest $request)
    {
        $data->each(function ($value, $key) {
            NovaSettingsModel::updateOrCreate(
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
            return NovaSettings::global($this->uriKey());
        } else {
            return $this->getModelSettings($this->getModel());
        }
    }

    public function getModelSettings($model)
    {
        throw_unless(
            in_array(HasNovaSettings::class, class_uses_recursive($this->getModel())),
            'Model should be use HasNovaSettings trait'
        );

        return $model->novaSettings()
            ->where('key', 'LIKE', "{$this->uriKey()}.%")
            ->get()
            ->map(function ($el) {
                $key = str($el->key)->after('.')->toString();
                $field = $this->findField($key);

                // set file url value
                if ($field?->component == 'file-field') {
                    // $el['value'] = Storage::disk($field->getStorageDisk())->url($el->value);
                }

                $el['key'] = $key;

                return $el;
            })
            ->pluck('value', 'key')
            ->toArray();
    }

    protected function afterSaved(NovaRequest $request)
    {
        // called after saved the form
    }

    private function uploadFile(UploadedFile $file, File $field)
    {
        return $file->store($field->getStoragePath(), [
            'disk' => $field->getStorageDisk(),
        ]);
    }

    private function findField($name)
    {
        return $this->getAllFieldsWithoutPanel()
            ->where('attribute', $name)
            ->first();
    }
}
