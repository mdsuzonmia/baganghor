<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVideos extends Migration
{
    public function up(): void
    {
        $timestamps = [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'seo_description' => ['type' => 'TEXT', 'null' => true],
        ] + $timestamps)->addKey('id', true)->addUniqueKey('slug')->addKey(['status', 'sort_order'])->createTable('video_categories');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 190],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 220],
            'youtube_url' => ['type' => 'VARCHAR', 'constraint' => 500],
            'youtube_id' => ['type' => 'VARCHAR', 'constraint' => 20],
            'thumbnail' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'short_description' => ['type' => 'TEXT', 'null' => true],
            'content' => ['type' => 'LONGTEXT'],
            'duration' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'featured' => ['type' => 'BOOLEAN', 'default' => false],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'views' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'seo_description' => ['type' => 'TEXT', 'null' => true],
        ] + $timestamps)->addKey('id', true)->addUniqueKey('slug')->addKey('category_id')->addKey(['status', 'featured', 'sort_order'])->addForeignKey('category_id', 'video_categories', 'id', 'SET NULL', 'CASCADE')->createTable('videos');

        $this->forge->addField([
            'video_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'product_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ])->addKey(['video_id', 'product_id'], true)->addForeignKey('video_id', 'videos', 'id', 'CASCADE', 'CASCADE')->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE')->createTable('video_products');

        $now = date('Y-m-d H:i:s');
        foreach (['video_categories.view' => 'View Video Categories', 'video_categories.manage' => 'Manage Video Categories', 'videos.view' => 'View Videos', 'videos.manage' => 'Manage Videos'] as $slug => $name) {
            $this->db->table('admin_permissions')->insert(['name' => $name, 'slug' => $slug, 'module' => 'videos', 'description' => $name, 'created_at' => $now, 'updated_at' => $now]);
            $permissionId = $this->db->insertID();
            foreach (['admin', 'manager'] as $roleSlug) {
                $role = $this->db->table('admin_roles')->where('slug', $roleSlug)->get()->getRowArray();
                if ($role) $this->db->table('admin_role_permissions')->insert(['role_id' => $role['id'], 'permission_id' => $permissionId, 'created_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        foreach (['video_products', 'videos', 'video_categories'] as $table) $this->forge->dropTable($table, true);
        foreach (['video_categories.view', 'video_categories.manage', 'videos.view', 'videos.manage'] as $slug) {
            $permission = $this->db->table('admin_permissions')->where('slug', $slug)->get()->getRowArray();
            if ($permission) $this->db->table('admin_permissions')->where('id', $permission['id'])->delete();
        }
    }
}
