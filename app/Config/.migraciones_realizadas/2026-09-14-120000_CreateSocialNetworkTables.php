<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSocialNetworkTables extends Migration
{
    public function up()
    {
        // 1. Tabla Usuarios (Sin id_log, limpio)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nickname' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'unique'     => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('usuarios');

        // 2. Tabla Publicaciones
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [ // Dueño de la publicación
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'img' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('publicaciones');

        // 3. Tabla Comentarios
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [
                'type'       => 'INT',
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'id_publicacion' => [
                'type'       => 'INT',
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'descripcion' => [
                'type' => 'TEXT',
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_publicacion', 'publicaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('comentarios');

        // 4. Tabla Likes (Separada para fácil manejo de dar/quitar like)
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
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        // Llave única compuesta para evitar que un usuario de like dos veces a la misma foto
        $this->forge->addUniqueKey(['id_usuario', 'id_publicacion']);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_publicacion', 'publicaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('likes');

        // 5. Tabla Guardados (Saved)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [
                'type'       => 'INT',
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'id_publicacion' => [
                'type'       => 'INT',
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'created_at datetime default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_usuario', 'id_publicacion']);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_publicacion', 'publicaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('guardados');

        // 6. Tabla Log de Sesiones / Acciones (Para Login / Logout)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [
                'type'       => 'INT',
                'constraint'     => 11,
                'unsigned'   => true,
            ],
            'tipo_accion' => [
                'type'       => 'ENUM',
                'constraint' => ['Login', 'Logout'],
            ],
            'fecha' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_usuario', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_sesiones');
    }

    public function down()
    {
        // Se eliminan en orden inverso por las dependencias de las llaves foráneas
        $this->forge->dropTable('log_sesiones', true);
        $this->forge->dropTable('guardados', true);
        $this->forge->dropTable('likes', true);
        $this->forge->dropTable('comentarios', true);
        $this->forge->dropTable('publicaciones', true);
        $this->forge->dropTable('usuarios', true);
    }
}
