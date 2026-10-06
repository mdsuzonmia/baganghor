<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { $this->call('AdminRoleSeeder'); $this->call('SettingsSeeder'); $this->call('SuperAdminSeeder'); $this->call('CatalogPermissionSeeder'); $this->call('HydroponicsGuideSeeder'); } }
