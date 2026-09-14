<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class Crea_tabla_logs extends Migration
{
    public function up()
    {
        // Tabla Log de Likes (historial de dar/quitar like, ya que la tabla
        // "likes" solo guarda el estado actual y se borra la fila al quitar el like)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_publicacion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_accion' => [
                'type'       => 'ENUM',
                'constraint' => ['Like', 'Unlike'],
            ],
            'fecha' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_publicacion', 'publicaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_likes');
    }

    public function down()
    {
        $this->forge->dropTable('log_likes', true);
    }
}
