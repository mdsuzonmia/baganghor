<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFulfillmentTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'code'=>['type'=>'VARCHAR','constraint'=>30],
            'name'=>['type'=>'VARCHAR','constraint'=>100],
            'booking_mode'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'manual'],
            'api_enabled'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'active'],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ])->addKey('id',true)->addUniqueKey('code')->createTable('courier_providers',true);

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'order_id'=>['type'=>'BIGINT','unsigned'=>true],
            'courier_provider_id'=>['type'=>'BIGINT','unsigned'=>true],
            'status'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'prepared'],
            'tracking_number'=>['type'=>'VARCHAR','constraint'=>120,'null'=>true],
            'tracking_url'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],
            'courier_reference'=>['type'=>'VARCHAR','constraint'=>120,'null'=>true],
            'cod_amount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],
            'package_weight_kg'=>['type'=>'DECIMAL','constraint'=>'8,3','null'=>true],
            'admin_note'=>['type'=>'TEXT','null'=>true],
            'booked_at'=>['type'=>'DATETIME','null'=>true],
            'picked_up_at'=>['type'=>'DATETIME','null'=>true],
            'delivered_at'=>['type'=>'DATETIME','null'=>true],
            'created_by_admin_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ])->addKey('id',true)->addUniqueKey('order_id')->addKey('tracking_number')
          ->addForeignKey('order_id','orders','id','CASCADE','CASCADE')
          ->addForeignKey('courier_provider_id','courier_providers','id','RESTRICT','CASCADE')
          ->createTable('shipments',true);

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'shipment_id'=>['type'=>'BIGINT','unsigned'=>true],
            'from_status'=>['type'=>'VARCHAR','constraint'=>30,'null'=>true],
            'to_status'=>['type'=>'VARCHAR','constraint'=>30],
            'tracking_note'=>['type'=>'TEXT','null'=>true],
            'source'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'admin'],
            'changed_by_admin_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'created_at'=>['type'=>'DATETIME','null'=>true],
        ])->addKey('id',true)->addKey('shipment_id')
          ->addForeignKey('shipment_id','shipments','id','CASCADE','CASCADE')
          ->createTable('shipment_events',true);

        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'order_id'=>['type'=>'BIGINT','unsigned'=>true],
            'event_key'=>['type'=>'VARCHAR','constraint'=>190],
            'event_type'=>['type'=>'VARCHAR','constraint'=>50],
            'recipient_mobile'=>['type'=>'VARCHAR','constraint'=>11],
            'message'=>['type'=>'TEXT'],
            'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'queued'],
            'attempts'=>['type'=>'INT','unsigned'=>true,'default'=>0],
            'provider_request_id'=>['type'=>'VARCHAR','constraint'=>120,'null'=>true],
            'last_error'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],
            'next_attempt_at'=>['type'=>'DATETIME','null'=>true],
            'sent_at'=>['type'=>'DATETIME','null'=>true],
            'created_at'=>['type'=>'DATETIME','null'=>true],
            'updated_at'=>['type'=>'DATETIME','null'=>true],
        ])->addKey('id',true)->addUniqueKey('event_key')->addKey(['status','next_attempt_at'])
          ->addForeignKey('order_id','orders','id','CASCADE','CASCADE')
          ->createTable('sms_messages',true);
    }

    public function down(): void
    {
        foreach(['sms_messages','shipment_events','shipments','courier_providers'] as $table) $this->forge->dropTable($table,true);
    }
}
