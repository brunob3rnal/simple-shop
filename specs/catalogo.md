# Catálogo de productos

| Campo        | Valor         |
| ------------ | ------------- |
| Jira         | BRAVOBRAVO-13 |
| Epic         | Catálogo      |
| Sprint       | Sprint 1      |
| Story points | 2             |
| Estado       | Por hacer     |

## Descripción

Mostrar los productos de la tienda.

## Tecnología

- Laravel + Vue (Inertia)
- Moneda: **USD**. Los precios se guardan como enteros en **centavos** (ej.: 19.99 USD = `1999`) y se muestran en USD con el formato `$19.99`.
- Los productos se cargan con un **seeder**.

## Depende de

Nada por ahora: el catálogo se ve sin iniciar sesión.

## Criterios de aceptación

Cada criterio debe poder comprobarse con sí o no, y tener al menos un test.

1. Hay 3 productos cargados con un seeder, cada uno con nombre y precio.
2. Los 3 productos se ven en una lista en el catálogo.
3. El catálogo vive en `/` y reemplaza la página de bienvenida.
4. El catálogo se puede ver sin iniciar sesión.
5. Los precios se muestran en USD con el formato `$19.99`.
6. El precio de cada producto se guarda en la base de datos como entero en centavos.
7. Los productos se ordenan como en el seeder (por id).
8. Ejecutar el seeder varias veces no duplica los productos: siempre quedan 3.
9. Si no hay productos, se muestra el texto exacto: `No hay productos disponibles`

## Mensajes exactos

| Situación     | Texto literal                  |
| ------------- | ------------------------------ |
| Sin productos | `No hay productos disponibles` |

## Fuera de alcance (Sprint 1)

- Descripción e imagen de los productos: van en una Story del Sprint 2.
- Botón "Agregar al carrito": queda para la Story del carrito (BRAVOBRAVO-14).
- Otras monedas distintas de USD. _(propuesto)_
- Crear, editar o borrar productos desde la tienda (administración). _(propuesto)_
- Búsqueda, filtros y paginación. _(propuesto)_

## Decisiones tomadas

- El catálogo se ve sin iniciar sesión.
- El catálogo vive en `/` y reemplaza la página de bienvenida.
- Cada producto tiene solo nombre y precio.
- Los precios se muestran en USD como `$19.99` y se guardan como enteros en centavos.
- Hay 3 productos cargados con un seeder. Se ordenan como en el seeder (por id).
- El seeder se puede ejecutar varias veces sin duplicar productos, y se usa en desarrollo y en la demo.
- Si no hay productos, se muestra `No hay productos disponibles`.
- La página de inicio conserva la cabecera con los enlaces "Log in / Register / Dashboard" de la bienvenida.
- Productos del seeder:

| Id  | Nombre           | Precio (centavos) | Se muestra |
| --- | ---------------- | ----------------- | ---------- |
| 1   | Camiseta básica  | `1999`            | `$19.99`   |
| 2   | Taza de cerámica | `950`             | `$9.50`    |
| 3   | Mochila urbana   | `3900`            | `$39.00`   |

## Preguntas abiertas

Ninguna por ahora.
