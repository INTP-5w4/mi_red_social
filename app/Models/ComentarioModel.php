<?php

namespace App\Models;

use CodeIgniter\Model;

class ComentarioModel extends Model
{
    protected $table            = 'comentarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['id_usuario', 'id_publicacion', 'descripcion'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'id_usuario'     => 'required|is_natural_no_zero',
        'id_publicacion' => 'required|is_natural_no_zero',
        'descripcion'    => 'required|min_length[1]|max_length[250]',
    ];
}