<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CrearOperador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operador:crear {email} {nombre} {--password= : Contraseña inicial (mínimo 8 caracteres)} {--role=admin : Rol del operador (superadmin o admin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea o actualiza una cuenta de operador técnico para el panel /admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $nombre = trim((string) $this->argument('nombre'));
        $role = strtolower(trim((string) $this->option('role')));

        if (! in_array($role, [User::ROLE_SUPERADMIN, User::ROLE_ADMIN], true)) {
            $this->error("El rol '{$role}' no es válido. Debe ser 'superadmin' o 'admin'.");

            return Command::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("El correo '{$email}' no tiene un formato válido.");

            return Command::FAILURE;
        }

        $password = (string) $this->option('password');
        if (empty($password)) {
            $password = (string) $this->secret('Introduce la contraseña del operador (mínimo 8 caracteres):');
        }

        if (mb_strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres por motivos de seguridad.');

            return Command::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'role' => $role,
                'password' => Hash::make($password),
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info("Operador '{$user->name}' ({$user->email}) guardado con éxito con rol '{$user->role}'.");
        $this->info('Accede a /admin para iniciar sesión y configurar tu segundo factor de autenticación TOTP.');

        return Command::SUCCESS;
    }
}
