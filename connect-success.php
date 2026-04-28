<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user    = currentUser();
$booking = $_SESSION['booking'] ?? [
    'driver'     => 'Arjun Sharma',
    'from'       => 'Sector 3, Noida',
    'to'         => 'Connaught Place, Delhi',
    'time_from'  => '07:30 AM',
    'time_to'    => '09:00 AM',
    'price'      => 120,
    'seats'      => 1,
    'booking_id' => 'PI-' . date('Y') . '-8841',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Confirmed | Pool India</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
<style>
body{background:linear-gradient(135deg,#0f1e3d 0%,#1d3a70 50%,#0d2440 100%);min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;}
@keyframes pop{0%{transform:scale(0) rotate(-10deg);opacity:0;}60%{transform:scale(1.15) rotate(3deg);}100%{transform:scale(1);opacity:1;}}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px);}to{opacity:1;transform:translateY(0);}}
@keyframes confettiFall{0%{transform:translateY(-20px) rotate(0);opacity:1;}100%{transform:translateY(100vh) rotate(720deg);opacity:0;}}
.pop-in{animation:pop .7s cubic-bezier(.34,1.56,.64,1) both;}
.fade-up{animation:fadeUp .5s ease both;}
.confetti{position:fixed;top:-10px;border-radius:2px;animation:confettiFall linear infinite;}
.glass{background:rgba(255,255,255,.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:1.5rem;}
.checkmark{width:100px;height:100px;background:linear-gradient(135deg,#1b8036,#22c55e);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 0 0 16px rgba(27,128,54,.15),0 0 0 32px rgba(27,128,54,.07);}
.action-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:15px;border-radius:14px;font-weight:800;font-size:15px;cursor:pointer;transition:all .25s;border:none;}
.btn-green{background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;box-shadow:0 8px 25px rgba(27,128,54,.4);}
.btn-green:hover{transform:translateY(-2px);}
.btn-ghost{background:rgba(255,255,255,.1);color:#fff;border:2px solid rgba(255,255,255,.2);}
.btn-ghost:hover{background:rgba(255,255,255,.18);}
.route-dg{width:11px;height:11px;border-radius:50%;border:2.5px solid #1b8036;background:#fff;}
.route-do{width:11px;height:11px;border-radius:50%;border:2.5px solid #f3821a;background:#fff;}
.route-ln{width:2px;background:linear-gradient(#1b8036,#f3821a);height:26px;margin:0 auto;}
</style>
</head>
<body class="flex items-center justify-center p-4">
<div id="confetti-wrap"></div>
<div class="w-full max-w-md py-8">

  <div class="pop-in mb-6 text-center">
    <div class="checkmark"><i class="fa-solid fa-check text-white text-4xl"></i></div>
  </div>

  <div class="text-center mb-8 fade-up" style="animation-delay:.2s">
    <h1 class="text-3xl font-black text-white mb-2">You're Connected! 🎉</h1>
    <p class="text-blue-200 font-medium"><?= h($booking['driver']) ?> has been notified.<br>Your seat is reserved.</p>
  </div>

  <!-- Summary -->
  <div class="glass p-6 mb-4 fade-up" style="animation-delay:.3s">
    <div class="flex items-center gap-4 mb-5 pb-5 border-b border-white/10">
      <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-14 h-14 rounded-2xl object-cover" alt="">
      <div class="flex-1">
        <p class="text-white font-black"><?= h($booking['driver']) ?></p>
        <p class="text-blue-300 text-xs font-semibold">Honda City · DL-3C AB 1234</p>
        <div class="text-brand-orange text-xs mt-0.5">★★★★★ <span class="text-blue-300 ml-1">4.9</span></div>
      </div>
      <div class="text-right">
        <p class="text-2xl font-black text-brand-green">₹<?= $booking['price'] ?></p>
        <p class="text-blue-400 text-xs"><?= $booking['seats'] ?> Seat<?= $booking['seats']>1?'s':'' ?></p>
      </div>
    </div>
    <div class="flex gap-4">
      <div class="flex flex-col items-center"><div class="route-dg"></div><div class="route-ln"></div><div class="route-do"></div></div>
      <div class="flex-1">
        <div class="mb-4"><p class="text-white font-black"><?= h($booking['time_from']) ?></p><p class="text-blue-300 text-xs"><?= h($booking['from']) ?></p></div>
        <div><p class="text-white font-black"><?= h($booking['time_to']) ?></p><p class="text-blue-300 text-xs"><?= h($booking['to']) ?></p></div>
      </div>
    </div>
  </div>

  <!-- Booking ID -->
  <div class="glass p-4 mb-4 fade-up flex items-center justify-between" style="animation-delay:.35s">
    <div>
      <p class="text-blue-300 text-xs font-bold uppercase tracking-wider">Booking ID</p>
      <p class="text-white font-black text-lg tracking-widest">#<?= h($booking['booking_id']) ?></p>
    </div>
    <button onclick="copyId()" id="copy-btn" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
      <i class="fa-regular fa-copy"></i> Copy
    </button>
  </div>

  <!-- Next Steps -->
  <div class="bg-white rounded-2xl p-5 mb-4 fade-up" style="animation-delay:.4s">
    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">What happens next?</p>
    <div class="space-y-3">
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full bg-brand-green/10 text-brand-green flex items-center justify-center font-black text-sm shrink-0">1</div><div><p class="font-bold text-brand-blue text-sm">Driver notified instantly</p><p class="text-gray-400 text-xs"><?= h($booking['driver']) ?> gets your request on the app</p></div></div>
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full bg-brand-orange/10 text-brand-orange flex items-center justify-center font-black text-sm shrink-0">2</div><div><p class="font-bold text-brand-blue text-sm">Chat & confirm pickup</p><p class="text-gray-400 text-xs">Coordinate exact location via in-app chat</p></div></div>
      <div class="flex gap-3"><div class="w-8 h-8 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center font-black text-sm shrink-0">3</div><div><p class="font-bold text-brand-blue text-sm">Pay after the ride</p><p class="text-gray-400 text-xs">₹<?= $booking['price'] ?> cash or UPI to driver</p></div></div>
    </div>
  </div>

  <!-- Actions -->
  <div class="space-y-3 fade-up" style="animation-delay:.45s">
    <button class="action-btn btn-green" onclick="window.location.href='profile.php?tab=rides'"><i class="fa-solid fa-list-check"></i> View My Rides</button>
    <button class="action-btn btn-ghost" onclick="window.location.href='index.php'"><i class="fa-solid fa-house"></i> Back to Home</button>
  </div>

  <div class="text-center mt-8 fade-up" style="animation-delay:.5s">
    <p class="text-blue-300 text-sm font-semibold mb-3">Track real-time on the Pool India app</p>
    <div class="flex justify-center gap-3">
      <a href="#" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"><i class="fa-brands fa-google-play text-brand-green"></i> Play Store</a>
      <a href="#" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"><i class="fa-brands fa-apple"></i> App Store</a>
    </div>
  </div>
</div>

<script>
(function(){
    const cols=['#1b8036','#f3821a','#1d3a70','#22c55e','#fbbf24','#fff'];
    for(let i=0;i<40;i++){
        const el=document.createElement('div');
        el.className='confetti';
        const sz=Math.random()*8+5;
        el.style.cssText=`width:${sz}px;height:${sz}px;background:${cols[Math.floor(Math.random()*cols.length)]};left:${Math.random()*100}vw;animation-duration:${Math.random()*3+2}s;animation-delay:${Math.random()*2}s;`;
        document.getElementById('confetti-wrap').appendChild(el);
    }
    setTimeout(()=>document.getElementById('confetti-wrap').innerHTML='',6000);
})();
function copyId(){
    const id='<?= h($booking['booking_id']) ?>';
    navigator.clipboard.writeText('#'+id).catch(()=>{});
    const b=document.getElementById('copy-btn');
    b.innerHTML='<i class="fa-solid fa-check"></i> Copied!';
    b.style.color='#4ade80';
    setTimeout(()=>{b.innerHTML='<i class="fa-regular fa-copy"></i> Copy';b.style.color='';},2000);
}
</script>
</body>
</html>
