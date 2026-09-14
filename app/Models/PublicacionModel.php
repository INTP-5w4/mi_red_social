<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicacionModel extends Model
{
    protected $table            = 'publicaciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['id_usuario', 'img', 'descripcion'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'id_usuario'  => 'required|is_natural_no_zero',
        'img'         => 'required|max_length[255]',
        'descripcion' => 'permit_empty|max_length[250]',
    ];
}