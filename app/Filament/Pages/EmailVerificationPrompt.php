<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Filament\Support\Enums\MaxWidth;

class EmailVerificationPrompt extends Page
{
    protected static string $view = 'filament.pages.email-verification-prompt';
    protected static bool $shouldRegisterNavigation = false;

    public function resendVerification(): void
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            $this->redirectPanelHome();
        }

        Auth::user()?->sendEmailVerificationNotification();

        $this->notify('success', 'Tautan verifikasi telah dikirim ulang!');
    }

    protected function redirectPanelHome(): void
    {
        $panel = filament()->getCurrentPanel();
        Redirect::to($panel->getUrl());
    }

    public function getLayout(): string
    {
        return 'layouts.auth';
    }


    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Small;
    }

    public function hasLogo(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Verifikasi Email';
    }

    public function getSubheading(): string
    {
        return 'Silakan verifikasi email kamu sebelum melanjutkan.';
    }
}
