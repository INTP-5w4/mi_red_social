<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['nickname', 'correo', 'password_hash'];

    // created_at ya tiene default CURRENT_TIMESTAMP a nivel de base de datos
    protected $useTimestamps = false;

    protected $validationRules = [
        'nickname'      => 'required|min_length[3]|max_length[100]',
        'correo'        => 'required|valid_email|is_unique[usuarios.correo]',
        'password_hash' => 'required|min_length[8]',
    ];

    protected $validationMessages = [
        'correo' => [
            'is_unique' => 'Ya existe una cuenta registrada con este correo.',
        ],
    ];

    protected $skipValidation = false;
}