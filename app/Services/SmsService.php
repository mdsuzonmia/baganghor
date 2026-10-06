<?php
namespace App\Services;

use App\Models\OrderModel;
use App\Models\SmsMessageModel;

class SmsService
{
    private const EVENTS = ['placed','confirmed','shipped','delivered','cancelled','shipment_booked'];

    public function configured(): bool
    {
        return trim((string) env('sms.apiKey','')) !== '';
    }

    public function enabled(): bool
    {
        return $this->configured() && ((new SettingService())->all('sms')['enabled'] ?? '0') === '1';
    }

    public function trigger(int $orderId, string $event, ?int $shipmentId = null): void
    {
        if (!in_array($event,self::EVENTS,true)) return;
        $order=(new OrderModel())->find($orderId);
        if (!$order) return;
        $settings=(new SettingService())->all('sms');
        $template=trim((string)($settings[$event]??''));
        if ($template==='') return;
        $trackUrl=site_url('track-order');
        $message=strtr($template,['{order_no}'=>$order['order_no'],'{track_url}'=>$trackUrl]);
        $key='order:'.$orderId.':'.$event.($shipmentId?':shipment:'.$shipmentId:'');
        $model=new SmsMessageModel();
        $existing=$model->where('event_key',$key)->first();
        if ($existing) return;
        $status=$this->enabled()?'queued':'suppressed';
        $id=(int)$model->insert(['order_id'=>$orderId,'event_key'=>$key,'event_type'=>$event,'recipient_mobile'=>$order['customer_mobile'],'message'=>$message,'status'=>$status,'next_attempt_at'=>$status==='queued'?date('Y-m-d H:i:s'):null],true);
        if ($status==='queued') $this->dispatchOne($id);
    }

    public function dispatchPending(int $limit=20): array
    {
        if (!$this->enabled()) return ['sent'=>0,'failed'=>0,'disabled'=>true];
        db_connect()->table('sms_messages')->where('status','sending')->where('updated_at <',date('Y-m-d H:i:s',time()-600))->update(['status'=>'needs_review','last_error'=>'Send result is unknown; check the gateway before retrying to avoid duplicate SMS.','next_attempt_at'=>null]);
        $rows=(new SmsMessageModel())->groupStart()->where('status','queued')->orWhere('status','failed')->groupEnd()->where('attempts <',5)->where('next_attempt_at <=',date('Y-m-d H:i:s'))->orderBy('id')->findAll(max(1,min(100,$limit)));
        $result=['sent'=>0,'failed'=>0,'disabled'=>false];
        foreach($rows as $row){$this->dispatchOne((int)$row['id'])?$result['sent']++:$result['failed']++;}
        return $result;
    }

    public function dispatchOne(int $id): bool
    {
        if (!$this->enabled()) return false;
        $db=db_connect();
        $db->table('sms_messages')->where('id',$id)->whereIn('status',['queued','failed'])->where('attempts <',5)->update(['status'=>'sending','attempts'=>new \CodeIgniter\Database\RawSql('attempts + 1'),'updated_at'=>date('Y-m-d H:i:s')]);
        if ($db->affectedRows()!==1) return false;
        $row=(new SmsMessageModel())->find($id);
        try {
            $body=['api_key'=>(string)env('sms.apiKey'),'msg'=>$row['message'],'to'=>$row['recipient_mobile']];
            $senderId=trim((string)env('sms.senderId',''));
            if($senderId!=='')$body['sender_id']=$senderId;
            $response=service('curlrequest')->post('https://api.sms.net.bd/sendsms',['form_params'=>$body,'timeout'=>5,'connect_timeout'=>3,'http_errors'=>false]);
            $data=json_decode((string)$response->getBody(),true);
            if ($response->getStatusCode()!==200 || !is_array($data) || (int)($data['error']??1)!==0) throw new \RuntimeException('SMS gateway rejected request (HTTP '.$response->getStatusCode().').');
            (new SmsMessageModel())->update($id,['status'=>'sent','provider_request_id'=>substr((string)($data['data']['request_id']??''),0,120)?:null,'last_error'=>null,'next_attempt_at'=>null,'sent_at'=>date('Y-m-d H:i:s')]);
            return true;
        } catch (\Throwable $e) {
            $attempts=(int)$row['attempts'];
            (new SmsMessageModel())->update($id,['status'=>'failed','last_error'=>substr($e->getMessage(),0,500),'next_attempt_at'=>date('Y-m-d H:i:s',time()+min(3600,60*(2**max(0,$attempts-1))))]);
            log_message('error','SMS dispatch failed for message {id}: {error}',['id'=>$id,'error'=>$e->getMessage()]);
            return false;
        }
    }
}
