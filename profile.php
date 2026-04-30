<?php
require_once __DIR__ . '/config.php';
requireLogin();

// Fallback user defaults if API isn't populated
$user = currentUser();
$phone = $user['mobile_No'] ?? '';

// Try to fetch live user profile data
$profileResp = ProfileService::getUserProfile($phone);
if (isset($profileResp['status']) && $profileResp['status'] === 1 && !empty($profileResp['data'])) {
    $user = array_merge($user, $profileResp['data']);
    $_SESSION['user'] = $user;
}

$tab  = h($_GET['tab'] ?? 'rides');
$name = h($user['name'] ?? 'User');
$email= h($user['email'] ?? '');

$isAadharVerified = !empty($user['isAadharVerified']);
$isOfficialEmailVerified = !empty($user['isOfficialEmailVerified']);

// Mock logic for completion % based on dart code
$completion = 0;
if ($phone) $completion += 15;
if ($email) $completion += 15;
if ($isAadharVerified) $completion += 30;
if ($isOfficialEmailVerified) $completion += 20;
if (!empty($user['profileImageUrl'])) $completion += 20;

// Example data
$myRides = [
    ['status'=>'confirmed','route'=>'Sector 3 → Connaught Place','date'=>'Tomorrow · 07:30 AM','seats'=>1,'price'=>120,'driver'=>'Arjun S.','action'=>'upcoming'],
    ['status'=>'completed','route'=>'Sector 18 → Saket, Delhi','date'=>'25 Apr · 08:00 AM','seats'=>1,'price'=>95,'driver'=>'Priya M.','action'=>'rate'],
];

// Response mock data designed for response_details masking logic
$responses = [
    ['name'=>'Neha Gupta','photo'=>'https://randomuser.me/api/portraits/women/55.jpg','route'=>'Sector 3→CP','date'=>'Tomorrow 07:30','status'=>'pending','mobile_no'=>'9876543210','email'=>'neha.gupta@example.com','seats'=>1],
    ['name'=>'Saurabh K.','photo'=>'https://randomuser.me/api/portraits/men/88.jpg','route'=>'Noida→CP','date'=>'20 Apr 09:00','status'=>'accepted','mobile_no'=>'9988776655','email'=>'saurabh.k@gmail.com','seats'=>2],
];

$statusBadge = ['confirmed'=>'pill-green','completed'=>'pill-blue','cancelled'=>'pill-red','pending'=>'pill-orange','accepted'=>'pill-green','declined'=>'pill-red'];
$statusLabel = ['confirmed'=>'● Confirmed','completed'=>'✓ Completed','cancelled'=>'✗ Cancelled','pending'=>'⏳ Pending','accepted'=>'✓ Accepted','declined'=>'✗ Declined'];

// Masking helpers
function maskPhone($ph) { return $ph && strlen($ph)>4 ? substr($ph,0,2).'****'.substr($ph,-2) : '****'; }
function maskEmail($em) { 
    if(!$em) return '****@****.com';
    $p = explode('@',$em);
    if(count($p)!=2) return '****@****.com';
    $usr = strlen($p[0])>2 ? substr($p[0],0,2).'****' : '****';
    $dmn = explode('.',$p[1]);
    $tld = count($dmn)>1 ? end($dmn) : 'com';
    return "$usr@****.$tld";
}
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
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;overflow:hidden;}
.tab-btn{padding:10px 18px;border-radius:999px;font-weight:700;font-size:13px;cursor:pointer;transition:all .25s;white-space:nowrap;border:none;}
.tab-btn.active{background:#1d3a70;color:#fff;box-shadow:0 4px 14px rgba(29,58,112,.3);}
.tab-btn:not(.active){color:#64748b;background:transparent;}
.pill-green{background:#dcfce7;color:#166534;}
.pill-blue{background:#dbeafe;color:#1e40af;}
.pill-orange{background:#ffedd5;color:#9a3412;}
.pill-red{background:#fee2e2;color:#991b1b;}
.status-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
.profile-hero{background:linear-gradient(135deg,#1d3a70 0%,#0d2252 100%);border-radius:0 0 2rem 2rem;}
.panel{display:none;}.panel.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.field-wrap label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.field{width:100%;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:12px 14px;font-size:14px;font-weight:600;color:#1d3a70;outline:none;transition:all .25s;}
.field:focus{border-color:#1b8036;background:#fff;}
.verify-ok{background:#dcfce7;border:1.5px solid #bbf7d0;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:12px;}
.verify-pend{background:#fff7ed;border:1.5px solid #fed7aa;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:12px;}
.progress-bar{height:6px;border-radius:999px;background:rgba(255,255,255,0.2);overflow:hidden;}
.progress-fill{height:100%;border-radius:999px;background:#1b8036;transition:width 1s ease-out;}
</style>
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-16">
  <!-- Profile Hero -->
  <div class="profile-hero px-4 pt-8 pb-14 relative z-10">
    <div class="max-w-3xl mx-auto flex items-center gap-5">
      <div class="relative shrink-0">
        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center text-white text-3xl font-black border-4 border-white/20">
          <?= strtoupper(substr($name,0,1)) ?>
        </div>
      </div>
      <div class="flex-1">
        <h1 class="text-white font-black text-2xl"><?= $name ?></h1>
        <p class="text-blue-300 text-sm font-semibold mb-2"><?= $phone ?><?= $email ? ' · '.$email : '' ?></p>
        
        <div class="mb-1 flex justify-between items-center">
            <span class="text-xs text-white font-bold">Profile Completion</span>
            <span class="text-xs font-black text-brand-green"><?= $completion ?>%</span>
        </div>
        <div class="progress-bar"><div class="progress-fill" style="width:<?= $completion ?>%"></div></div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="max-w-3xl mx-auto px-4 -mt-5 relative z-20">
    <div class="card p-1.5 flex gap-1 overflow-x-auto mb-5" style="border-radius:999px;">
      <button class="tab-btn <?= $tab==='rides'?'active':'' ?>" id="tb-rides" onclick="switchTab('rides')"><i class="fa-solid fa-car-side mr-1.5"></i>My Rides</button>
      <button class="tab-btn <?= $tab==='responses'?'active':'' ?>" id="tb-responses" onclick="switchTab('responses')"><i class="fa-solid fa-reply mr-1.5"></i>Responses</button>
      <button class="tab-btn <?= $tab==='profile'?'active':'' ?>" id="tb-profile" onclick="switchTab('profile')"><i class="fa-solid fa-user-pen mr-1.5"></i>Edit Profile</button>
    </div>

    <!-- MY RIDES -->
    <div class="panel <?= $tab==='rides'?'active':'' ?> fade-up" id="panel-rides">
      <div class="flex justify-between items-center mb-4">
        <p class="font-black text-brand-blue">My Rides</p>
        <a href="post-ride.php" class="bg-brand-green text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-green-700 transition">+ Post a Ride</a>
      </div>
      <div class="space-y-3">
        <?php foreach($myRides as $r): ?>
        <div class="card p-4 hover:border-brand-green transition">
          <div class="flex justify-between items-start mb-2">
            <div><span class="status-pill <?= $statusBadge[$r['status']] ?>"><?= $statusLabel[$r['status']] ?></span><p class="font-black text-brand-blue mt-1"><?= h($r['route']) ?></p><p class="text-xs text-gray-500 font-semibold mt-0.5"><?= h($r['date']) ?> · <?= $r['seats'] ?> Seat(s)</p></div>
            <div class="text-right"><p class="text-xl font-black text-brand-green">₹<?= $r['price'] ?></p><p class="text-xs text-gray-400">Driver: <?= h($r['driver']) ?></p></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- RESPONSES (Mimicking response_details_screen) -->
    <div class="panel <?= $tab==='responses'?'active':'' ?> fade-up" id="panel-responses">
      <p class="font-black text-brand-blue mb-4">Ride Requests</p>
      <div class="space-y-4">
        <?php foreach($responses as $r): $isAcc = $r['status']==='accepted'; ?>
        <div class="card shadow-sm">
          <!-- Request Header -->
          <div class="p-4 border-b border-gray-100 flex items-center gap-4">
            <img src="<?= h($r['photo']) ?>" class="w-14 h-14 rounded-full border-4 <?= $isAcc?'border-green-100':'border-orange-100' ?>">
            <div class="flex-1">
                <p class="font-black text-brand-blue text-lg"><?= h($r['name']) ?></p>
                <p class="text-xs text-gray-500 font-semibold">Requested <?= $r['seats'] ?> seat(s) for <?= h($r['route']) ?></p>
            </div>
            <span class="status-pill <?= $statusBadge[$r['status']] ?> px-3 py-1 text-xs"><?= $isAcc?'Accepted':'Pending' ?></span>
          </div>
          
          <!-- Ride Details -->
          <div class="p-4 bg-gray-50/50">
              <div class="flex items-center gap-3 mb-3">
                  <i class="fa-regular fa-calendar text-brand-green"></i>
                  <div><p class="text-xs text-gray-400 font-bold">Date & Time</p><p class="text-sm font-bold text-brand-blue"><?= h($r['date']) ?></p></div>
              </div>
          </div>

          <!-- Contact Info (Masked if pending) -->
          <div class="p-4 border-t border-gray-100 relative">
              <?php if(!$isAcc): ?>
              <div class="absolute right-4 top-4 bg-orange-100 text-orange-800 text-[10px] font-black px-2 py-1 rounded flex items-center gap-1"><i class="fa-solid fa-lock text-[9px]"></i> MASKED</div>
              <?php endif; ?>
              <p class="text-sm font-black text-brand-blue mb-3">Contact Information</p>
              
              <div class="space-y-2">
                  <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl bg-white">
                      <i class="fa-solid fa-phone text-brand-green"></i>
                      <p class="font-bold text-sm text-gray-700 flex-1"><?= $isAcc ? h($r['mobile_no']) : maskPhone($r['mobile_no']) ?></p>
                      <?php if($isAcc): ?><button class="text-brand-blue hover:text-brand-green"><i class="fa-solid fa-phone-volume"></i></button><?php endif; ?>
                  </div>
                  <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl bg-white">
                      <i class="fa-solid fa-envelope text-brand-green"></i>
                      <p class="font-bold text-sm text-gray-700 flex-1"><?= $isAcc ? h($r['email']) : maskEmail($r['email']) ?></p>
                      <?php if($isAcc): ?><button class="text-brand-blue hover:text-brand-green"><i class="fa-solid fa-paper-plane"></i></button><?php endif; ?>
                  </div>
              </div>
          </div>

          <!-- Actions -->
          <?php if(!$isAcc): ?>
          <div class="p-4 bg-white border-t border-gray-100 flex gap-3">
              <button class="flex-1 bg-brand-green text-white font-black py-3 rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-200"><i class="fa-solid fa-check mr-1"></i> Accept</button>
              <button class="flex-1 bg-red-50 text-red-500 font-black py-3 rounded-xl border-2 border-red-100 hover:bg-red-500 hover:text-white transition"><i class="fa-solid fa-xmark mr-1"></i> Decline</button>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- PROFILE SETTINGS -->
    <div class="panel <?= $tab==='profile'?'active':'' ?> fade-up" id="panel-profile">
      <p class="font-black text-brand-blue mb-4">Edit Profile</p>
      <form class="space-y-4">
        
        <!-- Verification Status -->
        <div class="card p-5">
          <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Verifications</p>
          <div class="space-y-3">
            <div class="<?= $isAadharVerified ? 'verify-ok' : 'verify-pend' ?>">
                <i class="fa-solid fa-id-card text-2xl <?= $isAadharVerified?'text-brand-green':'text-brand-orange' ?>"></i>
                <div class="flex-1"><p class="font-bold text-brand-blue text-sm">Aadhaar Identity</p><p class="text-xs text-gray-500"><?= $isAadharVerified?'Verified':'Pending Verification' ?></p></div>
                <?php if($isAadharVerified): ?><i class="fa-solid fa-check-circle text-brand-green text-xl"></i><?php else: ?><button type="button" class="bg-brand-orange text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-orange-600 transition">Verify</button><?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Personal Info -->
        <div class="card p-5">
          <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Personal Details</p>
          <div class="space-y-3">
            <div class="field-wrap"><label>Full Name</label><input type="text" class="field" value="<?= $name ?>"></div>
            <div class="field-wrap"><label>Mobile Number (Uneditable)</label><input type="text" class="field bg-gray-50 text-gray-400" value="<?= $phone ?>" readonly></div>
            <div class="field-wrap"><label>Personal Email</label><input type="email" class="field" value="<?= $email ?>" placeholder="your@email.com"></div>
            
            <div class="field-wrap">
                <label>Gender</label>
                <select class="field bg-white">
                    <option <?= ($user['gender']??'')==='Male'?'selected':'' ?>>Male</option>
                    <option <?= ($user['gender']??'')==='Female'?'selected':'' ?>>Female</option>
                    <option <?= ($user['gender']??'')==='Other'?'selected':'' ?>>Other</option>
                </select>
            </div>
          </div>
        </div>

        <!-- Car Details -->
        <div class="card p-5">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Vehicle Details (For Poolers)</p>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="field-wrap"><label>Model</label><input type="text" class="field" placeholder="Swift" value="<?= h($user['carModel']??'') ?>"></div>
                <div class="field-wrap"><label>Color</label><input type="text" class="field" placeholder="White" value="<?= h($user['carColor']??'') ?>"></div>
            </div>
            <div class="field-wrap mb-3"><label>Registration Number</label><input type="text" class="field" placeholder="DL 3C XX 1234" value="<?= h($user['carNumber']??'') ?>" style="text-transform:uppercase"></div>
            <div class="field-wrap"><label>Driving License</label><input type="text" class="field" placeholder="DL Number" value="<?= h($user['licenseNumber']??'') ?>"></div>
        </div>

        <button type="button" class="w-full bg-brand-blue text-white font-black py-4 rounded-xl hover:bg-blue-900 transition shadow-lg mb-4" onclick="toast('success', 'Profile Saved Successfully')">Save Changes</button>
        
        <a href="login.php?action=logout" class="flex items-center justify-center gap-2 w-full py-4 rounded-xl bg-red-50 text-red-500 font-bold border-2 border-red-100 hover:bg-red-500 hover:text-white transition mb-8">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </form>
    </div>

  </div>
</div>

<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-xl transition-all duration-300 opacity-0 pointer-events-none translate-y-4 z-50"></div>

<script>
function switchTab(name) {
    ['rides','responses','profile'].forEach(t=>{
        document.getElementById('panel-'+t).classList.toggle('active', t===name);
        document.getElementById('tb-'+t).classList.toggle('active', t===name);
    });
    history.replaceState(null,'','profile.php?tab='+name);
}

function toast(type, msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `fixed bottom-6 left-1/2 -translate-x-1/2 px-4 py-2 rounded-lg font-bold text-sm shadow-xl transition-all duration-300 z-50 opacity-100 translate-y-0 ${type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translate(-50%, 1rem)';
    }, 3000);
}
</script>
</body>
</html>
