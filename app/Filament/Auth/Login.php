<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Mengatur struktur form login
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    /**
     * Membuat komponen input Username
     */
    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('Username'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['name' => 'username']);
    }

    /**
     * Memberitahu Laravel untuk mencocokkan kolom 'username' di database
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    /**
     * Menampilkan pesan error jika login gagal
     */
    protected function throwFailureValidationException(): never
    {
        Notification::make()
            ->title('Peringatan Login!')
            ->body('Username atau Password anda salah, Coba Lagi!')
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->persistent()
            ->duration(5000)
            ->send();

        throw ValidationException::withMessages([
            // 'data.username' => 'Credentials mismatch',
        ]);
    }
}