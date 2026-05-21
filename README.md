# Sistema de Análisis y Clasificación de ECG para Detección de Arritmias

Este sistema consta de dos componentes principales:
1. **Modelo de IA (Backend de Inferencia):** Una API construida con **FastAPI** (Python) que procesa imágenes y archivos PDF de electrocardiogramas (ECG) de 12 derivaciones, realiza la digitalización de las señales y predice posibles arritmias utilizando un modelo de aprendizaje profundo (`.keras`).
2. **Plataforma Web (Frontend/Backend de Control):** Una aplicación web en **Laravel 12** con **Vite (TailwindCSS v4 y Alpine.js)** que permite a los usuarios gestionar pacientes, subir y visualizar análisis de ECG, descargar reportes y estadísticas, y registrar valoraciones de confirmación médica.

---

## 🛠️ Requisitos Previos

Asegúrate de tener instalados los siguientes componentes en tu sistema:

* **Para el Servidor Web (Laravel):**
  * **PHP >= 8.2**
  * **Composer**
  * **Node.js (LTS recomendado)** y **npm**
  * **MySQL** o **MariaDB** (u otro motor de base de datos compatible)

* **Para el Servidor del Modelo (Python):**
  * **Python >= 3.9** y **pip**
  * **Poppler** (Obligatorio para la conversión de PDF a imágenes mediante la librería `pdf2image` de Python).
    * *En Windows:* Descarga Poppler (por ejemplo, desde [GitHub de @oschwartz10612](https://github.com/oschwartz10612/poppler-windows/releases)), descomprímelo y añade la carpeta `bin` a las variables de entorno del sistema (`PATH`).

---

## ⚙️ Configuración del Sistema

### 1. Base de Datos (MySQL)
1. Inicia tu servidor MySQL.
2. Crea una base de datos llamada `bd_arritmias`:
   ```sql
   CREATE DATABASE bd_arritmias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importa el archivo de base de datos con la estructura básica de tablas y disparadores (*triggers*):
   * El script SQL se encuentra en: `sistema/database/bd_arritmias_mysql.sql`
   * Puedes importarlo mediante la consola de MySQL o tu gestor de base de datos preferido (phpMyAdmin, DBeaver, etc.):
     ```bash
     mysql -u tu_usuario -p bd_arritmias < sistema/database/bd_arritmias_mysql.sql
     ```

### 2. Configuración del Servidor del Modelo (Python)
1. Navega a la carpeta del modelo:
   ```bash
   cd modelo
   ```
2. (Recomendado) Crea e inicia un entorno virtual:
   ```bash
   # En Windows
   python -m venv venv
   .\venv\Scripts\activate

   # En macOS/Linux
   python3 -m venv venv
   source venv/bin/activate
   ```
3. Instala las dependencias necesarias indicadas en `requirements.txt`:
   ```bash
   pip install -r requirements.txt
   ```

### 3. Configuración del Proyecto Web (Laravel)
1. Navega a la carpeta del sistema Laravel:
   ```bash
   cd sistema
   ```
2. Copia el archivo de configuración de entorno y genera la clave de aplicación:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Abre el archivo `.env` y edita las siguientes variables clave según tus credenciales locales:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bd_arritmias
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contrasena

   # Dirección en la que correrá el backend de Python
   ECG_API_URL=http://localhost:8001
   ```
4. Instala las dependencias de PHP y JavaScript compilando los recursos iniciales:
   ```bash
   composer install
   npm install
   npm run build
   ```

---

## 🚀 Ejecución del Sistema

Para arrancar el sistema en modo de desarrollo, debes iniciar **ambos servidores** al mismo tiempo:

### Opción A: Ejecución Manual Paso a Paso (Recomendado)

#### Paso 1: Iniciar el Servidor de Inferencia (Python FastAPI)
1. Abre una terminal en la raíz del proyecto.
2. Navega a la carpeta del modelo e inicia la API con Uvicorn:
   ```bash
   cd modelo
   # (Asegúrate de tener el entorno virtual activo)
   uvicorn api:app --host 0.0.0.0 --port 8001 --reload
   ```
   *El servidor API estará disponible en `http://localhost:8001`.*

#### Paso 2: Iniciar la Aplicación Web (Laravel & Vite)
1. Abre otra terminal independiente en la raíz del proyecto.
2. Navega a la carpeta `sistema/` y arranca los servidores de desarrollo de Laravel y Vite:
   ```bash
   cd sistema
   # Consola de Laravel
   php artisan serve
   
   # Abre otra consola en la carpeta 'sistema' e inicia el compilador Vite
   npm run dev
   ```
   *La aplicación web estará disponible en `http://127.0.0.1:8000` (o el puerto que indique Laravel).*

---

### Opción B: Ejecución Concurrente Rápida (Solo Laravel)
Si tienes configurado el entorno y deseas ejecutar todo el backend de Laravel (servidor, cola de procesos, visor de logs y Vite) con un único comando:
1. Navega a la carpeta `sistema/`:
   ```bash
   cd sistema
   ```
2. Ejecuta el script configurado en Composer:
   ```bash
   composer run dev
   ```
   *Nota: Recuerda que aún debes levantar el Servidor de Inferencia (Python FastAPI) en una consola aparte.*

---

## 📁 Estructura del Proyecto

* **`/modelo`**: Contiene la lógica en Python.
  * `api.py`: Servidor FastAPI REST expone los endpoints `/predict` y `/preview`.
  * `pipeline_unificado.py`: Controla la carga de PDFs/imágenes, extracción de señal, remoción de cuadrícula y digitalización.
  * `modelo_arritmias_5seg.keras`: Archivo del modelo neuronal entrenado para clasificar ECG.
  * `rois_derivaciones.json`: Coordenadas de calibración para las 12 derivaciones estándar de ECG.
* **`/sistema`**: Contiene la aplicación web Laravel.
  * `app/Http/Controllers/`: Controladores para Autenticación, Dashboard, Historial, Reportes y Subida de archivos.
  * `database/bd_arritmias_mysql.sql`: Estructura e inserciones iniciales para la base de datos MySQL.
  * `resources/views/`: Interfaces HTML del sistema (autenticación, vistas de análisis, resúmenes interactivos).
