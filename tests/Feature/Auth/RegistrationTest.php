<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD_TOO_SHORT = 'La contraseña debe tener al menos 8 caracteres.';

    private const EMAIL_TAKEN = 'Ese email ya está registrado.';

    private const PASSWORDS_DIFFER = 'Las contraseñas no coinciden.';

    private const NAME_REQUIRED = 'El nombre es obligatorio.';

    private const EMAIL_INVALID = 'Introduce un email válido.';

    private const AGE_INVALID = 'La edad debe ser un número entero entre 1 y 120.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());

        // Se prueba el servidor (props de Inertia), no el front compilado.
        $this->withoutVite();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function register(array $overrides = []): TestResponse
    {
        return $this->post(route('register.store'), array_merge([
            'name' => 'Test User',
            'age' => 30,
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function validAges(): array
    {
        return [
            'el minimo, 1' => [1],
            'una edad normal' => [30],
            'el maximo, 120' => [120],
            'como texto, como llega del formulario' => ['45'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidAges(): array
    {
        return [
            'vacia' => [''],
            'nula' => [null],
            'cero' => [0],
            'por encima del maximo' => [121],
            'negativa' => [-5],
            'con decimales' => ['1.5'],
            'texto' => ['abc'],
            'numero seguido de texto' => ['12abc'],
        ];
    }

    // Criterio 1: el formulario pide nombre, edad, email y contrasena.
    public function test_registration_screen_can_be_rendered()
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/Register'));
    }

    public function test_the_form_asks_for_name_age_email_and_password_in_that_order()
    {
        $form = file_get_contents(resource_path('js/pages/auth/Register.vue'));

        $positions = array_map(
            fn (string $field) => strpos($form, 'name="'.$field.'"'),
            ['name', 'age', 'email', 'password', 'password_confirmation'],
        );

        foreach ($positions as $field => $position) {
            $this->assertNotFalse($position, "Falta el campo #{$field} en Register.vue");
        }

        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'Los campos no estan en el orden nombre, edad, email, contraseña, confirmacion.');

        // La confirmacion de contraseña se mantiene (decision de la Story).
        $this->assertStringContainsString('name="password_confirmation"', $form);
    }

    public function test_the_age_field_is_a_whole_number_between_1_and_120()
    {
        $form = file_get_contents(resource_path('js/pages/auth/Register.vue'));

        $this->assertMatchesRegularExpression('/type="number"[^>]*name="age"|name="age"[^>]*type="number"/s', $form);
        $this->assertStringContainsString('min="1"', $form);
        $this->assertStringContainsString('max="120"', $form);
        $this->assertStringContainsString('step="1"', $form);
    }

    public function test_the_form_does_not_let_the_browser_replace_the_exact_messages()
    {
        $form = file_get_contents(resource_path('js/pages/auth/Register.vue'));

        $this->assertStringContainsString('novalidate', $form);
    }

    public function test_the_form_label_for_age_is_in_the_same_language_as_the_others()
    {
        $form = file_get_contents(resource_path('js/pages/auth/Register.vue'));

        $this->assertStringContainsString('<Label for="age">Age</Label>', $form);
        $this->assertStringContainsString('<Label for="name">Name</Label>', $form);
    }

    public function test_the_registration_screen_asks_only_for_a_minimum_of_eight_characters()
    {
        $this->get(route('register'))->assertInertia(fn (Assert $page) => $page
            ->where('passwordRules', 'minlength: 8;'));
    }

    // Criterio 6: registro correcto => "Cuenta creada" y redireccion al login.
    public function test_a_valid_registration_creates_the_account_with_its_age()
    {
        $this->register(['name' => 'Ana Pérez', 'age' => 41, 'email' => 'Ana@Example.COM'])
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertSame('Ana Pérez', $user->name);
        $this->assertSame(41, $user->age);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertSame(1, User::count());
    }

    public function test_a_valid_registration_redirects_to_login_with_account_created()
    {
        $this->register()->assertRedirect(route('login'));

        $this->get(route('login'))->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->where('status', 'Cuenta creada'));
    }

    // Criterio 11: tras registrarse no queda con la sesion iniciada.
    public function test_registering_does_not_log_the_person_in()
    {
        $this->register();

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // Criterio 13: el registro no envia ningun correo de verificacion.
    public function test_registering_sends_no_verification_email()
    {
        Notification::fake();

        $this->register()->assertRedirect(route('login'));

        Notification::assertNothingSent();
    }

    // Criterio 2 y 10: la edad es un entero entre 1 y 120.
    #[DataProvider('validAges')]
    public function test_a_valid_age_is_accepted(mixed $age)
    {
        $this->register(['age' => $age])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertSame((int) $age, User::firstOrFail()->age);
    }

    #[DataProvider('invalidAges')]
    public function test_an_invalid_age_shows_the_exact_message_and_creates_no_account(mixed $age)
    {
        $this->register(['age' => $age])
            ->assertSessionHasErrors(['age' => self::AGE_INVALID]);

        $this->assertSame(0, User::count());
    }

    public function test_a_missing_age_shows_the_exact_message_and_creates_no_account()
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['age' => self::AGE_INVALID]);

        $this->assertSame(0, User::count());
    }

    // Criterio 3: minimo 8 caracteres y ninguna otra regla.
    public function test_a_password_of_exactly_eight_characters_is_accepted_with_no_other_rule()
    {
        // Sin mayusculas, numeros ni simbolos.
        $this->register(['password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertSame(1, User::count());
    }

    public function test_the_eight_characters_are_counted_as_characters_not_bytes()
    {
        $this->register(['password' => 'ñañañaña', 'password_confirmation' => 'ñañañaña'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, User::count());
    }

    public function test_a_long_simple_password_is_accepted()
    {
        $this->register(['password' => 'aaaaaaaaaaaaaaaaaaaa', 'password_confirmation' => 'aaaaaaaaaaaaaaaaaaaa'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, User::count());
    }

    // Criterio 4: con menos de 8 caracteres se muestra el mensaje exacto.
    public function test_a_password_shorter_than_eight_characters_shows_the_exact_message()
    {
        $this->register(['password' => 'abcdefg', 'password_confirmation' => 'abcdefg'])
            ->assertSessionHasErrors(['password' => self::PASSWORD_TOO_SHORT]);

        $this->assertSame(0, User::count());
    }

    public function test_seven_multibyte_characters_are_still_too_short()
    {
        $this->register(['password' => 'ñañañañ', 'password_confirmation' => 'ñañañañ'])
            ->assertSessionHasErrors(['password' => self::PASSWORD_TOO_SHORT]);
    }

    public function test_an_empty_password_shows_the_same_message()
    {
        $this->register(['password' => '', 'password_confirmation' => ''])
            ->assertSessionHasErrors(['password' => self::PASSWORD_TOO_SHORT]);

        $this->assertSame(0, User::count());
    }

    // Criterio 7: contrasenas distintas.
    public function test_different_passwords_show_the_exact_message()
    {
        $this->register(['password' => 'password', 'password_confirmation' => 'otra-clave'])
            ->assertSessionHasErrors(['password' => self::PASSWORDS_DIFFER]);

        $this->assertSame(0, User::count());
    }

    // Criterio 8: nombre vacio.
    public function test_an_empty_name_shows_the_exact_message()
    {
        $this->register(['name' => ''])
            ->assertSessionHasErrors(['name' => self::NAME_REQUIRED]);

        $this->assertSame(0, User::count());
    }

    // Criterio 9: email no valido.
    public function test_an_invalid_email_shows_the_exact_message()
    {
        $this->register(['email' => 'no-es-un-email'])
            ->assertSessionHasErrors(['email' => self::EMAIL_INVALID]);

        $this->assertSame(0, User::count());
    }

    public function test_an_empty_email_shows_the_same_message()
    {
        $this->register(['email' => ''])
            ->assertSessionHasErrors(['email' => self::EMAIL_INVALID]);

        $this->assertSame(0, User::count());
    }

    // Criterio 5: email ya registrado.
    public function test_an_email_that_is_already_registered_is_rejected_with_the_exact_message()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->register(['email' => 'test@example.com'])
            ->assertSessionHasErrors(['email' => self::EMAIL_TAKEN]);

        $this->assertSame(1, User::count());
    }

    public function test_the_registered_email_check_ignores_upper_and_lower_case()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->register(['email' => 'TEST@EXAMPLE.COM'])
            ->assertSessionHasErrors(['email' => self::EMAIL_TAKEN]);

        $this->assertSame(1, User::count());
    }
}
