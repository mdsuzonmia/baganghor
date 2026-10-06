<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-1">Create order</h1><p class="text-muted mb-0">Add the customer, items, delivery, and payment details.</p></div>
    <a class="btn btn-outline-secondary" href="<?= url_to('admin.orders') ?>">Back to orders</a>
</div>
<form method="post" action="<?= url_to('admin.orders.store') ?>" id="orderForm">
<?= csrf_field() ?><input type="hidden" name="checkout_token" value="<?= esc(old('checkout_token',$token),'attr') ?>">
<div class="row g-4"><div class="col-xl-8">
    <section class="card border-0 p-4 mb-4">
        <h2 class="h5 mb-3">Customer</h2>
        <div class="row g-3">
            <div class="col-12"><label class="form-label" for="customerId">Existing customer <span class="text-muted fw-normal">(optional)</span></label><select class="form-select" id="customerId" name="customer_id"><option value="">Guest / new customer</option><?php foreach($customers as $customer): ?><option value="<?= $customer['id'] ?>" data-name="<?= esc($customer['full_name'],'attr') ?>" data-mobile="<?= esc($customer['mobile'],'attr') ?>" <?= (int)old('customer_id')===(int)$customer['id']?'selected':'' ?>><?= esc($customer['full_name'].' — '.$customer['mobile']) ?></option><?php endforeach ?></select></div>
            <div class="col-md-6"><label class="form-label" for="customerName">Name *</label><input class="form-control" id="customerName" name="name" value="<?= esc(old('name')) ?>" required></div>
            <div class="col-md-6"><label class="form-label" for="customerMobile">Mobile *</label><input class="form-control" id="customerMobile" name="mobile" inputmode="tel" placeholder="01XXXXXXXXX" value="<?= esc(old('mobile')) ?>" required></div>
            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="createCustomer" name="create_customer" value="1" <?= old('create_customer')?'checked':'' ?>><label class="form-check-label" for="createCustomer">Add this person as a customer</label></div><div class="form-text">If the mobile number already exists, the order will be linked to that customer.</div></div>
            <div class="col-md-6 <?= old('create_customer')?'':'d-none' ?>" id="customerPasswordWrap"><label class="form-label" for="customerPassword">Temporary password *</label><input class="form-control" type="password" id="customerPassword" name="customer_password" minlength="6" autocomplete="new-password"><div class="form-text">Share this with the customer so they can sign in.</div></div>
        </div>
    </section>
    <section class="card border-0 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Items</h2><button class="btn btn-sm btn-outline-success" type="button" id="addItem">+ Add item</button></div>
        <div id="itemRows" class="d-grid gap-2">
        <?php $oldKeys=(array)old('item_key',[]);$oldQuantities=(array)old('quantity',[]);if(!$oldKeys)$oldKeys=[''];foreach($oldKeys as $index=>$selected): ?>
            <div class="row g-2 item-row"><div class="col-md-9"><label class="visually-hidden">Product or package</label><select class="form-select" name="item_key[]" required><option value="">Select a product or package</option><?php foreach($choices as $choice): ?><option value="<?= esc($choice['key'],'attr') ?>" <?= $selected===$choice['key']?'selected':'' ?>><?= esc($choice['label']) ?> — <?= format_bdt($choice['price']) ?><?= $choice['stock']!==null?' · Stock '.$choice['stock']:'' ?></option><?php endforeach ?></select></div><div class="col-8 col-md-2"><label class="visually-hidden">Quantity</label><input class="form-control" type="number" name="quantity[]" min="1" value="<?= max(1,(int)($oldQuantities[$index]??1)) ?>" required></div><div class="col-4 col-md-1"><button class="btn btn-outline-danger w-100 remove-item" type="button" aria-label="Remove item">&times;</button></div></div>
        <?php endforeach ?>
        </div>
    </section>
    <section class="card border-0 p-4">
        <h2 class="h5 mb-3">Delivery address</h2>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="district">District *</label><select class="form-select" id="district" name="district_id" required><option value="">Select district</option><?php foreach($districts as $district): ?><option value="<?= $district['id'] ?>" <?= (int)old('district_id')===(int)$district['id']?'selected':'' ?>><?= esc($district['name_en'].($district['name_bn']?' / '.$district['name_bn']:'')) ?></option><?php endforeach ?></select></div>
            <div class="col-md-6"><label class="form-label" for="upazila">Upazila / thana *</label><select class="form-select" id="upazila" name="upazila_id" required disabled><option value="">Select district first</option></select></div>
            <div class="col-md-6"><label class="form-label">Area / union</label><input class="form-control" name="area" value="<?= esc(old('area')) ?>"></div><div class="col-md-6"><label class="form-label">Landmark</label><input class="form-control" name="landmark" value="<?= esc(old('landmark')) ?>"></div>
            <div class="col-12"><label class="form-label">Full address *</label><textarea class="form-control" name="address_line" rows="3" required><?= esc(old('address_line')) ?></textarea></div>
        </div>
    </section>
</div><aside class="col-xl-4">
    <section class="card border-0 p-4 mb-4"><h2 class="h5 mb-3">Payment</h2><label class="form-label" for="paymentMethod">Method *</label><select class="form-select" id="paymentMethod" name="payment_method_id" required><option value="">Select method</option><?php foreach($methods as $method): ?><option value="<?= $method['id'] ?>" data-type="<?= esc($method['type'],'attr') ?>" <?= (int)old('payment_method_id')===(int)$method['id']?'selected':'' ?>><?= esc($method['name']) ?></option><?php endforeach ?></select><div id="paymentReference" class="mt-3 d-none"><label class="form-label" for="transactionId">Payment reference *</label><input class="form-control" id="transactionId" name="transaction_id" value="<?= esc(old('transaction_id')) ?>"><label class="form-label mt-3" for="senderMobile">Sender mobile</label><input class="form-control" id="senderMobile" name="sender_mobile" value="<?= esc(old('sender_mobile')) ?>"></div></section>
    <section class="card border-0 p-4 mb-4"><h2 class="h5 mb-3">Notes</h2><label class="form-label">Customer note</label><textarea class="form-control mb-3" name="customer_note" rows="2"><?= esc(old('customer_note')) ?></textarea><label class="form-label">Admin note</label><textarea class="form-control" name="admin_note" rows="3"><?= esc(old('admin_note')) ?></textarea></section>
    <button class="btn btn-success btn-lg w-100" type="submit">Create order</button>
</aside></div></form>
<template id="itemTemplate"><div class="row g-2 item-row"><div class="col-md-9"><label class="visually-hidden">Product or package</label><select class="form-select" name="item_key[]" required><option value="">Select a product or package</option><?php foreach($choices as $choice): ?><option value="<?= esc($choice['key'],'attr') ?>"><?= esc($choice['label']) ?> — <?= format_bdt($choice['price']) ?><?= $choice['stock']!==null?' · Stock '.$choice['stock']:'' ?></option><?php endforeach ?></select></div><div class="col-8 col-md-2"><label class="visually-hidden">Quantity</label><input class="form-control" type="number" name="quantity[]" min="1" value="1" required></div><div class="col-4 col-md-1"><button class="btn btn-outline-danger w-100 remove-item" type="button" aria-label="Remove item">&times;</button></div></div></template>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const rows=document.getElementById('itemRows'),customer=document.getElementById('customerId'),name=document.getElementById('customerName'),mobile=document.getElementById('customerMobile'),createCustomer=document.getElementById('createCustomer'),passwordWrap=document.getElementById('customerPasswordWrap'),password=document.getElementById('customerPassword'),district=document.getElementById('district'),upazila=document.getElementById('upazila'),payment=document.getElementById('paymentMethod'),reference=document.getElementById('paymentReference'),transaction=document.getElementById('transactionId');
 const bindRemove=()=>document.querySelectorAll('.remove-item').forEach(button=>button.onclick=()=>{if(rows.children.length>1)button.closest('.item-row').remove();});
 document.getElementById('addItem').addEventListener('click',()=>{rows.append(document.getElementById('itemTemplate').content.cloneNode(true));bindRemove();});bindRemove();
 const updateCustomerPassword=()=>{const show=createCustomer.checked&&!createCustomer.disabled;passwordWrap.classList.toggle('d-none',!show);password.required=show;};
 customer.addEventListener('change',()=>{const option=customer.selectedOptions[0],existing=Boolean(customer.value);if(existing){name.value=option.dataset.name;mobile.value=option.dataset.mobile;createCustomer.checked=false;}createCustomer.disabled=existing;updateCustomerPassword();});createCustomer.addEventListener('change',updateCustomerPassword);customer.dispatchEvent(new Event('change'));
 const loadUpazilas=async selected=>{if(!district.value){upazila.disabled=true;upazila.innerHTML='<option value="">Select district first</option>';return;}upazila.disabled=true;upazila.innerHTML='<option value="">Loading...</option>';try{const response=await fetch('<?= site_url('api/locations/upazilas') ?>/'+district.value);const data=await response.json();upazila.innerHTML='<option value="">Select upazila / thana</option>'+data.map(row=>`<option value="${row.id}">${row.name_en}${row.name_bn?' / '+row.name_bn:''}</option>`).join('');upazila.disabled=false;if(selected)upazila.value=String(selected);}catch(error){upazila.innerHTML='<option value="">Could not load locations</option>';}};
 district.addEventListener('change',()=>loadUpazilas(0));if(district.value)loadUpazilas(<?= (int)old('upazila_id') ?>);
 const updatePayment=()=>{const type=payment.selectedOptions[0]?.dataset.type||'',manual=['manual_mobile_banking','manual_bank_transfer'].includes(type);reference.classList.toggle('d-none',!manual);transaction.required=manual;};payment.addEventListener('change',updatePayment);updatePayment();
});
</script>
<?= $this->endSection() ?>
