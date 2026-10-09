# Tienda

Laravel + Vue (Inertia). Moneda USD; los precios se manejan como enteros en centavos.
Las specs están en `specs/` (Spec-Driven Development).

El nombre de la tienda que se ve a la izquierda de la cabecera (`Mi Tienda`) sale de `APP_NAME` en el `.env`. Como lleva un espacio, el valor va **entre comillas**: `APP_NAME="Mi Tienda"` (sin comillas, Laravel no puede leer el `.env`).

## Catálogo (BRAVOBRAVO-13)

El catálogo está en `/` y se ve sin iniciar sesión. Lista los productos ordenados por id, con el precio en USD (`$19.99`); se guardan como enteros en centavos (`price_cents`).

Para cargar los 3 productos:

```
php artisan migrate
php artisan db:seed
```

`php artisan db:seed` se puede ejecutar varias veces sin fallar ni duplicar nada: los productos tienen ids fijos 1 a 3 (se actualizan) y el usuario de prueba (`test@example.com`, contraseña `password`) solo se crea si no existe. Ninguno de los dos seeders usa Faker.

En la demo conviene cargar solo los productos, para no crear un usuario con contraseña conocida:

```
php artisan db:seed --class=ProductSeeder
```

Si no hay productos, el catálogo muestra `No hay productos disponibles`.

## Carrito (BRAVOBRAVO-14)

Cada producto del catálogo tiene un botón "Agregar al carrito". El carrito se ve en `/carrito` (enlace "Carrito" en la cabecera) y lo puede usar cualquier visitante, sin iniciar sesión.

- **Dónde vive:** en la sesión de Laravel (Sprint 1); en el Sprint 2 pasará a la base de datos. Toda la lógica está en `App\Services\CartService`.
- **Qué guarda:** solo el id del producto y la cantidad. Los precios, subtotales y el total se calculan siempre en el servidor con los precios de la base de datos, en centavos USD.
- **Cantidades:** agregar un producto que ya está en el carrito sube su cantidad en 1, sin máximo. Quitar productos y cambiar cantidades no está en el Sprint 1.
- **Sesión:** al iniciar sesión el carrito se conserva; al cerrar sesión se vacía. El carrito de un visitante se pierde a los 120 minutos sin actividad (`SESSION_LIFETIME`).

## Stripe (spike, BRAVOBRAVO-15)

Prueba de concepto de pago con **Stripe Checkout alojado** en **modo prueba**.
Página del spike: `/spike/stripe` (producto y precio fijos, sin login ni carrito).

### Claves necesarias

| Variable        | Qué es                                      | Dónde se obtiene                                       |
| --------------- | ------------------------------------------- | ------------------------------------------------------ |
| `STRIPE_SECRET` | Clave secreta **de prueba** (`sk_test_...`) | Stripe Dashboard (modo prueba) → Developers → API keys |

- Va solo en el `.env` local. Está vacía en `.env.example`. **Nunca** se sube al repo.
- No hacen falta la clave publicable, el Stripe CLI ni webhooks: el usuario es redirigido a la página de pago de Stripe.

### Cómo se confirma el pago

1. `POST /spike/stripe/checkout` crea una sesión de Checkout con el monto fijo del **servidor** (1999 centavos = 19.99 USD) y redirige a Stripe.
2. Al pagar, Stripe vuelve a `/spike/stripe/success?session_id=cs_test_...`.
3. El servidor **consulta a Stripe** esa sesión (`payment_status === 'paid'`). Solo entonces muestra `Compra realizada exitosamente`. El `session_id` de la URL no se considera prueba de pago por sí solo.
4. Si el usuario cancela, Stripe vuelve a `/spike/stripe/cancel`, que redirige a la página del spike sin cambios.

Confirmar al volver de Stripe es una decisión del Sprint 1: si el usuario cierra la pestaña antes de volver, la app no se entera del pago. La confirmación con webhook queda para el Sprint 2.

### Tarjetas de prueba

Cualquier fecha futura y cualquier CVC.

| Tarjeta               | Resultado                                           |
| --------------------- | --------------------------------------------------- |
| `4242 4242 4242 4242` | Aprobada                                            |
| `4000 0000 0000 0002` | Rechazada (el error lo muestra Stripe en su página) |

### Tests

```
php artisan test --filter=StripeSpikeTest
```

Los tests no llaman a Stripe: sustituyen `StripeGateway` por un mock.
