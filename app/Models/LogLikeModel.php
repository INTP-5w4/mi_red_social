<?php

namespace App\Models;

use CodeIgniter\Model;

class LogLikeModel extends Model
{
    protected $table            = 'log_likes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['id_usuario', 'id_publicacion', 'tipo_accion'];

    // "fecha" ya tiene default CURRENT_TIMESTAMP a nivel de base de datos
    protected $useTimestamps = false;

    protected $validationRules = [
        'id_usuario'     => 'required|is_natural_no_zero',
        'id_publicacion' => 'required|is_natural_no_zero',
        'tipo_accion'    => 'required|in_list[Like,Unlike]',
    ];

    /**
     * Dar o quitar like para que el usuario
     * pueda revisar su historial más adelante.
     */
    public function registrar(int $idUsuario, int $idPublicacion, string $tipoAccion): void
    {
        $this->insert([
            'id_usuario'     => $idUsuario,
            'id_publicacion' => $idPublicacion,
            'tipo_accion'    => $tipoAccion,
        ]);
    }
}