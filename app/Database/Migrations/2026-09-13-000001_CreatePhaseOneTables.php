<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhaseOneTables extends Migration
{
    public function up(): void
    {
        $this->roles();
        $this->customers();
        $this->addresses();
        $this->adminUsers();
        $this->permissions();
        $this->rolePermissions();
        $this->settings();
        $this->activityLogs();
        $this->loginAttempts();
    }

    private function timestamps(bool $softDelete = false): array
    {
        $fields = [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];
        if ($softDelete) {
            $fields['deleted_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        return $fields;
    }

    private function roles(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'name' => ['type' => 'VARCHAR', 'constraint' => 100], 'slug' => ['type' => 'VARCHAR', 'constraint' => 60], 'description' => ['type' => 'TEXT', 'null' => true], 'is_system' => ['type' => 'BOOLEAN', 'default' => true]] + $this->timestamps());
        $this->forge->addKey('id', true)->addUniqueKey('slug')->createTable('admin_roles', true);
    }

    private function customers(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'customer_code' => ['type' => 'VARCHAR', 'constraint' => 20], 'full_name' => ['type' => 'VARCHAR', 'constraint' => 150], 'mobile' => ['type' => 'VARCHAR', 'constraint' => 11], 'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true], 'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255], 'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'], 'email_verified_at' => ['type' => 'DATETIME', 'null' => true], 'mobile_verified_at' => ['type' => 'DATETIME', 'null' => true], 'last_login_at' => ['type' => 'DATETIME', 'null' => true], 'last_login_ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true], 'remember_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]] + $this->timestamps(true));
        $this->forge->addKey('id', true)->addUniqueKey('customer_code')->addUniqueKey('mobile')->addUniqueKey('email')->addKey('status')->createTable('customers', true);
    }

    private function addresses(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'customer_id' => ['type' => 'BIGINT', 'unsigned' => true], 'label' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true], 'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 150], 'mobile' => ['type' => 'VARCHAR', 'constraint' => 11], 'district' => ['type' => 'VARCHAR', 'constraint' => 100], 'upazila' => ['type' => 'VARCHAR', 'constraint' => 100], 'area' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true], 'address_line' => ['type' => 'TEXT'], 'landmark' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true], 'is_default' => ['type' => 'BOOLEAN', 'default' => false]] + $this->timestamps());
        $this->forge->addKey('id', true)->addKey('customer_id')->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE')->createTable('customer_addresses', true);
    }

    private function adminUsers(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'role_id' => ['type' => 'BIGINT', 'unsigned' => true], 'name' => ['type' => 'VARCHAR', 'constraint' => 150], 'email' => ['type' => 'VARCHAR', 'constraint' => 190], 'mobile' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255], 'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'], 'profile_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], 'last_login_at' => ['type' => 'DATETIME', 'null' => true], 'last_login_ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true]] + $this->timestamps(true));
        $this->forge->addKey('id', true)->addUniqueKey('email')->addKey('role_id')->addKey('status')->addForeignKey('role_id', 'admin_roles', 'id', 'RESTRICT', 'CASCADE')->createTable('admin_users', true);
    }

    private function permissions(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'name' => ['type' => 'VARCHAR', 'constraint' => 120], 'slug' => ['type' => 'VARCHAR', 'constraint' => 100], 'module' => ['type' => 'VARCHAR', 'constraint' => 60], 'description' => ['type' => 'TEXT', 'null' => true]] + $this->timestamps());
        $this->forge->addKey('id', true)->addUniqueKey('slug')->addKey('module')->createTable('admin_permissions', true);
    }

    private function rolePermissions(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'role_id' => ['type' => 'BIGINT', 'unsigned' => true], 'permission_id' => ['type' => 'BIGINT', 'unsigned' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addKey('id', true)->addUniqueKey(['role_id', 'permission_id'])->addForeignKey('role_id', 'admin_roles', 'id', 'CASCADE', 'CASCADE')->addForeignKey('permission_id', 'admin_permissions', 'id', 'CASCADE', 'CASCADE')->createTable('admin_role_permissions', true);
    }

    private function settings(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'group_name' => ['type' => 'VARCHAR', 'constraint' => 60], 'setting_key' => ['type' => 'VARCHAR', 'constraint' => 100], 'setting_value' => ['type' => 'TEXT', 'null' => true], 'setting_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'string'], 'is_public' => ['type' => 'BOOLEAN', 'default' => false]] + $this->timestamps());
        $this->forge->addKey('id', true)->addUniqueKey(['group_name', 'setting_key'])->createTable('settings', true);
    }

    private function activityLogs(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'admin_user_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true], 'action' => ['type' => 'VARCHAR', 'constraint' => 100], 'module' => ['type' => 'VARCHAR', 'constraint' => 60], 'description' => ['type' => 'TEXT', 'null' => true], 'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true], 'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], 'metadata' => ['type' => 'TEXT', 'null' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addKey('id', true)->addKey('admin_user_id')->addKey('module')->addKey('created_at')->addForeignKey('admin_user_id', 'admin_users', 'id', 'SET NULL', 'CASCADE')->createTable('activity_logs', true);
    }

    private function loginAttempts(): void
    {
        $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'login_type' => ['type' => 'VARCHAR', 'constraint' => 20], 'identifier' => ['type' => 'VARCHAR', 'constraint' => 190], 'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45], 'attempted_at' => ['type' => 'DATETIME'], 'success' => ['type' => 'BOOLEAN', 'default' => false]]);
        $this->forge->addKey('id', true)->addKey('identifier')->addKey('ip_address')->addKey('attempted_at')->addKey(['login_type', 'identifier', 'ip_address'])->createTable('login_attempts', true);
    }

    public function down(): void
    {
        foreach (['login_attempts', 'activity_logs', 'admin_role_permissions', 'admin_permissions', 'admin_users', 'customer_addresses', 'customers', 'settings', 'admin_roles'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
