# Correccion de refactorizacion

## Alcance
- Se corrigio la parte de `sistema/` que habia quedado desviada respecto a `REFACTORIZACION_NUEVO`.
- No se tocaron controladores ni modelos.
- El ajuste se limito a vistas, layouts, parciales, CSS y JS de presentacion.

## Que se corrijio
- Se elimino la inclusion del navbar viejo en `resources/views/plantillas/aplicacion.blade.php`.
- Se restauro el layout principal con sidebar fijo, header superior y footer clinico separado.
- Se alineo `resources/views/parciales/barra-lateral.blade.php` con la estructura refactorizada.
- Se alineo `resources/views/parciales/pie-clinico.blade.php` con el footer correcto.
- Se mantuvo `@stack('scripts')` en los layouts para soportar scripts de pagina.
- Se conservo la separacion de Alpine con `Alpine.data()` en `resources/js/aplicacion.js`.

## Resultado
- La interfaz vuelve a la estructura esperada por la refactorizacion.
- El navbar insertado por el conflicto ya no forma parte del layout principal.
- El sidebar y el footer vuelven a comportarse como en la version de referencia.

## Verificacion
- `resources/views/plantillas/aplicacion.blade.php` ya no incluye `parciales.navbar`.
- `resources/views/plantillas/aplicacion.blade.php` usa `parciales.barra-lateral` y `parciales.pie-clinico`.
- `resources/views/plantillas/invitado.blade.php` mantiene el footer clinico y el stack de scripts.
