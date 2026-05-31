<?php

namespace App\Traits;

use App\Services\ServicioAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::audit('crear', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();
            
            self::audit('actualizar', $model, $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            self::audit('eliminar', $model, $model->getAttributes(), null);
        });
    }

    protected static function audit(string $accion, Model $model, ?array $old, ?array $new)
    {
        // Obtener el nombre de la tabla como entidad
        $entidad = $model->getTable();
        $entidadId = $model->getKey();
        
        // Determinar el módulo basado en el nombre de la tabla (ajustar según convención)
        $modulo = self::getModuloForAuditoria($entidad);

        $descripcion = ucfirst($accion) . " en la entidad $entidad (ID: $entidadId)";

        ServicioAuditoria::registrar(
            $accion,
            $modulo,
            $entidad,
            $entidadId,
            $descripcion,
            $old,
            $new,
            request()
        );
    }

    protected static function getModuloForAuditoria(string $tabla): string
    {
        $map = [
            'users' => 'Seguridad',
            'roles' => 'Seguridad',
            'pacientes' => 'Clinico',
            'estudios' => 'Clinico',
            'imagenes' => 'Clinico',
            'predicciones' => 'Clinico',
            'diagnosticos' => 'Clinico',
            'ritmos_cardiacos' => 'Mantenimiento',
            'grupos_cardiacos' => 'Mantenimiento',
            'niveles_gravedad' => 'Mantenimiento',
            'clasificaciones_arritmia' => 'Mantenimiento',
            'prefijos_paciente' => 'Mantenimiento',
        ];

        return $map[$tabla] ?? 'Sistema';
    }
}
