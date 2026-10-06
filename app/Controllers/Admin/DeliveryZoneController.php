<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DeliveryZoneModel;
use App\Models\DistrictModel;
use App\Models\UpazilaModel;
use App\Services\ActivityLogService;

class DeliveryZoneController extends BaseController
{
    public function index()
    {
        $zones = db_connect()->table('delivery_zones z')
            ->select('z.*, d.name_en AS district_name_en, d.name_bn AS district_name_bn, u.name_en AS upazila_name_en, u.name_bn AS upazila_name_bn')
            ->join('bd_districts d', 'd.id = z.district_id', 'left')
            ->join('bd_upazilas u', 'u.id = z.upazila_id', 'left')
            ->orderBy('z.priority', 'DESC')->orderBy('z.name', 'ASC')
            ->get()->getResultArray();
        return view('admin/delivery_zones/index', ['title' => 'Delivery Zones', 'zones' => $zones]);
    }

    public function create() { return $this->form(); }
    public function edit(int $id)
    {
        $zone = (new DeliveryZoneModel())->find($id);
        if (!$zone) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $this->form($zone);
    }

    private function form(?array $zone = null)
    {
        return view('admin/delivery_zones/form', [
            'title' => $zone ? 'Edit Delivery Zone' : 'Create Delivery Zone', 'zone' => $zone,
            'districts' => (new DistrictModel())->where('status', 'active')->orderBy('sort_order')->findAll(),
            'upazilas' => $zone && $zone['district_id'] ? (new UpazilaModel())->where('district_id', $zone['district_id'])->orderBy('sort_order')->findAll() : [],
        ]);
    }

    public function store() { return $this->save(); }
    public function update(int $id) { return $this->save($id); }

    private function save(?int $id = null)
    {
        $type = (string) $this->request->getPost('zone_type');
        $name = trim((string) $this->request->getPost('name'));
        $charge = $this->request->getPost('delivery_charge');
        $minimum = $this->request->getPost('free_delivery_minimum');
        $districtId = in_array($type, ['district', 'upazila'], true) ? (int) $this->request->getPost('district_id') : null;
        $upazilaId = $type === 'upazila' ? (int) $this->request->getPost('upazila_id') : null;
        if ($id && !(new DeliveryZoneModel())->find($id)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($name === '' || !in_array($type, ['all_bangladesh', 'district', 'upazila'], true) || !is_numeric($charge) || (float) $charge < 0 || ($minimum !== '' && $minimum !== null && (!is_numeric($minimum) || (float) $minimum < 0))) {
            return redirect()->back()->withInput()->with('danger', 'Enter a valid name, zone type and non-negative charges.');
        }
        if ($districtId && !(new DistrictModel())->where(['id' => $districtId, 'status' => 'active'])->first()) return redirect()->back()->withInput()->with('danger', 'Select an active district.');
        if ($type !== 'all_bangladesh' && !$districtId) return redirect()->back()->withInput()->with('danger', 'District is required.');
        if ($type === 'upazila' && (!$upazilaId || !(new UpazilaModel())->where(['id' => $upazilaId, 'district_id' => $districtId, 'status' => 'active'])->first())) return redirect()->back()->withInput()->with('danger', 'Select an upazila in that district.');
        $status = $this->request->getPost('status') === 'active' ? 'active' : 'inactive';
        if ($status === 'active') {
            $duplicate = (new DeliveryZoneModel())->where(['status' => 'active', 'zone_type' => $type, 'district_id' => $districtId, 'upazila_id' => $upazilaId])->first();
            if ($duplicate && (int) $duplicate['id'] !== $id) return redirect()->back()->withInput()->with('danger', 'An active delivery zone already covers this exact location. Deactivate it before enabling another.');
        }
        $data = [
            'name' => $name, 'zone_type' => $type, 'district_id' => $districtId, 'upazila_id' => $upazilaId,
            'delivery_charge' => (float) $charge, 'free_delivery_minimum' => $minimum === '' || $minimum === null ? null : (float) $minimum,
            'estimated_delivery_text' => trim((string) $this->request->getPost('estimated_delivery_text')) ?: null,
            'priority' => (int) $this->request->getPost('priority'), 'status' => $status,
        ];
        $model = new DeliveryZoneModel();
        if ($id) $model->update($id, $data); else $id = (int) $model->insert($data, true);
        (new ActivityLogService())->log('delivery_zone.' . ($this->request->getPost('_method') === 'PUT' ? 'update' : 'create'), 'delivery', 'Delivery zone saved.', ['id' => $id]);
        return redirect()->to(url_to('admin.delivery'))->with('success', 'Delivery zone saved.');
    }

    public function delete(int $id)
    {
        $model = new DeliveryZoneModel();
        if (!$model->find($id)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $model->delete($id);
        (new ActivityLogService())->log('delivery_zone.delete', 'delivery', 'Delivery zone deleted.', ['id' => $id]);
        return redirect()->back()->with('success', 'Delivery zone deleted.');
    }
}
