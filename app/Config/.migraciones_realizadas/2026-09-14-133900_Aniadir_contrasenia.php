<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Aniadir_contrasenia extends Migration
{
    public function up()
    {
        $fields = [
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'correo', // Lo coloca visualmente después del campo correo
            ],
        ];

        // Añade el campo a la tabla existente 'usuarios'
        $this->forge->addColumn('usuarios', $fields);
    }

    public function down()
    {
        // Si necesitas revertir la migración, elimina la columna
        $this->forge->dropColumn('usuarios', 'password_hash');
    }
}