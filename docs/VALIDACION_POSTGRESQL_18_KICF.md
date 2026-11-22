# Validación de PostgreSQL 18 y QA del catálogo KICF

**Fecha** 22/11/2026, Bogotá. **Código probado** `00af441afb89df5b4322c4db66e5c15cc1e97722`. La evidencia original de migración, carga inicial y recuperación fue aportada por el usuario. Los controles nuevos sobre PostgreSQL se limitaron a lectura. La suite automatizada usó SQLite en memoria.

## Línea base y recuperación

| ID | Control | Evidencia y procedencia | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| BD-01 | Conexión Laravel | Usuario y nueva ejecución de `php artisan env` y `php artisan db:show` | `local`, PostgreSQL 18.6, `pgsql`, `kicf_development` en loopback. `intl` habilitada. | APROBADO |
| BD-02 | Migraciones | Usuario ejecutó las migraciones. Nueva lectura con `php artisan migrate:status`. | Las cuatro migraciones aplicadas en el lote 1. | APROBADO |
| BD-03 | Estructura e integridad | Usuario y nueva inspección de `pg_tables`, `pg_indexes` y `pg_constraint`. | 13 tablas, 27 índices y cuatro claves foráneas. Se verificaron restricciones de catálogo y líneas de pedido. | APROBADO |
| BD-04 | Productos | Carga inicial del usuario. Nueva comparación de nueve campos por SKU en transacción de solo lectura contra el JSON. | 20 productos activos. Cero diferencias en el manifiesto y cero nombres de imagen vacíos. | APROBADO |
| BD-05 | SKU | Usuario y nuevo `COUNT(DISTINCT sku)`. | 20 SKU únicos, PAP001–PAP020. | APROBADO |
| BD-06 | Categorías | Usuario y nueva consulta de categorías con unión desde productos. | Cuatro categorías, todas usadas, con cero productos huérfanos. | APROBADO |
| BD-07 | Inventario | Usuario y nuevo `SUM(stock)`. Comparación de stock por SKU antes y después de los controles Livewire. | 702 unidades. Las existencias coincidieron en los 20 SKU antes y después. | APROBADO |
| BD-08 | Respaldo | Usuario generó el archivo. Nueva lectura con `pg_restore --list`. | CUSTOM/gzip, 30.552 bytes. Cabecera de 93 entradas de TOC y 89 líneas numeradas impresas. Salida 0. SHA-256 `4f6fe10fb820162c6b13ff85d51029d39924599e8a2dbef3cc61f78b6c4362ed`. | APROBADO |
| BD-09 | Restauración | Ejecución y comprobación manual del usuario en una base independiente. | 20 productos, cuatro categorías y 702 unidades tras recuperar. | APROBADO · evidencia del usuario |
| BD-10 | Script SQL | Archivo original revisado por el usuario y cotejado con DDL consultado con `pg_dump --schema-only --no-owner --no-privileges`. | 15.382 bytes, 13 tablas y DDL equivalente al esquema activo. Se comprobaron índices, restricciones y ausencia de sentencias de carga de datos, roles o contraseñas. | APROBADO |

Las incidencias de `intl` y los dos intentos de autenticación de `pg_dump` quedaron resueltos antes de este corte. El respaldo permanece fuera de Git. El cotejo de DDL utilizó la salida en memoria de `pg_dump`. La restauración, migración y carga ya acreditadas sobre PostgreSQL no se repitieron. [Arquitectura y procedimientos](BASE_DATOS_POSTGRESQL.md).

## QA en esta ejecución

| ID | Control | Método y resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| QA-01 | Laravel y catálogo | Peticiones HTTP a Inicio, Catálogo y detalles mediante un servidor PHP temporal en loopback y sesión PostgreSQL de solo lectura. Esperado HTTP 200, 20 fichas y detalle con SKU, categoría, precio COP, descripción y stock. | Inicio y Catálogo HTTP 200. 20 fichas, 20 detalles válidos y HTTP 404 para SKU inexistente. | APROBADO |
| QA-02 | Imágenes y créditos | Consultar todas las URLs y cotejar código, MIME, bytes y atribución. Esperadas 20 imágenes reales y sus créditos. | 20/20 archivos HTTP 200 con MIME y bytes esperados, 20 pares imagen/detalle y 20 entradas de atribución. Cero marcadores en la cuadrícula. | APROBADO |
| QA-03 | Búsqueda y filtros | Edge headless y escenarios adicionales con `Livewire::test(ProductCatalog::class)` usando la conexión PostgreSQL de solo lectura. Esperados exacto/parcial, SKU, filtros individuales y combinados, caracteres literales, error de rango y restablecimiento. | Cuaderno parcial 2 y exacto 0. Nombre completo y SKU PAP007 devolvieron 1. Papel 5, Rojo 1, Papel y cartón 2, 8.000–8.000 COP 2. La combinación devolvió PAP007. El rango inválido mostró un aviso y mantuvo el resultado. `%`, `_` y barra invertida devolvieron 0 en este catálogo y se comprobaron sus parámetros SQL literales. Limpiar devolvió 20. | APROBADO |
| QA-04 | Regresión | Comprobar PDO, `PRAGMA database_list`, claves foráneas, conexión efectiva `sqlite :memory:` y ausencia de caché de configuración. Ejecutar `php artisan test --no-ansi --do-not-cache-result`. | 57 aprobadas, 0 fallidas, 0 omitidas. 1.238 aserciones. PHPUnit 9.912 ms y 15.194 ms de pared. Salida 0. | APROBADO |
| QA-05 | Responsive y teclado | Edge headless mediante DevTools, con el nuevo build servido desde una carpeta temporal. Esperadas 1, 2 y 4 columnas, sin desbordamiento horizontal. En móvil comprobar primer producto, Tab, Enter y estado expandido. | 390 × 844, 768 × 1024 y 1440 × 900 pasaron. A 390 px se vieron foto y título iniciales. Tab alcanzó el panel y Enter lo abrió y cerró. 34 controles HTTP y de navegador aprobados, 14 actualizaciones Livewire HTTP 200 y cero excepciones JavaScript observadas. | APROBADO |
| QA-06 | Sintaxis y estilo | `php -l` en PHP versionado, salvo plantillas Blade. `php vendor/bin/pint --test` con caché temporal. | 42 archivos de PHP válidos. Pint pasó. | APROBADO |
| QA-07 | Dependencias y build | `composer validate --no-check-publish`, `composer check-platform-reqs` y `npm.cmd run build -- --outDir <directorio-temporal> --configLoader runner`. | Composer y plataforma válidos. Vite 8.3.6 generó ambos manifiestos y los recursos. Aviso informativo sobre `fontaine` opcional. | APROBADO |
| QA-08 | Diff y publicación | Revisar lista de archivos y `git diff --cached --check` antes del commit. | La exportación SQL original contiene una línea en blanco final que señala la comprobación estándar. Se conserva el archivo revisado byte a byte. Aplicando la excepción `blank-at-eof` solo a ese archivo, el control documental pasa. | APROBADO CON OBSERVACIÓN |

Para repetir los controles, usa el procedimiento de [SQLite aislado](PLAN_PRUEBAS.md#ejecutar-phpunit-en-sqlite-aislado) para la suite. Para HTTP y Livewire, usa temporalmente `PGOPTIONS='-c default_transaction_read_only=on'`, verifica el valor con `SHOW default_transaction_read_only` y dirige caché, vistas y sesión a una carpeta temporal o a memoria. Sirve solo en loopback. Compara los recuentos de QA-03 y el contenido de los 20 SKU con el [manifiesto](../database/data/kicf-catalog.json). Los controles en Edge se registraron mediante respuestas HTTP y medidas calculadas del DOM.

## Alcance pendiente

**T13/P15 académica, T15 técnica, continúa abierta con avance.** Quedan pendientes la confirmación transaccional de pedidos, concurrencia de dos compras, bloqueo de inventario, prevención de sobreventa, actualización simultánea del stock y rollback. Deben verificarse en una instancia exclusiva de pruebas, después de contar con el flujo de pedidos. La aprobación formal de la línea base y los estados administrativos se registran aparte. [Estado de implementación](ESTADO_IMPLEMENTACION.md).
