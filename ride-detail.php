<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();

// ── Ride data comes from search results via GET params ──
$rideId    = (int)($_GET['id'] ?? 0);
$from      = h($_GET['from'] ?? '');
$to        = h($_GET['to'] ?? '');

// All the ride detail fields from PostRide API response
$rName     = h($_GET['name']    ?? 'Pooler');
$rUserType = h($_GET['utype']   ?? 'Pooler');
$rPrice    = (float)($_GET['price']  ?? 0);
$rSeats    = (int)($_GET['seats']    ?? 1);
$rFrom     = h($_GET['rfrom']   ?? $from);
$rTo       = h($_GET['rto']     ?? $to);
$rDate     = h($_GET['rdate']   ?? date('Y-m-d'));
$rFreq     = h($_GET['rfreq']   ?? 'One-time');
$rType     = h($_GET['rtype']   ?? 'One-way');
$rComment  = h($_GET['comment'] ?? '');
$rCarModel = h($_GET['car']     ?? '');
$rCarNum   = h($_GET['carnum']  ?? '');
$rCarColor = h($_GET['color']   ?? '');
$rFuel     = h($_GET['fuel']    ?? '');
$rPhoto    = $_GET['photo'] ?? '';
$rMobile   = h($_GET['mobile'] ?? '');
$rEmail    = h($_GET['email']  ?? '');
$rCompany  = h($_GET['company'] ?? '');
$rFromDist = (int)($_GET['fdist'] ?? 0);
$rToDist   = (int)($_GET['tdist'] ?? 0);
$rTotalDist= (int)($_GET['totdist'] ?? 0);
$rUserId   = (int)($_GET['uid'] ?? 0);
$rVerified = (int)($_GET['verified'] ?? 0);
$rShowNum  = (int)($_GET['shownum'] ?? 0);
$isVolunteer = $rPrice <= 0;

// Photo fallback
if (empty($rPhoto) || $rPhoto === 'null') {
    $rPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($rName) . '&background=1d3a70&color=fff&bold=true&size=128';
}

// Parse ride date/time
$dateObj = strtotime($rDate);
$dateFormatted = $dateObj ? date('D, d M Y', $dateObj) : $rDate;
$timeFormatted = $dateObj ? date('h:i A', $dateObj) : '--';

// Handle AJAX SendRideRequest
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'connect') {
    $resp = ApiService::post('app/SendRideRequest', [
        'userId' => $user['id'] ?? 0,
        'rideId' => $rideId,
    ]);
    $ok = isset($resp['status']) && $resp['status'] === 1;
    jsonResponse([
        'ok'      => $ok,
        'message' => $ok ? 'Connection request sent!' : ($resp['message'] ?? 'Failed. Please try again.'),
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $rName ?> · Ride Details | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
.route-dot-g{width:14px;height:14px;border-radius:50%;border:3px solid #1b8036;background:#fff;flex-shrink:0;}
.route-dot-o{width:14px;height:14px;border-radius:50%;border:3px solid #f3821a;background:#fff;flex-shrink:0;}
.route-ln{width:2px;background:linear-gradient(#1b8036,#f3821a);flex:1;min-height:40px;margin:0 auto;}
.connect-btn{background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;font-weight:800;font-size:17px;padding:17px;border-radius:16px;border:none;cursor:pointer;transition:all .3s;box-shadow:0 8px 25px rgba(27,128,54,.35);width:100%;}
.connect-btn:hover{transform:translateY(-2px);box-shadow:0 14px 35px rgba(27,128,54,.45);}
.connect-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:flex-end;justify-content:center;}
.modal-sheet{background:#fff;border-radius:2rem 2rem 0 0;padding:28px 24px 40px;width:100%;max-width:520px;}
@keyframes slideUp{from{transform:translateY(100%);}to{transform:translateY(0);}}
.slide-up{animation:slideUp .4s cubic-bezier(.4,0,.2,1);}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;}
.info-row:last-child{border-bottom:none;}
.info-label{color:#94a3b8;font-weight:700;font-size:13px;}
.info-val{color:#1d3a70;font-weight:800;font-size:13px;text-align:right;}
#toast{position:fixed;bottom:100px;left:50%;transform:translateX(-50%) translateY(80px);padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;z-index:9999;transition:transform .35s,opacity .35s;opacity:0;}
#toast.show{transform:translateX(-50%) translateY(0);opacity:1;}
#toast.success{background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0;}
#toast.error{background:#fef2f2;color:#991b1b;border:1.5px solid #fecaca;}
@media(max-width:639px){
    .card{padding:16px !important;}
    .driver-header{flex-direction:column;align-items:flex-start;gap:12px;}
    .driver-header .text-right{text-align:left;}
}
</style>
</head>
<body>
<div id="toast" class="success"><i class="fa-solid fa-circle-check"></i><span id="t-msg"></span></div>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-20 pb-32 max-w-3xl mx-auto px-4">

  <!-- Back -->
  <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-sm text-gray-500 font-bold hover:text-brand-blue transition mb-4">
    <i class="fa-solid fa-arrow-left"></i> Back to Results
  </a>

  <!-- ===== User / Driver Card ===== -->
  <div class="card p-6 mb-4 fade-up">
    <div class="flex items-center gap-5 driver-header">
      <div class="relative shrink-0">
        <img src="<?= h($rPhoto) ?>" class="w-20 h-20 rounded-2xl object-cover"
             onerror="this.src='https://ui-avatars.com/api/?name=User&background=1d3a70&color=fff'" alt="<?= $rName ?>">
        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
          <i class="fa-solid fa-check text-white text-[8px]"></i>
        </div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-start flex-wrap gap-2">
          <div>
            <p class="font-black text-brand-blue text-xl"><?= $rName ?></p>
            <div class="flex flex-wrap gap-1.5 mt-1.5">
              <?php if($rUserType === 'Pooler'): ?>
                <span class="badge" style="background:#dcfce7;color:#166534;"><i class="fa-solid fa-car-side text-[10px]"></i> Pooler</span>
              <?php elseif($rUserType === 'Seeker'): ?>
                <span class="badge" style="background:#fff7ed;color:#9a3412;"><i class="fa-solid fa-person-walking text-[10px]"></i> Seeker</span>
              <?php else: ?>
                <span class="badge" style="background:#ede9fe;color:#5b21b6;"><i class="fa-solid fa-arrows-left-right text-[10px]"></i> Either</span>
              <?php endif; ?>
              <span class="badge" style="background:#dbeafe;color:#1e40af;">✓ Verified</span>
              <?php if($isVolunteer): ?>
                <span class="badge" style="background:#fef3c7;color:#92400e;"><i class="fa-solid fa-heart text-[10px]"></i> Volunteer</span>
              <?php endif; ?>
              <?php if($rCompany): ?>
                <span class="badge" style="background:#f0f9ff;color:#0369a1;"><i class="fa-solid fa-building text-[10px]"></i> <?= $rCompany ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-right shrink-0">
            <?php if($isVolunteer): ?>
              <p class="text-2xl font-black text-brand-orange">FREE</p>
              <p class="text-xs text-gray-400 font-semibold">volunteer ride</p>
            <?php else: ?>
              <p class="text-3xl font-black text-brand-green">₹<?= (int)$rPrice ?></p>
              <p class="text-xs text-gray-400 font-semibold">per seat</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact -->
    <?php if($rShowNum && $rMobile): ?>
    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
      <a href="tel:<?= $rMobile ?>" class="flex items-center gap-2 bg-brand-green/10 text-brand-green font-bold text-sm px-4 py-2.5 rounded-xl hover:bg-brand-green hover:text-white transition">
        <i class="fa-solid fa-phone"></i> <?= $rMobile ?>
      </a>
      <?php if($rEmail): ?>
      <a href="mailto:<?= $rEmail ?>" class="flex items-center gap-2 bg-brand-blue/10 text-brand-blue font-bold text-sm px-4 py-2.5 rounded-xl hover:bg-brand-blue hover:text-white transition">
        <i class="fa-solid fa-envelope"></i> Email
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ===== Route & Timing ===== -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.06s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">📍 Route & Timing</p>
    <div class="flex gap-5">
      <div class="flex flex-col items-center">
        <div class="route-dot-g"></div>
        <div class="route-ln"></div>
        <div class="route-dot-o"></div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="pb-6">
          <p class="font-black text-brand-blue text-base"><?= $timeFormatted ?></p>
          <p class="font-semibold text-gray-600 text-sm truncate"><?= $rFrom ?></p>
          <?php if($rFromDist > 0): ?>
          <p class="text-xs text-brand-green mt-1 font-bold"><i class="fa-solid fa-location-crosshairs mr-1"></i><?= $rFromDist ?> km from your pickup</p>
          <?php endif; ?>
        </div>
        <div class="pt-2">
          <p class="font-black text-brand-blue text-base">Drop-off</p>
          <p class="font-semibold text-gray-600 text-sm truncate"><?= $rTo ?></p>
          <?php if($rToDist > 0): ?>
          <p class="text-xs text-brand-orange mt-1 font-bold"><i class="fa-solid fa-location-crosshairs mr-1"></i><?= $rToDist ?> km from your drop</p>
          <?php endif; ?>
        </div>
      </div>
      <?php if($rTotalDist > 0): ?>
      <div class="text-right shrink-0">
        <div class="bg-brand-blue/5 rounded-xl p-3 text-center">
          <p class="text-xs text-gray-500 font-bold">Distance</p>
          <p class="font-black text-brand-blue text-lg"><?= $rTotalDist ?> km</p>
          <p class="text-xs text-emerald-600 font-bold mt-1"><i class="fa-solid fa-leaf mr-1"></i><?= round($rTotalDist * 0.12, 1) ?> kg CO₂</p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ===== Ride Details Grid ===== -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.1s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">🚗 Ride Details</p>
    <div>
      <div class="info-row"><span class="info-label">📅 Date</span><span class="info-val"><?= $dateFormatted ?></span></div>
      <div class="info-row"><span class="info-label">🕐 Time</span><span class="info-val"><?= $timeFormatted ?></span></div>
      <div class="info-row"><span class="info-label">🔄 Frequency</span><span class="info-val"><?= $rFreq ?></span></div>
      <div class="info-row"><span class="info-label">↔️ Trip Type</span><span class="info-val"><?= $rType ?></span></div>
      <?php if($rSeats > 0): ?>
      <div class="info-row"><span class="info-label">💺 Seats</span><span class="info-val"><?= $rSeats ?> seat<?= $rSeats > 1 ? 's' : '' ?></span></div>
      <?php endif; ?>
      <div class="info-row"><span class="info-label">👤 User Type</span>
        <span class="info-val">
          <?php if($rUserType === 'Pooler'): ?>🚗 Pooler (Car Owner)
          <?php elseif($rUserType === 'Seeker'): ?>🚶 Seeker (Passenger)
          <?php else: ?>↔️ Either<?php endif; ?>
        </span>
      </div>
    </div>
  </div>

  <!-- ===== Vehicle Details ===== -->
  <?php if($rCarModel || $rCarNum || $rCarColor): ?>
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.14s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">🚘 Vehicle Details</p>
    <div class="flex justify-between items-center">
      <div>
        <?php if($rCarModel): ?>
        <p class="font-black text-brand-blue text-base"><?= $rCarModel ?></p>
        <?php endif; ?>
        <p class="text-gray-500 text-sm font-semibold">
          <?= implode(' · ', array_filter([$rCarNum, $rCarColor, $rFuel])) ?>
        </p>
      </div>
      <div class="w-14 h-14 bg-brand-blue/5 rounded-2xl flex items-center justify-center text-brand-blue text-2xl">
        <i class="fa-solid fa-car-side"></i>
      </div>
    </div>
    <?php if($rSeats > 0): ?>
    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
      <p class="text-sm font-bold text-gray-600 mr-2">Seats:</p>
      <?php for($s=0;$s<min($rSeats,4);$s++): ?>
      <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm border-2 border-brand-green bg-green-50 text-brand-green"><i class="fa-solid fa-user text-xs"></i></div>
      <?php endfor; ?>
      <span class="text-xs text-brand-green font-black ml-1"><?= $rSeats ?> available</span>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ===== Remarks ===== -->
  <?php if($rComment): ?>
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.18s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">💬 Remarks</p>
    <p class="text-gray-600 font-semibold text-sm leading-relaxed"><?= $rComment ?></p>
  </div>
  <?php endif; ?>

  <!-- ===== Price Breakdown ===== -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.22s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">💰 Price Breakdown</p>
    <div class="space-y-2 text-sm">
      <div class="flex justify-between">
        <span class="text-gray-600 font-semibold">Seat Price</span>
        <span class="font-bold text-brand-blue"><?= $isVolunteer ? 'FREE (Volunteer)' : '₹'.(int)$rPrice ?></span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-600 font-semibold">Platform Fee</span>
        <span class="font-bold text-brand-green">FREE</span>
      </div>
      <div class="flex justify-between border-t border-gray-100 pt-2 mt-2">
        <span class="font-black text-brand-blue">Total</span>
        <span class="font-black text-brand-green text-lg"><?= $isVolunteer ? 'FREE' : '₹'.(int)$rPrice ?></span>
      </div>
    </div>
    <p class="text-xs text-gray-400 mt-3"><i class="fa-solid fa-circle-info mr-1"></i>Pay cash or UPI directly to driver after the ride</p>
  </div>

  <!-- ===== GreenCar Impact ===== -->
  <?php if($rTotalDist > 0): ?>
  <div class="card p-5 mb-4 fade-up" style="animation-delay:.26s;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border-color:#bbf7d0;">
    <p class="text-xs font-black text-emerald-700 uppercase tracking-widest mb-3">🌿 Environmental Impact</p>
    <div class="flex gap-4 justify-between flex-wrap">
      <div class="text-center">
        <p class="text-2xl font-black text-emerald-600"><?= round($rTotalDist * 0.12, 1) ?></p>
        <p class="text-xs text-emerald-600/70 font-bold">kg CO₂ saved</p>
      </div>
      <div class="text-center">
        <p class="text-2xl font-black text-emerald-600"><?= round($rTotalDist / 22, 1) ?></p>
        <p class="text-xs text-emerald-600/70 font-bold">liters fuel saved</p>
      </div>
      <div class="text-center">
        <p class="text-2xl font-black text-emerald-600"><?= round($rTotalDist * 0.0076) ?></p>
        <p class="text-xs text-emerald-600/70 font-bold">trees equivalent</p>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- ===== Sticky Connect Bar ===== -->
<?php if($rUserId !== ($user['id'] ?? -1)): ?>
<div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-gray-200 p-4 z-40">
  <div class="max-w-3xl mx-auto flex gap-3">
    <button id="connect-btn" class="connect-btn" onclick="document.getElementById('connect-modal').classList.remove('hidden')">
      <i class="fa-solid fa-handshake mr-2"></i>Connect & Send Request
    </button>
    <?php if($rShowNum && $rMobile): ?>
    <a href="tel:<?= $rMobile ?>" class="w-14 h-14 rounded-2xl bg-brand-green/10 flex items-center justify-center text-brand-green hover:bg-brand-green hover:text-white transition shrink-0">
      <i class="fa-solid fa-phone text-xl"></i>
    </a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ===== Connect Modal ===== -->
<div class="modal-overlay hidden" id="connect-modal" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="modal-sheet slide-up">
    <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-6"></div>
    <h3 class="text-2xl font-black text-brand-blue mb-1">Send Connection Request</h3>
    <p class="text-gray-500 text-sm mb-5">Connecting with <?= $rName ?></p>
    <div class="bg-brand-green/5 border border-brand-green/20 rounded-2xl p-4 mb-5">
      <div class="flex justify-between mb-3">
        <p class="font-bold text-brand-blue text-sm"><i class="fa-solid fa-circle-dot text-brand-green mr-2"></i><?= $rFrom ?></p>
      </div>
      <div class="flex justify-between mb-3">
        <p class="font-bold text-brand-blue text-sm"><i class="fa-solid fa-location-dot text-brand-orange mr-2"></i><?= $rTo ?></p>
      </div>
      <div class="flex justify-between items-center border-t border-brand-green/20 pt-3">
        <span class="text-sm text-gray-500 font-semibold"><?= $dateFormatted ?> · <?= $timeFormatted ?></span>
        <span class="text-xl font-black text-brand-green"><?= $isVolunteer ? 'FREE' : '₹'.(int)$rPrice ?></span>
      </div>
    </div>
    <p class="text-xs text-gray-400 mb-5 leading-relaxed"><i class="fa-solid fa-info-circle mr-1"></i>A connection request will be sent to <?= $rName ?>. They will be notified via email, WhatsApp, and push notification. Once accepted, you can coordinate the ride details.</p>
    <button id="confirm-btn" class="connect-btn" onclick="doConnect()"><i class="fa-solid fa-paper-plane mr-2"></i>Send Request</button>
    <button onclick="document.getElementById('connect-modal').classList.add('hidden')" class="w-full mt-3 py-3 text-gray-500 font-bold text-sm hover:text-brand-blue transition">Cancel</button>
  </div>
</div>

<script>
async function doConnect() {
    const btn = document.getElementById('confirm-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Sending...';
    try {
        const fd = new FormData();
        fd.append('action', 'connect');
        const r = await fetch('ride-detail.php?id=<?= $rideId ?>', {method:'POST', body: fd});
        const d = await r.json();
        const toast = document.getElementById('toast');
        const msg = document.getElementById('t-msg');
        if(d.ok) {
            msg.textContent = d.message || 'Request sent successfully!';
            toast.className = 'success show';
            toast.style.cssText = 'position:fixed;bottom:100px;left:50%;transform:translateX(-50%);padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;z-index:9999;opacity:1;background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0;';
            document.getElementById('connect-modal').classList.add('hidden');
            // Disable connect btn
            const mainBtn = document.getElementById('connect-btn');
            if(mainBtn) {
                mainBtn.disabled = true;
                mainBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Request Sent';
                mainBtn.style.background = '#94a3b8';
                mainBtn.style.boxShadow = 'none';
            }
            setTimeout(()=> toast.style.opacity = '0', 4000);
        } else {
            msg.textContent = d.message || 'Failed. Please try again.';
            toast.style.cssText = 'position:fixed;bottom:100px;left:50%;transform:translateX(-50%);padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;z-index:9999;opacity:1;background:#fef2f2;color:#991b1b;border:1.5px solid #fecaca;';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Send Request';
            setTimeout(()=> toast.style.opacity = '0', 4000);
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Send Request';
        alert('Network error. Please try again.');
    }
}
</script>
</body>
</html>
