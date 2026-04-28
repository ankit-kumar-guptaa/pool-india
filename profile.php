<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();
$tab  = h($_GET['tab'] ?? 'rides');
$name = h($user['name'] ?? 'User');
$phone= h($user['mobile_No'] ?? '+91 XXXXXXXXXX');
$email= h($user['email'] ?? '');

$myRides = [
    ['status'=>'confirmed','route'=>'Sector 3 → Connaught Place','date'=>'Tomorrow · 07:30 AM','seats'=>1,'price'=>120,'driver'=>'Arjun S.','action'=>'upcoming'],
    ['status'=>'completed','route'=>'Sector 18 → Saket, Delhi','date'=>'25 Apr · 08:00 AM','seats'=>1,'price'=>95,'driver'=>'Priya M.','action'=>'rate'],
    ['status'=>'completed','route'=>'Noida City Centre → CP','date'=>'20 Apr · 09:00 AM','seats'=>1,'price'=>110,'driver'=>'Rahul V.','action'=>'done'],
    ['status'=>'cancelled','route'=>'Sector 62 → Gurgaon','date'=>'15 Apr · 07:00 AM','seats'=>1,'price'=>200,'driver'=>'-','action'=>'done'],
];
$connections = [
    ['name'=>'Arjun Sharma','photo'=>'https://randomuser.me/api/portraits/men/32.jpg','vehicle'=>'Honda City · Noida→CP','status'=>'active','time'=>'Tomorrow 07:30'],
    ['name'=>'Priya Mehta','photo'=>'https://randomuser.me/api/portraits/women/44.jpg','vehicle'=>'Maruti Swift · Noida→Saket','status'=>'past','rating'=>5],
    ['name'=>'Rahul Verma','photo'=>'https://randomuser.me/api/portraits/men/67.jpg','vehicle'=>'Royal Enfield · Noida→CP','status'=>'past','rating'=>5],
];
$responses = [
    ['name'=>'Neha Gupta','photo'=>'https://randomuser.me/api/portraits/women/55.jpg','detail'=>'Wants 1 seat · Sector 3→CP · Tomorrow 07:30','status'=>'pending'],
    ['name'=>'Amit Joshi','photo'=>'https://randomuser.me/api/portraits/men/23.jpg','detail'=>'Wants 2 seats · Noida→Gurgaon · 28 Apr','status'=>'pending'],
    ['name'=>'Saurabh K.','photo'=>'https://randomuser.me/api/portraits/men/88.jpg','detail'=>'Noida→CP · 20 Apr','status'=>'accepted'],
    ['name'=>'Divya M.','photo'=>'https://randomuser.me/api/portraits/women/33.jpg','detail'=>'Sec 62→Gurgaon · 18 Apr','status'=>'declined'],
];
$statusBadge = ['confirmed'=>'pill-green','completed'=>'pill-blue','cancelled'=>'pill-red','pending'=>'pill-orange','active'=>'pill-green','accepted'=>'pill-green','declined'=>'pill-red','past'=>'pill-blue'];
$statusLabel = ['confirmed'=>'● Confirmed','completed'=>'✓ Completed','cancelled'=>'✗ Cancelled','pending'=>'⏳ Pending','active'=>'● Active','accepted'=>'✓ Accepted','declined'=>'✗ Declined','past'=>'✓ Past'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
/* glass-nav defined in header.php */
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;}
.tab-btn{padding:10px 18px;border-radius:999px;font-weight:700;font-size:13px;cursor:pointer;transition:all .25s;white-space:nowrap;border:none;}
.tab-btn.active{background:#1d3a70;color:#fff;box-shadow:0 4px 14px rgba(29,58,112,.3);}
.tab-btn:not(.active){color:#64748b;background:transparent;}
.pill-green{background:#dcfce7;color:#166534;}
.pill-blue{background:#dbeafe;color:#1e40af;}
.pill-orange{background:#ffedd5;color:#9a3412;}
.pill-red{background:#fee2e2;color:#991b1b;}
.status-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
.ride-item{background:#f8fafc;border-radius:1.2rem;padding:14px;border:1.5px solid #e2e8f0;transition:all .2s;}
.ride-item:hover{border-color:#1b8036;background:#f0fdf4;}
.profile-hero{background:linear-gradient(135deg,#1d3a70 0%,#0d2252 100%);border-radius:0 0 2rem 2rem;}
.panel{display:none;}.panel.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.verify-ok{background:#dcfce7;border:1.5px solid #bbf7d0;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;}
.verify-pend{background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;}
</style>
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-16">
  <!-- Profile Hero -->
  <div class="profile-hero px-4 pt-8 pb-14">
    <div class="max-w-3xl mx-auto flex items-center gap-5">
      <div class="relative shrink-0">
        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center text-white text-3xl font-black border-4 border-white/20">
          <?= strtoupper(substr($name,0,1)) ?>
        </div>
      </div>
      <div class="flex-1">
        <h1 class="text-white font-black text-2xl"><?= $name ?></h1>
        <p class="text-blue-300 text-sm font-semibold"><?= $phone ?><?= $email ? ' · '.$email : '' ?></p>
        <div class="flex gap-2 mt-2 flex-wrap">
          <span class="status-pill pill-green" style="font-size:10px;">✓ Verified</span>
          <span class="status-pill" style="background:#fff3cd;color:#856404;font-size:10px;">⚠ DL Pending</span>
        </div>
      </div>
      <div class="text-right">
        <div class="text-brand-orange text-lg">★★★★☆</div>
        <p class="text-white font-black text-xl leading-none mt-0.5">4.7</p>
        <p class="text-blue-300 text-xs font-semibold">42 rides</p>
      </div>
    </div>
    <!-- Stats -->
    <div class="max-w-3xl mx-auto mt-5 bg-white/10 backdrop-blur-md rounded-2xl p-4 flex gap-2 border border-white/10">
      <div class="flex-1 text-center border-r border-white/10"><p class="text-2xl font-black text-white">42</p><p class="text-blue-300 text-xs font-semibold">Rides</p></div>
      <div class="flex-1 text-center border-r border-white/10"><p class="text-2xl font-black text-brand-green">₹3,840</p><p class="text-blue-300 text-xs font-semibold">Saved</p></div>
      <div class="flex-1 text-center border-r border-white/10"><p class="text-2xl font-black text-brand-orange">12</p><p class="text-blue-300 text-xs font-semibold">Connections</p></div>
      <div class="flex-1 text-center"><p class="text-2xl font-black text-white">8kg</p><p class="text-blue-300 text-xs font-semibold">CO₂ Saved</p></div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="max-w-3xl mx-auto px-4 -mt-5">
    <div class="card p-1.5 flex gap-1 overflow-x-auto mb-5" style="border-radius:999px;">
      <button class="tab-btn <?= $tab==='rides'?'active':'' ?>" id="tb-rides" onclick="switchTab('rides')"><i class="fa-solid fa-car-side mr-1.5"></i>My Rides</button>
      <button class="tab-btn <?= $tab==='connections'?'active':'' ?>" id="tb-connections" onclick="switchTab('connections')"><i class="fa-solid fa-handshake mr-1.5"></i>Connections</button>
      <button class="tab-btn <?= $tab==='responses'?'active':'' ?>" id="tb-responses" onclick="switchTab('responses')"><i class="fa-solid fa-reply mr-1.5"></i>Responses</button>
      <button class="tab-btn <?= $tab==='profile'?'active':'' ?>" id="tb-profile" onclick="switchTab('profile')"><i class="fa-solid fa-user mr-1.5"></i>Profile</button>
    </div>

    <!-- MY RIDES -->
    <div class="panel <?= $tab==='rides'?'active':'' ?> fade-up" id="panel-rides">
      <div class="flex justify-between items-center mb-4">
        <p class="font-black text-brand-blue">My Rides</p>
        <a href="rides.php" class="bg-brand-green text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-green-700 transition">+ Find a Ride</a>
      </div>
      <div class="space-y-3">
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Upcoming</p>
        <?php foreach($myRides as $r): if($r['action']!=='upcoming') continue; ?>
        <div class="ride-item">
          <div class="flex justify-between items-start mb-3">
            <div><span class="status-pill <?= $statusBadge[$r['status']] ?>"><?= $statusLabel[$r['status']] ?></span><p class="font-black text-brand-blue mt-1"><?= h($r['route']) ?></p><p class="text-xs text-gray-500 font-semibold mt-0.5"><?= h($r['date']) ?> · <?= $r['seats'] ?> Seat</p></div>
            <div class="text-right"><p class="text-xl font-black text-brand-green">₹<?= $r['price'] ?></p><p class="text-xs text-gray-400">with <?= h($r['driver']) ?></p></div>
          </div>
          <div class="flex gap-2">
            <button class="flex-1 py-2 rounded-xl bg-brand-blue/10 text-brand-blue text-xs font-bold hover:bg-brand-blue hover:text-white transition"><i class="fa-solid fa-comment-dots mr-1"></i>Chat</button>
            <button class="flex-1 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold hover:bg-red-50 hover:text-red-500 transition"><i class="fa-solid fa-xmark mr-1"></i>Cancel</button>
          </div>
        </div>
        <?php endforeach; ?>
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mt-4">Past Rides</p>
        <?php foreach($myRides as $r): if($r['action']==='upcoming') continue; ?>
        <div class="ride-item">
          <div class="flex justify-between items-start">
            <div><span class="status-pill <?= $statusBadge[$r['status']] ?>"><?= $statusLabel[$r['status']] ?></span><p class="font-black text-brand-blue mt-1"><?= h($r['route']) ?></p><p class="text-xs text-gray-500 font-semibold mt-0.5"><?= h($r['date']) ?></p></div>
            <div class="text-right"><p class="text-xl font-black text-gray-400">₹<?= $r['price'] ?></p><?php if($r['driver']!=='-'): ?><p class="text-xs text-gray-400">with <?= h($r['driver']) ?></p><?php endif; ?></div>
          </div>
          <?php if($r['action']==='rate'): ?>
          <div class="flex gap-2 mt-2">
            <button class="flex-1 py-2 rounded-xl bg-brand-orange/10 text-brand-orange text-xs font-bold"><i class="fa-solid fa-star mr-1"></i>Rate Ride</button>
            <button class="flex-1 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold"><i class="fa-solid fa-rotate-right mr-1"></i>Rebook</button>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- CONNECTIONS -->
    <div class="panel <?= $tab==='connections'?'active':'' ?> fade-up" id="panel-connections">
      <p class="font-black text-brand-blue mb-4">My Connections</p>
      <div class="space-y-3">
        <?php foreach($connections as $c): ?>
        <div class="card p-4 flex items-center gap-4">
          <img src="<?= h($c['photo']) ?>" class="w-12 h-12 rounded-2xl object-cover shrink-0" alt="">
          <div class="flex-1">
            <p class="font-black text-brand-blue"><?= h($c['name']) ?></p>
            <p class="text-xs text-gray-500 font-semibold"><?= h($c['vehicle']) ?></p>
            <div class="flex items-center gap-2 mt-1">
              <span class="status-pill <?= $statusBadge[$c['status']] ?>"><?= $statusLabel[$c['status']] ?></span>
              <?php if(isset($c['time'])): ?><span class="text-xs text-gray-400"><?= h($c['time']) ?></span><?php endif; ?>
              <?php if(isset($c['rating'])): ?><span class="text-brand-orange text-xs">★★★★★</span><?php endif; ?>
            </div>
          </div>
          <?php if($c['status']==='active'): ?>
          <div class="flex flex-col gap-1.5">
            <button class="w-9 h-9 rounded-xl bg-brand-blue/10 text-brand-blue flex items-center justify-center hover:bg-brand-blue hover:text-white transition"><i class="fa-solid fa-comment-dots text-sm"></i></button>
            <button class="w-9 h-9 rounded-xl bg-green-50 text-brand-green flex items-center justify-center"><i class="fa-solid fa-phone text-sm"></i></button>
          </div>
          <?php else: ?>
          <a href="rides.php" class="py-2 px-3 rounded-xl bg-brand-green/10 text-brand-green text-xs font-bold hover:bg-brand-green hover:text-white transition">Rebook</a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- RESPONSES -->
    <div class="panel <?= $tab==='responses'?'active':'' ?> fade-up" id="panel-responses">
      <p class="font-black text-brand-blue mb-4">My Responses</p>
      <div class="space-y-3">
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Pending Requests</p>
        <?php foreach($responses as $r): if($r['status']!=='pending') continue; ?>
        <div class="card p-4">
          <div class="flex items-center gap-3 mb-3">
            <img src="<?= h($r['photo']) ?>" class="w-11 h-11 rounded-xl object-cover shrink-0" alt="">
            <div class="flex-1"><p class="font-black text-brand-blue"><?= h($r['name']) ?></p><p class="text-xs text-gray-500 font-semibold"><?= h($r['detail']) ?></p></div>
            <span class="status-pill <?= $statusBadge[$r['status']] ?>"><?= $statusLabel[$r['status']] ?></span>
          </div>
          <div class="flex gap-2">
            <button class="flex-1 py-2.5 rounded-xl bg-brand-green text-white text-sm font-bold hover:bg-green-700 transition"><i class="fa-solid fa-check mr-1"></i>Accept</button>
            <button class="flex-1 py-2.5 rounded-xl bg-red-50 text-red-500 text-sm font-bold hover:bg-red-500 hover:text-white transition"><i class="fa-solid fa-xmark mr-1"></i>Decline</button>
          </div>
        </div>
        <?php endforeach; ?>
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mt-3">Past Responses</p>
        <?php foreach($responses as $r): if($r['status']==='pending') continue; ?>
        <div class="ride-item flex justify-between items-center">
          <div class="flex items-center gap-3">
            <img src="<?= h($r['photo']) ?>" class="w-10 h-10 rounded-xl object-cover" alt="">
            <div><p class="font-bold text-brand-blue text-sm"><?= h($r['name']) ?></p><p class="text-xs text-gray-400"><?= h($r['detail']) ?></p></div>
          </div>
          <span class="status-pill <?= $statusBadge[$r['status']] ?>"><?= $statusLabel[$r['status']] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- PROFILE SETTINGS -->
    <div class="panel <?= $tab==='profile'?'active':'' ?> fade-up" id="panel-profile">
      <p class="font-black text-brand-blue mb-4">Profile Settings</p>
      <div class="space-y-4">
        <!-- Personal Info -->
        <div class="card p-5">
          <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Personal Information</p>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500 font-semibold">Full Name</span><span class="font-bold text-brand-blue"><?= $name ?></span></div>
            <div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500 font-semibold">Mobile</span><span class="font-bold text-brand-blue"><?= $phone ?></span></div>
            <?php if($email): ?><div class="flex justify-between py-2 border-b border-gray-100"><span class="text-gray-500 font-semibold">Email</span><span class="font-bold text-brand-blue"><?= $email ?></span></div><?php endif; ?>
            <div class="flex justify-between py-2"><span class="text-gray-500 font-semibold">Home Area</span><span class="font-bold text-brand-blue"><?= h($user['Address'] ?? 'Sector 3, Noida') ?></span></div>
          </div>
        </div>
        <!-- Verification -->
        <div class="card p-5">
          <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Verification</p>
          <div class="space-y-3">
            <div class="verify-ok"><i class="fa-solid fa-id-card text-brand-green text-xl"></i><div><p class="font-bold text-brand-blue text-sm">Aadhaar Verified</p><p class="text-xs text-gray-500">Identity confirmed</p></div><i class="fa-solid fa-check-circle text-brand-green ml-auto text-xl"></i></div>
            <div class="verify-pend"><i class="fa-solid fa-id-badge text-brand-orange text-xl"></i><div class="flex-1"><p class="font-bold text-brand-blue text-sm">Driving Licence</p><p class="text-xs text-gray-500">Upload to drive on Pool India</p></div><button class="ml-auto bg-brand-orange text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-orange-600 transition">Upload</button></div>
          </div>
        </div>
        <!-- Viksit Bharat -->
        <div class="card p-5" style="background:linear-gradient(135deg,#fff7ed,#fef3c7);border-color:#fed7aa;">
          <div class="flex items-center gap-3 mb-3">
            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/41/Flag_of_India.svg/120px-Flag_of_India.svg.png" class="w-8 h-5 object-cover rounded" alt="India">
            <p class="font-black text-brand-blue">Viksit Bharat Contribution</p>
          </div>
          <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-white/70 rounded-xl p-3"><p class="text-xl font-black text-brand-green">8 kg</p><p class="text-xs text-gray-600 font-semibold">CO₂ Saved</p></div>
            <div class="bg-white/70 rounded-xl p-3"><p class="text-xl font-black text-brand-blue">42</p><p class="text-xs text-gray-600 font-semibold">Rides</p></div>
            <div class="bg-white/70 rounded-xl p-3"><p class="text-xl font-black text-brand-orange">🌱 3</p><p class="text-xs text-gray-600 font-semibold">Trees Equiv.</p></div>
          </div>
        </div>
        <a href="login.php?action=logout" class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl bg-red-50 text-red-500 font-bold border-2 border-red-100 hover:bg-red-500 hover:text-white transition mb-8">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>

  </div>
</div>

<script>
function switchTab(name) {
    ['rides','connections','responses','profile'].forEach(t=>{
        document.getElementById('panel-'+t).classList.toggle('active', t===name);
        document.getElementById('tb-'+t).classList.toggle('active', t===name);
    });
    window.scrollTo({top:250,behavior:'smooth'});
    history.replaceState(null,'','profile.php?tab='+name);
}
</script>
</body>
</html>
