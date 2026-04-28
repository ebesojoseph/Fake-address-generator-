<?php
// templates/generator_widget.php — Reusable generator card + sidebar form
// Variables expected: $defaultCountry (string), $initialAddress (array)
$defaultCountry ??= 'us';
$initialAddress ??= AddressGenerator::generate($defaultCountry);
$countries = AddressGenerator::countries();
$states    = AddressGenerator::states($defaultCountry);
?>

<div class="addr-card-wrapper card">
  <div class="card-header">
    <h3>Generated Address Details</h3>
  </div>
  <div id="addr-loading"><span class="spinner"></span> Generating…</div>

  <div class="address-display card-body" id="addr-display">
    <?php
    $fields = [
      'Name'    => ['id'=>'addr-name',    'val'=>$initialAddress['name']],
      'Gender'  => ['id'=>'addr-gender',  'val'=>$initialAddress['gender']],
      'Street'  => ['id'=>'addr-street',  'val'=>$initialAddress['street']],
      'City'    => ['id'=>'addr-city',    'val'=>$initialAddress['city']],
      'State'   => ['id'=>'addr-state',   'val'=>$initialAddress['state']],
      'ZIP'     => ['id'=>'addr-zip',     'val'=>$initialAddress['zip']],
      'Country' => ['id'=>'addr-country', 'val'=>$initialAddress['country']],
      'Phone'   => ['id'=>'addr-phone',   'val'=>$initialAddress['phone']],
      'Email'   => ['id'=>'addr-email',   'val'=>$initialAddress['email']],
    ];
    foreach ($fields as $label => $f): ?>
      <div class="addr-row">
        <span class="addr-label"><?= e($label) ?>:</span>
        <strong class="addr-value" id="<?= $f['id'] ?>"><?= e($f['val']) ?></strong>
        <button class="copy-btn" data-target="<?= $f['id'] ?>" type="button">Copy</button>
      </div>
    <?php endforeach; ?>
  </div>

  <form id="addr-form" style="display:none">
    <input type="hidden" name="country" value="<?= e($defaultCountry) ?>">
    <input type="hidden" name="gender"  value="random">
    <input type="hidden" name="state"   value="">
  </form>

  <div style="padding:0 20px 10px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    <button class="btn-generate" id="btn-generate" type="button" onclick="document.getElementById('addr-form').dispatchEvent(new Event('submit'))">
      ↻ Generate New Address
    </button>
    <button class="btn btn-outline" id="copy-all-btn" type="button">Copy All</button>
  </div>
</div>
