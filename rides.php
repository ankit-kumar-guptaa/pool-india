<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();
$from  = h($_GET['from']  ?? 'Sector 3, Noida');
$to    = h($_GET['to']    ?? 'Connaught Place, Delhi');
$date  = h($_GET['date']  ?? date('Y-m-d'));
$seats = (int)($_GET['seats'] ?? 1);

// Dummy ride data (replace with real API call)
$rides = [
    ['id'=>1,'name'=>'Arjun Sharma','photo'=>'https://randomuser.me/api/portraits/men/32.jpg','rating'=>4.9,'trips'=>148,'price'=>120,'from_time'=>'07:30 AM','to_time'=>'09:00 AM','duration'=>'1h 30m','vehicle'=>'Honda City','plate'=>'DL-3C','seats_left'=>2,'type'=>'Carpool','verified'=>true,'women_only'=>false],
    ['id'=>2,'name'=>'Priya Mehta','photo'=>'https://randomuser.me/api/portraits/women/44.jpg','rating'=>4.7,'trips'=>89,'price'=>90,'from_time'=>'08:00 AM','to_time'=>'09:30 AM','duration'=>'1h 30m','vehicle'=>'Maruti Swift','plate'=>'UP-16','seats_left'=>1,'type'=>'Carpool','verified'=>true,'women_only'=>true],
    ['id'=>3,'name'=>'Rahul Verma','photo'=>'https://randomuser.me/api/portraits/men/67.jpg','rating'=>5.0,'trips'=>212,'price'=>70,'from_time'=>'08:30 AM','to_time'=>'10:00 AM','duration'=>'1h 30m','vehicle'=>'Royal Enfield','plate'=>'DL-7S','seats_left'=>1,'type'=>'Bike','verified'=>true,'women_only'=>false],
    ['id'=>4,'name'=>'Vikram Singh','photo'=>'https://randomuser.me/api/portraits/men/11.jpg','rating'=>4.6,'trips'=>53,'price'=>150,'from_time'=>'09:00 AM','to_time'=>'10:20 AM','duration'=>'1h 20m','vehicle'=>'Toyota Innova','plate'=>'UP-14','seats_left'=>3,'type'=>'CabShare','verified'=>true,'women_only'=>false],
    ['id'=>5,'name'=>'Sneha Rao','photo'=>'https://randomuser.me/api/portraits/women/55.jpg','rating'=>4.8,'trips'=>117,'price'=>100,'from_time'=>'09:30 AM','to_time'=>'11:00 AM','duration'=>'1h 30m','vehicle'=>'Honda Activa','plate'=>'DL-2B','seats_left'=>1,'type'=>'Bike','verified'=>true,'women_only'=>false],
];
$typeIcons = ['Carpool'=>'fa-car-side','Bike'=>'fa-motorcycle','CabShare'=>'fa-taxi','Shuttle'=>'fa-van-shuttle'];
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
/* glass-nav defined in header.php */
.ride-card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;transition:all .3s;cursor:pointer;}
.ride-card:hover{border-color:#1b8036;box-shadow:0 8px 32px rgba(27,128,54,.13);transform:translateY(-3px);}
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
.chip{padding:8px 16px;border-radius:999px;border:2px solid #e2e8f0;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s;background:#fff;color:#64748b;}
.chip.active,.chip:hover{border-color:#1b8036;background:#f0fdf4;color:#1b8036;}
.search-wrap{background:#fff;border-radius:0 0 2rem 2rem;padding:16px 20px 20px;border-bottom:1px solid #e2e8f0;}
.src-field{background:#f8fafc;border:2px solid #e2e8f0;border-radius:14px;padding:11px 14px;display:flex;align-items:center;gap:10px;transition:all .25s;position:relative;}
.src-field:focus-within{border-color:#1b8036;background:#fff;box-shadow:0 0 0 3px rgba(27,128,54,.08);}
.src-field.to:focus-within{border-color:#f3821a;box-shadow:0 0 0 3px rgba(243,130,26,.08);}
.src-input{border:none;outline:none;background:transparent;font-size:14px;font-weight:700;color:#1d3a70;width:100%;font-family:'Plus Jakarta Sans',sans-serif;}
.src-label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:2px;}
.route-dot-g{width:10px;height:10px;border-radius:50%;border:2.5px solid #1b8036;background:#fff;flex-shrink:0;}
.route-dot-o{width:10px;height:10px;border-radius:50%;border:2.5px solid #f3821a;background:#fff;flex-shrink:0;}
.route-ln{width:1.5px;height:20px;background:linear-gradient(#1b8036,#f3821a);margin:0 auto;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
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
          <div class="flex items-stretch gap-3">
            <!-- From -->
            <div class="flex-1 relative">
              <div class="flex items-center gap-2 mb-1">
                <div class="route-dot-g"></div>
                <span class="text-[10px] font-black text-white/60 uppercase tracking-wider">From</span>
              </div>
              <input id="r-from" name="from" type="text" autocomplete="off"
                value="<?= $from ?>" placeholder="Leaving from..."
                class="w-full bg-white/15 border-2 border-white/20 rounded-xl px-3 py-2.5 text-white font-bold text-sm outline-none placeholder-white/40 focus:border-white/60 focus:bg-white/25 transition">
              <input type="hidden" id="r-from-lat" name="from_lat" value="<?= h($_GET['from_lat'] ?? '') ?>">
              <input type="hidden" id="r-from-lng" name="from_lng" value="<?= h($_GET['from_lng'] ?? '') ?>">
            </div>

            <!-- Swap -->
            <div class="flex flex-col justify-center gap-1">
              <button type="button" onclick="rSwap()" id="r-swap"
                class="w-8 h-8 rounded-full bg-white/15 border border-white/30 text-white/70 hover:bg-white hover:text-brand-blue flex items-center justify-center transition text-xs">
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
              <input type="hidden" id="r-to-lat" name="to_lat" value="<?= h($_GET['to_lat'] ?? '') ?>">
              <input type="hidden" id="r-to-lng" name="to_lng" value="<?= h($_GET['to_lng'] ?? '') ?>">
            </div>
          </div>

          <!-- Distance pill -->
          <div id="r-dist-pill" class="<?= (isset($_GET['from_lat']) && isset($_GET['to_lat'])) ? '' : 'hidden' ?> text-center my-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30">
              <i class="fa-solid fa-road text-brand-green"></i>
              <span id="r-dist-txt"><?= h($_GET['dist_text'] ?? 'Calculating...') ?></span>
              <span id="r-dur-txt" class="text-white/60"><?= isset($_GET['dur_text']) ? '• '.$_GET['dur_text'] : '' ?></span>
            </span>
          </div>

          <!-- Date + Seats + Search -->
          <div class="flex gap-2 mt-3">
            <input name="date" type="date" value="<?= $date ?>" min="<?= date('Y-m-d') ?>"
              class="flex-1 bg-white/15 border-2 border-white/20 rounded-xl px-3 py-2.5 text-white font-bold text-sm outline-none focus:border-white/60 transition">
            <input name="seats" type="number" min="1" max="4" value="<?= $seats ?>"
              class="w-16 bg-white/15 border-2 border-white/20 rounded-xl px-2 py-2.5 text-white font-bold text-sm outline-none text-center focus:border-white/60 transition">
            <button type="submit"
              class="bg-brand-green text-white font-black px-5 py-2.5 rounded-xl text-sm hover:bg-green-600 transition flex items-center gap-2 shadow-lg">
              <i class="fa-solid fa-magnifying-glass"></i>
              <span class="hidden sm:inline">Search</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Filters -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-5">
      <button class="chip active" onclick="filterRides('all',this)"><i class="fa-solid fa-border-all mr-1"></i>All</button>
      <button class="chip" onclick="filterRides('Carpool',this)"><i class="fa-solid fa-car-side mr-1"></i>Carpool</button>
      <button class="chip" onclick="filterRides('Bike',this)"><i class="fa-solid fa-motorcycle mr-1"></i>Bike</button>
      <button class="chip" onclick="filterRides('CabShare',this)"><i class="fa-solid fa-taxi mr-1"></i>CabShare</button>
      <button class="chip" onclick="filterRides('women',this)"><i class="fa-solid fa-venus mr-1"></i>Women Only</button>
    </div>

    <p class="text-sm text-gray-500 font-semibold mb-4" id="result-count"><?= count($rides) ?> rides found · Sorted by departure time</p>

    <!-- Ride Cards -->
    <div class="space-y-4" id="rides-list">
      <?php foreach ($rides as $i => $r): ?>
      <div class="ride-card p-5 fade-up ride-item"
           data-type="<?= h($r['type']) ?>"
           data-women="<?= $r['women_only'] ? '1' : '0' ?>"
           style="animation-delay:<?= $i * 0.07 ?>s"
           onclick="window.location.href='ride-detail.php?id=<?= $r['id'] ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>'">
        <div class="flex items-start gap-4">
          <img src="<?= h($r['photo']) ?>" class="w-14 h-14 rounded-2xl object-cover shrink-0" alt="<?= h($r['name']) ?>">
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-black text-brand-blue text-base"><?= h($r['name']) ?></p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                  <span class="text-brand-orange text-xs">★★★★<?= $r['rating'] >= 5 ? '★' : '☆' ?></span>
                  <span class="text-xs text-gray-500 font-semibold"><?= $r['rating'] ?> · <?= $r['trips'] ?> rides</span>
                  <?php if($r['verified']): ?><span class="badge" style="background:#dcfce7;color:#166534;">✓ Verified</span><?php endif; ?>
                  <?php if($r['women_only']): ?><span class="badge" style="background:#fce7f3;color:#9d174d;"><i class="fa-solid fa-venus"></i>Women Only</span><?php endif; ?>
                </div>
              </div>
              <div class="text-right">
                <p class="text-2xl font-black text-brand-green">₹<?= $r['price'] ?></p>
                <p class="text-xs text-gray-400 font-semibold">per seat</p>
              </div>
            </div>
            <div class="flex items-center gap-4 mt-3">
              <div class="flex flex-col items-center">
                <div class="route-dot-g"></div><div class="route-ln"></div><div class="route-dot-o"></div>
              </div>
              <div class="flex-1">
                <p class="font-bold text-sm text-brand-blue"><?= h($r['from_time']) ?> · <?= h($from) ?></p>
                <p class="font-bold text-sm text-brand-blue mt-4"><?= h($r['to_time']) ?> · <?= h($to) ?></p>
              </div>
              <div class="text-right text-xs text-gray-400 font-semibold"><?= h($r['duration']) ?></div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
              <div class="flex items-center gap-2">
                <span class="badge bg-blue-50 text-brand-blue"><i class="fa-solid <?= $typeIcons[$r['type']] ?? 'fa-car' ?>"></i> <?= h($r['type']) ?></span>
                <span class="badge bg-gray-50 text-gray-600"><i class="fa-regular fa-user"></i> <?= $r['seats_left'] ?> left</span>
              </div>
              <span class="text-xs text-gray-500 font-semibold"><?= h($r['vehicle']) ?> · <?= h($r['plate']) ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="js/places-ac.js"></script>
<script>
function filterRides(type, el) {
    document.querySelectorAll('.chip').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    let count = 0;
    document.querySelectorAll('.ride-item').forEach(card=>{
        const t = card.dataset.type;
        const w = card.dataset.women === '1';
        const show = type==='all' || t===type || (type==='women' && w);
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
