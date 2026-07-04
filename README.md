# Sistema de Análisis de ECG con Inteligencia Artificial

Este proyecto combina un sistema web desarrollado en Laravel con un modelo de predicción basado en Python (CNN-LSTM) para el análisis automatizado de electrocardiogramas (ECG).

---

## ⚠️ REQUISITOS CRÍTICOS DEL SISTEMA (INSTALACIÓN OBLIGATORIA)

Para que el sistema no falle al procesar los PDFs, **debes instalar y configurar estas herramientas antes de levantar el proyecto**.

### 1. Extensión Imagick para PHP
El sistema utiliza Imagick para leer y manipular imágenes iniciales.
**Guía para Windows (XAMPP):**
1. Descarga el archivo `php_imagick.dll` correspondiente a tu versión de PHP y arquitectura desde [PECL Imagick Windows](https://pecl.php.net/package/imagick).
2. Copia el archivo `php_imagick.dll` a tu carpeta de extensiones: `C:\xampp\php\ext\`.
3. **CRÍTICO:** Copia **todos los demás archivos `.dll`** (que empiezan con `CORE_RL_` y `IM_MOD_RL_`) que vienen en el `.zip` descargado, y pégalos directamente en la carpeta raíz de PHP: `C:\xampp\php\`. *(Si no lo haces, `php artisan serve` te dará error).*
4. Abre `C:\xampp\php\php.ini`, agrega la línea `extension=imagick` al final y reinicia XAMPP.

### 2. Poppler para Python (`pdf2image`)
El script de IA depende de Poppler para digitalizar el PDF.
1. Descarga Poppler precompilado para Windows (ej. [Release-24.02.0-0.zip](https://github.com/oschwartz10612/poppler-windows/releases/download/v24.02.0-0/Release-24.02.0-0.zip)).
2. Descomprímelo en tu disco (ej: `C:\xampp\poppler-24.02.0`).
3. En el archivo `modelo/pipeline_unificado.py`, verifica que la ruta apunte correctamente a la carpeta `bin` de Poppler:
   `convert_from_path(pdf_path, dpi=dpi, poppler_path=r'C:\xampp\poppler-24.02.0\Library\bin')`

### 3. Dependencias del Modelo Python
Entra a la carpeta `modelo/`, crea tu entorno e instala las librerías:
```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
```

---

## CREAR ARCHIVO .env TOMAR EL .env.example Y CONFIGURAR CONEXIONES

Entra a la carpeta `sistema/` y crea o edita tu archivo `.env`:

```env
APP_NAME="ECG Analizacion"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_arritmias
DB_USERNAME=root
DB_PASSWORD=admin

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

JWT_SECRET=
```

---

## COMANDO EJECUTAR

Para instalar todas las librerías necesarias del backend y frontend (dentro de la carpeta `sistema/`):

```bash
composer install

composer update
```

---

## GENERAR CLAVE
Genera la llave de seguridad de la aplicación Laravel:
```bash
php artisan key:generate
```

---

## GENERAR CLAVE JWT
Genera la llave secreta para la autenticación de la API:
```bash
php artisan jwt:secret
```

---

## OTROS COMANDOS IMPORTANTES

Para que las imágenes y reportes PDF se puedan ver en la web:
```bash
php artisan storage:link
```

Para crear las tablas en la base de datos:
```bash
php artisan migrate
```

Para encender el servidor:
```bash
php artisan serve
```

---

## BASE DE DATOS EN DIRECTORIO
_La estructura o respaldos SQL de este proyecto se encuentran en:_ `(Colocar ruta aquí si aplica)`

---

## COLECCION DE APIS PRUEBAS EN DIRECTORIO
_Las colecciones de Postman para probar las rutas de la API se encuentran en:_ `(Colocar ruta aquí si aplica)`
