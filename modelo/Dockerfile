FROM python:3.10-slim

# Instalar dependencias del sistema necesarias para pdf2image (poppler) y OpenCV
RUN apt-get update && apt-get install -y --no-install-recommends \
    poppler-utils \
    libgl1 \
    libglib2.0-0 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivo de requerimientos e instalar dependencias de Python
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copiar todo el código de la carpeta del modelo
COPY . .

# Definir puerto por defecto
ENV PORT=8000
EXPOSE 8000

# Iniciar la API con uvicorn apuntando al puerto dinámico de Render
CMD uvicorn api:app --host 0.0.0.0 --port ${PORT}
