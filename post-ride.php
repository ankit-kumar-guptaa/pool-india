<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();

$success = false;
$bookingId = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode         = $_POST['mode'] ?? 'carpool';
    $userType     = $_POST['userType'] ?? 'Pooler';
    $rideType     = $_POST['rideType'] ?? 'One-time'; 
    $tripType     = $_POST['tripType'] ?? 'One-way'; 
    
    $from         = trim($_POST['from'] ?? '');
    $to           = trim($_POST['to'] ?? '');
    $from_lat     = $_POST['from_lat'] ?? '0';
    $from_lng     = $_POST['from_lng'] ?? '0';
    $to_lat       = $_POST['to_lat'] ?? '0';
    $to_lng       = $_POST['to_lng'] ?? '0';
    
    $via1         = trim($_POST['via1'] ?? '');
    $via1_lat     = $_POST['via1_lat'] ?? '0';
    $via1_lng     = $_POST['via1_lng'] ?? '0';
    
    $via2         = trim($_POST['via2'] ?? '');
    $via2_lat     = $_POST['via2_lat'] ?? '0';
    $via2_lng     = $_POST['via2_lng'] ?? '0';
    
    $date         = trim($_POST['date'] ?? '');
    $time         = trim($_POST['time'] ?? '');
    
    $returnDate   = trim($_POST['returnDate'] ?? '');
    $returnTime   = trim($_POST['returnTime'] ?? '');
    
    $days         = $_POST['days'] ?? [];
    $selectedDays = array_fill(0, 7, false);
    if ($rideType === 'Recurring') {
        foreach($days as $dayIdx) {
            if (is_numeric($dayIdx) && $dayIdx >= 0 && $dayIdx <= 6) {
                $selectedDays[(int)$dayIdx] = true;
            }
        }
    }
    
    $seats        = (int)($_POST['seats']  ?? 1);
    $isVolunteer  = isset($_POST['volunteer']);
    $price        = $isVolunteer ? 0 : (float)($_POST['price']  ?? 0);
    $note         = trim($_POST['note']    ?? '');
    $fuelType     = trim($_POST['fuelType'] ?? 'Petrol');
    $totalDist    = (int)($_POST['totaldistance'] ?? 0);
    
    $carModel     = trim($_POST['carModel'] ?? '');
    $carNumber    = trim($_POST['carNumber'] ?? '');
    $carColor     = trim($_POST['carColor'] ?? '');
    $licenseNum   = trim($_POST['licenseNumber'] ?? '');

    if ($from && $to && $time) {
        $viaPoints = [];
        $viaCoords = [];
        if ($via1) { $viaPoints[] = $via1; $viaCoords[] = ['lat' => (float)$via1_lat, 'lng' => (float)$via1_lng]; }
        if ($via2) { $viaPoints[] = $via2; $viaCoords[] = ['lat' => (float)$via2_lat, 'lng' => (float)$via2_lng]; }

        $payload = [
            'userId' => $user['id'] ?? 1,
            'rideID' => 1,
            'userName' => $user['name'] ?? 'User',
            'userEmail' => $user['email'] ?? '',
            'userMobileNo' => $user['mobile_No'] ?? '',
            'userType' => $userType,
            'from_Address' => $from,
            'to_Address' => $to,
            'form_Latitude' => $from_lat,
            'form_Longitude' => $from_lng,
            'to_Latitude' => $to_lat,
            'to_Longitude' => $to_lng,
            'via' => $viaPoints,
            'viaCoordinates' => !empty($viaCoords) ? $viaCoords : null,
            'ride_Type' => $tripType,
            'ride_Date' => ($rideType === 'One-time' && $date) ? $date . 'T' . $time . ':00' : date('Y-m-d') . 'T' . $time . ':00',
            'ride_Frequency' => $rideType,
            'seats' => $seats,
            'price' => $price,
            'user_Comment' => $note,
            'carModel' => $carModel,
            'carNumber' => $carNumber,
            'carColor' => $carColor,
            'licenseNumber' => $licenseNum,
            'fuelType' => $fuelType,
            'returnDate' => $returnDate ?: null,
            'returnTime' => $returnTime ?: null,
            'selectedDays' => $rideType === 'Recurring' ? $selectedDays : null,
            'internal_code' => 'XYZ123',
            'senderName' => $user['name'] ?? 'User',
            'isSendRequest' => true,
            'isSearch' => 0,
            'userPhoto' => '',
            'companyName' => $user['companyName'] ?? '',
            'totaldistance' => $totalDist,
            'displayNumberOnSearch' => isset($_POST['displayNumberOnSearch'])
        ];
        
        $resp = RideService::postRide($payload);
        
        if (isset($resp['status']) && $resp['status'] === 1) {
            $bookingId = 'PI-POST-' . strtoupper(substr(md5(uniqid()), 0, 6));
            $_SESSION['posted_ride'] = compact('mode','from','to','date','time','seats','price','carModel','carNumber','note','bookingId');
            $success = true;
        } else {
            $errorMsg = $resp['message'] ?? 'Failed to post ride. Please try again.';
        }
    }
}

$mode = $_GET['mode'] ?? 'carpool';
$modeLabel = $mode === 'bike' ? 'Bike Buddy' : 'Carpool';
$modeIcon  = $mode === 'bike' ? 'fa-motorcycle' : 'fa-car-side';
$modeColor = $mode === 'bike' ? '#f3821a' : '#1b8036';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Post a Ride | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;}
.field-wrap label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.field{width:100%;background:#f8fafc;border:2px solid #e2e8f0;border-radius:14px;padding:13px 16px;font-size:15px;font-weight:600;color:#1d3a70;outline:none;transition:all .25s;font-family:'Plus Jakarta Sans',sans-serif;}
.field:focus{border-color:#1b8036;background:#fff;box-shadow:0 0 0 3px rgba(27,128,54,.1);}
.mode-tab{flex:1;padding:12px;border-radius:14px;font-weight:800;font-size:14px;cursor:pointer;transition:all .3s;border:2px solid #e2e8f0;background:#fff;color:#64748b;display:flex;align-items:center;justify-content:center;gap:8px;}
.mode-tab.active{border-color:var(--mc);background:var(--mcl);color:var(--mc);}
.submit-btn{width:100%;padding:16px;border-radius:16px;font-weight:800;font-size:17px;border:none;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:10px;}
.submit-carpool{background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;box-shadow:0 8px 25px rgba(27,128,54,.35);}
.submit-bike{background:linear-gradient(135deg,#f3821a,#e06b0a);color:#fff;box-shadow:0 8px 25px rgba(243,130,26,.35);}
.submit-btn:hover{transform:translateY(-2px);}
.pill-btn{padding:8px 16px;border:2px solid #e2e8f0;border-radius:999px;font-size:13px;font-weight:700;color:#64748b;cursor:pointer;transition:all .2s;}
.pill-btn.active{border-color:#1b8036;background:#f0fdf4;color:#1b8036;}
.day-btn{width:36px;height:36px;border-radius:50%;border:2px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#64748b;cursor:pointer;transition:all .2s;}
.day-btn.active{border-color:#1b8036;background:#1b8036;color:#fff;}
.seat-pill{flex:1;padding:10px;border:2px solid #e2e8f0;border-radius:10px;text-align:center;font-weight:800;cursor:pointer;transition:all .2s;background:#fff;color:#64748b;font-size:14px;}
.seat-pill.sel{border-color:var(--mc);background:var(--mcl);color:var(--mc);}
@keyframes pop{0%{transform:scale(0) rotate(-8deg);}60%{transform:scale(1.1) rotate(2deg);}100%{transform:scale(1);}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.pop-in{animation:pop .6s cubic-bezier(.34,1.56,.64,1) both;}
.fade-up{animation:fadeUp .4s ease both;}
</style>
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-20 pb-12 max-w-2xl mx-auto px-4">

<?php if ($success): ?>
<!-- ===== SUCCESS STATE ===== -->
<div class="text-center py-8 fade-up">
  <div class="pop-in w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6"
       style="background:linear-gradient(135deg,<?= $modeColor ?>,<?= $modeColor ?>aa);box-shadow:0 0 0 16px <?= $modeColor ?>22,0 0 0 32px <?= $modeColor ?>0d;">
    <i class="fa-solid fa-check text-white text-4xl"></i>
  </div>
  <h1 class="text-3xl font-black text-brand-blue mb-2">Ride Posted! 🎉</h1>
  <p class="text-gray-500 font-semibold mb-6">Your ride is live. Commuters can now request seats.</p>

  <div class="card p-6 text-left mb-6">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Ride Summary</p>
    <div class="space-y-3 text-sm">
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Booking ID</span><span class="font-black text-brand-blue tracking-wider">#<?= h($bookingId) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Type</span><span class="font-bold text-brand-blue"><?= h($modeLabel) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">From</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['from'] ?? '') ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">To</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['to'] ?? '') ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Time</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['time'] ?? '') ?></span></div>
      <div class="flex justify-between border-t border-gray-100 pt-3 mt-1"><span class="font-black text-brand-blue">Price/Seat</span><span class="font-black text-brand-green text-lg">₹<?= (int)($_SESSION['posted_ride']['price'] ?? 0) ?></span></div>
    </div>
  </div>

  <div class="flex flex-col gap-3">
    <a href="profile.php?tab=responses" class="submit-btn <?= $mode==='bike'?'submit-bike':'submit-carpool' ?>"><i class="fa-solid fa-reply"></i>View Requests</a>
    <a href="post-ride.php?mode=<?= $mode ?>" class="submit-btn" style="background:#f1f5f9;color:#1d3a70;"><i class="fa-solid fa-plus"></i>Post Another Ride</a>
  </div>
</div>

<?php else: ?>
<!-- ===== FORM STATE ===== -->
<div class="mb-6 fade-up">
  <h1 class="text-3xl font-black text-brand-blue mt-1">Post a Ride 🚗</h1>
  <p class="text-gray-500 font-semibold text-sm mt-1">Offer seats and earn while you travel</p>
</div>

<?php if ($errorMsg): ?>
<div class="bg-red-50 border-2 border-red-200 text-red-600 rounded-xl p-4 mb-6 font-bold text-sm">
    <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= h($errorMsg) ?>
</div>
<?php endif; ?>

<form method="POST" action="post-ride.php" id="post-form" class="space-y-4 fade-up" style="--mc:<?= $modeColor ?>;--mcl:<?= $modeColor ?>0d;">
  <input type="hidden" name="mode" value="<?= h($mode) ?>">
  <input type="hidden" name="userType" id="f-userType" value="Pooler">
  <input type="hidden" name="rideType" id="f-rideType" value="One-time">
  <input type="hidden" name="tripType" id="f-tripType" value="One-way">

  <!-- Type Selections -->
  <div class="card p-5 mb-4">
    <div class="mb-4">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Vehicle Type</label>
        <div class="flex gap-2">
          <a href="post-ride.php?mode=carpool" class="mode-tab <?= $mode==='carpool'?'active':'' ?>"><i class="fa-solid fa-car-side"></i> Car</a>
          <a href="post-ride.php?mode=bike" class="mode-tab <?= $mode==='bike'?'active':'' ?>"><i class="fa-solid fa-motorcycle"></i> Bike</a>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">I am a</label>
        <div class="flex gap-2 overflow-x-auto pb-1">
            <button type="button" class="pill-btn active" onclick="setUType('Pooler', this)">Pooler (Car Owner)</button>
            <button type="button" class="pill-btn" onclick="setUType('Seeker', this)">Seeker (Passenger)</button>
            <button type="button" class="pill-btn" onclick="setUType('Either', this)">Either</button>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Ride Frequency</label>
        <div class="flex gap-2">
            <button type="button" class="pill-btn active" onclick="setRType('One-time', this)">One-time</button>
            <button type="button" class="pill-btn" onclick="setRType('Recurring', this)">Recurring</button>
        </div>
        
        <div id="recurring-days" class="hidden mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm font-bold text-gray-600 mb-2">Select Days</p>
            <div class="flex gap-2 justify-between">
                <?php $days = ['S','M','T','W','T','F','S']; foreach($days as $i => $d): ?>
                <label class="day-btn">
                    <input type="checkbox" name="days[]" value="<?= $i ?>" class="hidden peer" onchange="this.parentElement.classList.toggle('active', this.checked)">
                    <span><?= $d ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div>
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Trip Type</label>
        <div class="flex gap-2">
            <button type="button" class="pill-btn active" onclick="setTType('One-way', this)">One-way</button>
            <button type="button" class="pill-btn" onclick="setTType('Round-trip', this)">Round-trip</button>
        </div>
    </div>
  </div>

  <!-- Route & Date -->
  <div class="card p-6 mb-4">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">📍 Route Details</p>
    <div class="space-y-3">
        <!-- FROM -->
        <div class="field-wrap">
          <label>Leaving From</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden" style="padding:0">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#f0fdf4;border-right:2px solid #e2e8f0;padding:12px 10px"><i class="fa-solid fa-circle-dot text-brand-green text-sm"></i></div>
            <input name="from" id="f-from" type="text" autocomplete="off" class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3" placeholder="Starting point..." required>
            <input type="hidden" name="from_lat" id="f-from-lat"><input type="hidden" name="from_lng" id="f-from-lng">
          </div>
        </div>
        
        <!-- Via Points Toggle -->
        <div class="px-2 py-1 flex justify-between items-center text-sm">
            <button type="button" class="text-brand-green font-bold hover:underline" onclick="toggleVia(1)">+ Add Via Point 1</button>
            <button type="button" class="text-brand-green font-bold hover:underline hidden" id="add-via-2" onclick="toggleVia(2)">+ Add Via Point 2</button>
        </div>
        
        <!-- Via Point 1 -->
        <div class="field-wrap hidden" id="via-1-wrap">
          <label>Via Point 1</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#eff6ff;border-right:2px solid #e2e8f0;padding:12px 10px"><i class="fa-solid fa-map-pin text-blue-500 text-sm"></i></div>
            <input name="via1" id="f-via1" type="text" autocomplete="off" class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3" placeholder="Via location...">
            <input type="hidden" name="via1_lat" id="f-via1-lat"><input type="hidden" name="via1_lng" id="f-via1-lng">
            <button type="button" class="pr-3 text-red-400" onclick="hideVia(1)"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>
        
        <!-- Via Point 2 -->
        <div class="field-wrap hidden" id="via-2-wrap">
          <label>Via Point 2</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#eff6ff;border-right:2px solid #e2e8f0;padding:12px 10px"><i class="fa-solid fa-map-pin text-blue-500 text-sm"></i></div>
            <input name="via2" id="f-via2" type="text" autocomplete="off" class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3" placeholder="Via location...">
            <input type="hidden" name="via2_lat" id="f-via2-lat"><input type="hidden" name="via2_lng" id="f-via2-lng">
            <button type="button" class="pr-3 text-red-400" onclick="hideVia(2)"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>

        <!-- TO -->
        <div class="field-wrap">
          <label>Going To</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden" style="padding:0">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#fff7ed;border-right:2px solid #e2e8f0;padding:12px 10px"><i class="fa-solid fa-location-dot text-brand-orange text-sm"></i></div>
            <input name="to" id="f-to" type="text" autocomplete="off" class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3" placeholder="Destination..." required>
            <input type="hidden" name="to_lat" id="f-to-lat"><input type="hidden" name="to_lng" id="f-to-lng">
          </div>
        </div>

        <!-- Date + Time -->
        <div class="grid grid-cols-2 gap-3 pt-3">
          <div class="field-wrap" id="date-wrap">
            <label>Date</label>
            <input name="date" type="date" class="field" min="<?= date('Y-m-d') ?>">
          </div>
          <div class="field-wrap">
            <label>Departure Time</label>
            <input name="time" type="time" class="field" required>
          </div>
        </div>
        
        <!-- Return Date + Time -->
        <div class="grid grid-cols-2 gap-3 pt-3 hidden" id="return-wrap">
          <div class="field-wrap">
            <label>Return Date</label>
            <input name="returnDate" type="date" class="field" min="<?= date('Y-m-d') ?>">
          </div>
          <div class="field-wrap">
            <label>Return Time</label>
            <input name="returnTime" type="time" class="field">
          </div>
        </div>
    </div>
  </div>

  <!-- Route Distance Display -->
  <div class="card p-5 mb-4 hidden" id="route-info-card">
    <div class="flex items-center gap-4 flex-wrap">
      <div class="flex items-center gap-2 bg-green-50 px-4 py-2.5 rounded-xl">
        <i class="fa-solid fa-road text-brand-green"></i>
        <span class="text-sm font-black text-brand-green" id="route-dist">-- km</span>
      </div>
      <div class="flex items-center gap-2 bg-blue-50 px-4 py-2.5 rounded-xl">
        <i class="fa-solid fa-clock text-brand-blue"></i>
        <span class="text-sm font-black text-brand-blue" id="route-dur">-- min</span>
      </div>
      <div class="flex items-center gap-2 bg-emerald-50 px-4 py-2.5 rounded-xl">
        <i class="fa-solid fa-leaf text-emerald-600"></i>
        <span class="text-sm font-black text-emerald-600" id="route-co2">-- kg CO₂</span>
      </div>
    </div>
    <input type="hidden" name="totaldistance" id="f-totaldist" value="0">
  </div>

  <!-- Seats, Price & Fuel -->
  <div class="card p-6 mb-4">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">💺 Seats & Pricing</p>
    <div class="space-y-5">
        <div class="field-wrap">
          <label>Seats Available/Needed</label>
          <div class="flex gap-2">
            <?php $maxSeats = $mode==='bike' ? 1 : 4; for($s=1;$s<=$maxSeats;$s++): ?>
            <button type="button" class="seat-pill <?= $s===1?'sel':'' ?>" onclick="setSeats(<?= $s ?>,this)"><?= $s ?></button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="seats" id="f-seats" value="1">
        </div>

        <!-- Fuel Type -->
        <div class="field-wrap" id="fuel-type-wrap">
          <label>Fuel Type</label>
          <div class="flex gap-2 flex-wrap">
            <button type="button" class="pill-btn active" onclick="setFuel('Petrol',this)">⛽ Petrol</button>
            <button type="button" class="pill-btn" onclick="setFuel('Diesel',this)">🛢️ Diesel</button>
            <button type="button" class="pill-btn" onclick="setFuel('CNG',this)">💨 CNG</button>
            <button type="button" class="pill-btn" onclick="setFuel('Electric',this)">🔋 EV</button>
          </div>
          <input type="hidden" name="fuelType" id="f-fuelType" value="Petrol">
        </div>

        <!-- Volunteer Toggle -->
        <div class="flex items-center gap-3 p-4 rounded-2xl border-2 border-dashed transition-all" id="volunteer-wrap" style="border-color:#fde68a;background:#fffbeb;">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="volunteer" id="f-volunteer" class="sr-only peer" onchange="toggleVolunteer()">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-orange"></div>
          </label>
          <div>
            <p class="font-black text-sm text-amber-700">🤝 Volunteer for Cause</p>
            <p class="text-xs text-amber-600/70 font-semibold">Offer free ride — help build a greener India!</p>
          </div>
        </div>

        <!-- Dynamic Price -->
        <div class="field-wrap" id="price-wrap">
          <label>Price per Seat (₹)</label>
          <div class="flex items-center gap-3">
            <button type="button" onclick="adjustPrice(-5)" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 font-black text-lg flex items-center justify-center hover:bg-red-100 transition">−</button>
            <div class="flex-1 relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-brand-green text-lg">₹</span>
              <input name="price" id="f-price" type="number" class="field text-center text-xl font-black" style="padding-left:36px" placeholder="0" min="0" value="50">
            </div>
            <button type="button" onclick="adjustPrice(5)" class="w-10 h-10 rounded-xl bg-green-50 text-brand-green font-black text-lg flex items-center justify-center hover:bg-green-100 transition">+</button>
          </div>
          <div class="mt-2 text-xs font-bold text-gray-400 flex justify-between" id="price-hint">
            <span>Suggested: <span class="text-brand-green" id="suggested-price">₹50</span></span>
            <span>Range: <span id="price-range">₹25 – ₹100</span></span>
          </div>
        </div>
    </div>
  </div>

  <!-- Car Details -->
  <div class="card p-6 mb-4" id="car-details">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">🚗 Vehicle Details</p>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div class="field-wrap"><label>Model</label><input name="carModel" class="field" placeholder="Swift" value="<?= h($user['carModel'] ?? '') ?>"></div>
            <div class="field-wrap"><label>Color</label><input name="carColor" class="field" placeholder="White" value="<?= h($user['carColor'] ?? '') ?>"></div>
        </div>
        <div class="field-wrap"><label>Reg Number</label><input name="carNumber" class="field" placeholder="DL 3C XX 1234" value="<?= h($user['carNumber'] ?? '') ?>" style="text-transform:uppercase"></div>
        <div class="field-wrap"><label>Driving License</label><input name="licenseNumber" class="field" placeholder="Optional" value="<?= h($user['licenseNumber'] ?? '') ?>"></div>
    </div>
  </div>
  
  <div class="card p-6 mb-6">
    <div class="field-wrap">
        <label>Remarks</label>
        <textarea name="note" class="field" rows="2" placeholder="Any special instructions..."></textarea>
    </div>
    <div class="mt-4 flex items-center gap-3">
        <input type="checkbox" name="displayNumberOnSearch" id="disp-num" class="w-5 h-5 accent-brand-green">
        <label for="disp-num" class="text-sm font-bold text-gray-600">Show Mobile Number on Search</label>
    </div>
  </div>

  <button type="submit" class="submit-btn <?= $mode==='bike'?'submit-bike':'submit-carpool' ?>" id="submit-btn">
    <i class="fa-solid <?= $modeIcon ?>"></i> Submit
  </button>
</form>
<?php endif; ?>
</div>

<script src="js/places-ac.js"></script>
<script>
let _suggestedPrice = 50, _minPrice = 25, _maxPrice = 100, _distKm = 0;

function setUType(val, el) {
    document.getElementById('f-userType').value = val;
    el.parentElement.querySelectorAll('.pill-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    const isSeeker = val==='Seeker';
    document.getElementById('car-details').style.display = isSeeker ? 'none' : 'block';
    document.getElementById('fuel-type-wrap').style.display = isSeeker ? 'none' : 'block';
}

function setRType(val, el) {
    document.getElementById('f-rideType').value = val;
    el.parentElement.querySelectorAll('.pill-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('recurring-days').style.display = val==='Recurring' ? 'block' : 'none';
    document.getElementById('date-wrap').style.display = val==='Recurring' ? 'none' : 'block';
    const dtInp = document.querySelector('[name=date]');
    if(val==='Recurring') dtInp.removeAttribute('required'); else dtInp.setAttribute('required','required');
}

function setTType(val, el) {
    document.getElementById('f-tripType').value = val;
    el.parentElement.querySelectorAll('.pill-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('return-wrap').style.display = val==='Round-trip' ? 'flex' : 'none';
}

function setSeats(n, el) {
    document.getElementById('f-seats').value = n;
    document.querySelectorAll('.seat-pill').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
}

function setFuel(val, el) {
    document.getElementById('f-fuelType').value = val;
    el.parentElement.querySelectorAll('.pill-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
}

function toggleVolunteer() {
    const checked = document.getElementById('f-volunteer').checked;
    const priceWrap = document.getElementById('price-wrap');
    const volWrap = document.getElementById('volunteer-wrap');
    if (checked) {
        priceWrap.style.display = 'none';
        document.getElementById('f-price').value = 0;
        volWrap.style.borderColor = '#f3821a';
        volWrap.style.background = '#fff7ed';
    } else {
        priceWrap.style.display = 'block';
        document.getElementById('f-price').value = _suggestedPrice;
        volWrap.style.borderColor = '#fde68a';
        volWrap.style.background = '#fffbeb';
    }
}

function adjustPrice(delta) {
    const inp = document.getElementById('f-price');
    let val = parseInt(inp.value || 0) + delta;
    if (val < 0) val = 0;
    inp.value = val;
}

// Dynamic price from distance (Flutter formula: ₹50 base + ₹3.5/km after 15km)
function updatePriceFromDistance(km) {
    _distKm = km;
    let base = 50;
    if (km > 15) base += (km - 15) * 3.5;
    _suggestedPrice = Math.round(base);
    _minPrice = Math.round(_suggestedPrice * 0.5);
    _maxPrice = Math.round(_suggestedPrice * 2);

    document.getElementById('suggested-price').textContent = '₹' + _suggestedPrice;
    document.getElementById('price-range').textContent = '₹' + _minPrice + ' – ₹' + _maxPrice;

    // Only update price if not volunteer and user hasn't manually edited
    if (!document.getElementById('f-volunteer').checked) {
        document.getElementById('f-price').value = _suggestedPrice;
    }
    document.getElementById('f-totaldist').value = Math.round(km);
}

// Route distance calculation
async function calcRoute() {
    const flat = document.getElementById('f-from-lat')?.value;
    const flng = document.getElementById('f-from-lng')?.value;
    const tlat = document.getElementById('f-to-lat')?.value;
    const tlng = document.getElementById('f-to-lng')?.value;
    const card = document.getElementById('route-info-card');

    if (!flat || !tlat || !flng || !tlng) {
        card?.classList.add('hidden');
        return;
    }
    try {
        const d = await PI_Places.getDistance({lat:+flat,lng:+flng},{lat:+tlat,lng:+tlng});
        document.getElementById('route-dist').textContent = d.distance_text;
        document.getElementById('route-dur').textContent = d.duration_text;
        document.getElementById('route-co2').textContent = (d.distance_km * 0.12).toFixed(1) + ' kg CO₂';
        card?.classList.remove('hidden');
        updatePriceFromDistance(d.distance_km);
    } catch(e) {
        console.warn('Distance calc error:', e);
    }
}

function toggleVia(n) {
    document.getElementById(`via-${n}-wrap`).classList.remove('hidden');
    if(n===1) document.getElementById('add-via-2').classList.remove('hidden');
}
function hideVia(n) {
    document.getElementById(`via-${n}-wrap`).classList.add('hidden');
    document.getElementById(`f-via${n}`).value = '';
    if(n===1) {
        document.getElementById('add-via-2').classList.add('hidden');
        hideVia(2);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if(typeof PI_Places !== 'undefined') {
        PI_Places.initAll([
            { inputId: 'f-from', latId: 'f-from-lat', lngId: 'f-from-lng', onSelect: () => calcRoute() },
            { inputId: 'f-to', latId: 'f-to-lat', lngId: 'f-to-lng', onSelect: () => calcRoute() },
            { inputId: 'f-via1', latId: 'f-via1-lat', lngId: 'f-via1-lng' },
            { inputId: 'f-via2', latId: 'f-via2-lat', lngId: 'f-via2-lng' }
        ]);
    }
    document.getElementById('post-form')?.addEventListener('submit', function() {
        document.getElementById('submit-btn').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Posting...';
        document.getElementById('submit-btn').disabled = true;
    });
});
</script>
</body>
</html>
