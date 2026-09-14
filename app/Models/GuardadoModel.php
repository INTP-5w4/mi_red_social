<?php

namespace App\Models;

use CodeIgniter\Model;

class GuardadoModel extends Model
{
    protected $table            = 'guardados';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['id_usuario', 'id_publicacion'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'id_usuario'     => 'required|is_natural_no_zero',
        'id_publicacion' => 'required|is_natural_no_zero',
    ];

    /**
     * Indica si un usuario ya guardó una publicación.
     */
    public function yaGuardado(int $idUsuario, int $idPublicacion): bool
    {
        return (bool) $this
            ->where('id_usuario', $idUsuario)
            ->where('id_publicacion', $idPublicacion)
            ->first();
    }
}