<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Item extends Migration
{
    public function up()
    {
        // Columns
        // id -> int -> PK -> AI
        // detail -> text
        // status -> enum('to-do', 'done', 'hidden')
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'detail' => [
                'type'       => 'TEXT',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM("to-do","done", "hidden")',
                'default' => 'to-do',
                'null' => false,
            ],
        ]);
        // Menambahkan primary key
        $this->forge->addKey('id', true);
        // membentuk table items
        $this->forge->createTable('items');
    }

    public function down()
    {
        // mebghapus table items
        $this->forge->dropTable('items');
    }
}
