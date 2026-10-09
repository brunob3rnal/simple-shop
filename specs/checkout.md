# Checkout con Stripe (modo prueba)

| Campo        | Valor            |
| ------------ | ---------------- |
| Jira         | BRAVOBRAVO-15    |
| Epic         | Checkout y pagos |
| Sprint       | Sprint 1         |
| Story points | 5                |
| Estado       | Por hacer        |

## Descripción

Permitir que un usuario con sesión pague el contenido de su carrito con una tarjeta de prueba de Stripe y vea si la compra salió bien o no.

## Tecnología

- Laravel + Vue (Inertia)
- Stripe en **modo prueba** (claves de prueba en `.env`)
- Moneda: **USD**. Los montos se manejan como enteros en **centavos** (ej.: 19.99 USD = `1999`).

## Depende de

- Iniciar sesión (BRAVOBRAVO-12): para pagar hay que estar autenticado.
- Carrito de compras (BRAVOBRAVO-14): el pago cobra lo que hay en el carrito.

## Criterios de aceptación

Cada criterio debe poder comprobarse con sí o no, y tener al menos un test.

1. Desde el carrito hay un botón **"Pagar"** que inicia el pago con Stripe en modo prueba. _(texto del botón: propuesto)_
2. Si alguien sin sesión pulsa "Pagar", se le redirige a iniciar sesión, y al entrar vuelve al carrito con sus productos.
3. El monto que se cobra es el total del carrito **calculado en el servidor** con los precios de la base de datos, en centavos USD. Nunca se usa un precio enviado por el navegador.
4. Con una tarjeta de prueba aprobada se muestra el mensaje exacto: `Compra realizada exitosamente`
5. Con una tarjeta de prueba rechazada, Stripe muestra su propio error en su página de pago y el usuario puede reintentar o cancelar. No se guarda ningún pedido y el carrito queda igual.
6. Si el carrito está vacío, el botón "Pagar" no se muestra. _(propuesto)_
7. Si el usuario cancela el pago en Stripe, vuelve al carrito sin cambios. _(propuesto)_
8. Tras una compra exitosa, el carrito queda vacío. _(propuesto)_
9. Las claves de Stripe solo existen en `.env`. Ninguna clave aparece en el código ni en el repositorio.

## Mensajes exactos

| Situación     | Texto literal                   |
| ------------- | ------------------------------- |
| Pago aprobado | `Compra realizada exitosamente` |

El mensaje de tarjeta rechazada lo muestra Stripe en su página, no la tienda.

## Fuera de alcance (Sprint 1)

- Tarjetas reales y modo producción de Stripe.
- Confirmación del pago con webhook (Story aparte en el Backlog).
- Otras monedas distintas de USD.
- Cupones, envíos e impuestos.
- Guardar el pedido en la base de datos: lo cubre la Story "Guardar pedido" (BRAVOBRAVO-16).

## Decisiones tomadas

- Hay que iniciar sesión para pagar. El catálogo y el carrito sí funcionan sin sesión.
- En el Sprint 1 el pago se confirma al volver de Stripe. El webhook queda para el Sprint 2.
- El carrito vive en la sesión de Laravel durante el Sprint 1.
- Se usa la **página de pago alojada por Stripe** (Stripe Checkout), no un formulario propio: es más simple y los datos de tarjeta nunca pasan por la tienda. Por eso el error de tarjeta rechazada lo muestra Stripe.

## Preguntas abiertas

Ninguna por ahora.

## Notas técnicas (verificar en la documentación de Stripe)

- Tarjetas de prueba conocidas: `4242 4242 4242 4242` (aprobada) y `4000 0000 0000 0002` (rechazada). Cualquier fecha futura y cualquier CVC.
- Stripe recibe el monto en la unidad más pequeña de la moneda (centavos para USD).

## Subtarea: Spike de Stripe

**Objetivo:** aprender cómo funciona Stripe en modo prueba antes de construir la Story completa.
**Tope de tiempo:** 3 horas. Si se pasa, se para y se replantea.

Criterios del spike:

- Con un producto y un precio fijos, sin carrito ni login, un botón lleva a pagar con Stripe en modo prueba.
- Con la tarjeta de prueba aprobada se vuelve a la app y se muestra `Compra realizada exitosamente`.
- Con la tarjeta de prueba rechazada, Stripe muestra su error y al cancelar se vuelve al carrito sin cambios.
- Queda anotado en el README cómo se confirma el pago y qué claves hacen falta.
