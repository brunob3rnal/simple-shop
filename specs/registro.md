# Registro de usuarios

| Campo        | Valor            |
| ------------ | ---------------- |
| Jira         | BRAVOBRAVO-11    |
| Epic         | Login y registro |
| Sprint       | Sprint 1         |
| Story points | 3                |
| Estado       | Por hacer        |

## Descripción

Permitir que una persona cree una cuenta para poder comprar.

## Tecnología

- Laravel + Vue (Inertia)
- Parte del registro que ya trae el starter kit (Laravel Fortify), adaptado a esta Story.

## Depende de

- Iniciar sesión (BRAVOBRAVO-12): al registrarse se lleva a la pantalla de iniciar sesión.

Lo usa: Checkout con Stripe (BRAVOBRAVO-15), donde para pagar hay que estar autenticado.

## Criterios de aceptación

Cada criterio debe poder comprobarse con sí o no, y tener al menos un test.

1. El formulario pide nombre, edad, email y contraseña.
2. La edad es un número entero entre 1 y 120.
3. La contraseña debe tener mínimo 8 caracteres (8 o más). No se exige ninguna otra regla.
4. Con menos de 8 caracteres se muestra: `La contraseña debe tener al menos 8 caracteres.`
5. Si el email ya está registrado se rechaza el registro con el mensaje: `Ese email ya está registrado.`
6. Si el registro es correcto se muestra `Cuenta creada` y se redirige a la pantalla de iniciar sesión.
7. Si las contraseñas no coinciden se muestra: `Las contraseñas no coinciden.`
8. Si el nombre está vacío se muestra: `El nombre es obligatorio.`
9. Si el email no es válido se muestra: `Introduce un email válido.`
10. Si la edad no es válida se muestra: `La edad debe ser un número entero entre 1 y 120.`
11. Tras registrarse la persona no queda con la sesión iniciada: tiene que iniciar sesión ella misma.
12. Si el visitante tenía productos en el carrito, se conservan al registrarse y al iniciar sesión.
13. En el Sprint 1 no se verifica el email: ninguna ruta pide el email verificado y el registro no envía ningún correo de verificación.

## Mensajes exactos

| Situación                  | Texto literal                                      |
| -------------------------- | -------------------------------------------------- |
| Contraseña demasiado corta | `La contraseña debe tener al menos 8 caracteres.`  |
| Email ya registrado        | `Ese email ya está registrado.`                    |
| Registro correcto          | `Cuenta creada`                                    |
| Contraseñas distintas      | `Las contraseñas no coinciden.`                    |
| Nombre vacío               | `El nombre es obligatorio.`                        |
| Email no válido            | `Introduce un email válido.`                       |
| Edad no válida             | `La edad debe ser un número entero entre 1 y 120.` |

## Fuera de alcance

- Unificar las reglas de contraseña de registro, restablecer y cambiar contraseña: lo cubre la Story del Sprint 2 "Contraseña segura con 7 criterios". En el Sprint 1 solo el registro pasa a mínimo 8; restablecer y cambiar contraseña se quedan como están.
- Traducir toda la interfaz: lo cubre la Story del Sprint 2 "Idioma de la interfaz".
- Verificación de email: desactivada en el Sprint 1.
- Cambiar la edad u otros datos después de registrarse (perfil). _(propuesto)_
- Registro con cuentas externas (Google, etc.). _(propuesto)_

## Decisiones tomadas

- El formulario mantiene el campo "Confirmar contraseña".
- Solo el registro exige mínimo 8 caracteres; restablecer y cambiar contraseña quedan como están.
- El formulario no se traduce en esta Story. El campo nuevo (edad) se etiqueta en el mismo idioma que los demás (inglés: "Age").
- La edad es opcional en la base de datos (cuentas que ya existen, como el usuario de prueba del seeder) y obligatoria en el formulario.
- Tras registrarse la persona no queda con la sesión iniciada: se redirige a la pantalla de iniciar sesión con el mensaje `Cuenta creada`.
- La verificación de email queda desactivada en el Sprint 1 y ninguna ruta depende de ella.
- `Introduce un email válido.` también se usa cuando el email está vacío.
- `La contraseña debe tener al menos 8 caracteres.` también se usa cuando la contraseña está vacía.
- `La edad debe ser un número entero entre 1 y 120.` se usa para edad vacía, no entera o fuera de rango.
- Un nombre o un email de más de 255 caracteres siguen mostrando el mensaje de Laravel en inglés: no hay texto definido.

## Preguntas abiertas

Ninguna por ahora.

## Notas técnicas (verificar)

- El registro del starter ya valida el email como único (índice único en la base de datos y emails en minúsculas) y pedía repetir la contraseña, pero no tenía edad, sus mensajes estaban en inglés y, al registrar, **iniciaba la sesión** del usuario y lo llevaba a `/dashboard`.
- La regla de contraseña por defecto cambia en producción (`AppServiceProvider`): allí exige mucho más que 8 caracteres. Para cumplir el criterio 3 el registro usa una regla simple de longitud mínima (`min:8`), que además admite el mensaje propio del criterio 4; la regla `Password` de Laravel no lo admite por validador.
- El formulario lleva `novalidate`: sin él, el navegador bloquearía el envío con sus propios avisos (campo vacío, email o edad fuera de rango) y los textos exactos de esta spec no llegarían a verse.
- Verificación de email desactivada: se quita `MustVerifyEmail` del usuario, la función `emailVerification` de Fortify, su pantalla, el middleware `verified` de las rutas y el aviso del perfil. La columna `email_verified_at` se queda en la base de datos.
- Registrarse no inicia sesión ni invalida la sesión: el carrito, que vive en la sesión, se conserva hasta que la persona inicia sesión.
- `composer.json` conserva el hook `install:features` del instalador del starter (chisel), que nunca se completó. Tras quitar la verificación a mano, un `composer update` podría relanzarlo con las opciones por defecto. Conviene retirarlo en otra tarea.
