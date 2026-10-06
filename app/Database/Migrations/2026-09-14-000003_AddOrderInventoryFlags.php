<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderInventoryFlags extends Migration
{
    public function up(): void
    {
        $field = [
            'stock_deducted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'purchase_cost_snapshot',
            ],
        ];

        $this->forge->addColumn('order_items', $field);
        $this->forge->addColumn('order_item_components', $field);
    }

    public function down(): void
    {
        $this->forge->dropColumn('order_item_components', 'stock_deducted');
        $this->forge->dropColumn('order_items', 'stock_deducted');
    }
}
