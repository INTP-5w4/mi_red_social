<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Aniadir_descripcion extends Migration
{
    public function up()
    {
        $fields = [
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint'=>'250',
                'null'       => true,
                'after'      => 'img', // Lo coloca visualmente después del campo img
            ],
        ];

        // Añade el campo a la tabla existente 'publicaciones'
        $this->forge->addColumn('publicaciones', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('publicaciones', 'descripcion');
    }
}