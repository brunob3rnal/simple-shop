# Carrito de compras

| Campo        | Valor         |
| ------------ | ------------- |
| Jira         | BRAVOBRAVO-14 |
| Epic         | Por definir   |
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
2. Al pulsar "Agregar al carrito", ese producto queda en el carrito. _(propuesto)_
3. El carrito muestra los productos agregados y el total. _(propuesto)_
4. Cualquier visitante puede agregar productos sin iniciar sesión.
5. Al iniciar sesión, el carrito conserva sus productos.
6. Al cerrar sesión, el carrito se vacía. _(propuesto, por confirmar)_
7. El total se calcula en el servidor con los precios de la base de datos, en centavos USD. Nunca se usa un precio enviado por el navegador. _(propuesto)_

## Mensajes exactos

| Situación              | Texto literal        |
| ---------------------- | -------------------- |
| Botón en cada producto | `Agregar al carrito` |

## Fuera de alcance (Sprint 1)

- Guardar el carrito en la base de datos: pasa en el Sprint 2.
- Pagar: lo cubre la Story de Checkout (BRAVOBRAVO-15).
- Stock o inventario de productos. _(propuesto)_
- Cupones, envíos e impuestos. _(propuesto)_

## Decisiones tomadas

- El carrito vive en la sesión de Laravel durante el Sprint 1.
- La lógica del carrito va en una sola clase, `CartService`.
- Cualquier visitante puede agregar productos sin iniciar sesión.
- El carrito se conserva al iniciar sesión.

## Preguntas abiertas

1. Si se agrega un producto que ya está en el carrito, ¿sube la cantidad (×2, y el total la multiplica) o no se agrega otra vez? Si hay cantidades, ¿hay un máximo por producto?
2. ¿Se pueden quitar productos o cambiar la cantidad desde el carrito, o queda fuera de esta Story?
3. ¿Dónde se ve el carrito: una página propia (por ejemplo `/carrito`), un panel lateral o ambos? ¿Hay enlace o contador en la cabecera?
4. ¿Qué ve el usuario justo después de pulsar "Agregar al carrito": se queda en el catálogo con un aviso, o va al carrito? ¿Hay un texto exacto?
5. ¿Qué texto exacto se muestra cuando el carrito está vacío?
6. ¿Confirmas que al cerrar sesión el carrito se vacía? (Es lo que hace Laravel por defecto.) Y un visitante que no inicia sesión pierde el carrito al caducar su sesión (120 minutos sin actividad): ¿es aceptable?
7. ¿Cuál es el Epic? El Sprint 1 está deducido de `specs/checkout.md` ("el carrito vive en la sesión durante el Sprint 1"); ¿lo confirmas?

## Notas técnicas (verificar)

- Al iniciar sesión, registrarse o pasar el reto 2FA, Fortify regenera el id de sesión pero **conserva sus datos**: el criterio 5 se cumple sin código extra. Al cerrar sesión llama a `session()->invalidate()`, que **borra todos los datos**: el criterio 6 también. Ambos necesitan test.
- Driver de sesión `database`, vida de 120 minutos (`SESSION_LIFETIME`).
- En la sesión se guarda solo el id del producto (y la cantidad, si la hay); los precios se leen siempre de la base de datos.
- El checkout necesitará de `CartService`: el contenido del carrito, el total en centavos, saber si está vacío y vaciarlo tras pagar.
