<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentFilenameToPromosiLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_promosi_log', [
            'attachment_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'error_message',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_promosi_log', 'attachment_filename');
    }
}
