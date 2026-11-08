# Condiciones y Reglas del Proyecto: Catálogo Virtual

Este documento resume las pautas, instrucciones y condiciones extraídas de las especificaciones iniciales y de la **Metodología WSP** para el desarrollo del Proyecto Colaborativo (Entrega 1, 2 y 3).

## 1. Reglas Generales del Proyecto
- **Metodología:** Desarrollo de Software en Equipos bajo el marco TSP (Team Software Process).
- **Entregas:** 
  - **Entrega 1 (Semana 3):** Planeación, asignación de roles y cronograma.
  - **Entrega 2 (Semana 5):** Avance de desarrollo, reportes de equipo y ajustes.
  - **Entrega 3 (Semanas 7-8):** Consolidación, conclusiones y video de sustentación.
- **Regla de No Participación:** Si un integrante no trabaja, su nombre debe incluirse en la sección "NO PARTICIPARON". Si alguien sube entregas por separado y a la vez de forma grupal, se penaliza con -20% a ese estudiante y -10% al equipo.
- **Formato de Entrega:** Solo una persona sube el archivo PDF nombrado como `TSP_E1_SGXX.pdf` (XX es el número de subgrupo) antes de la fecha límite en el campus.

## 2. Requerimientos del Sistema (Catálogo Virtual)
El producto a desarrollar es un catálogo virtual de productos (categoría a elección) que debe incluir los siguientes módulos funcionales:

1. **Registro e Inicio de Sesión:** Obligatorio para realizar pedidos.
2. **Búsqueda Básica:** Por nombre exacto o parcial del producto.
3. **Búsqueda Avanzada:** Por características o filtros del producto.
4. **Visualización de Artículos:** Debe mostrar cantidad en stock, imágenes y una descripción detallada que se amplíe al hacer clic en la imagen.
5. **Selección de Compras:** Posibilidad de indicar el artículo y la cantidad exacta a comprar.
6. **Carrito de Compras:** Totalización de productos añadidos y cálculo preciso del valor final a pagar.
7. **Población de Datos:** La base de datos debe inicializarse con un mínimo de **20 artículos**.
8. **Módulo de Administrador (Añadido):** Capacidad para gestionar (agregar y eliminar) productos del inventario.
9. **Validación Estricta de Stock (Añadido):** El sistema debe impedir las compras o sumas al carrito que excedan las existencias del inventario registrado.

## 3. Especificaciones Técnicas del Equipo
Debido a que el equipo actual es de **2 integrantes**, se han adaptado los siguientes recursos técnicos y organizativos:

- **Frontend:** Framework Blade integrado con Laravel y TailwindCSS.
- **Backend:** Laravel.
- **Base de Datos:** PostgreSQL para entorno final / SQLite para pruebas rápidas y setup inicial.
- **Control de Versiones:** GitHub (obligatorio Pull Request y revisión cruzada antes de hacer integraciones a la rama principal).
- **Gestión de Tareas:** Trello.
- **Comunicación Principal:** WhatsApp. Se exige reporte de bloqueos inmediatamente y tiempo máximo de respuesta de 12 horas.
- **Organización de Roles (TSPi):** Los 5 roles obligatorios se dividen:
  - *Integrante 1:* Asume Líder de Equipo y Gerente de Desarrollo.
  - *Integrante 2:* Asume Gerente de Planeación, Gerente de Calidad y Proceso, y Gerente de Soporte.

## 4. Fechas Clave del Cronograma (4 Semanas)
- **Inicio de planeación y desarrollo:** 07 de Noviembre de 2026
- **Hito 1 (14/11/2026):** Setup inicial del repositorio, migraciones e inserción de 20 productos base.
- **Hito 2 (21/11/2026):** Sistemas de autenticación (Login/Registro), Panel de administración básico y visualización del catálogo con filtros.
- **Hito 3 (28/11/2026):** Lógica funcional y validación de inventario para el carrito de compras.
- **Entrega Final (05/12/2026):** Ejecución de pruebas de calidad, depuración y grabación del video de sustentación final.