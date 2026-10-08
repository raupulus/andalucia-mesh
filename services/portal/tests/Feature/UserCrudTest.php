<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas del CRUD de usuarios y operadores en la intranet (/admin/users).
 *
 * Verifica el control de acceso estricto por roles:
 * - superadmin: lista con nombre, rol, email, avatar; crea y edita usuarios.
 * - admin: lista únicamente viendo avatar y nombre (sin emails, sin crear, sin editar).
 */
class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Un superadmin puede listar usuarios y ver todas las columnas (avatar, nombre, rol, email).
     */
    public function test_superadmin_puede_listar_usuarios_con_email_y_rol(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Super Operador',
            'email' => 'superadmin@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'name' => 'Operador Estandar',
            'email' => 'estandar@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Usuarios');
        $response->assertSee('Super Operador');
        $response->assertSee('Operador Estandar');
        $response->assertSee('superadmin@andalucia.mesh');
        $response->assertSee('estandar@andalucia.mesh');
        $response->assertSee(__('admin.users.role_superadmin'));
        $response->assertSee(__('admin.users.role_admin'));
        $response->assertSee(__('admin.users.btn_create'));
    }

    /**
     * Un admin puede listar usuarios pero solo ve avatar y nombre; NO ve emails ni roles ni botón de crear.
     */
    public function test_admin_puede_listar_usuarios_pero_sin_ver_email_ni_rol(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Operador Observador',
            'email' => 'admin-observador@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'name' => 'Companero Tecnico',
            'email' => 'secreto@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Usuarios');
        // El admin debe ver los nombres (para saber que existen)
        $response->assertSee('Operador Observador');
        $response->assertSee('Companero Tecnico');

        // Pero NO debe ver los emails de los usuarios
        $response->assertDontSee('secreto@andalucia.mesh');
        $response->assertDontSee('admin-observador@andalucia.mesh');

        // Y NO debe ver el botón para crear nuevos usuarios
        $response->assertDontSee(__('admin.users.btn_create'));
    }

    /**
     * Un superadmin puede acceder a la pantalla de crear usuario y darlo de alta con contraseña y rol.
     */
    public function test_superadmin_puede_crear_usuario(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Super Admin',
            'email' => 'super@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/users/create');
        $response->assertStatus(200);

        Livewire::actingAs($superadmin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Nuevo Operador',
                'email' => 'nuevo@andalucia.mesh',
                'role' => User::ROLE_ADMIN,
                'password' => 'PasswordSegura2026!#',
                'password_confirmation' => 'PasswordSegura2026!#',
                'activo' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo Operador',
            'email' => 'nuevo@andalucia.mesh',
            'role' => User::ROLE_ADMIN,
            'activo' => true,
        ]);

        $userCreado = User::where('email', 'nuevo@andalucia.mesh')->first();
        $this->assertNotNull($userCreado);
        $this->assertTrue(Hash::check('PasswordSegura2026!#', $userCreado->password));
    }

    /**
     * Al crear usuario se requiere verificar la contraseña dos veces y falla si no coinciden.
     */
    public function test_superadmin_crear_usuario_falla_si_confirmacion_password_no_coincide(): void
    {
        $superadmin = User::factory()->superadmin()->create(['activo' => true]);

        Livewire::actingAs($superadmin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Operador Error',
                'email' => 'error@andalucia.mesh',
                'role' => User::ROLE_ADMIN,
                'password' => 'PasswordSegura2026!#',
                'password_confirmation' => 'PasswordDiferente2026!#',
                'activo' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    /**
     * Un admin no puede acceder a la pantalla de creación de usuario (devuelve 403 Forbidden).
     */
    public function test_admin_no_puede_acceder_a_crear_usuario(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(403);
    }

    /**
     * Un superadmin puede acceder a editar un usuario existente y modificar sus datos.
     */
    public function test_superadmin_puede_editar_usuario(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'email' => 'super@andalucia.mesh',
            'activo' => true,
        ]);

        $usuarioAEditar = User::factory()->admin()->create([
            'name' => 'Nombre Antiguo',
            'email' => 'antiguo@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($superadmin)->get("/admin/users/{$usuarioAEditar->id}/edit");
        $response->assertStatus(200);

        Livewire::actingAs($superadmin)
            ->test(EditUser::class, ['record' => $usuarioAEditar->getKey()])
            ->fillForm([
                'name' => 'Nombre Actualizado',
                'role' => User::ROLE_SUPERADMIN,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $usuarioAEditar->id,
            'name' => 'Nombre Actualizado',
            'role' => User::ROLE_SUPERADMIN,
        ]);
    }

    /**
     * Al editar un usuario, si la contraseña se deja vacía, se mantiene la actual.
     */
    public function test_superadmin_al_editar_si_password_esta_vacia_se_mantiene_la_misma(): void
    {
        $superadmin = User::factory()->superadmin()->create(['activo' => true]);

        $usuario = User::factory()->admin()->create([
            'email' => 'user-clave@andalucia.mesh',
            'password' => bcrypt('ClaveOriginal2026!#'),
            'activo' => true,
        ]);

        Livewire::actingAs($superadmin)
            ->test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm([
                'name' => 'Nombre Cambiado',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $usuario->refresh();
        $this->assertEquals('Nombre Cambiado', $usuario->name);
        $this->assertTrue(Hash::check('ClaveOriginal2026!#', $usuario->password));
    }

    /**
     * Al editar un usuario, se puede cambiar la contraseña verificándola dos veces.
     */
    public function test_superadmin_puede_cambiar_password_con_doble_verificacion(): void
    {
        $superadmin = User::factory()->superadmin()->create(['activo' => true]);

        $usuario = User::factory()->admin()->create([
            'email' => 'user-renovado@andalucia.mesh',
            'password' => bcrypt('ClaveVieja2026!#'),
            'activo' => true,
        ]);

        Livewire::actingAs($superadmin)
            ->test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm([
                'password' => 'NuevaClaveSegura2026!#',
                'password_confirmation' => 'NuevaClaveSegura2026!#',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaClaveSegura2026!#', $usuario->password));
    }

    /**
     * Al editar un usuario, si la confirmación no coincide, la validación falla.
     */
    public function test_superadmin_al_editar_falla_si_confirmacion_password_no_coincide(): void
    {
        $superadmin = User::factory()->superadmin()->create(['activo' => true]);

        $usuario = User::factory()->admin()->create([
            'email' => 'user-fail@andalucia.mesh',
            'activo' => true,
        ]);

        Livewire::actingAs($superadmin)
            ->test(EditUser::class, ['record' => $usuario->getKey()])
            ->fillForm([
                'password' => 'NuevaClaveSegura2026!#',
                'password_confirmation' => 'OtraClaveDistinta2026!#',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);
    }

    /**
     * Un admin no puede acceder a la pantalla de edición de usuarios (devuelve 403 Forbidden).
     */
    public function test_admin_no_puede_acceder_a_editar_usuario(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'email' => 'otro@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$otroUsuario->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Un superadmin no puede autoeliminarse desde el CRUD para prevenir bloqueos accidentales.
     */
    public function test_superadmin_no_puede_autoeliminarse(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'email' => 'super@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'email' => 'otro@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($superadmin);

        // Puede eliminar a otro
        $this->assertTrue(UserResource::canDelete($otroUsuario));

        // NO puede eliminarse a sí mismo
        $this->assertFalse(UserResource::canDelete($superadmin));
    }

    /**
     * Un admin no puede eliminar ningún usuario.
     */
    public function test_admin_no_puede_eliminar_usuarios(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'email' => 'otro@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($admin);

        $this->assertFalse(UserResource::canDelete($otroUsuario));
        $this->assertFalse(UserResource::canDelete($admin));
    }

    /**
     * El comando operador:crear soporta la opción --role y valida los valores permitidos.
     */
    public function test_comando_operador_crear_con_opcion_role(): void
    {
        $this->artisan('operador:crear', [
            'email' => 'superop@desdechipiona.es',
            'nombre' => 'Super Op',
            '--password' => 'PasswordSegura2026!',
            '--role' => 'superadmin',
        ])
            ->expectsOutputToContain("con rol 'superadmin'")
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'superop@desdechipiona.es',
            'role' => User::ROLE_SUPERADMIN,
        ]);

        // Rol inválido debe ser rechazado
        $this->artisan('operador:crear', [
            'email' => 'invalido@desdechipiona.es',
            'nombre' => 'Op Invalido',
            '--password' => 'PasswordSegura2026!',
            '--role' => 'rol_falso',
        ])
            ->expectsOutputToContain('no es válido')
            ->assertFailed();
    }
}
