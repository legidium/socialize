# Laravel 4.2 Socialize

**Table of contents**

- [About](#about)
- [Currently supported providers](#currently-supported-providers)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Contribution](#contribution)

## About

The package helps you get user data from social networks and use it in your Laravel application.

## Currently supported providers:

- Facebook
- Google
- Vkontakte

## Installation

Begin the installation by editing your `composer.json` file. Add this to the required section for the latest version.

```php
"require": {
	...
    "legidium/socialize": "dev-master"
}
```

Next, update Composer from the Terminal:

```
composer update
```

Once this operation completes, it is time to add the service provider. Open `config/app.php`, add a new item to the `providers` array.

```
'Legidium\Socialize\SocializeServiceProvider'
```

You may also want to register the Socialize facade in the `aliases` array in this file.

```
Socialize' => 'Legidium\Socialize\Facades\Socialize'
```

That`s it! The installation is finished.

## Configuration

Before using the Socialize package, you need to configure it.

Publish the package config file:

```
php artisan config:publish legidium/socialize
```

Now, open the package config directory (by default `app/config/packages/legidium/socialize`) and edit the `providers` file.

### Primary options

Params, applied to every provider.

- **enabled** - Enable or disable the provider.
- **id** - Set the provider client id.
- **secret** - Set the provider client secret key.
- **scopes** - Specify the scopes for request.
- **additional** - **(array)** Specify additional query params for the request.

### Additional options

Params, applied only to some providers.

- **version** - Api version to be used. [Facebook]

## Usage

Simple example of usage with Laravel 4.2. The `routes`file was used in the example below.

```php
Route::get('socialize/{provider}', function ($provider)
{
	// Get the provider instance
	$provider = Socialize::with($provider);

	// Check, if the user authorised previously.
	// If so, get the User instance with all data, 
	// else redirect to the provider auth screen.
	if (Input::has('code'))
	{
		$user = $provider->user();

		return var_dump($user);
	}
	else
	{
		return $provider->redirect();
	}
});
```

## Contribution
Feel free to contribute to the project. Create new issues, pull requests.

Don't be shy!
