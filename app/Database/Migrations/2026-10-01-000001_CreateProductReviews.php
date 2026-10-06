<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductReviews extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'order_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'order_item_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'rating' => ['type' => 'TINYINT', 'unsigned' => true],
            'review_text' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'is_verified_purchase' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'admin_reply' => ['type' => 'TEXT', 'null' => true],
            'replied_at' => ['type' => 'DATETIME', 'null' => true],
            'replied_by_admin_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true)
            ->addUniqueKey('order_item_id')
            ->addKey(['product_id', 'status', 'created_at'])
            ->addKey('order_id')
            ->addKey('customer_id')
            ->addForeignKey('customer_id', 'customers', 'id', 'SET NULL', 'CASCADE')
            ->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE')
            ->addForeignKey('order_item_id', 'order_items', 'id', 'CASCADE', 'CASCADE')
            ->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE')
            ->addForeignKey('replied_by_admin_id', 'admin_users', 'id', 'SET NULL', 'CASCADE')
            ->createTable('product_reviews');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'review_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'sort_order' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true)->addKey('review_id')
            ->addForeignKey('review_id', 'product_reviews', 'id', 'CASCADE', 'CASCADE')
            ->createTable('product_review_images');

        $now = date('Y-m-d H:i:s');
        foreach (['reviews.view' => 'View Reviews', 'reviews.manage' => 'Manage Reviews'] as $slug => $name) {
            if ($this->db->table('admin_permissions')->where('slug', $slug)->countAllResults()) continue;
            $this->db->table('admin_permissions')->insert(['name' => $name, 'slug' => $slug, 'module' => 'reviews', 'description' => $name, 'created_at' => $now, 'updated_at' => $now]);
            $permissionId = $this->db->insertID();
            foreach (['admin', 'manager'] as $roleSlug) {
                $role = $this->db->table('admin_roles')->where('slug', $roleSlug)->get()->getRowArray();
                if ($role) $this->db->table('admin_role_permissions')->insert(['role_id' => $role['id'], 'permission_id' => $permissionId, 'created_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('product_review_images', true);
        $this->forge->dropTable('product_reviews', true);
        foreach (['reviews.view', 'reviews.manage'] as $slug) {
            $permission = $this->db->table('admin_permissions')->where('slug', $slug)->get()->getRowArray();
            if ($permission) $this->db->table('admin_permissions')->where('id', $permission['id'])->delete();
        }
    }
}
