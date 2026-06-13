<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Pages\Auth\Login
{
    protected static string $view = 'filament.pages.auth.login';

    protected static string $layout = 'filament.components.layout.auth-simple';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Zaloguj się';
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
