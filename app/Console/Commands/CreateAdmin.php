<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Créer le premier compte administrateur du portfolio';

    public function handle(): int
    {
        if (User::query()->where('is_admin', true)->exists()) {
            $this->error('Un compte administrateur existe déjà.');

            return self::FAILURE;
        }

        $name = trim($this->ask('Nom affiché'));
        $email = Str::lower(trim($this->ask('Adresse e-mail')));
        $username = Str::lower(trim($this->ask('Identifiant administrateur')));
        $password = $this->secret('Mot de passe (12 caractères minimum)');
        $confirmation = $this->secret('Confirmer le mot de passe');
        $strongPassword = Password::min(12)->letters()->mixedCase()->numbers()->symbols();

        if (
            $name === ''
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! preg_match('/^[a-zA-Z0-9_-]{3,40}$/', $username)
            || ! $strongPassword->passes('password', $password)
            || $password !== $confirmation
        ) {
            $this->error('Informations invalides. Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un symbole.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->orWhere('username', $username)->exists()) {
            $this->error('Cette adresse e-mail ou cet identifiant est déjà utilisé.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'is_admin' => true,
        ]);

        $this->info('Compte administrateur créé. Conservez vos identifiants en lieu sûr.');

        return self::SUCCESS;
    }
}
