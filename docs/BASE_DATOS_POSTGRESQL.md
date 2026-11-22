# Base de datos PostgreSQL de KICF

## Entorno local

La aplicación Laravel 13 usa PostgreSQL 18.6 en desarrollo y SQLite en memoria para la suite automatizada. PHP 8.5 utiliza `pdo_pgsql` para la conexión. La extensión `intl` se comprobó habilitada.

| Parámetro de desarrollo | Valor |
| --- | --- |
| `APP_ENV` | `local` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `kicf_development` |
| `DB_USERNAME` | `kicf_dev` |

La contraseña se configura en el entorno local. Mantén `.env`, respaldos, bases SQLite y registros con datos sensibles fuera de Git. Antes de ejecutar pruebas comprueba la conexión efectiva, el aislamiento de SQLite y la configuración de caché. [Procedimiento de pruebas](PLAN_PRUEBAS.md#ejecutar-phpunit-en-sqlite-aislado).

## Esquema y datos

Las migraciones Laravel son la fuente de evolución del esquema. `migrate:status` reportó las cuatro en el lote 1:

- `0001_01_01_000000_create_users_table`
- `0001_01_01_000001_create_cache_table`
- `0001_01_01_000002_create_jobs_table`
- `2026_11_21_000001_create_catalog_and_orders_tables`

Las 13 tablas públicas se agrupan por función:

| Función | Tablas |
| --- | --- |
| Usuarios y sesión | `users`, `password_reset_tokens`, `sessions` |
| Caché | `cache`, `cache_locks` |
| Colas | `jobs`, `job_batches`, `failed_jobs` |
| Historial de esquema | `migrations` |
| Catálogo y pedidos | `categories`, `products`, `orders`, `order_items` |

El catálogo relaciona productos con categorías. Los pedidos apuntan a usuarios y sus líneas a pedidos y productos. Las líneas conservan el SKU, el nombre y el precio de compra. Las claves foráneas protegen categorías, usuarios y productos referenciados. La eliminación de un pedido se propaga a sus líneas. El SKU y el par pedido/producto son únicos. Las restricciones comprueban precio positivo, stock no negativo y cantidad positiva. La inspección de metadatos devolvió cuatro claves foráneas y 27 índices públicos, incluidos los de claves y unicidad. [Migración del catálogo y pedidos](../database/migrations/2026_11_21_000001_create_catalog_and_orders_tables.php).

El seeder `KicfCatalogSeeder` lee el manifiesto versionado. El corte verificó 20 productos, 20 SKU únicos, cuatro categorías usadas, 702 unidades, cero referencias de categoría huérfanas y cero campos de imagen vacíos. Los nueve campos de las 20 filas coincidieron con el JSON, incluidos precios, existencias y nombres de imagen. [Seeder](../database/seeders/KicfCatalogSeeder.php) · [Manifiesto](../database/data/kicf-catalog.json).

La [fotografía SQL de la línea base](../database/sql/postgresql_schema.sql) se generó con PostgreSQL 18.6. Contiene DDL de las 13 tablas, secuencias, índices y restricciones. Se cotejó con una salida en memoria de `pg_dump --schema-only --no-owner --no-privileges`. Las sentencias coincidieron al omitir comentarios, espacios al margen y los marcadores aleatorios de `psql`. Su SHA-256 en el archivo local es `634d2b229f1ad2f9f3d68e691e87aef9d8a77ff3344852f31b5737c8c3ef6de3`.

## Respaldo y recuperación

El respaldo completo de estructura y datos queda fuera del repositorio, en la carpeta `KICF-Backups` del perfil local. La evidencia de este corte corresponde a `kicf_development_20261122_123422.dump`, formato CUSTOM con gzip. `pg_restore --list` salió con código 0. La cabecera reportó 93 entradas de índice y el listado imprimió 89 filas numeradas. El usuario realizó la restauración en una base independiente y obtuvo 20 productos, cuatro categorías y 702 unidades. [Matriz de evidencias](VALIDACION_POSTGRESQL_18_KICF.md).

Para respaldos futuros, confirma el origen antes de ejecutar `pg_dump --format=custom --compress=gzip --file <ruta-externa> --dbname <base-origen>` con el host y el usuario autorizados. Usa el aviso interactivo de PostgreSQL o el mecanismo de credenciales aprobado por el equipo. Comprueba el código de salida, el listado con `pg_restore --list <respaldo>` y la huella SHA-256. Guarda el archivo con acceso restringido.

Como política recomendada para desarrollo, guarda un respaldo antes de cambios de esquema o importaciones relevantes y después de hitos de datos. Mantén copias fechadas fuera del repositorio y verifica periódicamente una restauración independiente. Acordar retención y almacenamiento con los responsables del entorno forma parte de la operación del proyecto. [Documentación de pg_dump](https://www.postgresql.org/docs/18/app-pgdump.html).

Ante un fallo, identifica el incidente y el último respaldo válido. Verifica su huella y listado. Usa una base destino independiente, vacía y autorizada para ensayar `pg_restore --exit-on-error --single-transaction --dbname <base-independiente> <respaldo>`. Tras la carga, compara migraciones, tablas, SKU, categorías y stock con el respaldo. Cualquier cambio de conexión o recuperación sobre un entorno existente requiere un plan aprobado. Conserva la evidencia y los respaldos durante la investigación. [Documentación de pg_restore](https://www.postgresql.org/docs/18/app-pgrestore.html).

La cobertura de pedidos transaccionales, bloqueos de inventario, actualización simultánea, prevención de sobreventa y rollback permanece abierta en **T13/P15 académica**, identificada como **T15** en el plan técnico. [Seguimiento operativo](ESTADO_IMPLEMENTACION.md).
