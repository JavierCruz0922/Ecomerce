<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
# Instrucciones adicionales del proyecto Ecomerce

## 1. Documentación en Obsidian

La bóveda de Obsidian está ubicada en:

`docs/Ecomerce/`

El documento principal de requisitos es:

`docs/Ecomerce/condiciones_y_reglas_del_proyecto.md`

Antes de implementar funcionalidades, consultar este documento y respetar sus requisitos.

## 2. Arquitectura tecnológica

- Backend: Laravel y PHP.
- Frontend: Blade y Tailwind CSS.
- Base de datos final: PostgreSQL.
- Base de datos para pruebas iniciales: SQLite.
- Control de versiones: Git y GitHub.
- Documentación técnica: Obsidian y Markdown.

## 3. Reglas de desarrollo

1. Analizar los requisitos antes de modificar el código.
2. Respetar las convenciones de Laravel.
3. Implementar funcionalidades de manera modular.
4. Incorporar validaciones y controles de seguridad.
5. Crear pruebas para las funcionalidades desarrolladas.
6. No modificar los requisitos sin autorización.
7. No ejecutar migraciones destructivas sin autorización.
8. No modificar credenciales ni archivos de configuración sensibles sin autorización.

## 4. Documentación automática

Después de cada cambio relevante:

- Actualizar la documentación técnica correspondiente.
- Documentar las decisiones de arquitectura.
- Registrar los cambios realizados en la base de datos.
- Documentar los errores importantes y sus soluciones.
- Mantener los documentos Markdown compatibles con Obsidian.

## 5. Organización de documentación

- `docs/Ecomerce/01-Arquitectura/`
- `docs/Ecomerce/02-Base-de-Datos/`
- `docs/Ecomerce/03-Desarrollo/`
- `docs/Ecomerce/04-Decisiones/`
- `docs/Ecomerce/05-Pruebas/`
- `docs/Ecomerce/06-Errores/`

## 6. Funcionalidades obligatorias

El sistema debe implementar:

1. Registro e inicio de sesión.
2. Catálogo de productos.
3. Búsqueda básica y avanzada.
4. Visualización de productos con imágenes y disponibilidad.
5. Carrito de compras.
6. Validación estricta de inventario.
7. Panel de administración de productos.
8. Inicialización de la base de datos con al menos 20 productos.

## 7. Control de versiones

- Trabajar mediante ramas de desarrollo.
- Utilizar Pull Requests para integrar cambios.
- Realizar revisión cruzada antes de integrar a la rama principal.
- No realizar commits ni push sin autorización.
- No publicar archivos `.env`, credenciales o información sensible.

## 8. Metodología

Aplicar la metodología TSP definida en el documento principal de requisitos.

Respetar los roles del equipo, los entregables y el cronograma del proyecto.