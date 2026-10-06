<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SmsMessageModel;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use App\Services\SmsService;

class SmsController extends BaseController
{
    private const EVENTS=['placed','confirmed','shipped','delivered','cancelled','shipment_booked'];

    public function index()
    {
        $model=new SmsMessageModel();
        return view('admin/sms/index',['title'=>'Order SMS','settings'=>(new SettingService())->all('sms'),'configured'=>(new SmsService())->configured(),'messages'=>$model->orderBy('id','DESC')->paginate(30),'pager'=>$model->pager]);
    }

    public function update()
    {
        $enabled=$this->request->getPost('enabled')==='1'?'1':'0';
        if($enabled==='1' && !(new SmsService())->configured()) return redirect()->back()->with('danger','Set sms.apiKey in .env before enabling automatic SMS.');
        $values=['enabled'=>$enabled];
        foreach(self::EVENTS as $event) {
            $template=trim((string)$this->request->getPost($event));
            if(mb_strlen($template)>500 || preg_match('/\{(?!order_no\}|track_url\})[^}]+\}/u',$template)) return redirect()->back()->withInput()->with('danger','Templates must be under 500 characters and use only {order_no} or {track_url}.');
            $values[$event]=$template;
        }
        (new SettingService())->setMany('sms',$values);
        (new ActivityLogService())->log('sms.settings','sms','Order SMS settings updated.');
        return redirect()->back()->with('success','SMS settings updated.');
    }

    public function retry(int $id)
    {
        $model=new SmsMessageModel();$message=$model->find($id);
        if(!$message || !in_array($message['status'],['failed','queued','needs_review'],true)) return redirect()->back()->with('danger','This message cannot be retried.');
        if($message['status']==='needs_review' && $this->request->getPost('confirm_unknown')!=='1') return redirect()->back()->with('danger','Confirm you checked the gateway before retrying an unknown send result.');
        $model->update($id,['status'=>'queued','attempts'=>0,'next_attempt_at'=>date('Y-m-d H:i:s')]);
        $sent=(new SmsService())->dispatchOne($id);
        (new ActivityLogService())->log('sms.retry','sms','SMS retry requested.',['message_id'=>$id]);
        return redirect()->back()->with($sent?'success':'danger',$sent?'SMS sent.':'SMS could not be sent; retry remains scheduled.');
    }
}
