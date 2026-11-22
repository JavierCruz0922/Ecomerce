# Validación de PostgreSQL 18 y QA del catálogo KICF

- **Primer corte:** 22/11/2026, hora de Bogotá, sobre el código `00af441afb89df5b4322c4db66e5c15cc1e97722`.
- **Revalidación:** 22/11/2026, de 13:05 a 13:45, sobre la rama `chore/kicf-postgresql-baseline-validation` en `bcc3c9cab7795765b58e63a7ebd8d911ee92189c`. Su código de aplicación es idéntico a `00af441`; el diff solo añade el SQL de referencia y esta documentación.
- **Alcance de la revalidación:** no se repitieron la creación de PostgreSQL, las migraciones, la carga inicial ni la restauración. Sobre `kicf_development` solo se hicieron lecturas, con `default_transaction_read_only=on` en cada sesión. La suite automatizada se ejecutó en SQLite en memoria.

La columna «Procedencia» distingue entre:

- **Manual:** evidencia ejecutada y validada manualmente por el usuario.
- **Reproducida:** comprobación ejecutada de nuevo por el agente de QA en la revalidación.
- **Pendiente:** no hay evidencia todavía.
- **No disponible:** no se puede comprobar con el acceso actual.

## Línea base y recuperación

| ID | Control | Evidencia | Procedencia | Resultado | Estado |
| --- | --- | --- | --- | --- | --- |
| BD-01 | Conexión PostgreSQL 18.6 | `php artisan env` y `php artisan db:show` | Manual y reproducida | Entorno `local`, PostgreSQL 18.6, `pgsql`, `kicf_development` en 127.0.0.1:5432, usuario `kicf_dev`. `intl` habilitada. | APROBADO |
| BD-02 | Cuatro migraciones | Registro PowerShell del usuario y `php artisan migrate:status` | Manual y reproducida | Las cuatro migraciones figuran como `Ran` en el lote 1. | APROBADO |
| BD-03 | Trece tablas | `db:show` y consultas a `pg_tables`, `pg_indexes` y `pg_constraint` | Manual y reproducida | 13 tablas, 27 índices y 4 claves foráneas. Hay 5 restricciones únicas, entre ellas `products_sku_unique`, y 4 de comprobación. | APROBADO |
| BD-04 | Veinte productos | Consulta SQL y comparación de los nueve campos de cada SKU con el manifiesto JSON | Manual y reproducida | 20 productos activos y 0 diferencias con el manifiesto. | APROBADO |
| BD-05 | Veinte SKU únicos | `COUNT(DISTINCT sku)` | Manual y reproducida | 20 SKU únicos, de PAP001 a PAP020. | APROBADO |
| BD-06 | Cuatro categorías | Unión de categorías y productos | Manual y reproducida | 4 categorías, todas usadas, con 5 productos cada una. 0 productos huérfanos. | APROBADO |
| BD-07 | 702 existencias | `SUM(stock)` y huella MD5 de `sku:stock` | Manual y reproducida | 702 unidades. La huella `b10195efffda79fb29d6589b2012b415` es la misma antes y después de los controles. | APROBADO |
| BD-08 | Archivo de respaldo | `pg_restore --list`, sin restaurar | Manual y reproducida | Formato CUSTOM con gzip, 30.552 bytes. 93 entradas de TOC y 89 líneas numeradas, 13 de ellas `TABLE DATA`. Código 0. El SHA-256 `4f6fe10fb820162c6b13ff85d51029d39924599e8a2dbef3cc61f78b6c4362ed` coincide con el del primer corte. | APROBADO |
| BD-09 | Restauración | Consulta del usuario sobre la base restaurada | Manual | 20 productos, 4 categorías y 702 unidades en una base independiente. No se repitió, por indicación expresa. | APROBADO · evidencia del usuario |
| BD-10 | Script SQL | Revisión del usuario y cotejo con `pg_dump --schema-only --no-owner --no-privileges` en memoria | Manual y reproducida | El archivo tiene 15.382 bytes y SHA-256 `634d2b229f1ad2f9f3d68e691e87aef9d8a77ff3344852f31b5737c8c3ef6de3`, sin cambios frente a `HEAD`. Contiene 13 `CREATE TABLE`, 9 índices y 4 claves foráneas. No contiene `INSERT`, `COPY`, roles ni contraseñas: las 7 coincidencias de «password» son la estructura de `password_reset_tokens`. El cotejo con el DDL vivo dio 241 líneas normalizadas en cada lado y 0 diferencias. | APROBADO |

Notas:

- Las incidencias de `intl` y los dos intentos fallidos de autenticación de `pg_dump` se resolvieron antes del primer corte.
- El respaldo permanece fuera de Git, en el perfil local. Desde este corte, `.gitignore` excluye `*.dump`.
- [Arquitectura y procedimientos](BASE_DATOS_POSTGRESQL.md).

## QA en esta ejecución

| ID | Control | Método y resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| QA-01 | Integración Laravel | Clon limpio de la rama sin `.env`, servido con `php -S` en 127.0.0.1:8010 en modo `production`. Antes de servir se comprobó la conexión efectiva: `pgsql`, `kicf_development`, solo lectura, sesión en archivo temporal y caché en memoria. Se espera HTTP 200 en Inicio, Catálogo y Créditos, y 20 detalles con todos sus campos. | `/`, `/catalogo`, `/catalogo/PAP006` y `/creditos-imagenes` responden 200. PAP999 y `/demo/livewire` responden 404, igual que `.env` por recorrido de ruta. Ninguna respuesta expone detalles internos. Los 20 detalles están completos: nombre, SKU, precio COP, categoría, color, material, descripción, existencias e imagen. La huella de productos, stock, `sessions`, `cache`, `users`, `orders` y `order_items` es idéntica antes y después. `laravel.log` está vacío. | APROBADO |
| QA-02 | Imágenes y créditos | Lectura SQL de `image_path` contra `public/images/catalogo` y `config/catalog_images.php`. Se esperan las 20 URL con HTTP 200, MIME de imagen y los mismos bytes que el archivo, y en Edge 20 imágenes reales con créditos completos. | 0 archivos ausentes y 0 SKU sin crédito con autor y licencia. Las 20 URL responden 200 con `image/jpeg` o `image/png` y tamaño exacto. En Edge (R04a, R04b, I01 a I08) hay 20 imágenes, 0 marcadores provisionales y 20 enlaces al detalle de su SKU. `/creditos-imagenes` tiene 20 fichas completas. | APROBADO |
| QA-03 | Búsquedas y filtros | Edge headless por DevTools contra PostgreSQL de solo lectura. Se esperan búsqueda exacta y parcial (también por SKU), filtros individuales y combinados, caracteres literales, validación del rango de precios y limpieza de filtros. | «Cuaderno» devuelve 2 en modo parcial y 0 en exacto; «Cuaderno rayado» y «pap007» devuelven 1 en exacto. Papel devuelve 5; con 3.500–8.000, 4; añadiendo «Cuaderno», 2. Papel + Rojo + Papel y cartón + 8.000 devuelve PAP007; con Azul, PAP006; con Negro, 0 y el mensaje de vacío. `%`, `PAP0_0` y `\` devuelven 0. Un rango inválido muestra el error accesible. Limpiar devuelve 20 productos y el modo Parcial. Las 23 actualizaciones Livewire en escritorio y las 7 en móvil responden HTTP 200, sin recarga. | APROBADO |
| QA-04 | PHPUnit | En un clon limpio, PHPUnit directo y el [bloque exacto del plan](PLAN_PRUEBAS.md#ejecutar-phpunit-en-sqlite-aislado). En la copia de trabajo, cuyo `.env` apunta a PostgreSQL, primero una prueba externa de aislamiento y después `php artisan test --no-ansi --do-not-cache-result`. | Clon limpio: 57 pruebas y 1.238 aserciones en ambos modos; el bloque compiló 17 vistas aisladas. Copia de trabajo: el aislamiento confirmó entorno `testing`, `sqlite` y `:memory:`, `PRAGMA database_list` sin archivo, configuración sin caché y sesión `array`. Resultado: **57 ejecutadas, 57 aprobadas, 0 fallidas, 0 errores, 0 omitidas y 1.238 aserciones**, en 8,5 s de PHPUnit (11 s de reloj), código 0. | APROBADO |
| QA-05 | Responsive y teclado | Edge headless a 390 × 844, 768 × 1024 y 1440 × 900 px. | 1, 2 y 4 columnas sin desbordamiento horizontal. A 390 px el panel de filtros avanzados empieza plegado, el primer producto empieza a 497 px con la foto y el nombre visibles, Tab y Enter abren el panel, que conserva los valores al plegarse, y las opciones de los selectores caben sin recortarse. A 768 y 1440 px los filtros están visibles. Contraste de 6,39 a 13,47:1, sin errores HTTP 5xx ni de JavaScript. **47 controles aprobados, 6 informativos y 0 fallidos.** | APROBADO |
| QA-06 | Sintaxis y estilo | `php -l` en el PHP versionado, excepto las plantillas Blade, y `php vendor/bin/pint --test`. | 42 archivos válidos. Pint `passed`. | APROBADO |
| QA-07 | Dependencias y build | `composer validate --no-check-publish`, `composer check-platform-reqs` y `npm.cmd run build -- --outDir <carpeta temporal> --configLoader runner`. | Composer y plataforma válidos. Vite 8.3.6 generó los manifiestos, con el aviso informativo sobre el paquete opcional `fontaine`. La carpeta temporal se eliminó. | APROBADO |
| QA-08 | Diff y publicación | `git diff --check` y búsqueda de secretos antes del commit. | La única señal de `git diff --check` es `blank-at-eof` en `database/sql/postgresql_schema.sql`, la exportación original, que se conserva byte a byte. Los cambios locales tienen 0 coincidencias de secretos y `.env` queda ignorado. | APROBADO CON OBSERVACIÓN |

El primer corte registró 34 controles de navegador y 14 actualizaciones Livewire con otro guion. Esta revalidación usó el guion de la revisión cruzada de T06, ampliado con datos reales de PostgreSQL: en esta base PAP020 tiene existencias, por lo que se espera «disponible». Los dos cortes coinciden en los resultados funcionales.

Para repetir los controles:

- **Suite:** usa el procedimiento de [SQLite aislado](PLAN_PRUEBAS.md#ejecutar-phpunit-en-sqlite-aislado).
- **HTTP y Livewire sobre PostgreSQL:**
  - Usa temporalmente `PGOPTIONS='-c default_transaction_read_only=on'` y verifica el valor con `SHOW default_transaction_read_only`.
  - Usa `SESSION_DRIVER=file` en una carpeta temporal y `CACHE_STORE=array`; el `.env` de desarrollo usa `database` para ambos.
  - Dirige vistas y cachés de arranque a una carpeta temporal.
  - Sirve solo en loopback.
  - Compara la huella de stock antes y después.

## Alcance pendiente

**T13/P15 académica (T15 técnica) sigue abierta, ahora en curso.** Falta verificar:

- la confirmación transaccional de pedidos;
- la concurrencia de dos compras;
- el bloqueo de filas de inventario;
- la prevención de sobreventa;
- la actualización simultánea del stock;
- el rollback ante errores.

Se verificarán en una instancia exclusiva de pruebas cuando exista el flujo de pedidos de T07. La aprobación formal de la línea base y los estados administrativos se registran aparte, en el [estado de implementación](ESTADO_IMPLEMENTACION.md).
