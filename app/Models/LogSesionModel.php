<?php

namespace App\Models;

use CodeIgniter\Model;

class LogSesionModel extends Model
{
    protected $table            = 'log_sesiones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['id_usuario', 'tipo_accion'];

    // "fecha" ya tiene default CURRENT_TIMESTAMP a nivel de base de datos
    protected $useTimestamps = false;

    protected $validationRules = [
        'id_usuario'  => 'required|is_natural_no_zero',
        'tipo_accion' => 'required|in_list[Login,Logout]',
    ];

    /**
     * Registra un evento de inicio/cierre de sesión para que el usuario
     * pueda revisar su historial más adelante.
     */
    public function registrar(int $idUsuario, string $tipoAccion): void
    {
        $this->insert([
            'id_usuario'  => $idUsuario,
            'tipo_accion' => $tipoAccion,
        ]);
    }
}