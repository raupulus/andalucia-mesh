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
     * Un admin puede listar usuarios viendo nombre y rol pero con privacidad estricta: NO ve emails ni último acceso ni botón crear.
     */
    public function test_admin_puede_listar_usuarios_con_rol_pero_sin_email_ni_ultimo_acceso(): void
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
        $response->assertSee('Operador Observador');
        $response->assertSee('Companero Tecnico');
        $response->assertSee(__('admin.users.role_admin'));

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
     * Un admin no puede acceder a la pantalla de edición de un superadmin (devuelve 403 Forbidden).
     */
    public function test_admin_no_puede_acceder_a_editar_superadmin(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@andalucia.mesh',
            'activo' => true,
        ]);

        $superadmin = User::factory()->superadmin()->create([
            'email' => 'super@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$superadmin->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Un editor no puede acceder a la pantalla de edición de otro usuario (devuelve 403 Forbidden).
     */
    public function test_editor_no_puede_acceder_a_editar_otro_usuario(): void
    {
        $editor = User::factory()->editor()->create([
            'email' => 'editor@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'email' => 'otro@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($editor)->get("/admin/users/{$otroUsuario->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Un admin sí puede acceder a editar otros usuarios (admin o editor).
     */
    public function test_admin_puede_acceder_a_editar_otro_admin(): void
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

        $response->assertStatus(200);
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
     * Un editor puede acceder a editar su propia cuenta en el panel.
     */
    public function test_editor_puede_acceder_a_editar_su_propia_cuenta(): void
    {
        $editor = User::factory()->editor()->create([
            'name' => 'Editor Propio',
            'email' => 'editor-propio@andalucia.mesh',
            'activo' => true,
        ]);

        $response = $this->actingAs($editor)->get("/admin/users/{$editor->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * Un editor al editarse puede cambiar su nombre pero no puede auto-promocionarse ni desactivarse.
     */
    public function test_editor_al_editarse_no_puede_cambiar_su_rol_ni_desactivarse(): void
    {
        $editor = User::factory()->editor()->create([
            'name' => 'Nombre Editor',
            'email' => 'editor@andalucia.mesh',
            'role' => User::ROLE_EDITOR,
            'activo' => true,
        ]);

        Livewire::actingAs($editor)
            ->test(EditUser::class, ['record' => $editor->getKey()])
            ->fillForm([
                'name' => 'Nombre Editor Renovado',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $editor->refresh();
        $this->assertEquals('Nombre Editor Renovado', $editor->name);
        $this->assertEquals(User::ROLE_EDITOR, $editor->role);
        $this->assertTrue((bool) $editor->activo);
    }

    /**
     * Un editor no puede eliminar ningún usuario ni a sí mismo.
     */
    public function test_editor_no_puede_eliminar_usuarios(): void
    {
        $editor = User::factory()->editor()->create([
            'email' => 'editor@andalucia.mesh',
            'activo' => true,
        ]);

        $otroUsuario = User::factory()->admin()->create([
            'email' => 'otro@andalucia.mesh',
            'activo' => true,
        ]);

        $this->actingAs($editor);

        $this->assertFalse(UserResource::canDelete($otroUsuario));
        $this->assertFalse(UserResource::canDelete($editor));
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

    /**
     * Un operador que navega por la intranet registra su último acceso automáticamente
     * y las peticiones sucesivas se amortiguan en una ventana de 5 minutos.
     */
    public function test_actividad_intranet_registra_timestamp_ultimo_acceso_y_amortigua_actualizaciones(): void
    {
        $operador = User::factory()->admin()->create([
            'ultimo_acceso' => null,
            'activo' => true,
        ]);

        $this->assertNull($operador->ultimo_acceso);

        // 1. Primera petición en la intranet: debe registrar el timestamp
        $this->travelTo(now());
        $this->actingAs($operador)->get('/admin');

        $primerAcceso = $operador->fresh()->ultimo_acceso;
        $this->assertNotNull($primerAcceso);

        // 2. Petición 2 minutos después: no debe disparar actualización de base de datos
        $this->travel(2)->minutes();
        $this->actingAs($operador)->get('/admin');
        $this->assertEquals($primerAcceso->toIso8601String(), $operador->fresh()->ultimo_acceso->toIso8601String());

        // 3. Petición 6 minutos después: debe actualizarse el timestamp
        $this->travel(4)->minutes(); // 2 + 4 = 6 min
        $this->actingAs($operador)->get('/admin');
        $segundoAcceso = $operador->fresh()->ultimo_acceso;
        $this->assertTrue($segundoAcceso->greaterThan($primerAcceso));
    }

    /**
     * El superadmin visualiza la columna último acceso en la tabla de usuarios con formato y zona horaria.
     */
    public function test_superadmin_ve_columna_ultimo_acceso_con_formato(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Super Supervisor',
            'email' => 'supervisando@andalucia.mesh',
            'activo' => true,
        ]);

        $fechaPrueba = now()->setTimezone('Europe/Madrid');
        $usuarioActivo = User::factory()->admin()->create([
            'name' => 'Usuario Conectado',
            'email' => 'conectado@andalucia.mesh',
            'activo' => true,
            'ultimo_acceso' => $fechaPrueba,
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee(__('admin.users.col_last_login'));
        $response->assertSee($fechaPrueba->format('d/m/Y H:i'));
    }
}
