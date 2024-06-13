### Nova Settings

A settings management UI for Laravel Nova ™ dashboard using **Native fields**

### Install

`composer require visanduma/nova-settings`

### Configuration

publish config & migrations

```
php artisan vendor:publish --tag=nova-settings
```

run migration

```
php artisan migrate
```

update config file (optional)

```php

<?php

return [
     /* default path for load settings class */
    'settings_path' => app_path('Nova/Settings'),
];

```

Use trait in Model. generally in `User.php`

```php
use Visanduma\NovaSettings\HasNovaSettings;

class User extends Authenticatable
{
    use HasNovaSettings;

}

```

### Create your first setting class

You can use `nova-settings:create` command to build fresh settings class

```

php artisan nova-settings:create Contact

```

add fields like in Nova Resource

```php
class Contact extends NovaSettingsMum

{

    public $icon = 'phone';

    public function fields()
    
    {

        return [

            Text::make('Name')->rules('required'),

            Text::make('Address')->rules('required'),

            Text::make('City')->rules('required'),

            Select::make('Province')->options([
                'NC' => 'North Central',
                'N' => 'Northern',
            ]),

            Country::make('Country')->rules('required'),

            Panel::make('Home', [

                Text::make('Contact name'),

                Text::make('Phone'),

            ])->help('Update you home contacts'),

        ];

    }
}

```

### Registering settings

All settings class in default path is automatically registered. if you are going to use different path please configure it on `nova-settings.php`

If you want to register settings class manually use `NovaSettings::register` method in service provider

```php
namespace App\Nova\Settings;

public function boot()

{
        NovaSettings::register([
            Contact::class,
        ]);
}
```

### Customizing settings section

You can customize settings class as per our needs

customizing settings menu icon. you can use any [Heroicons](https://v1.heroicons.com/)

```php
public $icon = 'bell';
```

customizing section label

```php
 public function label():string
 
 {

    return 'User contacts';

 }
```

customizing uriKey. ```uriKey``` is used when saving/retrieving the settings

```php
 public function uriKey(): string
 
 {
    return 'user-contacts';
 }
```

### User settings vs Global settings

There are two type of settings. **User settings** & **Global Settings**.
User settings is always bind to an entity (Generally for User Model) while global settings does not bind to any entity

you can easily configure the settings type with ```global``` property in settings class

```php
    protected bool $global = false;
```

### Retrieving the settings

retrieve user settings

```php
use Visanduma\NovaSettings\NovaSettings;

// getting single value
NovaSettings::get('contact.name', 'default value');
nova_settings('contact.name','default value');

// getting whole settings array
NovaSettings::get('contact');
nova_settings('contact');


// getting global settings
NovaSettings::global('system.email');
NovaSettings::global('system');
nova_settings_global('system.phone'),
```

### Transforming inputs

If you need to customize user submitted form data, override following method in settings class. this method will receive form data array as it's argument

```php
protected function transformInputs(array $inputs): array
    
{
    // do your modifications

    return $inputs;
}
```


### Hooks

```php
protected function afterSaved(NovaRequest $request)

{
        // called after saved the form
}
```

### Known issues

- Uploaded files cannot be delete
- Not works well with 3rd party fields

### Todo

- [ ] Authorization
- [ ] Caching
- [ ] Events


## Credits

-   [Visanduma](https://github.com/Visanduma)
-   [LaHiRu](https://github.com/lahirulhr)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


