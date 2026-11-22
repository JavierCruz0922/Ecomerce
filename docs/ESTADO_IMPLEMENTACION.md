# Estado de implementación del catálogo KICF

**Corte 22/11/2026, Bogotá.** La revisión se realizó sobre la línea base de código `00af441afb89df5b4322c4db66e5c15cc1e97722`. La revalidación de las 13:05–13:45 usó la rama `chore/kicf-postgresql-baseline-validation` en `bcc3c9c`, con el mismo código de aplicación.

El [CSV académico](Seguimiento_Tareas_TSP_ampliado.csv) conserva 14 tareas y 74 horas estimadas. T13 es la tarea académica de PostgreSQL; corresponde a P15 y al ID técnico T15. El estado de las tareas distingue implementación, evidencia técnica y aceptación humana.

| Tarea | Estado anterior en el CSV | Estado verificado | Evidencia | Pendiente |
| --- | --- | --- | --- | --- |
| T01/P00–P01 · Línea base | Pendiente | PHP, Composer, conexión de Laravel y regresión comprobados para este corte. | [Validación PostgreSQL y QA](VALIDACION_POSTGRESQL_18_KICF.md) | Acreditar revisión humana y el cierre de T01. |
| T02/P06 · Esquema | Lista para revisión | Esquema integrado y cuatro migraciones aplicadas en PostgreSQL. | [PR #1](https://github.com/JavierCruz0922/Ecomerce/pull/1) · [Esquema](BASE_DATOS_POSTGRESQL.md) | Acreditar revisión cruzada. |
| T03/P07 · Datos del catálogo | Lista para revisión | Seeder integrado. Las 20 filas en PostgreSQL coinciden con el manifiesto. | [PR #3](https://github.com/JavierCruz0922/Ecomerce/pull/3) · [BD-04 a BD-07](VALIDACION_POSTGRESQL_18_KICF.md) | Acreditar revisión cruzada. |
| T04/P08 y P13 · Autenticación y administración | Pendiente | Los flujos siguen pendientes. La prueba parametrizada de usuario acredita persistencia y credenciales del modelo. | [PR #2](https://github.com/JavierCruz0922/Ecomerce/pull/2) | Acreditar dependencias de T02 y T12 antes de P08, y T05 para el cierre de P13. |
| T05/P09 · Catálogo e imágenes | Lista para revisión | Vistas, detalle, originales y créditos integrados. Validación nueva sobre PostgreSQL en la matriz. | [PR #5](https://github.com/JavierCruz0922/Ecomerce/pull/5) · [PR #6](https://github.com/JavierCruz0922/Ecomerce/pull/6) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Acreditar revisión cruzada. La incidencia de imágenes documentada se cerró en desarrollo local. |
| T06/P10 · Búsqueda | Pendiente. Pasó a «Lista para revisión» en este corte, con el PR #7 integrado y su evidencia. | Coincidencias y filtros integrados. Controles funcionales del corte en la matriz. | [PR #7](https://github.com/JavierCruz0922/Ecomerce/pull/7) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Acreditar revisión cruzada. |
| T07/P11 y P12 · Carrito y pedidos | Pendiente | El esquema soporta pedidos. El flujo de carrito y confirmación sigue pendiente. | [Esquema y relaciones](BASE_DATOS_POSTGRESQL.md#esquema-y-datos) | Implementar selección, cantidades, total, revalidación de stock y confirmación. |
| T08/P11 · Interfaz del carrito | Pendiente | La interfaz y los mensajes de stock del carrito siguen pendientes. | [Matriz de aceptación](PLAN_PRUEBAS.md#3-matriz-de-casos-de-aceptación) | Completar T07 y ejecutar las pruebas de interfaz. |
| T09/P14 y P19 · QA | Lista para revisión | PR #2 integra la preparación. La regresión aislada y los controles de navegador sobre PostgreSQL de solo lectura se repitieron en la revalidación: 57/1.238 y 47 controles aprobados. | [PR #2](https://github.com/JavierCruz0922/Ecomerce/pull/2) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Ejecutar la matriz integral R01–R09 y acreditar la revisión. |
| T10/P17 · Sustentación y entrega | Pendiente | El cierre académico sigue pendiente. | [Requisitos y cronograma](condiciones_y_reglas_del_proyecto.md#4-fechas-clave-del-cronograma-4-semanas) | Completar QA, recuperación de evidencia administrativa y entregables. |
| T11/P02 · Propuesta de catálogo | Completado | La fuente de 20 SKU se conserva y el cierre histórico está registrado en el tablero. | [Inventario de atribución](catalogo/FUENTES_IMAGENES.md) · [Seguimiento](Seguimiento_Tareas_TSP_ampliado.csv) | Confirmar el estado vigente del issue si se necesita evidencia administrativa actual. |
| T12/P03 · Livewire | Lista para revisión | Livewire integrado con demo restringida a `local` y `testing`. | [PR #4](https://github.com/JavierCruz0922/Ecomerce/pull/4) | Acreditar revisión y confirmar asignación y semana. |
| T13/P15 · PostgreSQL y concurrencia (T15 técnica) | Pendiente. Pasó a «En curso» en este corte. | **Abierta y en curso.** PostgreSQL 18.6 operativo y revalidado en solo lectura: 13 tablas, catálogo íntegro, respaldo legible, restauración independiente acreditada por el usuario y DDL sin diferencias. | [Validación técnica](VALIDACION_POSTGRESQL_18_KICF.md) · [Issue académico #13](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/13) | Pedidos transaccionales, concurrencia, bloqueos, sobreventa, stock simultáneo y rollback en PostgreSQL de pruebas. |
| T14/P04 y P16 · CI y espejo | Pendiente | Ambos `main` apuntan a `00af441` antes del PR de este corte. GitHub es origen de `main` y GitLab su espejo. | [GitHub](https://github.com/JavierCruz0922/Ecomerce/tree/main) · [GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/tree/main) | CI y revisión cruzada. Sincronizar código solo tras una integración aprobada. A las 13:05 seguían iguales: `main` = `00af441` en ambos remotos; la rama de validación solo existe en GitHub, como corresponde mientras el PR #8 esté en revisión. La API de issues de GitLab rechaza la credencial disponible (HTTP 403). |

## Estado por tipo de evidencia

Estas categorías no son intercambiables:

- **IMPLEMENTADO:** el código existe y hay evidencia técnica.
- **VALIDADO:** la implementación se comprobó con resultado satisfactorio.
- **APROBADO:** consta una aceptación o revisión humana formal.
- **EN PROGRESO:** la implementación es parcial.
- **PENDIENTE:** no hay implementación o la evidencia no basta.
- **BLOQUEADO:** hay un impedimento verificable.

| Estado | Tareas y actividades | Evidencia |
| --- | --- | --- |
| VALIDADO | Implementación, persistencia inicial, respaldo y recuperación de PostgreSQL 18.6: `kicf_development` con 4 migraciones, 13 tablas, 20 productos, 4 categorías y 702 unidades; respaldo CUSTOM; restauración independiente; script SQL. La restauración consta como evidencia del usuario y el resto se reprodujo en solo lectura. | [BD-01 a BD-10](VALIDACION_POSTGRESQL_18_KICF.md#línea-base-y-recuperación) |
| VALIDADO, sin APROBADO | T02/P06, T03/P07, T05/P09, T06/P10 y T12/P03: integradas en `main` y comprobadas sobre PostgreSQL y SQLite. Ninguna tiene revisión formal registrada en GitHub. | [QA-01 a QA-08](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) · PR #1 y #3 a #7 |
| EN PROGRESO | T13/P15 (T15 técnica): la línea base está validada, pero faltan pedidos transaccionales, concurrencia, bloqueo de filas, sobreventa y rollback. T09/P14: la preparación y la regresión existen, pero falta la matriz R01–R09. T01/P00–P01: la línea base está comprobada, pero falta la revisión humana y su cierre. | [Alcance pendiente](VALIDACION_POSTGRESQL_18_KICF.md#alcance-pendiente) |
| PENDIENTE | T04/P08 y P13, T07, T08, T10 y T14 (CI). | [CSV académico](Seguimiento_Tareas_TSP_ampliado.csv) |
| APROBADO | Ninguna tarea tiene una aprobación formal verificable. T11/P02 figura como «Completado» en el tablero por un cierre histórico, pero eso no acredita una aceptación formal, y su estado vigente en GitLab no se pudo confirmar. | [Seguimiento](Seguimiento_Tareas_TSP_ampliado.csv) |

### Bloqueos verificables

- **Issues de GitLab.** La credencial guardada para Git solo permite operaciones de repositorio: la API responde HTTP 403 a `/api/v4/user`. No se pueden leer ni comentar los issues #1 a #14, así que el avance de este corte está pendiente de publicar allí.
- **Revisión cruzada humana.** Los PR #1 a #8 no tienen ninguna revisión formal en GitHub. El plan exige acreditar T02 y T12 antes de iniciar T04/P08.
- **Responsables y semana** de T12, T13 y T14 siguen «Por acordar» en el CSV.

### Próxima actividad

Según las dependencias del CSV, la cadena que permite cerrar T13 es T04 → T07 → T08 → T09 → T13. T13 depende de T09, y las pruebas de concurrencia necesitan el flujo de pedidos de T07.

- **Siguiente actividad técnica:** **T04/P08**, registro e inicio y cierre de sesión. Sus dependencias T02, T12 y T05 están integradas y validadas.
- **Antes de empezar:** registrar la revisión cruzada humana de T02 y T12, como exige el plan. Sin esa acreditación, el siguiente trabajo es la revisión del PR #8 y de los PR #1 a #7.
- **Más adelante:** P13 (administración) también requiere T05. T07 y T08 van después de T04. El cierre de T13 espera al flujo de pedidos.

Las notas históricas `BITACORA_CUMPLIMIENTO_TSP.md`, `TAREAS_AGENTES.md` y `PROMPTS_AGENTES.md` se conservan sin cambios en el stash protegido `0c2df82`. Esta nota es el resumen de estado vigente en la bóveda.
