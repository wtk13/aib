<?php

namespace App\Filament\Pages\Auth;

use Illuminate\Contracts\Support\Htmlable;

class Login extends \Filament\Pages\Auth\Login
{
    protected static string $view = 'filament.pages.auth.login';

    protected static string $layout = 'filament.components.layout.auth-simple';

    public function getHeading(): string|Htmlable
    {
        return __('auth.login.heading');
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
