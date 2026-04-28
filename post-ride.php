<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();

$mode    = $_GET['mode'] ?? 'carpool';
$success = false;
$bookingId = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode     = $_POST['mode']      ?? 'carpool';
    $from     = trim($_POST['from']    ?? '');
    $to       = trim($_POST['to']      ?? '');
    $date     = trim($_POST['date']    ?? '');
    $time     = trim($_POST['time']    ?? '');
    $seats    = (int)($_POST['seats']  ?? 1);
    $price    = (int)($_POST['price']  ?? 0);
    $vehicle  = trim($_POST['vehicle'] ?? '');
    $plate    = trim($_POST['plate']   ?? '');
    $note     = trim($_POST['note']    ?? '');

    if ($from && $to && $date && $time && $price > 0) {
        // In production: POST to real API
        // $resp = apiPost('Ride/PostRide', [...]);
        // Simulated success:
        $bookingId = 'PI-POST-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $_SESSION['posted_ride'] = compact('mode','from','to','date','time','seats','price','vehicle','plate','note','bookingId');
        $success = true;
    }
}

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
/* glass-nav defined in header.php */
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;}
.field-wrap label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.field{width:100%;background:#f8fafc;border:2px solid #e2e8f0;border-radius:14px;padding:13px 16px;font-size:15px;font-weight:600;color:#1d3a70;outline:none;transition:all .25s;font-family:'Plus Jakarta Sans',sans-serif;}
.field:focus{border-color:#1b8036;background:#fff;box-shadow:0 0 0 3px rgba(27,128,54,.1);}
.field.err{border-color:#ef4444;}
.mode-tab{flex:1;padding:12px;border-radius:14px;font-weight:800;font-size:14px;cursor:pointer;transition:all .3s;border:2px solid #e2e8f0;background:#fff;color:#64748b;display:flex;align-items:center;justify-content:center;gap:8px;}
.mode-tab.active-carpool{border-color:#1b8036;background:#f0fdf4;color:#1b8036;}
.mode-tab.active-bike{border-color:#f3821a;background:#fff7ed;color:#f3821a;}
.submit-btn{width:100%;padding:16px;border-radius:16px;font-weight:800;font-size:17px;border:none;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:10px;}
.submit-carpool{background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;box-shadow:0 8px 25px rgba(27,128,54,.35);}
.submit-bike{background:linear-gradient(135deg,#f3821a,#e06b0a);color:#fff;box-shadow:0 8px 25px rgba(243,130,26,.35);}
.submit-btn:hover{transform:translateY(-2px);}
.tip-card{border-radius:1.2rem;padding:16px;display:flex;align-items:flex-start;gap:12px;}
.seats-row{display:flex;gap:8px;}
.seat-pill{flex:1;padding:10px;border:2px solid #e2e8f0;border-radius:10px;text-align:center;font-weight:800;cursor:pointer;transition:all .2s;background:#fff;color:#64748b;font-size:14px;}
.seat-pill.sel{border-color:var(--mc);background:var(--mcl);color:var(--mc);}
@keyframes pop{0%{transform:scale(0) rotate(-8deg);}60%{transform:scale(1.1) rotate(2deg);}100%{transform:scale(1);}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.pop-in{animation:pop .6s cubic-bezier(.34,1.56,.64,1) both;}
.fade-up{animation:fadeUp .4s ease both;}
.progress-bar{height:4px;border-radius:999px;background:#e2e8f0;overflow:hidden;margin-bottom:24px;}
.progress-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#1b8036,#22c55e);transition:width .4s;}
.step-indicator{display:flex;gap:4px;margin-bottom:20px;}
.step-dot{flex:1;height:4px;border-radius:999px;background:#e2e8f0;transition:all .3s;}
.step-dot.done{background:#1b8036;}
.step-dot.bike-done{background:#f3821a;}
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
  <p class="text-gray-500 font-semibold mb-6">Your <?= h($modeLabel) ?> ride is live. Commuters can now request seats.</p>

  <div class="card p-6 text-left mb-6">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Ride Summary</p>
    <div class="space-y-3 text-sm">
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Booking ID</span><span class="font-black text-brand-blue tracking-wider">#<?= h($bookingId) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Type</span><span class="font-bold text-brand-blue"><?= h($modeLabel) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">From</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['from'] ?? '') ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">To</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['to'] ?? '') ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Date & Time</span><span class="font-bold text-brand-blue"><?= h($_SESSION['posted_ride']['date'] ?? '') ?> · <?= h($_SESSION['posted_ride']['time'] ?? '') ?></span></div>
      <div class="flex justify-between"><span class="text-gray-500 font-semibold">Seats Offered</span><span class="font-bold text-brand-blue"><?= (int)($_SESSION['posted_ride']['seats'] ?? 1) ?></span></div>
      <div class="flex justify-between border-t border-gray-100 pt-3 mt-1"><span class="font-black text-brand-blue">Price/Seat</span><span class="font-black text-brand-green text-lg">₹<?= (int)($_SESSION['posted_ride']['price'] ?? 0) ?></span></div>
    </div>
  </div>

  <!-- What's next -->
  <div class="card p-5 text-left mb-6">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">What happens next?</p>
    <div class="space-y-3">
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm shrink-0" style="background:<?= $modeColor ?>22;color:<?= $modeColor ?>">1</div><div><p class="font-bold text-brand-blue text-sm">Commuters find your ride</p><p class="text-gray-400 text-xs">Your ride shows up in search results for matching routes</p></div></div>
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm shrink-0" style="background:<?= $modeColor ?>22;color:<?= $modeColor ?>">2</div><div><p class="font-bold text-brand-blue text-sm">You receive seat requests</p><p class="text-gray-400 text-xs">Accept or decline requests from the Responses tab</p></div></div>
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm shrink-0" style="background:<?= $modeColor ?>22;color:<?= $modeColor ?>">3</div><div><p class="font-bold text-brand-blue text-sm">Collect fare after the ride</p><p class="text-gray-400 text-xs">Accept cash or UPI from co-passengers</p></div></div>
    </div>
  </div>

  <div class="flex flex-col gap-3">
    <a href="profile.php?tab=responses" class="submit-btn <?= $mode==='bike'?'submit-bike':'submit-carpool' ?>"><i class="fa-solid fa-reply"></i>View Requests</a>
    <a href="post-ride.php?mode=<?= $mode ?>" class="submit-btn" style="background:#f1f5f9;color:#1d3a70;"><i class="fa-solid fa-plus"></i>Post Another Ride</a>
    <a href="index.php" class="submit-btn" style="background:transparent;color:#94a3b8;border:2px solid #e2e8f0;"><i class="fa-solid fa-house"></i>Back to Home</a>
  </div>
</div>

<?php else: ?>
<!-- ===== FORM STATE ===== -->

<!-- Header -->
<div class="mb-6 fade-up">
  <a href="index.php" class="text-sm text-gray-400 font-bold hover:text-brand-blue transition mb-4 inline-flex items-center gap-1"><i class="fa-solid fa-arrow-left text-xs"></i> Back to Home</a>
  <h1 class="text-3xl font-black text-brand-blue mt-1">Post a Ride 🚗</h1>
  <p class="text-gray-500 font-semibold text-sm mt-1">Offer seats on your daily commute and earn while you travel</p>
</div>

<!-- Mode Selector -->
<div class="flex gap-3 mb-6 fade-up" style="animation-delay:.05s">
  <a href="post-ride.php?mode=carpool" class="mode-tab <?= $mode==='carpool'?'active-carpool':'' ?>">
    <i class="fa-solid fa-car-side text-xl"></i> Carpool
  </a>
  <a href="post-ride.php?mode=bike" class="mode-tab <?= $mode==='bike'?'active-bike':'' ?>">
    <i class="fa-solid fa-motorcycle text-xl"></i> Bike Buddy
  </a>
</div>

<!-- Tip Card -->
<div class="tip-card mb-6 fade-up" style="animation-delay:.08s;background:<?= $modeColor ?>0d;border:1.5px solid <?= $modeColor ?>33;">
  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0" style="background:<?= $modeColor ?>22;color:<?= $modeColor ?>">
    <i class="fa-solid <?= $modeIcon ?>"></i>
  </div>
  <div>
    <p class="font-black text-brand-blue text-sm">Posting a <?= h($modeLabel) ?> ride</p>
    <p class="text-gray-500 text-xs font-semibold mt-0.5">
      <?= $mode==='bike' ? 'Offer a pillion seat on your daily bike commute. Earn ₹50–₹150 per trip.' : 'Share your car commute and split fuel costs. Earn ₹80–₹200 per seat per trip.' ?>
    </p>
  </div>
</div>

<!-- FORM -->
<form method="POST" action="post-ride.php" id="post-form" class="space-y-4 fade-up" style="animation-delay:.1s">
  <input type="hidden" name="mode" value="<?= h($mode) ?>">

  <!-- Step dots -->
  <div class="step-indicator" id="step-dots">
    <div class="step-dot <?= $mode==='bike'?'bike-done':'done' ?>" id="dot1"></div>
    <div class="step-dot" id="dot2"></div>
    <div class="step-dot" id="dot3"></div>
  </div>

  <!-- STEP 1: Route -->
  <div id="step-1">
    <div class="card p-6 mb-4">
      <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">📍 Step 1 — Route Details</p>
      <div class="space-y-3">

        <!-- FROM -->
        <div class="field-wrap">
          <label>Leaving From</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden" style="padding:0">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#f0fdf4;border-right:2px solid #e2e8f0;padding:12px 10px">
              <i class="fa-solid fa-circle-dot text-brand-green text-sm"></i>
            </div>
            <input name="from" id="f-from" type="text" autocomplete="off"
              class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3"
              placeholder="Search your starting point..." required
              value="<?= h($_POST['from'] ?? '') ?>">
            <input type="hidden" name="from_lat" id="f-from-lat">
            <input type="hidden" name="from_lng" id="f-from-lng">
          </div>
        </div>

        <!-- Route line + Swap -->
        <div class="flex items-center gap-3 px-2">
          <div class="flex flex-col items-center gap-0.5">
            <div style="width:1.5px;height:8px;background:#1b8036"></div>
            <div style="width:1.5px;height:8px;background:linear-gradient(#1b8036,#f3821a)"></div>
          </div>
          <!-- Distance badge (shown live) -->
          <div id="pr-dist-wrap" class="flex-1 hidden">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black" style="background:#f0fdf4;border:1.5px solid #bbf7d0;color:#1b8036">
              <i class="fa-solid fa-road text-[11px]"></i>
              <span id="pr-dist-text"></span>
              <span class="text-gray-400 font-semibold" id="pr-dur-text"></span>
            </div>
          </div>
          <button type="button" onclick="prSwap()" id="pr-swap"
            class="w-8 h-8 rounded-full border-2 border-gray-200 bg-white hover:border-brand-blue hover:bg-brand-blue hover:text-white text-gray-400 flex items-center justify-center transition-all text-xs shadow-sm ml-auto">
            <i class="fa-solid fa-arrow-up-arrow-down"></i>
          </button>
        </div>

        <!-- TO -->
        <div class="field-wrap">
          <label>Going To</label>
          <div class="field flex items-center gap-2 p-0 overflow-hidden" style="padding:0">
            <div class="w-9 h-full flex items-center justify-center shrink-0" style="background:#fff7ed;border-right:2px solid #e2e8f0;padding:12px 10px">
              <i class="fa-solid fa-location-dot text-brand-orange text-sm"></i>
            </div>
            <input name="to" id="f-to" type="text" autocomplete="off"
              class="flex-1 bg-transparent border-none outline-none font-semibold text-brand-blue text-[15px] px-3 py-3"
              placeholder="Search your destination..." required
              value="<?= h($_POST['to'] ?? '') ?>">
            <input type="hidden" name="to_lat" id="f-to-lat">
            <input type="hidden" name="to_lng" id="f-to-lng">
          </div>
        </div>

        <!-- Date + Time -->
        <div class="grid grid-cols-2 gap-3">
          <div class="field-wrap">
            <label>Date</label>
            <input name="date" type="date" class="field" min="<?= date('Y-m-d') ?>" required
                   value="<?= h($_POST['date'] ?? date('Y-m-d')) ?>">
          </div>
          <div class="field-wrap">
            <label>Departure Time</label>
            <input name="time" type="time" class="field" required
                   value="<?= h($_POST['time'] ?? '08:00') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2: Seats & Price -->
    <div class="card p-6 mb-4">
      <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">💺 Step 2 — Seats & Price</p>
      <div class="space-y-4">
        <div class="field-wrap">
          <label>Seats Available</label>
          <div class="seats-row" style="--mc:<?= $modeColor ?>;--mcl:<?= $modeColor ?>0d" id="seats-row">
            <?php $maxSeats = $mode==='bike' ? 1 : 3;
            for($s=1;$s<=$maxSeats;$s++): ?>
            <button type="button" class="seat-pill <?= $s===1?'sel':'' ?>" onclick="selectSeat(<?= $s ?>,this)">
              <?= $s ?> <span class="text-xs block font-semibold"><?= $s===1?'Seat':'Seats' ?></span>
            </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="seats" id="f-seats" value="1">
        </div>
        <div class="field-wrap">
          <label>Price per Seat (₹)</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-brand-green text-lg">₹</span>
            <input name="price" type="number" min="10" max="5000" class="field" style="padding-left:36px"
                   placeholder="<?= $mode==='bike'?'e.g. 80':'e.g. 150' ?>" required
                   value="<?= h($_POST['price'] ?? ($mode==='bike'?80:150)) ?>">
          </div>
          <p class="text-xs text-gray-400 font-semibold mt-1">
            <?= $mode==='bike' ? 'Suggested: ₹50–₹120 for bike rides' : 'Suggested: ₹80–₹250 based on distance' ?>
          </p>
        </div>
        <!-- Price Preview -->
        <div class="rounded-xl p-4 flex justify-between items-center" style="background:<?= $modeColor ?>0d;border:1.5px solid <?= $modeColor ?>22">
          <div>
            <p class="text-xs font-bold text-gray-500">Estimated Monthly Earnings</p>
            <p class="text-xl font-black" style="color:<?= $modeColor ?>" id="earning-preview">₹—</p>
          </div>
          <i class="fa-solid fa-indian-rupee-sign text-3xl opacity-20" style="color:<?= $modeColor ?>"></i>
        </div>
      </div>
    </div>

    <!-- STEP 3: Vehicle -->
    <div class="card p-6 mb-4">
      <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-5">🚗 Step 3 — Vehicle Details</p>
      <div class="space-y-4">
        <div class="field-wrap">
          <label><?= $mode==='bike'?'Bike Model':'Car Model' ?></label>
          <input name="vehicle" class="field" placeholder="<?= $mode==='bike'?'e.g. Honda Activa, Royal Enfield':'e.g. Honda City, Maruti Swift' ?>"
                 value="<?= h($_POST['vehicle'] ?? $user['VehicleType'] ?? '') ?>">
        </div>
        <div class="field-wrap">
          <label>Registration Number</label>
          <input name="plate" class="field" placeholder="e.g. DL 3C AB 1234" style="text-transform:uppercase"
                 value="<?= h($_POST['plate'] ?? '') ?>">
        </div>
        <div class="field-wrap">
          <label>Pickup Note (Optional)</label>
          <textarea name="note" rows="2" class="field" placeholder="e.g. I'll be at Sector 3 Metro Gate No. 2"><?= h($_POST['note'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Preferences -->
    <div class="card p-6 mb-6">
      <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">✅ Ride Preferences</p>
      <div class="grid grid-cols-2 gap-3">
        <?php
        $prefs = [
            ['id'=>'music',    'icon'=>'fa-music',          'label'=>'Music OK'],
            ['id'=>'ac',       'icon'=>'fa-wind',           'label'=>'AC Available'],
            ['id'=>'smoking',  'icon'=>'fa-ban-smoking',    'label'=>'No Smoking'],
            ['id'=>'luggage',  'icon'=>'fa-suitcase',       'label'=>'Luggage OK'],
            ['id'=>'chat',     'icon'=>'fa-comment-dots',   'label'=>'Chat Welcome'],
            ['id'=>'women',    'icon'=>'fa-venus',          'label'=>'Women Only'],
        ];
        if ($mode === 'bike') {
            $prefs = array_filter($prefs, fn($p) => !in_array($p['id'], ['ac','luggage']));
        }
        foreach($prefs as $p):
        ?>
        <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-gray-200 transition has-[:checked]:border-[<?= $modeColor ?>] has-[:checked]:bg-[<?= $modeColor ?>0d]">
          <input type="checkbox" name="prefs[]" value="<?= $p['id'] ?>" class="hidden peer">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 peer-checked:text-[<?= $modeColor ?>] peer-checked:bg-[<?= $modeColor ?>22] transition" style="background:#f1f5f9">
            <i class="fa-solid <?= $p['icon'] ?> text-sm"></i>
          </div>
          <span class="font-bold text-sm text-gray-600"><?= $p['label'] ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Submit -->
    <button type="submit" class="submit-btn <?= $mode==='bike'?'submit-bike':'submit-carpool' ?>" id="submit-btn">
      <i class="fa-solid <?= $modeIcon ?>"></i>
      Post <?= h($modeLabel) ?> Ride
    </button>
    <p class="text-center text-xs text-gray-400 font-semibold mt-3">
      By posting, you agree to Pool India's <a href="#" class="text-brand-blue underline">Community Guidelines</a>
    </p>
  </div>
</form>
<?php endif; ?>
</div>

<script>
let selectedSeats = 1;
const modeColor = '<?= $modeColor ?>';

function selectSeat(n, el) {
    selectedSeats = n;
    document.getElementById('f-seats').value = n;
    document.querySelectorAll('.seat-pill').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    updateEarnings();
}

function updateEarnings() {
    const price = parseInt(document.querySelector('[name=price]')?.value || 0);
    if (!price) { document.getElementById('earning-preview').textContent = '₹—'; return; }
    const perDay = price * selectedSeats;
    const monthly = perDay * 22;
    document.getElementById('earning-preview').textContent = '₹' + monthly.toLocaleString('en-IN') + '/mo';
}

document.querySelector('[name=price]')?.addEventListener('input', updateEarnings);
updateEarnings();

document.getElementById('post-form')?.addEventListener('submit', function(e) {
    const from  = document.getElementById('f-from')?.value.trim();
    const to    = document.getElementById('f-to')?.value.trim();
    const price = document.querySelector('[name=price]')?.value;
    if (!from || !to) { e.preventDefault(); alert('Please fill From and To fields.'); return; }
    if (!price || parseInt(price) < 10) { e.preventDefault(); alert('Enter a valid price.'); return; }
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch" style="animation:spin .8s linear infinite"></i> Posting...';
});

// Step dots animation
const steps = ['dot1','dot2','dot3'];
let dotIdx = 0;
const mc = '<?= $modeColor ?>';
setInterval(() => {
    dotIdx = (dotIdx + 1) % steps.length;
    steps.forEach((id, i) => {
        const el = document.getElementById(id);
        el.style.background = i <= dotIdx ? mc : '';
    });
}, 1800);
</script>

<script src="js/places-ac.js"></script>
<script>
// ---- Post-Ride Autocomplete ----
document.addEventListener('DOMContentLoaded', () => {
    PI_Places.initAll([
        {
            inputId: 'f-from', latId: 'f-from-lat', lngId: 'f-from-lng',
            onSelect: () => _prTryDist()
        },
        {
            inputId: 'f-to', latId: 'f-to-lat', lngId: 'f-to-lng',
            onSelect: () => _prTryDist()
        }
    ]);
});

function prSwap() {
    const f  = document.getElementById('f-from');
    const t  = document.getElementById('f-to');
    const fl = document.getElementById('f-from-lat');
    const fn = document.getElementById('f-from-lng');
    const tl = document.getElementById('f-to-lat');
    const tn = document.getElementById('f-to-lng');
    [f.value, t.value]   = [t.value, f.value];
    [fl.value, tl.value] = [tl.value, fl.value];
    [fn.value, tn.value] = [tn.value, fn.value];
    const btn = document.getElementById('pr-swap');
    btn.style.transform = 'rotate(180deg)';
    setTimeout(() => btn.style.transform = '', 400);
    _prTryDist();
}

// Price suggestion thresholds (mirrors Flutter app logic)
function _suggestPrice(km) {
    let base = 50;
    if (km > 15) base += (km - 15) * 3.5;
    return Math.round(base);
}

async function _prTryDist() {
    const flat = document.getElementById('f-from-lat')?.value;
    const flng = document.getElementById('f-from-lng')?.value;
    const tlat = document.getElementById('f-to-lat')?.value;
    const tlng = document.getElementById('f-to-lng')?.value;
    const wrap = document.getElementById('pr-dist-wrap');
    if (!flat || !tlat) { wrap?.classList.add('hidden'); return; }
    try {
        const d = await PI_Places.getDistance({lat:+flat,lng:+flng},{lat:+tlat,lng:+tlng});
        document.getElementById('pr-dist-text').textContent = d.distance_text;
        document.getElementById('pr-dur-text').textContent  = '• ' + d.duration_text;
        wrap?.classList.remove('hidden');
        // Auto-fill suggested price if empty / still default
        const priceIn = document.querySelector('[name=price]');
        if (priceIn && (!priceIn.value || parseInt(priceIn.value) === 50)) {
            priceIn.value = _suggestPrice(d.distance_km);
            updateEarnings();
        }
    } catch(e) {}
}
</script>
</body>
</html>

