# Laravel download util.

![Packagist License](https://img.shields.io/packagist/l/think.studio/laravel-download-util?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/laravel-download-util)](https://packagist.org/packages/think.studio/laravel-download-util)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/laravel-download-util)](https://packagist.org/packages/think.studio/laravel-download-util)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/laravel-download-util/?branch=main)

Util to create download archive.

## Installation

Install the package via composer:

```shell
composer require think.studio/laravel-download-util
```

Optionally you can publish the config file with:

```shell
php artisan vendor:publish --provider="LaravelDownloadUtil\ServiceProvider" --tag="config"
```

## Usage

### Archivers

#### ZipArchiver

```php
$storage = Storage::disk('my-disk');

$zipFileName = (new ZipArchiver($storage))
            ->setArchiveCreationCallback(function ($archive, $files,) {
                foreach ($files as $directory => $filesList) {
                    foreach ($filesList as $fileName => $file) {
                        $archive->addFile($file, "{$directory}/".Str::afterLast($fileName, '/'));
                    }
                }
            })->create($files, "app/assets-".Carbon::now()->format('Y-m-d-his'));

if($zipFileName) {
    return $storage->url($zipFileName)''
}
```

### Commands

```php
$schedule->command('download-util:prune-outdated prunable_downloads -S 36000 -E ".zip"')->everyThirtyMinutes();
```


## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/) 
