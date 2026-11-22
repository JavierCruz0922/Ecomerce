# Plan de pruebas

Este plan reúne los controles de regresión y la matriz de aceptación del catálogo. Registra el resultado de cada ejecución en el PR correspondiente. El [PR del esquema](https://github.com/JavierCruz0922/Ecomerce/pull/1) contiene una muestra de evidencia técnica.

Antes de ejecutar pruebas, inspecciona phpunit.xml, la clase TestCase, los proveedores de la aplicación y las variables y caché de configuración que puedan modificar la conexión. Comprueba la conexión efectiva sin imprimir valores sensibles. Usa SQLite en memoria o temporal para pruebas locales y una base PostgreSQL exclusiva de pruebas para T15. No ejecutes migraciones destructivas. Dirige cachés, compilación y registros a ubicaciones aisladas cuando la comprobación sea de solo lectura.

Ejecuta la suite y el build tras cambios relevantes y las auditorías de dependencias cuando la consulta externa esté autorizada. Para Livewire registra renderizado y build. Para autenticación comprueba registro, inicio y cierre de sesión y el acceso al pedido. Prueba también SKU duplicado, devolución ante error, confirmación repetida y conservación de historial de pedidos al retirar productos. En PostgreSQL usa conexiones independientes y verifica que ambas compitan por la última unidad.

## Controles por pull request

Usa la [plantilla de GitHub](../.github/pull_request_template.md). Desde la raíz del repositorio, identifica el SHA probado y la rama base del PR. Revisa el diff final, los cambios preparados y los archivos nuevos antes de compartir evidencia.

Cada cambio funcional debe crear o ampliar pruebas automatizadas. Incluye los casos de regresión aplicables y referencia sus archivos o métodos en el PR. Para cada control, registra el comando, los datos de prueba cuando correspondan, el resultado esperado antes de ejecutar y el resultado observado con su evidencia. En cambios exclusivamente documentales, justifica la ausencia de pruebas automatizadas y registra la validación de enlaces y comandos.

```powershell
git status --short --branch
git rev-parse HEAD
git diff --check
git diff --cached --check
```

| Cambio del PR | Controles que corresponden |
| --- | --- |
| Documentación | `git diff --check` y verificación de enlaces, comandos y referencias. |
| PHP, backend o datos | PHPUnit completo en SQLite aislado y Pint. Añade pruebas del comportamiento modificado. Para migraciones, comprueba reversión y reaplicación en SQLite aislado. Para seeders, usa datos de prueba y demuestra idempotencia. |
| Blade, Livewire, CSS o JavaScript | Build aislado. Verifica el flujo afectado con teclado y en móvil cuando cambie la interfaz. Si hay PHP o Livewire, ejecuta además PHPUnit y Pint. |
| Dependencias de Composer o npm | Validación de Composer y plataforma para cambios PHP. Ejecuta el build y la regresión afectados. Las auditorías externas requieren autorización. |
| Autenticación, administración o carrito | Verifica permisos con invitado, cliente y admin según el flujo. Para carrito y pedidos, prueba cantidades inválidas, límites de stock y rollback. |
| PostgreSQL, CI o sincronización | Ejecuta los escenarios de T13/P15 o T14/P16 en el entorno de pruebas definido por esas tareas. Registra los comandos de la suite, el enlace del pipeline o los SHA comparados. Si falta el destino aprobado, registra NO VERIFICADO. |

## Ejecutar PHPUnit en SQLite aislado

Usa una terminal PowerShell separada desde la raíz del proyecto. El bloque configura variables solo para esa sesión, dirige vistas y cachés a una carpeta temporal y verifica con PDO que SQLite esté en memoria, sin archivo y con claves foráneas activas. La suite corre únicamente si pasa esa comprobación. Cierra esa terminal al terminar.

Para acreditar una ejecución limpia, usa una copia temporal de los archivos versionados del SHA del PR e instala las dependencias con `composer install --no-interaction --prefer-dist --no-progress`. La copia debe empezar sin `.env`, `.env.testing`, base SQLite persistente ni cachés de la copia de trabajo. Comprueba que la terminal de prueba tampoco tenga una `APP_KEY` heredada. PHPUnit obtiene la clave pública de prueba desde `phpunit.xml`; su configuración la limita al proceso de PHPUnit.

```powershell
$ErrorActionPreference = 'Stop'
$proof = Join-Path ([IO.Path]::GetTempPath()) ('ecomerce-pr-tests-' + [guid]::NewGuid())
if ((Split-Path -Qualifier $proof) -ne (Split-Path -Qualifier (Get-Location).Path)) { throw 'Temporary directory must be on the current drive' }
$null = New-Item -ItemType Directory -Path $proof
$absolute = ($proof.Substring(2)).Replace('\','/')
$env:APP_ENV = 'testing'
$env:APP_MAINTENANCE_DRIVER = 'file'
$env:APP_CONFIG_CACHE = "$absolute/config.php"
$env:APP_ROUTES_CACHE = "$absolute/routes.php"
$env:APP_EVENTS_CACHE = "$absolute/events.php"
$env:APP_PACKAGES_CACHE = "$absolute/packages.php"
$env:APP_SERVICES_CACHE = "$absolute/services.php"
$env:LARAVEL_STORAGE_PATH = "$absolute/storage"
$env:VIEW_COMPILED_PATH = "$absolute/storage/framework/views"
$env:LOG_CHANNEL = 'stderr'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = 'null'
$env:DB_FOREIGN_KEYS = 'true'
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'array'
$env:QUEUE_CONNECTION = 'sync'
$env:MAIL_MAILER = 'array'
$env:BROADCAST_CONNECTION = 'null'
foreach ($path in @('storage/framework/views','storage/framework/cache/data','storage/framework/sessions','storage/logs','storage/app/private','storage/app/public')) {
    $null = New-Item -ItemType Directory -Path (Join-Path $proof $path) -Force
}
$result = @'
<?php
try {
    require 'vendor/autoload.php';
    $app = require 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $db = $app->make('db')->connection();
    if (!$app->environment('testing') || $app->configurationIsCached()
        || $db->getDriverName() !== 'sqlite' || $db->getDatabaseName() !== ':memory:' || $db->getConfig('url')
        || !realpath(getenv('VIEW_COMPILED_PATH')) || !realpath(getenv('LARAVEL_STORAGE_PATH'))
        || realpath($app['config']['view.compiled']) !== realpath(getenv('VIEW_COMPILED_PATH'))
        || realpath($app->storagePath()) !== realpath(getenv('LARAVEL_STORAGE_PATH'))) {
        exit(1);
    }
    if ($db->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite'
        || $db->selectOne('PRAGMA database_list')->file !== ''
        || (int) $db->selectOne('PRAGMA foreign_keys')->foreign_keys !== 1) {
        exit(2);
    }
    echo "ISOLATION_OK\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Isolation bootstrap failed: ".get_class($e)."\n");
    exit(3);
}
'@ | php
if ($LASTEXITCODE -ne 0 -or $result -notcontains 'ISOLATION_OK') { throw 'Testing isolation was not confirmed' }
'APROBADO: PDO SQLite :memory:, PRAGMA database_list sin archivo y foreign_keys=1; cache de Laravel y vistas aisladas.'
$evidence = Join-Path $proof 'phpunit.txt'
php vendor/bin/phpunit --do-not-cache-result | Tee-Object -FilePath $evidence
$suiteExit = $LASTEXITCODE
"PHPUnit exit=$suiteExit"
"Evidencia local: $evidence"
if ($suiteExit -ne 0) { throw "PHPUnit failed (exit=$suiteExit)" }
```

Para diagnosticar T02/P06 en particular, después de la misma verificación puedes ejecutar `php vendor/bin/phpunit --do-not-cache-result --filter CatalogSchemaTest`. El PR conserva también el resultado de la suite completa. Consulta los casos aplicables de la [matriz](#3-matriz-de-casos-de-aceptación).

## Prueba parametrizada de usuario nuevo

La [prueba CustomUserTest](../tests/Feature/CustomUserTest.php) admite un nombre y un correo sintéticos por variables de la terminal. Si los omites, usa un nombre de prueba y genera un correo único. La contraseña se genera al ejecutar y se descarta con la conexión en memoria.

En la terminal preparada con el bloque de [SQLite aislado](#ejecutar-phpunit-en-sqlite-aislado), pasa los datos antes de ejecutar la prueba. Cambia el nombre o el correo para repetirla con un caso nuevo.

```powershell
$env:ECOMERCE_TEST_USER_NAME = 'Cliente QA 2026'
$env:ECOMERCE_TEST_USER_EMAIL = 'cliente.qa.2026@example.test'
$pruebaUsuario = Join-Path $proof 'custom-user-phpunit.txt'
php vendor/bin/phpunit --do-not-cache-result --filter CustomUserTest |
    Tee-Object -FilePath $pruebaUsuario
$codigoUsuario = $LASTEXITCODE
"PHPUnit exit=$codigoUsuario"
if ($codigoUsuario -ne 0) { throw "CustomUserTest failed (exit=$codigoUsuario)" }
```

**Resultado esperado.** El correo aún no existe al iniciar. Se crea exactamente un usuario con el nombre y correo indicados. La contraseña queda almacenada como hash y fuera de la serialización del usuario. El proveedor de Laravel acepta la credencial correcta y rechaza una incorrecta. El caso pasa con código de salida 0. La prueba cubre persistencia y validación de credenciales; las rutas de registro, inicio y cierre de sesión se verifican en T04/P08.

## Ejecutar estilo, Composer y build

Para PHP utiliza Pint en modo comprobación. Para dependencias, ejecuta las comprobaciones de Composer. Anota el resultado de cada comando por separado.

```powershell
$cachePint = Join-Path ([IO.Path]::GetTempPath()) ('ecomerce-pint-' + [guid]::NewGuid() + '.cache')
php vendor/bin/pint --test "--cache-file=$cachePint"
composer validate --no-check-publish
composer check-platform-reqs
```

Para el frontend, ejecuta el build sobre una copia temporal del código y las dependencias ya instaladas cuando el entorno deba quedar intacto. Excluye `.env`, bases de datos locales y `.git` de esa copia. Desde la raíz de la copia, escribe en un directorio temporal y comprueba que el manifiesto referencia recursos generados.

```powershell
$salida = Join-Path ([IO.Path]::GetTempPath()) ('ecomerce-vite-' + [guid]::NewGuid())
npm.cmd run build -- --outDir "$salida" --configLoader runner
```

Para CI, enlaza la ejecución del mismo SHA probado. Para PostgreSQL, registra el comando y la conexión efectiva de la suite de T13/P15 antes de operaciones con escritura. Una ejecución en SQLite acredita únicamente la regresión de ese motor.

### T12/P03 · Livewire, Blade y Tailwind

En una copia temporal sin `.env`, aplica los controles de aislamiento anteriores y usa la clave de prueba versionada en `phpunit.xml`. En Windows, asigna rutas con unidad a `LARAVEL_STORAGE_PATH` y `VIEW_COMPILED_PATH` y conserva los valores de `APP_*_CACHE` sin unidad y con barra inicial.

```powershell
php vendor/bin/phpunit --do-not-cache-result --filter LivewireDemoTest
php vendor/bin/phpunit --do-not-cache-result
```

La prueba específica debe devolver HTTP 200 y encontrar el componente y el contador en `0`. Dos incrementos cambian el valor a `1` y `2`. El botón de reinicio lo devuelve a `0`. Ambos comandos deben salir con código `0`.

Para la revisión en navegador, genera los recursos con `npm.cmd run build` en la copia temporal y sirve esa copia en `127.0.0.1`. Usa sesiones de archivo dentro de la carpeta temporal, SQLite `:memory:` y la clave de `phpunit.xml` para el proceso del servidor. Abre `/demo/livewire`, confirma que se aplica Tailwind y que los botones se alcanzan con Tab. Incrementa y reinicia el contador con el teclado. La página debe conservarse cargada y cada interacción debe devolver HTTP 200 en el endpoint de actualización de Livewire. Repite a 390 px de ancho y comprueba que los controles sigan visibles.

## 3. Matriz de casos de aceptación

| ID | Función | Caso de prueba | Resultado esperado |
|---|---|---|---|
| QA-01 | Inicio | Acceder a `/` o ruta inicial acordada | Catálogo, no bienvenida Laravel |
| QA-02 | Productos | Contar registros luego del primer seeder | 20 registros de PAP001 a PAP020 |
| QA-03 | Idempotencia | Ejecutar seeder dos veces en DB efímera | Siguen los 20 SKU únicos y se conservan existencias ya modificadas |
| QA-04 | Imagen | Abrir detalle de cada SKU | Imagen correcta, no enlaces rotos; descripción visible |
| QA-05 | Búsqueda | Nombre exacto | Producto esperado |
| QA-06 | Búsqueda | Subcadena de nombre | Lista correcta, sin inyección SQL |
| QA-07 | Filtros | Categoría, color, material y combinaciones presentes | Solo productos coincidentes; estado vacío útil |
| QA-08 | Selección | Cantidad 1 | Producto añadido con total correcto |
| QA-09 | Carrito | Cantidad 0, -1, decimal o texto | Rechazo con error de validación |
| QA-10 | Carrito | Cantidad mayor que stock | Rechazo, sin alterar stock |
| QA-11 | Carrito | Repetir añadir hasta exceder stock acumulado | Rechazo al superar stock total en carrito |
| QA-12 | Precio | Manipular precio en request | Servidor toma precio desde BD |
| QA-13 | Total | Varios artículos y cantidades | Suma exacta de `cantidad × precio` en COP |
| QA-14 | Auth | Invitado intenta confirmar pedido | Redirección a login o rechazo autorizado |
| QA-15 | Admin | Usuario normal entra por URL o POST de admin | 403 / denegado |
| QA-16 | Admin | Admin crea/elimina producto válido | Cambios permitidos y validados |
| QA-17 | Pedido | Stock cambia entre carrito y confirmación | Revalidación y rechazo o ajuste informado |
| QA-18 | Pedido | Falla transacción a mitad | Rollback; no pedido parcial ni descuento incompleto |
| QA-19 | Concurrencia | Dos pedidos simultáneos de última unidad en PostgreSQL | A lo sumo uno confirma; stock nunca negativo |
| QA-20 | UI | Teléfono y escritorio | Diseño usable, controles accesibles y fotos con texto alternativo |
| QA-21 | Seguridad | Revisar secretos, autorización y archivos a publicar | Sin secretos ni archivos sin derechos en los cambios; autorización verificada |
| QA-22 | Dual remote | `main` local, GitHub, GitLab | SHA idéntico tras integración aprobada |
| QA-23 | Usuario de prueba | Ejecutar `CustomUserTest` con nombre y correo sintéticos nuevos | Usuario persistido, contraseña protegida y validación correcta e incorrecta comprobadas |

## Evidencias

Registra para cada control el comando o script, resultado esperado, resultado obtenido, código de salida, entorno, fecha, SHA probado y enlace de evidencia. Verifica que los logs y capturas que compartas estén libres de credenciales, datos locales y rutas personales. Usa APROBADO, FALLIDO o NO VERIFICADO. Si un control no aplica, registra el motivo. Vincula cada fallo a su tarea y entrega el relevo a A0. Una ejecución SQLite no acredita concurrencia PostgreSQL y los resultados de la validación inicial corresponden a su propia fecha.
