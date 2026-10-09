# Carrito de compras

| Campo        | Valor         |
| ------------ | ------------- |
| Jira         | BRAVOBRAVO-14 |
| Epic         | Carrito       |
| Sprint       | Sprint 1      |
| Story points | 3             |
| Estado       | Por hacer     |

## Descripción

Permitir juntar productos antes de pagar.

## Tecnología

- Laravel + Vue (Inertia)
- El carrito vive en la **sesión de Laravel** durante el Sprint 1. En el Sprint 2 pasa a la base de datos.
- Moneda: **USD**. Los montos se manejan como enteros en **centavos** y se muestran como `$19.99`.
- La lógica del carrito va en una sola clase, `CartService`.

## Depende de

- Catálogo (BRAVOBRAVO-13): el botón "Agregar al carrito" vive en cada producto del catálogo.
- Iniciar sesión (BRAVOBRAVO-12): solo para comprobar que el carrito se conserva al entrar; agregar productos no la requiere.

Lo usa: Checkout con Stripe (BRAVOBRAVO-15), que cobra lo que hay en el carrito.

## Criterios de aceptación

Cada criterio debe poder comprobarse con sí o no, y tener al menos un test.

1. Cada producto tiene un botón **"Agregar al carrito"**.
2. Al pulsar "Agregar al carrito", ese producto queda en el carrito.
3. El carrito muestra los productos agregados y el total: cada producto con su nombre, su cantidad, su precio unitario y su subtotal (cantidad × precio), y debajo el total. Todos con el formato `$19.99`.
4. Cualquier visitante puede agregar productos sin iniciar sesión.
5. Al iniciar sesión, el carrito conserva sus productos.
6. Al cerrar sesión, el carrito se vacía.
7. Los subtotales y el total se calculan en el servidor con los precios de la base de datos, en centavos USD. Nunca se usa un precio enviado por el navegador.
8. Si se agrega un producto que ya está en el carrito, su cantidad sube en 1. No hay máximo de unidades.
9. El carrito tiene su propia página en `/carrito`.
10. En la cabecera hay un enlace **"Carrito"**, sin contador, que lleva a `/carrito`.
11. Al agregar un producto el usuario se queda en el catálogo y ve el texto exacto: `Producto agregado al carrito`
12. Con el carrito vacío se muestra el texto exacto: `Tu carrito está vacío`
13. En la cabecera, a la izquierda, está el nombre de la tienda, `Mi Tienda`, con un enlace a `/`. Se ve igual para visitantes y para usuarios con sesión.

## Mensajes exactos

| Situación              | Texto literal                  |
| ---------------------- | ------------------------------ |
| Botón en cada producto | `Agregar al carrito`           |
| Enlace en la cabecera  | `Carrito`                      |
| Nombre de la tienda    | `Mi Tienda`                    |
| Producto agregado      | `Producto agregado al carrito` |
| Carrito vacío          | `Tu carrito está vacío`        |

## Fuera de alcance (Sprint 1)

- Guardar el carrito en la base de datos: Story del Sprint 2.
- Quitar productos y cambiar cantidades: quedan fuera del Sprint 1 (decidido).
- Contador de productos en la cabecera: va al Sprint 2.
- Pagar: lo cubre la Story de Checkout (BRAVOBRAVO-15).
- Stock o inventario de productos. _(propuesto)_
- Cupones, envíos e impuestos. _(propuesto)_

## Decisiones tomadas

- El carrito vive en la sesión de Laravel durante el Sprint 1.
- La lógica del carrito va en una sola clase, `CartService`.
- Cualquier visitante puede agregar productos sin iniciar sesión.
- El carrito se conserva al iniciar sesión y se vacía al cerrar sesión.
- Agregar un producto que ya está en el carrito sube su cantidad en 1, sin máximo en el Sprint 1.
- Las líneas del carrito salen en el orden en que se agregaron (no por nombre ni por id).
- El carrito tiene su propia página en `/carrito`. En la cabecera hay un enlace "Carrito", sin contador (el contador va al Sprint 2).
- Al agregar un producto el usuario se queda en el catálogo y ve el aviso `Producto agregado al carrito`.
- En la página del carrito, cada producto muestra nombre, cantidad, precio unitario y subtotal, y abajo el total. Con el carrito vacío se muestra `Tu carrito está vacío`.
- La cabecera lleva a la izquierda el nombre de la tienda, `Mi Tienda`, con enlace a `/`. El nombre sale de `APP_NAME`, así que se cambia en un solo sitio.
- El carrito de un visitante se pierde a los 120 minutos sin actividad (caducidad de la sesión): es aceptable en el Sprint 1. Guardarlo en la base de datos es una Story del Sprint 2.

## Preguntas abiertas

Ninguna por ahora.

## Notas técnicas (verificar)

- Al iniciar sesión o pasar el reto 2FA, Fortify regenera el id de sesión pero **conserva sus datos**: el criterio 5 se cumple sin código extra. Al cerrar sesión llama a `session()->invalidate()`, que **borra todos los datos**: el criterio 6 también. Ambos necesitan test.
- Registrarse ya no inicia sesión (BRAVOBRAVO-11): lleva a la pantalla de iniciar sesión y tampoco invalida la sesión, así que el carrito sigue ahí hasta que la persona inicia sesión. Hay un test que lo comprueba (registrarse y luego iniciar sesión).
- Driver de sesión `database`, vida de 120 minutos (`SESSION_LIFETIME`).
- En la sesión se guarda solo el id del producto y la cantidad; los precios se leen siempre de la base de datos.
- Si un producto desaparece de la base de datos, desaparece también del carrito.
- El nombre de la tienda lo comparte Inertia como la prop `name` (`config('app.name')`, es decir `APP_NAME`). En `.env.example` vale `"Mi Tienda"`; cada `.env` local debe llevar el mismo valor, si no la cabecera muestra el nombre que tenga.
- El checkout necesitará de `CartService`: el contenido del carrito, el total en centavos, saber si está vacío y vaciarlo tras pagar.
