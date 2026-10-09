# Registro de usuarios

| Campo        | Valor         |
| ------------ | ------------- |
| Jira         | BRAVOBRAVO-11 |
| Epic         | Por definir   |
| Sprint       | Por definir   |
| Story points | Por definir   |
| Estado       | Por hacer     |

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

## Mensajes exactos

| Situación                  | Texto literal                                     |
| -------------------------- | ------------------------------------------------- |
| Contraseña demasiado corta | `La contraseña debe tener al menos 8 caracteres.` |
| Email ya registrado        | `Ese email ya está registrado.`                   |
| Registro correcto          | `Cuenta creada`                                   |

## Fuera de alcance

- Cambiar la edad u otros datos después de registrarse (perfil). _(propuesto)_
- Registro con cuentas externas (Google, etc.). _(propuesto)_

## Decisiones tomadas

- Para la contraseña solo se exige la longitud mínima de 8; ninguna otra regla.
- La edad es un entero de 1 a 120.

## Preguntas abiertas

1. **Confirmar contraseña:** la Story pide nombre, edad, email y contraseña, pero el formulario actual pide además repetir la contraseña y exige que coincida. ¿Se mantiene? ("No se exige ninguna otra regla" podría excluirla.)
2. **Otros flujos de contraseña:** restablecer y cambiar la contraseña comparten hoy las reglas del starter (en producción: 12 o más caracteres, mayúsculas y minúsculas, números, símbolos y que no esté filtrada en brechas). ¿Pasan a la regla simple de 8 (una sola política) o se quedan como están, con lo que el registro sería más permisivo que cambiar la contraseña?
3. **Mensajes no especificados:** nombre vacío, email vacío o con formato inválido, edad vacía, no entera o fuera de 1 a 120, y contraseñas que no coinciden. ¿Texto exacto en español, o se quedan los de Laravel en inglés?
4. **Idioma de la pantalla:** las etiquetas y textos del formulario están en inglés ("Create an account", "Name"...), pero los mensajes de la Story van en español. ¿Se traduce el formulario?
5. **Verificación de email:** hoy se envía el correo de verificación y hay rutas que piden el email verificado. ¿Se mantiene?
6. **Edad en la base de datos:** ¿es obligatoria también para las cuentas que ya existen (por ejemplo el usuario de prueba del seeder)? Propuesta: columna opcional en la base de datos y obligatoria en el formulario.
7. **Sesión tras registrarse:** hoy el usuario queda con la sesión iniciada y va al dashboard; la Story manda a la pantalla de iniciar sesión. ¿Confirmas que **no** debe quedar con la sesión iniciada y que debe iniciar sesión él mismo?
8. **Epic, Sprint y Story points.**

## Notas técnicas (verificar)

- El registro del starter ya valida el email como único (índice único en la base de datos y emails en minúsculas) y la contraseña con la regla por defecto de Laravel, pero no tiene edad, sus mensajes están en inglés y, al registrar, **inicia la sesión** del usuario y lo lleva a `/dashboard`.
- La regla de contraseña por defecto cambia en producción (`AppServiceProvider`): allí exige mucho más que 8 caracteres. Para cumplir el criterio 3 hay que usar una regla simple de longitud mínima.
- Para poder personalizar el texto del criterio 4, la regla debe ser `min:8`: la regla `Password` de Laravel no admite mensajes propios por validador.
- Los tests actuales de `RegistrationTest` y del carrito (`CartTest`) asumen que, tras registrarse, el usuario queda con la sesión iniciada; habría que reescribirlos si se confirma la pregunta 7.
- El carrito vive en la sesión: al no iniciar sesión tras registrarse (y no invalidar la sesión) el carrito se conserva.
