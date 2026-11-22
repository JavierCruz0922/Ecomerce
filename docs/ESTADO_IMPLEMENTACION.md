# Estado de implementación del catálogo KICF

**Corte 22/11/2026, Bogotá.** La revisión se realizó sobre la línea base de código `00af441afb89df5b4322c4db66e5c15cc1e97722`. El [CSV académico](Seguimiento_Tareas_TSP_ampliado.csv) conserva 14 tareas y 74 horas estimadas. T13 es la tarea académica de PostgreSQL y corresponde a P15 y al ID técnico T15. El estado de las tareas distingue implementación, evidencia técnica y aceptación humana.

| Tarea | Estado anterior en el CSV | Estado verificado | Evidencia | Pendiente |
| --- | --- | --- | --- | --- |
| T01/P00–P01 · Línea base | Pendiente | PHP, Composer, conexión de Laravel y regresión comprobados para este corte. | [Validación PostgreSQL y QA](VALIDACION_POSTGRESQL_18_KICF.md) | Acreditar revisión humana y el cierre de T01. |
| T02/P06 · Esquema | Lista para revisión | Esquema integrado y cuatro migraciones aplicadas en PostgreSQL. | [PR #1](https://github.com/JavierCruz0922/Ecomerce/pull/1) · [Esquema](BASE_DATOS_POSTGRESQL.md) | Acreditar revisión cruzada. |
| T03/P07 · Datos del catálogo | Lista para revisión | Seeder integrado. Las 20 filas en PostgreSQL coinciden con el manifiesto. | [PR #3](https://github.com/JavierCruz0922/Ecomerce/pull/3) · [BD-04 a BD-07](VALIDACION_POSTGRESQL_18_KICF.md) | Acreditar revisión cruzada. |
| T04/P08 y P13 · Autenticación y administración | Pendiente | Los flujos siguen pendientes. La prueba parametrizada de usuario acredita persistencia y credenciales del modelo. | [PR #2](https://github.com/JavierCruz0922/Ecomerce/pull/2) | Acreditar dependencias de T02 y T12 antes de P08, y T05 para el cierre de P13. |
| T05/P09 · Catálogo e imágenes | Lista para revisión | Vistas, detalle, originales y créditos integrados. Validación nueva sobre PostgreSQL en la matriz. | [PR #5](https://github.com/JavierCruz0922/Ecomerce/pull/5) · [PR #6](https://github.com/JavierCruz0922/Ecomerce/pull/6) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Acreditar revisión cruzada. La incidencia de imágenes documentada se cerró en desarrollo local. |
| T06/P10 · Búsqueda | Lista para revisión | Coincidencias y filtros integrados. Controles funcionales del corte en la matriz. | [PR #7](https://github.com/JavierCruz0922/Ecomerce/pull/7) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Acreditar revisión cruzada. |
| T07/P11 y P12 · Carrito y pedidos | Pendiente | El esquema soporta pedidos. El flujo de carrito y confirmación sigue pendiente. | [Esquema y relaciones](BASE_DATOS_POSTGRESQL.md#esquema-y-datos) | Implementar selección, cantidades, total, revalidación de stock y confirmación. |
| T08/P11 · Interfaz del carrito | Pendiente | La interfaz y los mensajes de stock del carrito siguen pendientes. | [Matriz de aceptación](PLAN_PRUEBAS.md#3-matriz-de-casos-de-aceptación) | Completar T07 y ejecutar las pruebas de interfaz. |
| T09/P14 y P19 · QA | Lista para revisión | PR #2 integra la preparación. Se ejecutó una nueva regresión aislada del código base. | [PR #2](https://github.com/JavierCruz0922/Ecomerce/pull/2) · [QA](VALIDACION_POSTGRESQL_18_KICF.md#qa-en-esta-ejecución) | Ejecutar la matriz integral R01–R09 y acreditar la revisión. |
| T10/P17 · Sustentación y entrega | Pendiente | El cierre académico sigue pendiente. | [Requisitos y cronograma](condiciones_y_reglas_del_proyecto.md#4-fechas-clave-del-cronograma-4-semanas) | Completar QA, recuperación de evidencia administrativa y entregables. |
| T11/P02 · Propuesta de catálogo | Completado | La fuente de 20 SKU se conserva y el cierre histórico está registrado en el tablero. | [Inventario de atribución](catalogo/FUENTES_IMAGENES.md) · [Seguimiento](Seguimiento_Tareas_TSP_ampliado.csv) | Confirmar el estado vigente del issue si se necesita evidencia administrativa actual. |
| T12/P03 · Livewire | Lista para revisión | Livewire integrado con demo restringida a `local` y `testing`. | [PR #4](https://github.com/JavierCruz0922/Ecomerce/pull/4) | Acreditar revisión y confirmar asignación y semana. |
| T13/P15 · PostgreSQL y concurrencia (T15 técnica) | Pendiente | **Abierta con avance.** PostgreSQL 18.6 operativo, 13 tablas, catálogo íntegro, respaldo localizado, restauración independiente documentada y esquema comparado. | [Validación técnica](VALIDACION_POSTGRESQL_18_KICF.md) · [Issue académico #13](https://gitlab.com/javiercruz0922/ecomerce/-/work_items/13) | Pedidos transaccionales, concurrencia, bloqueos, sobreventa, stock simultáneo y rollback en PostgreSQL de pruebas. |
| T14/P04 y P16 · CI y espejo | Pendiente | Ambos `main` apuntan a `00af441` antes del PR de este corte. GitHub es origen de `main` y GitLab su espejo. | [GitHub](https://github.com/JavierCruz0922/Ecomerce/tree/main) · [GitLab](https://gitlab.com/javiercruz0922/ecomerce/-/tree/main) | CI y revisión cruzada. Sincronizar código solamente tras integración aprobada. La API de issues no pudo verificarse con el alcance disponible. |

## Completado técnicamente

Conexión y esquema de la línea base PostgreSQL, comprobación de integridad del catálogo, inventario del respaldo y cotejo del DDL. Las funciones integradas y sus resultados de QA están referidos por PR o por control. La restauración ejecutada por el usuario consta como evidencia histórica.

## En progreso

Aceptación humana de la línea base de T01 y de los PR integrados, verificación de estados actuales de GitLab, matriz funcional y pruebas posteriores de T13/P15. Los documentos históricos `BITACORA_CUMPLIMIENTO_TSP.md` y `TAREAS_AGENTES.md` se conservan en el archivo Git del proyecto. El presente resumen es la nota vigente de estado en la bóveda.

## Pendiente

T04/P08 es la siguiente tarea funcional una vez acreditadas sus dependencias. El flujo de administración P13, carrito, pedidos, stock concurrente y entrega quedan ordenados por el [tablero académico](Seguimiento_Tareas_TSP_ampliado.csv). El cierre técnico de PostgreSQL no acredita las dependencias humanas por sí solo.
