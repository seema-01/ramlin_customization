<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_top_trending_categories extends CI_Migration
{
    public function up()
    {
        $this->dbforge->modify_column('pending_orders', [
            'rejected_riders' => [
                'type' => 'LONGTEXT',
                'null' => TRUE,
                'default' => NULL,
            ],
        ]);

        // 6. Modify balance column default to 0
        $this->dbforge->modify_column('users', [
            'balance' => [
                'type' => 'DOUBLE',
                'null' => FALSE,
                'default' => 0,
            ],
        ]);
    }

    public function down()
    {
       $this->dbforge->modify_column('pending_orders', [
            'rejected_riders' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'default' => NULL,
            ],
        ]);

        // Revert balance column (remove default)
        $this->dbforge->modify_column('users', [
            'balance' => [
                'type' => 'DOUBLE',
                'null' => FALSE,
            ],
        ]);
    }
}
