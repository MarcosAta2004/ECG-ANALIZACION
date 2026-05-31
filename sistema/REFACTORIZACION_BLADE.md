# Refactorizacion de Vistas Blade

## Alcance
- No se tocaron controladores ni modelos.
- Se refactorizo solo la capa de presentacion en `resources/views/`, `resources/js/` y `resources/css/`.

## Que se corrigio

### JavaScript embebido
- Se extrajo la logica de pagina a `resources/js/pages/`.
- Nuevos modulos:
  - `resources/js/pages/dashboard.js`
  - `resources/js/pages/historial.js`
  - `resources/js/pages/usuarios.js`
  - `resources/js/pages/subir-ecg.js`
  - `resources/js/pages/login.js`
  - `resources/js/pages/patients.js`
- `resources/js/aplicacion.js` ahora registra las paginas con `Alpine.data()` y ya no depende de funciones inline en Blade.

### Vistas actualizadas
- `resources/views/historial.blade.php`
  - La configuracion de reviews se paso a `x-data`.
  - Las rutas de guardar/eliminar usan plantillas generadas con `route()`.
- `resources/views/usuarios.blade.php`
  - La logica de modales salio del `<script>` embebido.
- `resources/views/subir-ecg.blade.php`
  - Se elimino `env()` de la vista.
  - La URL del API, la ruta de analisis y el CSRF se pasaron por `data-*`.
- `resources/views/autenticacion/index.blade.php`
  - El formulario usa `loginForm` desde Alpine.data.
- `resources/views/pacientes.blade.php`
  - La edicion de pacientes usa `patientsPage` desde JS.

### Layouts y parciales
- `resources/views/plantillas/aplicacion.blade.php`
  - Usa sidebar fijo y footer clinico separado.
  - Ya no incluye el navbar viejo.
- `resources/views/plantillas/invitado.blade.php`
  - Mantiene el footer clinico y el stack de scripts.
- `resources/views/parciales/barra-lateral.blade.php`
  - Se restauro la estructura lateral correcta.
- `resources/views/parciales/pie-clinico.blade.php`
  - Se mantuvo como footer reutilizable y modal clinico global.

### CSS reutilizable
- `resources/css/aplicacion.css` conserva las superficies compartidas para reducir estilos inline repetidos:
  - `surface-success-soft`
  - `surface-warning-soft`
  - `surface-primary-soft`
  - `surface-primary-strong`
  - `surface-muted-soft`
  - `surface-muted-subtle`
  - `surface-card`
  - `surface-card-muted`
  - `surface-card-glass`
  - `overlay-backdrop`

## Por que
- Para sacar la logica Alpine fuera de Blade.
- Para evitar scripts largos dentro de las vistas.
- Para mantener una estructura consistente entre layout, sidebar, footer y modales.
- Para reutilizar estilos visuales sin repetir `style=""`.

## Estado
- El flujo activo del sistema ya quedó sincronizado con la refactorizacion.
- `metricas/index.blade.php` quedó asignada a la ruta `dashboard`.
- `dashboard` muestra la vista analitica de métricas y `resumen` conserva el resumen corto.

## Migracion a carpetas

### Vistas movidas
- `dashboard/index.blade.php`
- `historial/index.blade.php`
- `usuarios/index.blade.php`
- `subir-ecg/index.blade.php`
- `pacientes/index.blade.php`
- `reportes/index.blade.php`
- `auditoria/index.blade.php`
- `bienvenida/index.blade.php`
- `metricas/index.blade.php`

### Rutas actualizadas
- `dashboard` y `resumen` apuntan al mismo controlador principal.
- `history` apunta a historial.
- `upload` apunta a subir ECG.
- `reports` apunta a reportes.
- Los controladores activos ahora retornan vistas con sufijo `.index`.

### Resultado
- La carpeta `resources/views/` quedó organizada por modulo.
- Cada modulo activo queda dentro de una carpeta con su `index.blade.php`.
- La navegacion y los links del sistema siguen funcionando con los nuevos nombres.

## Verificacion
- `resources/js` ya contiene la carpeta `pages/`.
- `resources/js/aplicacion.js` usa `Alpine.data()`.
- Las vistas activas ya no dependen de funciones inline para Alpine.
