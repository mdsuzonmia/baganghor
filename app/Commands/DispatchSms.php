<?php
namespace App\Commands;

use App\Services\SmsService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DispatchSms extends BaseCommand
{
    protected $group='Taharat Agro';
    protected $name='sms:dispatch';
    protected $description='Send queued order SMS and retry transient failures.';

    public function run(array $params)
    {
        $result=(new SmsService())->dispatchPending((int)($params[0]??20));
        CLI::write($result['disabled']?'SMS is disabled or unconfigured.':'Sent: '.$result['sent'].'; failed: '.$result['failed']);
    }
}
