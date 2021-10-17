<p align="center">
<a href="https://github.com/hwavina/hwa-meta" target="_blank">
<img src="https://hwavina.me/wp-content/uploads/2021/09/logo.png" height="148">
</a>
</p>


<p align="center">
<a href="https://packagist.org/packages/hwavina/hwa-meta"><img src="https://img.shields.io/packagist/dt/hwavina/hwa-meta" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/hwavina/hwa-meta"><img src="https://img.shields.io/packagist/v/hwavina/hwa-meta" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/hwavina/hwa-meta"><img src="https://img.shields.io/packagist/l/hwavina/hwa-meta" alt="License"></a>
</p>

## About

[hwa-meta](https://github.com/hwavina/hwa-meta) is a meta package. It helps us to build and develop faster with pre-built functions. This saves a lot of time on future projects.

We share this package to give programmers an extra useful library. We hope people use this package not for commercialization or profit of any kind.

And finally, hope to receive more contributions and shares from all of you.

Thank you very much 🧡

## Install

1. You can install the package via composer:

```shell
composer require hwavina/hwa-meta
```

2. Optional: The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file:

```php
'providers' => array(
    // ...
    Hwavina\HwaMeta\HwaMetaServiceProvider::class,
);
```

3. You can customize the [`config/hwa_meta.php` config file](https://github.com/hwavina/hwa-meta/blob/main/config/hwa_meta.php) . If you customize file `config/hwa_meta.php`. You need to run the command below to clear cache and update the changes.

```shell script
php artisan config:cache

# or

php artisan optimize:clear
```

### Default config file contents

You can view the default config file contents at:

[https://github.com/hwavina/hwa-meta/blob/main/config/hwa_meta.php](https://github.com/hwavina/hwa-meta/blob/main/config/hwa_meta.php)

## Usage

### MetaTools

This tool helps you to easily manage the extended data fields of certain objects.

1. Create Meta Class with command.

Example: We need CustomerMeta. This tools help us make model file and migration file.

```shell script
php artisan hwa:make:meta Customer -m
```

The above command we have added -m to create the migration file.

2. Add allow type to `config/hwa_meta.php`

```php
'allow_type' => [
    .....
    'customer' => ['id', 'customer_id'],
],
```
3. You need to run the command below to clear cache and update the changes.

```shell script
php artisan config:cache

# or

php artisan optimize:clear
```

4. Run command to migrate the meta migration file to database

```shell script
php artisan migrate
```

After completing the above configuration steps you can use the methods available in the meta model in the classes you want.

```php
// Example
CustomerMeta:_update(1, 'gender', 'male'); // update or add new gender for customer has id is 1
```

Beside the functions and methods we have built, people can also build their own by inheriting our classes.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security

If you discover a security vulnerability within [hwa-meta](https://github.com/hwavina/hwa-meta), please send an e-mail to [Phi Hoang](https://github.com/hwavina) via [hwavina@gmail.com](mailto:hwavina@gmail.com). All security vulnerabilities will be promptly addressed.

## Credits

- [Phi Hoang](https://github.com/hoangphidev)
- [Hwavina Inc.](https://github.com/hwavina)
- [All Contributors](../../contributors)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
