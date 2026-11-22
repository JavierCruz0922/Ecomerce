# Incidencias del catálogo KICF

## PHPUnit en un clon sin clave de aplicación

En el primer SHA del PR #2, la prueba HTTP de Inicio fallaba en una copia limpia sin `.env` ni `APP_KEY` heredada. El commit `1dc5883` añadió a `phpunit.xml` una clave pública exclusiva de pruebas compatible con AES-256. Al repetir en SQLite en memoria, pasaron 26 pruebas y 271 aserciones con salida 0. [Reproducción y corrección](https://github.com/JavierCruz0922/Ecomerce/pull/2).

## Compilación de vistas Blade en PowerShell

Las rutas temporales usadas en el procedimiento aislado impedían compilar vistas en Windows. El PR #3 corrigió la configuración del entorno. El PR #4 la revalidó mediante el procedimiento del plan y registró 38 pruebas y 525 aserciones. [PR #3](https://github.com/JavierCruz0922/Ecomerce/pull/3#issuecomment-7073060462) · [PR #4](https://github.com/JavierCruz0922/Ecomerce/pull/4#issuecomment-7073313506) · [Procedimiento de SQLite aislado](../../PLAN_PRUEBAS.md#ejecutar-phpunit-en-sqlite-aislado).

## Imágenes ausentes en el servidor de desarrollo

**Resuelta el 22/11/2026 en desarrollo local.** El puerto 8000 seguía sirviendo una previsualización temporal cuya raíz pública carecía de los originales versionados. PAP001 devolvía HTTP 404. En el checkout de `main` estaban los 20 archivos. Al usar esa raíz se detectaron además una SQLite sin productos y un manifiesto de paquetes obsoleto que impedía registrar Livewire.

Se verificó un respaldo de SQLite, se cargaron 20 productos y cuatro categorías con `KicfCatalogSeeder` y se inició el servidor desde `main`. Se regeneró el manifiesto con `php artisan package:discover`, se limpiaron las vistas con `php artisan view:clear` y se compiló el frontend. Las demás tablas coincidieron con el respaldo. Los precios y las existencias cargados coincidieron con la fuente.

Edge verificó 20 imágenes y cero marcadores a 1440, 768 y 390 px. Las 20 URL estáticas y las 20 actualizaciones Livewire devolvieron HTTP 200. Se verificaron además el detalle, los créditos y los filtros. La regresión pasó con 57 pruebas y 1.238 aserciones. La recuperación se hizo en el entorno local sobre `00af441`. [Seguimiento del PR #6](https://github.com/JavierCruz0922/Ecomerce/pull/6#issuecomment-7081342321) · [Avances por PR](../03-Desarrollo/Avances-PR-KICF.md).
