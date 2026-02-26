<?php

namespace SpyApp\ThemeInverness;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SpyApp\ThemeInverness\Console\InstallCommand;

class ThemeInvernessServiceProvider extends PackageServiceProvider
{
    public static string $name = 'theme-inverness';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasCommand(InstallCommand::class);
    }

    public function packageBooted(): void
    {
        //
    }
}
