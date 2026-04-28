<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user  = currentUser();
$id    = (int)($_GET['id'] ?? 1);
$from  = h($_GET['from'] ?? 'Sector 3, Noida');
$to    = h($_GET['to']   ?? 'Connaught Place, Delhi');

// Handle connect AJAX
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='connect') {
    $seats = (int)($_POST['seats'] ?? 1);
    // Save booking to session
    $_SESSION['booking'] = [
        'ride_id'    => $id,
        'driver'     => 'Arjun Sharma',
        'from'       => $_POST['from'] ?? $from,
        'to'         => $_POST['to']   ?? $to,
        'time_from'  => '07:30 AM',
        'time_to'    => '09:00 AM',
        'price'      => 120 * $seats,
        'seats'      => $seats,
        'booking_id' => 'PI-' . date('Y') . '-' . rand(1000,9999),
    ];
    jsonResponse(['ok'=>true, 'redirect'=>'connect-success.php']);
}

$rides = [
    1=>['name'=>'Arjun Sharma','photo'=>'https://randomuser.me/api/portraits/men/32.jpg','rating'=>4.9,'trips'=>148,'price'=>120,'from_time'=>'07:30 AM','to_time'=>'09:00 AM','duration'=>'1h 30m','distance'=>'38 km','vehicle'=>'Honda City','plate'=>'DL-3C AB 1234','year'=>2022,'color'=>'Silver','type'=>'Carpool','seats_left'=>2,'pickup_note'=>'Near Metro Gate No. 2','drop_note'=>'CP Metro Station Exit 5'],
    2=>['name'=>'Priya Mehta','photo'=>'https://randomuser.me/api/portraits/women/44.jpg','rating'=>4.7,'trips'=>89,'price'=>90,'from_time'=>'08:00 AM','to_time'=>'09:30 AM','duration'=>'1h 30m','distance'=>'38 km','vehicle'=>'Maruti Swift','plate'=>'UP-16 CD 5678','year'=>2021,'color'=>'White','type'=>'Carpool','seats_left'=>1,'pickup_note'=>'Sector 3 Bus Stop','drop_note'=>'Rajiv Chowk'],
    3=>['name'=>'Rahul Verma','photo'=>'https://randomuser.me/api/portraits/men/67.jpg','rating'=>5.0,'trips'=>212,'price'=>70,'from_time'=>'08:30 AM','to_time'=>'10:00 AM','duration'=>'1h 30m','distance'=>'36 km','vehicle'=>'Royal Enfield','plate'=>'DL-7S EF 9012','year'=>2023,'color'=>'Black','type'=>'Bike','seats_left'=>1,'pickup_note'=>'Sector 18 Metro','drop_note'=>'Barakhamba Road'],
    4=>['name'=>'Vikram Singh','photo'=>'https://randomuser.me/api/portraits/men/11.jpg','rating'=>4.6,'trips'=>53,'price'=>150,'from_time'=>'09:00 AM','to_time'=>'10:20 AM','duration'=>'1h 20m','distance'=>'40 km','vehicle'=>'Toyota Innova','plate'=>'UP-14 GH 3456','year'=>2020,'color'=>'Grey','type'=>'CabShare','seats_left'=>3,'pickup_note'=>'Noida Sec 62 Gate','drop_note'=>'Connaught Place Inner Circle'],
];
$r = $rides[$id] ?? $rides[1];

$prefs = ['Music OK','AC','No Smoking','No Pets','Chit-chat OK'];
$icons = ['fa-music','fa-wind','fa-ban-smoking','fa-paw','fa-comment-dots'];
$colors= ['text-brand-green','text-brand-blue','text-red-400','text-amber-500','text-gray-400'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ride Details | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
/* glass-nav defined in header.php */
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;}
.connect-btn{background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;font-weight:800;font-size:17px;padding:17px;border-radius:16px;border:none;cursor:pointer;transition:all .3s;box-shadow:0 8px 25px rgba(27,128,54,.35);width:100%;}
.connect-btn:hover{transform:translateY(-2px);box-shadow:0 14px 35px rgba(27,128,54,.45);}
.connect-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.route-dot-g{width:14px;height:14px;border-radius:50%;border:3px solid #1b8036;background:#fff;flex-shrink:0;}
.route-dot-o{width:14px;height:14px;border-radius:50%;border:3px solid #f3821a;background:#fff;flex-shrink:0;}
.route-ln{width:2px;background:linear-gradient(#1b8036,#f3821a);flex:1;min-height:32px;margin:0 auto;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:flex-end;justify-content:center;}
.modal-sheet{background:#fff;border-radius:2rem 2rem 0 0;padding:28px 24px 40px;width:100%;max-width:520px;}
@keyframes slideUp{from{transform:translateY(100%);}to{transform:translateY(0);}}
.slide-up{animation:slideUp .4s cubic-bezier(.4,0,.2,1);}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.seat-btn{flex:1;padding:12px;border-radius:12px;border:2px solid #e2e8f0;font-weight:800;font-size:15px;cursor:pointer;transition:all .2s;background:#fff;color:#64748b;}
.seat-btn.active{border-color:#1b8036;background:#f0fdf4;color:#1b8036;}
.pref-tag{background:#f1f5f9;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;color:#64748b;}
.spin{animation:spin .8s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
#toast{position:fixed;bottom:100px;left:50%;transform:translateX(-50%) translateY(80px);padding:12px 20px;border-radius:12px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;z-index:9999;transition:transform .35s,opacity .35s;opacity:0;background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0;}
#toast.show{transform:translateX(-50%) translateY(0);opacity:1;}
</style>
</head>
<body>
<div id="toast"><i class="fa-solid fa-circle-check"></i><span id="t-msg"></span></div>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-20 pb-32 max-w-3xl mx-auto px-4">

  <!-- Driver -->
  <div class="card p-6 mb-4 fade-up">
    <div class="flex items-center gap-5">
      <div class="relative shrink-0">
        <img src="<?= h($r['photo']) ?>" class="w-20 h-20 rounded-2xl object-cover" alt="">
        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 rounded-full border-2 border-white flex items-center justify-center"><i class="fa-solid fa-check text-white text-[8px]"></i></div>
      </div>
      <div class="flex-1">
        <div class="flex justify-between items-start">
          <div>
            <p class="font-black text-brand-blue text-xl"><?= h($r['name']) ?></p>
            <div class="flex flex-wrap gap-2 mt-1">
              <span class="text-xs font-bold px-2 py-1 rounded-full" style="background:#dcfce7;color:#166534;">✓ Aadhaar Verified</span>
              <span class="text-xs font-bold px-2 py-1 rounded-full" style="background:#dbeafe;color:#1e40af;">✓ DL Verified</span>
            </div>
          </div>
          <div class="text-right"><p class="text-3xl font-black text-brand-green">₹<?= $r['price'] ?></p><p class="text-xs text-gray-400">per seat</p></div>
        </div>
        <div class="flex items-center gap-3 mt-2">
          <span class="text-brand-orange text-sm">★★★★<?= $r['rating']>=5?'★':'☆' ?></span>
          <span class="font-bold text-sm text-gray-600"><?= $r['rating'] ?></span>
          <span class="text-gray-300">·</span>
          <span class="text-xs text-gray-500 font-semibold"><?= $r['trips'] ?> rides · Member since 2023</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Route -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.07s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Route & Timing</p>
    <div class="flex gap-5">
      <div class="flex flex-col items-center"><div class="route-dot-g"></div><div class="route-ln"></div><div class="route-dot-o"></div></div>
      <div class="flex-1">
        <div class="pb-5">
          <p class="font-black text-brand-blue text-lg"><?= h($r['from_time']) ?></p>
          <p class="font-semibold text-gray-600 text-sm"><?= $from ?></p>
          <p class="text-xs text-gray-400 mt-1"><?= h($r['pickup_note']) ?></p>
        </div>
        <div class="pt-5">
          <p class="font-black text-brand-blue text-lg"><?= h($r['to_time']) ?></p>
          <p class="font-semibold text-gray-600 text-sm"><?= $to ?></p>
          <p class="text-xs text-gray-400 mt-1"><?= h($r['drop_note']) ?></p>
        </div>
      </div>
      <div class="text-right shrink-0">
        <div class="bg-brand-blue/5 rounded-xl p-3 text-center">
          <p class="text-xs text-gray-500 font-bold">Duration</p>
          <p class="font-black text-brand-blue text-lg"><?= h($r['duration']) ?></p>
          <p class="text-xs text-gray-400"><?= h($r['distance']) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Vehicle -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.12s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Vehicle</p>
    <div class="flex justify-between items-center">
      <div>
        <p class="font-black text-brand-blue text-base"><?= h($r['vehicle']) ?></p>
        <p class="text-gray-500 text-sm font-semibold"><?= h($r['plate']) ?> · <?= h($r['color']) ?> · <?= h($r['year']) ?></p>
      </div>
      <div class="w-14 h-14 bg-brand-blue/5 rounded-2xl flex items-center justify-center text-brand-blue text-2xl"><i class="fa-solid fa-car-side"></i></div>
    </div>
    <div class="flex items-center gap-2 mt-4">
      <p class="text-sm font-bold text-gray-600">Seats:</p>
      <?php for($s=0;$s<4;$s++): ?>
      <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm border-2 <?= $s < $r['seats_left'] ? 'border-brand-green bg-green-50 text-brand-green' : 'border-gray-200 bg-gray-50 text-gray-300' ?>"><i class="fa-solid fa-user text-xs"></i></div>
      <?php endfor; ?>
      <span class="text-xs text-brand-green font-black ml-1"><?= $r['seats_left'] ?> left</span>
    </div>
  </div>

  <!-- Preferences -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.16s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Preferences</p>
    <div class="flex flex-wrap gap-2">
      <?php foreach($prefs as $pi=>$pref): ?>
      <span class="pref-tag"><i class="fa-solid <?= $icons[$pi] ?> mr-1 <?= $colors[$pi] ?>"></i><?= $pref ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Price -->
  <div class="card p-6 mb-4 fade-up" style="animation-delay:.2s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Price Breakdown</p>
    <div class="space-y-2 text-sm">
      <div class="flex justify-between"><span class="text-gray-600 font-semibold">Seat Price</span><span class="font-bold text-brand-blue">₹<?= $r['price'] ?></span></div>
      <div class="flex justify-between"><span class="text-gray-600 font-semibold">Platform Fee</span><span class="font-bold text-brand-green">FREE</span></div>
      <div class="flex justify-between border-t border-gray-100 pt-2 mt-2"><span class="font-black text-brand-blue">Total</span><span class="font-black text-brand-green text-lg">₹<?= $r['price'] ?></span></div>
    </div>
    <p class="text-xs text-gray-400 mt-3"><i class="fa-solid fa-circle-info mr-1"></i>Pay cash or UPI directly to driver after the ride</p>
  </div>

</div>

<!-- Sticky Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-40">
  <div class="max-w-3xl mx-auto flex gap-3">
    <button class="connect-btn" onclick="document.getElementById('connect-modal').classList.remove('hidden')">
      <i class="fa-solid fa-handshake mr-2"></i>Connect & Book Seat
    </button>
    <a href="tel:" class="w-14 h-14 rounded-2xl bg-brand-green/10 flex items-center justify-center text-brand-green hover:bg-brand-green hover:text-white transition shrink-0">
      <i class="fa-solid fa-phone text-xl"></i>
    </a>
  </div>
</div>

<!-- Connect Modal -->
<div class="modal-overlay hidden" id="connect-modal" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="modal-sheet slide-up">
    <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-6"></div>
    <h3 class="text-2xl font-black text-brand-blue mb-1">Confirm Booking</h3>
    <p class="text-gray-500 text-sm mb-5">Connecting with <?= h($r['name']) ?></p>
    <div class="bg-brand-green/5 border border-brand-green/20 rounded-2xl p-4 mb-5">
      <div class="flex justify-between mb-2">
        <p class="font-bold text-brand-blue text-sm"><i class="fa-solid fa-circle-dot text-brand-green mr-2"></i><?= $from ?></p>
        <p class="text-xs text-gray-500"><?= h($r['from_time']) ?></p>
      </div>
      <div class="flex justify-between">
        <p class="font-bold text-brand-blue text-sm"><i class="fa-solid fa-location-dot text-brand-orange mr-2"></i><?= $to ?></p>
        <p class="text-xs text-gray-500"><?= h($r['to_time']) ?></p>
      </div>
      <div class="border-t border-brand-green/20 mt-3 pt-3 flex justify-between">
        <span class="text-sm font-bold text-gray-600">Per Seat</span>
        <span class="text-xl font-black text-brand-green">₹<?= $r['price'] ?></span>
      </div>
    </div>
    <div class="mb-5">
      <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Seats Required</p>
      <div class="flex gap-2" id="seat-btns">
        <?php for($s=1;$s<=$r['seats_left'];$s++): ?>
        <button class="seat-btn <?= $s===1?'active':'' ?>" onclick="selectSeat(<?= $s ?>,this)"><?= $s ?> Seat<?= $s>1?'s':'' ?></button>
        <?php endfor; ?>
      </div>
    </div>
    <button id="confirm-btn" class="connect-btn" onclick="doConnect()"><i class="fa-solid fa-check mr-2"></i>Confirm & Connect</button>
    <button onclick="document.getElementById('connect-modal').classList.add('hidden')" class="w-full mt-3 py-3 text-gray-500 font-bold text-sm hover:text-brand-blue transition">Cancel</button>
  </div>
</div>

<script>
let selectedSeats = 1;
function selectSeat(n, el) {
    selectedSeats = n;
    document.querySelectorAll('.seat-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
}
async function doConnect() {
    const btn = document.getElementById('confirm-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch spin mr-2"></i>Connecting...';
    const fd = new FormData();
    fd.append('action','connect');
    fd.append('seats', selectedSeats);
    fd.append('from','<?= addslashes($from) ?>');
    fd.append('to','<?= addslashes($to) ?>');
    const r = await fetch('ride-detail.php?id=<?= $id ?>', {method:'POST',body:fd});
    const d = await r.json();
    if(d.ok) { window.location.href = d.redirect; }
    else { 
        document.getElementById('t-msg').textContent = d.msg||'Error connecting.';
        document.getElementById('toast').classList.add('show');
        setTimeout(()=>document.getElementById('toast').classList.remove('show'),3000);
        btn.disabled=false;
        btn.innerHTML='<i class="fa-solid fa-check mr-2"></i>Confirm & Connect';
    }
}
</script>
</body>
</html>
