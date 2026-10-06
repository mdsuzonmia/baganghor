<?php
namespace App\Models;
use CodeIgniter\Model;
class CustomerAddressModel extends Model
{
    protected $table = 'customer_addresses'; protected $returnType = 'array'; protected $useTimestamps = true;
    protected $allowedFields = ['customer_id','label','recipient_name','mobile','district','upazila','area','address_line','landmark','is_default'];
}
