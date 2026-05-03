<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();
$from  = h($_GET['from']  ?? '');
$to    = h($_GET['to']    ?? '');
$date  = h($_GET['date']  ?? date('Y-m-d'));
$seats = (int)($_GET['seats'] ?? 1);
$fromLat = $_GET['from_lat'] ?? '';
$fromLng = $_GET['from_lng'] ?? '';
$toLat   = $_GET['to_lat'] ?? '';
$toLng   = $_GET['to_lng'] ?? '';

// Build FULL PostRide model matching Flutter's toJson() format
// The API uses the same PostRide endpoint for both posting (isSearch=0) and searching (isSearch=1)
$rides = [];
if ($fromLat && $toLat && $from && $to) {
    $searchPayload = [
        'via'             => [],
        'userId'          => $user['id'] ?? 0,
        'rideID'          => 1,
        'userName'        => $user['name'] ?? 'User',
        'userEmail'       => $user['email'] ?? '',
        'userMobileNo'    => $user['mobile_No'] ?? '',
        'userType'        => 'Pooler',
        'from_Address'    => $_GET['from'] ?? '',
        'to_Address'      => $_GET['to'] ?? '',
        'form_Latitude'   => $fromLat,
        'form_Longitude'  => $fromLng,
        'to_Latitude'     => $toLat,
        'to_Longitude'    => $toLng,
        'ride_Type'       => 'One-time',
        'ride_Date'       => $date . 'T00:00:00',
        'ride_Frequency'  => 'One-way',
        'user_Comment'    => '',
        'internal_code'   => 'XYZ123',
        'senderName'      => $user['name'] ?? 'User',
        'isSendRequest'   => false,
        'isSearch'        => 1,  // KEY: This tells the API to SEARCH, not post
        'seats'           => 0,
        'userPhoto'       => '',
        'companyName'     => $user['companyName'] ?? '',
        'price'           => 0,
        'displayNumberOnSearch' => false,
        'totaldistance'   => 0,
    ];

    $ridesResp = RideService::searchRides($searchPayload);
    if (isset($ridesResp['data']) && is_array($ridesResp['data'])) {
        $rides = $ridesResp['data'];
    }
}

// Ensure type icons mapping covers backend terms
$typeIcons = ['Carpool'=>'fa-car-side','Bike'=>'fa-motorcycle','CabShare'=>'fa-taxi','Shuttle'=>'fa-van-shuttle', 'Daily'=>'fa-car-side', 'Recurring'=>'fa-car-side', 'Onetime'=>'fa-car-side'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Search Rides | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
.ride-card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;transition:all .3s;cursor:pointer;}
.ride-card:hover{border-color:#1b8036;box-shadow:0 8px 32px rgba(27,128,54,.13);transform:translateY(-3px);}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
.chip{padding:8px 16px;border-radius:999px;border:2px solid #e2e8f0;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;background:#fff;color:#64748b;white-space:nowrap;}
.chip.active,.chip:hover{border-color:#1b8036;background:#f0fdf4;color:#1b8036;}
.src-input{border:none;outline:none;background:transparent;font-size:14px;font-weight:700;color:#fff;width:100%;font-family:'Plus Jakarta Sans',sans-serif;}
.src-input::placeholder{color:rgba(255,255,255,.4);}
.route-dot-g{width:10px;height:10px;border-radius:50%;border:2.5px solid #22c55e;background:#fff;flex-shrink:0;}
.route-dot-o{width:10px;height:10px;border-radius:50%;border:2.5px solid #f3821a;background:#fff;flex-shrink:0;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.no-rides-wrap{padding:60px 20px;text-align:center;}
.no-rides-icon{font-size:64px;color:#cbd5e1;margin-bottom:16px;}
/* Mobile responsive */
@media (max-width: 639px) {
    .search-route-row{flex-direction:column;gap:8px;}
    .search-route-row .flex-1{width:100%;}
    .search-swap-btn{transform:rotate(90deg);margin:4px auto;}
    .search-actions{flex-direction:column;}
    .search-actions input,.search-actions button{width:100%;}
    .ride-card{padding:16px !important;}
    .ride-card .flex.items-start{flex-direction:column;gap:12px;}
    .ride-card img{width:48px;height:48px;}
    .ride-card .w-56{width:100%;}
}
</style>
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-16">
  <!-- Search Header -->
  <div style="background:linear-gradient(135deg,#1d3a70,#0d2252)">
    <div class="max-w-4xl mx-auto px-4 py-5">
      <form method="GET" action="rides.php" id="ride-search-form" autocomplete="off">
        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-4">

          <!-- Route Row -->
          <div class="flex items-stretch gap-3 search-route-row">
            <!-- From -->
            <div class="flex-1 relative">
              <div class="flex items-center gap-2 mb-1">
                <div class="route-dot-g"></div>
                <span class="text-[10px] font-black text-white/60 uppercase tracking-wider">From</span>
              </div>
              <input id="r-from" name="from" type="text" autocomplete="off"
                value="<?= $from ?>" placeholder="Leaving from..."
                class="w-full bg-white/15 border-2 border-white/20 rounded-xl px-3 py-2.5 text-white font-bold text-sm outline-none placeholder-white/40 focus:border-white/60 focus:bg-white/25 transition">
              <input type="hidden" id="r-from-lat" name="from_lat" value="<?= h($fromLat) ?>">
              <input type="hidden" id="r-from-lng" name="from_lng" value="<?= h($fromLng) ?>">
            </div>

            <!-- Swap -->
            <div class="flex flex-col justify-center gap-1">
              <button type="button" onclick="rSwap()" id="r-swap"
                class="search-swap-btn w-8 h-8 rounded-full bg-white/15 border border-white/30 text-white/70 hover:bg-white hover:text-brand-blue flex items-center justify-center transition text-xs">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
              </button>
            </div>

            <!-- To -->
            <div class="flex-1 relative">
              <div class="flex items-center gap-2 mb-1">
                <div class="route-dot-o"></div>
                <span class="text-[10px] font-black text-white/60 uppercase tracking-wider">To</span>
              </div>
              <input id="r-to" name="to" type="text" autocomplete="off"
                value="<?= $to ?>" placeholder="Going to..."
                class="w-full bg-white/15 border-2 border-white/20 rounded-xl px-3 py-2.5 text-white font-bold text-sm outline-none placeholder-white/40 focus:border-white/60 focus:bg-white/25 transition">
              <input type="hidden" id="r-to-lat" name="to_lat" value="<?= h($toLat) ?>">
              <input type="hidden" id="r-to-lng" name="to_lng" value="<?= h($toLng) ?>">
            </div>
          </div>

          <!-- Distance pill -->
          <div id="r-dist-pill" class="<?= ($fromLat && $toLat) ? '' : 'hidden' ?> text-center my-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30">
              <i class="fa-solid fa-road text-brand-green"></i>
              <span id="r-dist-txt"><?= h($_GET['dist_text'] ?? 'Calculating...') ?></span>
              <span id="r-dur-txt" class="text-white/60"><?= isset($_GET['dur_text']) ? '• '.$_GET['dur_text'] : '' ?></span>
            </span>
          </div>

          <!-- Date + Seats + Search -->
          <div class="flex gap-2 mt-3 search-actions flex-wrap">
            <input name="date" type="date" value="<?= $date ?>" min="<?= date('Y-m-d') ?>"
              class="flex-1 min-w-[130px] bg-white/15 border-2 border-white/20 rounded-xl px-3 py-2.5 text-white font-bold text-sm outline-none focus:border-white/60 transition">
            <input name="seats" type="number" min="1" max="4" value="<?= $seats ?>"
              class="w-16 bg-white/15 border-2 border-white/20 rounded-xl px-2 py-2.5 text-white font-bold text-sm outline-none text-center focus:border-white/60 transition">
            <button type="submit"
              class="bg-brand-green text-white font-black px-5 py-2.5 rounded-xl text-sm hover:bg-green-600 transition flex items-center gap-2 shadow-lg flex-shrink-0">
              <i class="fa-solid fa-magnifying-glass"></i>
              <span>Search</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Filters -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-5 -mx-1 px-1">
      <button class="chip active" onclick="filterRides('all',this)"><i class="fa-solid fa-border-all mr-1"></i>All</button>
      <button class="chip" onclick="filterRides('Pooler',this)"><i class="fa-solid fa-car-side mr-1"></i>Pooler</button>
      <button class="chip" onclick="filterRides('Seeker',this)"><i class="fa-solid fa-person-walking mr-1"></i>Seeker</button>
      <button class="chip" onclick="filterRides('Either',this)"><i class="fa-solid fa-arrows-left-right mr-1"></i>Either</button>
    </div>

    <p class="text-sm text-gray-500 font-semibold mb-4" id="result-count"><?= count($rides) ?> rides found<?= $from && $to ? " · $from → $to" : '' ?></p>

    <!-- Ride Cards -->
    <div class="space-y-4" id="rides-list">
      <?php if(empty($rides)): ?>
        <div class="no-rides-wrap">
            <div class="no-rides-icon"><i class="fa-solid fa-route"></i></div>
            <?php if (!$from || !$to): ?>
                <p class="text-gray-500 font-bold text-lg mb-2">Search for rides</p>
                <p class="text-gray-400 text-sm">Enter your From & To locations above to find matching rides.</p>
            <?php else: ?>
                <p class="text-gray-500 font-bold text-lg mb-2">No rides found</p>
                <p class="text-gray-400 text-sm mb-4">No rides matching <strong><?= $from ?></strong> → <strong><?= $to ?></strong></p>
                <a href="post-ride.php?mode=carpool" class="inline-flex items-center gap-2 bg-brand-green text-white font-bold px-6 py-3 rounded-xl hover:bg-green-700 transition shadow-lg">
                    <i class="fa-solid fa-plus"></i> Post a Ride Instead
                </a>
            <?php endif; ?>
        </div>
      <?php else: ?>
      <?php foreach ($rides as $i => $r): 
          $rName = h($r['userName'] ?? $r['name'] ?? 'Pooler');
          $rUserType = h($r['userType'] ?? 'Pooler');
          $rType = h($r['ride_Type'] ?? $r['type'] ?? 'One-time');
          $rPrice = $r['price'] ?? 0;
          $rSeats = $r['seats'] ?? $r['seats_left'] ?? 1;
          $rFrom = h($r['from_Address'] ?? $from);
          $rTo = h($r['to_Address'] ?? $to);
          $rTime = h($r['ride_Date'] ?? $date);
          $rPhoto = !empty($r['userPhoto']) ? h($r['userPhoto']) : 'https://ui-avatars.com/api/?name='.urlencode($rName).'&background=1d3a70&color=fff';
          $isVerified = !empty($r['verified']) || true;
          $rideId = $r['rideID'] ?? $r['id'] ?? 0;
          $userId = $r['userId'] ?? 0;
          $fromDist = $r['from_Distance'] ?? 0;
          $toDist = $r['to_Distance'] ?? 0;
          $rFrequency = $r['ride_Frequency'] ?? 'One-time';
          $rCompany = $r['companyName'] ?? '';
          $isVolunteer = (float)$rPrice <= 0;
          $profilePct = $r['profileVerificationPercentage'] ?? 0;
          $rComment = $r['user_Comment'] ?? '';
          $rCarModel = $r['carModel'] ?? '';
          $rCarNum = $r['carNumber'] ?? '';
          $rCarColor = $r['carColor'] ?? '';
          $rFuel = $r['fuelType'] ?? '';
          $rMobile = $r['userMobileNo'] ?? '';
          $rEmail = $r['userEmail'] ?? '';
          $rTotalDist = $r['totaldistance'] ?? 0;
          $rShowNum = !empty($r['displayNumberOnSearch']);
          // Build detail URL with all params
          $detailParams = http_build_query([
              'id' => $rideId, 'from' => $from, 'to' => $to,
              'name' => $rName, 'utype' => $rUserType, 'price' => $rPrice,
              'seats' => $rSeats, 'rfrom' => $r['from_Address'] ?? $from, 'rto' => $r['to_Address'] ?? $to,
              'rdate' => $r['ride_Date'] ?? $date, 'rfreq' => $rFrequency, 'rtype' => $rType,
              'comment' => $rComment, 'car' => $rCarModel, 'carnum' => $rCarNum,
              'color' => $rCarColor, 'fuel' => $rFuel, 'photo' => $r['userPhoto'] ?? '',
              'mobile' => $rMobile, 'email' => $rEmail, 'company' => $rCompany,
              'fdist' => $fromDist, 'tdist' => $toDist, 'totdist' => $rTotalDist,
              'uid' => $userId, 'verified' => $isVerified ? 1 : 0, 'shownum' => $rShowNum ? 1 : 0,
          ]);
      ?>
      <div class="ride-card p-5 fade-up ride-item"
           data-type="<?= $rUserType ?>"
           style="animation-delay:<?= $i * 0.07 ?>s"
           onclick="window.location.href='ride-detail.php?<?= h($detailParams) ?>'">
        <div class="flex items-start gap-4">
          <img src="<?= $rPhoto ?>" class="w-14 h-14 rounded-2xl object-cover shrink-0" alt="<?= $rName ?>" onerror="this.src='https://ui-avatars.com/api/?name=User&background=1d3a70&color=fff'">
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-black text-brand-blue text-base"><?= $rName ?></p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                  <?php if($rUserType === 'Pooler'): ?>
                    <span class="badge" style="background:#dcfce7;color:#166534;"><i class="fa-solid fa-car-side text-[10px]"></i> Pooler</span>
                  <?php elseif($rUserType === 'Seeker'): ?>
                    <span class="badge" style="background:#fff7ed;color:#9a3412;"><i class="fa-solid fa-person-walking text-[10px]"></i> Seeker</span>
                  <?php else: ?>
                    <span class="badge" style="background:#ede9fe;color:#5b21b6;"><i class="fa-solid fa-arrows-left-right text-[10px]"></i> Either</span>
                  <?php endif; ?>
                  <?php if($isVerified): ?><span class="badge" style="background:#dbeafe;color:#1e40af;">✓ Verified</span><?php endif; ?>
                  <?php if($isVolunteer): ?><span class="badge" style="background:#fef3c7;color:#92400e;"><i class="fa-solid fa-heart text-[10px]"></i> Volunteer</span><?php endif; ?>
                  <?php if($rCompany): ?><span class="badge" style="background:#f0f9ff;color:#0369a1;"><i class="fa-solid fa-building text-[10px]"></i> <?= h($rCompany) ?></span><?php endif; ?>
                </div>
              </div>
              <div class="text-right shrink-0">
                <?php if($isVolunteer): ?>
                  <p class="text-lg font-black text-brand-orange">FREE</p>
                  <p class="text-xs text-gray-400 font-semibold">volunteer</p>
                <?php else: ?>
                  <p class="text-2xl font-black text-brand-green">₹<?= (int)$rPrice ?></p>
                  <p class="text-xs text-gray-400 font-semibold">per seat</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex items-center gap-4 mt-3 bg-gray-50 p-3 rounded-xl">
              <div class="flex flex-col items-center">
                <div class="w-2 h-2 rounded-full bg-brand-green"></div>
                <div class="w-0.5 h-6 bg-gray-300 my-1"></div>
                <div class="w-2 h-2 rounded-full bg-brand-orange"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-bold text-xs text-gray-700 truncate"><?= $rFrom ?></p>
                <p class="font-bold text-xs text-gray-700 mt-3 truncate"><?= $rTo ?></p>
              </div>
              <div class="text-right text-xs text-gray-500 font-black shrink-0">
                  <?php 
                      $timeStr = strtotime($rTime);
                      echo $timeStr ? date('h:i A', $timeStr) : 'Anytime';
                  ?>
              </div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 flex-wrap gap-2">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="badge bg-blue-50 text-brand-blue"><i class="fa-regular fa-calendar"></i> <?= $rFrequency ?></span>
                <?php if($rSeats > 0): ?>
                <span class="badge bg-gray-50 text-gray-600"><i class="fa-regular fa-user"></i> <?= $rSeats ?> seat<?= $rSeats > 1 ? 's' : '' ?></span>
                <?php endif; ?>
                <?php if($fromDist > 0): ?>
                <span class="badge bg-green-50 text-green-700"><i class="fa-solid fa-location-crosshairs text-[10px]"></i> <?= $fromDist ?> km away</span>
                <?php endif; ?>
              </div>
              <span class="text-xs text-brand-green font-black uppercase tracking-widest"><i class="fa-solid fa-arrow-right"></i> Details</span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Places Autocomplete Helper (loads Google Maps API internally) -->
<script src="js/places-ac.js"></script>
<script>
function filterRides(type, el) {
    document.querySelectorAll('.chip').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    let count = 0;
    document.querySelectorAll('.ride-item').forEach(card=>{
        const t = card.dataset.type;
        const show = type==='all' || t===type;
        card.style.display = show ? '' : 'none';
        if(show) count++;
    });
    document.getElementById('result-count').textContent = count + ' rides found';
}

// Rides page autocomplete
document.addEventListener('DOMContentLoaded', () => {
    PI_Places.initAll([
        {
            inputId: 'r-from', latId: 'r-from-lat', lngId: 'r-from-lng',
            onSelect: ({lat,lng,formatted_address}) => _rTryDist()
        },
        {
            inputId: 'r-to', latId: 'r-to-lat', lngId: 'r-to-lng',
            onSelect: ({lat,lng,formatted_address}) => _rTryDist()
        }
    ]);
});

function rSwap() {
    const f = document.getElementById('r-from');
    const t = document.getElementById('r-to');
    const fl = document.getElementById('r-from-lat');
    const fn = document.getElementById('r-from-lng');
    const tl = document.getElementById('r-to-lat');
    const tn = document.getElementById('r-to-lng');
    [f.value, t.value] = [t.value, f.value];
    [fl.value, tl.value] = [tl.value, fl.value];
    [fn.value, tn.value] = [tn.value, fn.value];
    const btn = document.getElementById('r-swap');
    btn.style.transform = 'rotate(180deg)';
    setTimeout(()=>btn.style.transform='', 400);
    _rTryDist();
}

async function _rTryDist() {
    const flat = document.getElementById('r-from-lat')?.value;
    const flng = document.getElementById('r-from-lng')?.value;
    const tlat = document.getElementById('r-to-lat')?.value;
    const tlng = document.getElementById('r-to-lng')?.value;
    const pill = document.getElementById('r-dist-pill');
    if (!flat || !tlat) { pill?.classList.add('hidden'); return; }
    try {
        const d = await PI_Places.getDistance({lat:+flat,lng:+flng},{lat:+tlat,lng:+tlng});
        document.getElementById('r-dist-txt').textContent = d.distance_text;
        document.getElementById('r-dur-txt').textContent  = '• ' + d.duration_text;
        pill?.classList.remove('hidden');
    } catch(e) {}
}
</script>
</body>
</html>
