# Avances por PR del catálogo KICF

**Corte del 22/11/2026.** Los PR #1 a #7 están integrados en `main`. GitHub y GitLab se verificaron en `00af441afb89df5b4322c4db66e5c15cc1e97722`. Antes del push, GitLab estaba 19 commits por detrás y tenía cero commits exclusivos. Se sincronizó por fast-forward. Después, `composer test` pasó en SQLite en memoria con **57 pruebas, 1.238 aserciones y salida 0**. [GitHub](https://github.com/JavierCruz0922/Ecomerce/tree/main) · [GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/tree/main) · [Resumen de sincronización](https://gitlab.com/javiercruz0922/ecomerce/-/wikis/home).

## PR #1 · T02/P06 · Esquema de catálogo y pedidos

Integrado en `883fc4f`. La migración crea `categories`, `products`, `orders` y `order_items`. El esquema exige SKU único, precios enteros positivos en COP, existencias enteras no negativas y cantidades positivas. Los pedidos pertenecen a usuarios. Sus líneas guardan el SKU, el nombre y el precio de compra. El retiro lógico y las claves foráneas conservan el historial.

- **Validación.** `php vendor/bin/phpunit --do-not-cache-result` debía comprobar relaciones, restricciones y la reversión y reaplicación acotadas sin perder usuarios ni tablas nativas. Pasaron 25 pruebas y 255 aserciones en SQLite en memoria. El PR reportó sintaxis, Pint y plataforma Composer aprobados. Se compiló el SQL de PostgreSQL sin conexión. La validación integral de ese motor se remitió a T13/P15. [PR #1](https://github.com/JavierCruz0922/Ecomerce/pull/1) · [Registro de PHPUnit](https://github.com/JavierCruz0922/Ecomerce/pull/1#issuecomment-7070202895) · [T02 en GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/2).

## PR #2 · Evidencias de PR y prueba de usuario

Integrado en `ea036cd`. Añade una plantilla de PR, el procedimiento reproducible de SQLite aislado y `CustomUserTest`. La prueba recibe un nombre y un correo sintéticos y verifica persistencia, hash y credenciales correctas e incorrectas. La configuración incorpora una clave pública exclusiva para PHPUnit que permite ejecutar la suite en una copia limpia.

- **Validación.** Se esperaba que la suite completa pasara sin `.env` y que la prueba específica persistiera un solo usuario con sus credenciales protegidas. En el SHA corregido `1dc5883`, `php vendor/bin/phpunit --do-not-cache-result` pasó con 26 pruebas, 271 aserciones y salida 0. Con `--filter CustomUserTest` pasó 1 prueba y 16 aserciones por ejecución con parámetros por defecto y dos juegos sintéticos. La prueba prepara T04/P08, que cubre las rutas de registro y sesión. [PR #2 y verificación](https://github.com/JavierCruz0922/Ecomerce/pull/2) · [T09 en GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/9).

## PR #3 · T03/P07 · Carga inicial del catálogo

Integrado en `5ea987c`. Un exportador genera el manifiesto JSON desde la hoja Productos. `KicfCatalogSeeder` carga en una transacción los 20 SKU de `PAP001` a `PAP020` y cuatro categorías. Las recargas conservan precios, existencias y productos retirados. Las filas inválidas impiden escribir el lote. También se corrigieron las rutas temporales para las vistas Blade en Windows.

- **Validación.** Se esperaba una carga de 20 filas, idempotencia y rechazo de lotes inválidos. PHPUnit pasó con 34 pruebas y 513 aserciones tanto en el SHA publicado como en el merge de prueba, con salida 0. Con `--filter KicfCatalogSeederTest` pasaron 8 pruebas y 242 aserciones. El exportador con `--check` verificó las 20 filas y la revisión independiente aprobó 14 de 14 controles, incluido el rollback. [PR #3](https://github.com/JavierCruz0922/Ecomerce/pull/3) · [Revisión técnica](https://github.com/JavierCruz0922/Ecomerce/pull/3#issuecomment-7073060462) · [Procedimiento de carga](../../PLAN_PRUEBAS.md#carga-inicial-del-catálogo-kicf).

## PR #4 · T12/P03 · Livewire, Blade y Tailwind

Integrado en `e86fad6`. Incorpora Livewire 4 y un contador reactivo con incremento, reinicio y una propiedad protegida. La ruta `demo.livewire` en `/demo/livewire` devuelve HTTP 200 en `local` y `testing` y HTTP 404 en `production`. Se integra la corrección de rutas temporales de T03.

- **Validación.** Se esperaba el flujo `0 → 1 → 2 → 0` en `LivewireDemoTest` y la restricción por entorno. Pasaron 4 pruebas y 12 aserciones específicas. La regresión con el bloque Windows pasó con 38 pruebas y 525 aserciones. El build de Vite, Pint y Composer pasaron. Edge verificó Tab, Enter, la vista de 390 px y ocho actualizaciones normales HTTP 200. [PR #4](https://github.com/JavierCruz0922/Ecomerce/pull/4) · [Revisión técnica](https://github.com/JavierCruz0922/Ecomerce/pull/4#issuecomment-7073313506) · [T12 en GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/12).

## PR #5 · T05/P09 · Catálogo visual y navegación

Integrado en `a8858ab`. Inicio (`home`, `/`) enlaza Catálogo (`catalog.index`, `/catalogo`). La imagen y el título de cada tarjeta abren el detalle por SKU (`catalog.show`, `/catalogo/{product:sku}`). La cuadrícula muestra precios en COP, disponibilidad y estado agotado. Añade búsqueda parcial por nombre o SKU, filtros de categoría y precio inclusivo, limpieza, estado vacío y paginación. Catálogo conserva el estado activo en el detalle y los SKU inexistentes o retirados devuelven HTTP 404. La demo se muestra en el menú de `local` y `testing`.

- **Validación.** `ProductCatalogTest` y `StoreNavigationTest` debían cubrir filtros, enlaces, rutas y conservación de existencias. Pasaron 6 pruebas con 70 aserciones y 5 pruebas con 236 aserciones, respectivamente. La regresión pasó con 49 pruebas y 831 aserciones. Edge verificó navegación desde Inicio, teclado, foco, detalle y vista móvil. El build de Vite pasó. Las imágenes provisionales dieron paso a los originales del PR #6. [PR #5](https://github.com/JavierCruz0922/Ecomerce/pull/5) · [Cierre técnico](https://github.com/JavierCruz0922/Ecomerce/pull/5#issuecomment-7079676118) · [T05 en GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/5).

## PR #6 · T05/P09 · Imágenes y licencias

Integrado en `4a6709f`. Incorpora 20 originales, ocho de Wikimedia Commons y doce de Openclipart. El componente de imagen valida la correspondencia entre archivo y SKU autorizado y muestra un marcador ante un archivo ausente o una referencia distinta. Las imágenes tienen texto alternativo. La ruta pública `images.credits` en `/creditos-imagenes` se enlaza desde el pie de página. Cada detalle enlaza su atribución.

- **Validación.** Se esperaba correspondencia entre los 20 SKU, los archivos y las atribuciones, además de un marcador de respaldo. `CatalogImageCreditsTest` pasó con 4 pruebas y 273 aserciones. La suite y el bloque Windows pasaron con 53 pruebas y 1.184 aserciones por ejecución. Edge verificó 20 imágenes HTTP 200 y los créditos a 1440, 768 y 390 px.
- **Seguimiento.** La incidencia posterior del servidor local quedó resuelta. El inventario documenta las observaciones de PAP007, PAP016 y PAP019. Queda planteado optimizar miniaturas con conservación de originales y atribuciones. [PR #6](https://github.com/JavierCruz0922/Ecomerce/pull/6) · [Validación](https://github.com/JavierCruz0922/Ecomerce/pull/6#issuecomment-7080213189) · [Fuentes y licencias](../../catalogo/FUENTES_IMAGENES.md) · [Incidencias](../06-Errores/Incidencias-KICF.md).

## PR #7 · T06/P10 · Búsqueda y filtros avanzados

Integrado en `00af441`. Añade coincidencia exacta y parcial por nombre o SKU y combina categoría, color, material y precio inclusivo. Conserva los acentos y trata los comodines como texto literal. Una entrada inválida conserva el último resultado válido. En móvil, los filtros avanzados empiezan plegados, se operan con teclado y conservan los valores al plegarse. A 390 × 844 px se ven la foto y el nombre del primer producto al entrar.

- **Validación.** En el escenario de prueba, la búsqueda exacta por nombre o SKU y los filtros combinados debían devolver PAP007. `ProductCatalogTest` pasó con 10 pruebas y 124 aserciones. La regresión pasó con 57 pruebas y 1.238 aserciones. La revisión final registró 13 de 13 casos límite en SQLite y 47 controles aprobados en Edge. Una validación previa reprodujo la búsqueda y los filtros en PostgreSQL 18 temporal.
- **Seguimiento.** Se mantienen las mejoras de persistencia de filtros al volver del detalle, búsqueda sin distinción de tildes y optimización de imágenes. [PR #7](https://github.com/JavierCruz0922/Ecomerce/pull/7) · [Validación final](https://github.com/JavierCruz0922/Ecomerce/pull/7#issuecomment-7081063936) · [Bitácora T06/P10](../../catalogo/T06-P10.md).

## Seguimiento TSP

- [ ] **Revisión cruzada humana de los PR #1 a #7.** GitHub muestra los siete PR integrados y cero revisiones formales registradas al corte. Los enlaces de cada sección recogen la evidencia técnica. Queda pendiente registrar el alcance, la fecha, el SHA, la decisión y el enlace de la revisión del segundo integrante.
- [ ] **Módulos pendientes.** Registro, inicio y cierre de sesión, administración de productos, selección de cantidades, carrito y confirmación de pedidos con validación estricta de inventario.
- [ ] **Validación integral pendiente.** Verificar esquema, pedidos y concurrencia en PostgreSQL según T13/P15 y el pipeline según T14/P16.

Los comandos y datos de cada control se mantienen en el [plan de pruebas](../../PLAN_PRUEBAS.md). Las tareas pendientes siguen el alcance del [documento de requisitos](../../condiciones_y_reglas_del_proyecto.md).
