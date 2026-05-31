# Sistema de Análisis y Clasificación de ECG para Detección de Arritmias

Este sistema consta de dos componentes principales que trabajan de manera conjunta para digitalizar y clasificar señales de electrocardiogramas (ECG) de 12 derivaciones:

1. **Servidor de Inferencia (Backend de IA):** Una API construida con **FastAPI** (Python) que recibe archivos PDF, imágenes o archivos de texto/CSV de ECG, realiza la digitalización de las señales (remoción de cuadrículas y extracción de curvas) y predice posibles arritmias cardiacas utilizando un modelo de aprendizaje profundo (`modelo_arritmias_Fina_v4.keras`).
2. **Plataforma Web (Frontend/Control de Laravel):** Una aplicación web en **Laravel 12** con **Vite (TailwindCSS y Alpine.js)** que permite gestionar expedientes de pacientes, subir ECGs, visualizar las señales digitalizadas y la predicción del modelo, descargar reportes clínicos en PDF y registrar valoraciones de médicos especialistas.

---

## 🛠️ Requisitos Previos del Sistema

Asegúrate de contar con los siguientes requisitos en el entorno donde vayas a desplegar la aplicación:

### 1. Entorno del Modelo de IA (Python)
* **Python == 3.11.x** (Verificado y recomendado en **Python 3.11.9**).
  > [!IMPORTANT]
  > **Compatibilidad de Python y TensorFlow:** TensorFlow es altamente sensible a la versión de Python instalada. La versión **3.11.9** está completamente validada. No se recomienda usar Python 3.12+ ya que algunas librerías como TensorFlow 2.15 requieren adaptaciones complejas para compilar en entornos más nuevos.
* **Poppler** (Herramienta obligatoria para que la librería `pdf2image` pueda convertir las páginas del PDF del ECG en imágenes legibles para el pipeline de procesamiento).
  
#### 📥 Instrucciones de instalación de Poppler por Sistema Operativo:
* **Windows:**
  1. Descarga la versión compilada más reciente para Windows (por ejemplo, desde el repositorio de [oschwartz10612](https://github.com/oschwartz10612/poppler-windows/releases)).
  2. Descomprime el archivo en un directorio permanente (ej. `C:\poppler`).
  3. Agrega la ruta de la carpeta `bin` (ej. `C:\poppler\Library\bin` o `C:\poppler\bin`) a la variable de entorno `PATH` del sistema.
* **macOS:**
  Instala vía Homebrew ejecutando en la terminal:
  ```bash
  brew install poppler
  ```
* **Linux (Ubuntu/Debian):**
  Instala mediante apt:
  ```bash
  sudo apt-get update
  sudo apt-get install poppler-utils
  ```

### 2. Entorno de Base de Datos y Web (Laravel)
* **PHP >= 8.2** con las extensiones comunes habilitadas (`pdo_mysql`, `mbstring`, `openssl`, `xml`, `zip`, `gd`, `ctype`).
* **Composer** (gestor de dependencias de PHP).
* **Node.js (LTS)** y **npm** (para compilar y servir los assets de JavaScript/CSS).
* **MySQL >= 8.0** o **MariaDB** como motor de base de datos.

---

## ⚙️ Configuración y Despliegue del Sistema

Sigue los pasos a continuación para configurar ambos entornos en cualquier ordenador de forma local.

### Paso 1: Configurar el Servidor del Modelo (Python FastAPI)

1. Abre una terminal y colócate en la carpeta `/modelo` del proyecto:
   ```bash
   cd modelo
   ```

2. Crea un entorno virtual para aislar las dependencias:
   ```bash
   # En Windows
   python -m venv venv
   .\venv\Scripts\activate

   # En macOS/Linux
   python3 -m venv venv
   source venv/bin/activate
   ```

3. Instala los paquetes requeridos definidos en `requirements.txt`:
   ```bash
   python -m pip install --upgrade pip
   pip install -r requirements.txt
   ```
   
   > [!IMPORTANT]
   > **Dependencias Pinned (Congeladas):**
   > Las dependencias en `requirements.txt` han sido fijadas a las versiones exactas que han sido probadas en producción (como `tensorflow==2.15.0` y `numpy==1.26.4`). Esto previene el error crítico que ocurre con las versiones de TensorFlow inferiores a 2.16 cuando se intenta utilizar NumPy 2.x, lo cual produce fallos del tipo `AttributeError: module 'numpy' has no attribute 'typeDict'`.

4. **Colocar el Archivo del Modelo:**
   Asegúrate de que el archivo neuronal con el nombre exacto `modelo_arritmias_Fina_v4.keras` se encuentra dentro de la carpeta `modelo/`.

---

### Paso 2: Configurar la Plataforma Web (Laravel 12)

1. En una nueva terminal, colócate en la carpeta `/sistema` del proyecto:
   ```bash
   cd sistema
   ```

2. Crea el archivo de variables de entorno `.env`:
   ```bash
   cp .env.example .env
   ```

3. Genera la clave de seguridad de la aplicación:
   ```bash
   php artisan key:generate
   ```

4. **Configurar el archivo `.env`:**
   Abre el archivo `.env` y edita las siguientes líneas clave con las credenciales de tu base de datos local y la dirección de la API de inferencia:
   ```env
   APP_TIMEZONE=America/Lima

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bd_arritmias
   DB_USERNAME=tu_usuario_mysql
   DB_PASSWORD=tu_contraseña_mysql

   # URL de conexión con el Servidor del Modelo de Python
   ECG_API_URL=http://localhost:8001
   ```

5. **Instalar Dependencias de PHP y Node.js:**
   ```bash
   composer install
   npm install
   ```

6. **Inicializar la Base de Datos:**
   Crea la base de datos `bd_arritmias` en tu servidor de MySQL y luego ejecuta las migraciones de Laravel junto con los seeders iniciales para poblar los datos clínicos base (roles, usuarios de prueba, tipos de identidad, ritmos, etc.):
   ```bash
   php artisan migrate --seed
   ```

7. **Compilar Recursos del Frontend:**
   Compila las hojas de estilo y scripts JS de TailwindCSS utilizando Vite:
   ```bash
   npm run build
   ```

---

## 🚀 Ejecución del Sistema

Para que el sistema funcione normalmente, debes tener encendidos **tanto el servidor de Laravel como el servidor FastAPI de Python** de forma simultánea.

### Terminal A: Levantar el Servidor de Inferencia (Python)
1. Navega a `modelo/` y activa el entorno virtual.
2. Inicia el servidor mediante Uvicorn:
   ```bash
   uvicorn api:app --host 0.0.0.0 --port 8001 --reload
   ```
   *La API REST de predicción estará escuchando peticiones en `http://localhost:8001`.*

### Terminal B: Levantar el Servidor de la Plataforma Web (Laravel)
1. Navega a `sistema/` e inicia el servidor local de desarrollo de PHP:
   ```bash
   php artisan serve
   ```
   *La plataforma web estará accesible en `http://127.0.0.1:8000`.*

### Terminal C: Servidor de Desarrollo Frontend (Opcional)
Si vas a realizar modificaciones estéticas o lógicas en tiempo real sobre las vistas Blade o componentes Alpine.js, inicia el servidor de desarrollo de Vite:
```bash
cd sistema
npm run dev
```

---

## 🧪 Verificación de la Conexión

Una vez que ambos servidores estén encendidos, puedes comprobar la correcta comunicación e inicialización del modelo de la siguiente forma:

1. Ejecuta una petición al endpoint de salud del servidor de Python:
   * **En Windows (PowerShell):**
     ```powershell
     curl.exe -s http://localhost:8001/health
     ```
   * **En macOS/Linux (Bash):**
     ```bash
     curl -s http://localhost:8001/health
     ```

2. Deberías obtener una respuesta JSON confirmando el estado correcto de conexión y la carga del modelo:
   ```json
   {"status":"ok","model":"modelo_arritmias_Fina_v4.keras"}
   ```

Si obtienes esa respuesta, el sistema está completamente configurado y listo para digitalizar y clasificar electrocardiogramas en producción o desarrollo local.
