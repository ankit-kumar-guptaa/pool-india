<?php
require_once __DIR__ . '/config.php';

// ── AJAX: Send Phone OTP ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'send_otp') {
    $phone = preg_replace('/\D/', '', $_GET['mobile'] ?? '');
    if (strlen($phone) !== 10) {
        jsonResponse(['ok' => false, 'msg' => 'Enter a valid 10-digit mobile number.']);
    }
    
    $resp = AuthService::sendOTP($phone);
    
    if (isset($resp['__curl_error'])) {
        jsonResponse(['ok' => false, 'msg' => 'Network error. Try again.']);
    }
    if (($resp['status'] ?? '') === 'ok') {
        $_SESSION['otp_phone']  = $phone;
        $_SESSION['otp_secret'] = substr($resp['requestid'] ?? '', 8, 6);
        jsonResponse(['ok' => true, 'msg' => 'OTP sent to +91 ' . $phone]);
    }
    jsonResponse(['ok' => false, 'msg' => 'Failed to send OTP. Try again.']);
}

// ── AJAX: Verify Phone OTP & Login ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $entered = trim($_POST['otp'] ?? '');
    $secret  = $_SESSION['otp_secret'] ?? '';
    if (!$secret) jsonResponse(['ok' => false, 'msg' => 'OTP session expired. Resend OTP.']);
    if ($entered !== $secret) jsonResponse(['ok' => false, 'msg' => 'Incorrect OTP. Try again.']);

    $phone   = $_SESSION['otp_phone'] ?? '';
    $resp    = AuthService::verifyUserPhone($phone);

    if (
        isset($resp['status']) && $resp['status'] === 1
        && !empty($resp['data']['dataset']['table'])
    ) {
        $user = json_decode($resp['data']['dataset']['table1'][0]['userdetails'], true)[0] ?? [];
        $_SESSION['user'] = $user;
        unset($_SESSION['otp_secret'], $_SESSION['otp_phone']);
        jsonResponse(['ok' => true, 'name' => $user['name'] ?? 'User', 'redirect' => urldecode($_POST['redirect'] ?? 'rides.php')]);
    }

    // OTP matched but no record — still allow (first time)
    $_SESSION['user'] = ['name' => 'User', 'mobile_No' => $phone];
    unset($_SESSION['otp_secret'], $_SESSION['otp_phone']);
    jsonResponse(['ok' => true, 'name' => 'User', 'redirect' => 'rides.php']);
}

// ── LOGOUT ───────────────────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('login.php');
}

// ── Already logged in ─────────────────────────────────────────────────────────
if (isLoggedIn()) redirect('profile.php');

$redirect = h($_GET['redirect'] ?? 'rides.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pool India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{green:'#1b8036',blue:'#1d3a70',orange:'#f3821a'}}}}}</script>
    <style>
        body{background:#f1f5f9;font-family:'Plus Jakarta Sans',sans-serif;}
        .left-panel{background:linear-gradient(145deg,#1d3a70 0%,#0d2252 60%,#1b3a60 100%);}
        .auth-card{background:#fff;border-radius:0 2rem 2rem 0;box-shadow:0 20px 60px rgba(29,58,112,0.1);}
        @media (max-width: 1024px) {
            .auth-card { border-radius: 2rem; }
        }
        .field-wrap{position:relative;}
        .field-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;pointer-events:none;}
        .field-input{width:100%;background:#f8fafc;border:2px solid #e2e8f0;border-radius:14px;padding:14px 16px 14px 46px;font-size:15px;font-weight:600;color:#1d3a70;outline:none;transition:all .25s;font-family:'Plus Jakarta Sans',sans-serif;}
        .field-input:focus{border-color:#1b8036;background:#fff;box-shadow:0 0 0 3px rgba(27,128,54,.1);}
        .field-input.err{border-color:#ef4444;background:#fff5f5;}
        .otp-box{width:48px;height:54px;text-align:center;font-size:22px;font-weight:800;border:2px solid #e2e8f0;border-radius:12px;background:#f8fafc;color:#1d3a70;outline:none;transition:all .2s;}
        .otp-box:focus{border-color:#1b8036;background:#fff;box-shadow:0 0 0 3px rgba(27,128,54,.1);}
        .btn-green{width:100%;background:linear-gradient(135deg,#1b8036,#157a2e);color:#fff;font-weight:800;font-size:16px;padding:15px;border-radius:14px;border:none;cursor:pointer;transition:all .25s;box-shadow:0 6px 20px rgba(27,128,54,.3);}
        .btn-green:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 10px 28px rgba(27,128,54,.4);}
        .btn-green:disabled{opacity:.65;cursor:not-allowed;}
        .panel{display:none;} .panel.active{display:block;}
        #toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(80px);padding:13px 22px;border-radius:14px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:10px;z-index:9999;transition:transform .35s,opacity .35s;opacity:0;white-space:nowrap;box-shadow:0 8px 30px rgba(0,0,0,.12);}
        #toast.show{transform:translateX(-50%) translateY(0);opacity:1;}
        #toast.success{background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0;}
        #toast.error{background:#fff5f5;color:#991b1b;border:1.5px solid #fecaca;}
        #toast.info{background:#eff6ff;color:#1e40af;border:1.5px solid #bfdbfe;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
        .fade-up{animation:fadeUp .4s ease both;}
        @keyframes floatY{0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);}}
        .float-y{animation:floatY 5s ease-in-out infinite;}
        .spin{animation:spin .8s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div id="toast"><i id="t-icon" class="fa-solid fa-circle-check"></i><span id="t-msg"></span></div>

<div class="w-full max-w-5xl flex rounded-[2.5rem] overflow-hidden shadow-2xl" style="min-height:590px;">

    <!-- LEFT PANEL -->
    <div class="left-panel hidden lg:flex lg:w-[44%] flex-col justify-between p-10 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:28px 28px;"></div>
        <a href="index.php" class="flex items-center gap-3 relative z-10">
            <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center border border-white/20">
                <img src="images/logo.png" alt="Pool India" class="w-9 h-9 object-contain">
            </div>
            <span class="text-white font-black text-xl">POOL <span class="text-brand-green">India</span></span>
        </a>
        <div class="relative z-10">
            <h2 class="text-4xl font-black text-white leading-tight mb-4">India's Smarter<br>Way to <span class="text-brand-orange">Commute.</span></h2>
            <p class="text-blue-200 font-medium text-sm leading-relaxed mb-8">Join 5L+ verified commuters saving money &amp; reducing traffic — one shared ride at a time.</p>
            <div class="space-y-3">
                <div class="float-y bg-white/10 border border-white/15 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-brand-green/25 rounded-xl flex items-center justify-center text-brand-green shrink-0"><i class="fa-solid fa-shield-halved"></i></div>
                    <div><p class="text-white font-bold text-sm">100% Verified Profiles</p><p class="text-blue-300 text-xs">Aadhaar + DL verified commuters</p></div>
                </div>
                <div class="float-y bg-white/10 border border-white/15 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-sm" style="animation-delay:1.5s">
                    <div class="w-10 h-10 bg-brand-orange/25 rounded-xl flex items-center justify-center text-brand-orange shrink-0"><i class="fa-solid fa-leaf"></i></div>
                    <div><p class="text-white font-bold text-sm">Viksit Bharat 🇮🇳</p><p class="text-blue-300 text-xs">2,400+ tonnes CO₂ saved together</p></div>
                </div>
            </div>
        </div>
        <p class="text-blue-300/50 text-xs relative z-10">© 2026 Pool India. All rights reserved.</p>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-card flex-1 p-8 sm:p-10 flex flex-col justify-center">
        <div class="lg:hidden flex items-center gap-3 mb-10 justify-center">
            <img src="images/logo.png" class="w-10 h-10 object-contain" alt="">
            <span class="font-black text-brand-blue text-2xl">POOL <span class="text-brand-green">India</span></span>
        </div>

        <!-- PHONE STEP 1 -->
        <div class="panel active fade-up" id="panel-phone-enter">
            <h1 class="text-3xl font-black text-brand-blue mb-2">Welcome! 👋</h1>
            <p class="text-gray-400 text-sm font-semibold mb-8">Enter your mobile number to sign in or register instantly.</p>
            
            <div class="field-wrap mb-6">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-blue font-bold text-sm pointer-events-none">🇮🇳 +91</span>
                <input id="ph-num" type="tel" maxlength="10" placeholder="98765 43210" class="field-input text-lg tracking-wider" style="padding-left:72px" oninput="this.value=this.value.replace(/\D/g,'')" onkeydown="if(event.key==='Enter')doSendOtp()">
            </div>
            
            <button id="btn-send-otp" class="btn-green text-lg py-4 mb-6" onclick="doSendOtp()">Continue with Mobile <i class="fa-solid fa-arrow-right ml-2"></i></button>
            
            <div class="text-center bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <p class="text-brand-blue text-xs font-semibold leading-relaxed">
                    By continuing, you agree to Pool India's<br>
                    <a href="#" class="text-brand-green hover:underline font-bold">Terms of Service</a> and <a href="#" class="text-brand-green hover:underline font-bold">Privacy Policy</a>.
                </p>
            </div>
        </div>

        <!-- PHONE STEP 2 -->
        <div class="panel" id="panel-phone-otp">
            <button onclick="showPanel('panel-phone-enter')" class="w-10 h-10 rounded-full border-2 border-gray-100 flex items-center justify-center text-gray-400 hover:border-brand-blue hover:text-brand-blue hover:bg-blue-50 transition mb-6"><i class="fa-solid fa-arrow-left"></i></button>
            
            <h1 class="text-3xl font-black text-brand-blue mb-2">Verify Number 🔐</h1>
            <p class="text-gray-400 text-sm font-semibold mb-8">Enter the 6-digit code sent to <span id="otp-ph-disp" class="text-brand-blue font-black"></span></p>
            
            <div class="flex justify-between gap-2 mb-8" id="otp-boxes">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
                <input type="text" maxlength="1" class="otp-box" oninput="otpNext(this)" onkeydown="otpBack(event,this)">
            </div>
            
            <button id="btn-verify-otp" class="btn-green text-lg py-4 mb-4" onclick="doVerifyOtp()">Verify & Login <i class="fa-solid fa-check ml-2"></i></button>
            
            <p class="text-center text-sm text-gray-400 font-semibold">
                Didn't receive code?
                <button id="resend-btn" class="text-brand-green font-black hover:underline ml-1 px-2 py-1" onclick="doSendOtp()">Resend OTP</button>
                <span id="resend-timer" class="hidden font-bold ml-1 text-brand-blue bg-blue-50 px-3 py-1 rounded-full text-xs"></span>
            </p>
        </div>

        <div class="mt-auto pt-8 text-center"><a href="index.php" class="text-gray-400 text-sm font-semibold hover:text-brand-blue transition"><i class="fa-solid fa-house mr-1"></i> Back to Home</a></div>
    </div>
</div>

<script>
const REDIRECT = '<?= $redirect ?>';
let timerInterval;

/* ── Toast ── */
let tTimer;
function toast(type, msg) {
    const t = document.getElementById('toast');
    const icons = {success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
    t.className = 'show ' + type;
    document.getElementById('t-icon').className = 'fa-solid ' + (icons[type]||icons.info);
    document.getElementById('t-msg').textContent = msg;
    clearTimeout(tTimer);
    tTimer = setTimeout(()=>t.classList.remove('show'), 3500);
}

/* ── Panel ── */
function showPanel(id) {
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
    const el = document.getElementById(id);
    el.classList.add('active','fade-up');
}

/* ── Loading state ── */
function setBtn(id, loading, html='') {
    const b = document.getElementById(id);
    b.disabled = loading;
    if (!loading && html) b.innerHTML = html;
    if (loading) b.innerHTML = '<i class="fa-solid fa-circle-notch spin mr-2"></i>Please wait...';
}

/* ── Send Phone OTP ── */
async function doSendOtp() {
    const ph = document.getElementById('ph-num').value.trim();
    if (ph.length!==10) { toast('error','Enter a valid 10-digit number.'); return; }

    setBtn('btn-send-otp', true);
    try {
        const r = await fetch(`login.php?action=send_otp&mobile=${ph}`);
        const d = await r.json();
        setBtn('btn-send-otp', false, 'Continue with Mobile <i class="fa-solid fa-arrow-right ml-2"></i>');

        if (d.ok) {
            document.getElementById('otp-ph-disp').textContent = '+91 ' + ph;
            showPanel('panel-phone-otp');
            document.querySelector('#otp-boxes .otp-box').focus();
            startTimer();
            toast('success', d.msg);
        } else {
            toast('error', d.msg);
        }
    } catch(e) {
        setBtn('btn-send-otp', false, 'Continue with Mobile <i class="fa-solid fa-arrow-right ml-2"></i>');
        toast('error', 'Network error. Please try again.');
    }
}

/* ── Verify OTP ── */
async function doVerifyOtp() {
    const otp = [...document.querySelectorAll('#otp-boxes .otp-box')].map(i=>i.value).join('');
    if (otp.length<6) { toast('error','Enter the complete 6-digit OTP.'); return; }

    setBtn('btn-verify-otp', true);
    const fd = new FormData();
    fd.append('action','verify_otp');
    fd.append('otp', otp);
    fd.append('redirect', REDIRECT);

    try {
        const r = await fetch('login.php', {method:'POST', body:fd});
        const d = await r.json();
        if (d.ok) {
            toast('success', `Welcome, ${d.name}! 🎉`);
            setTimeout(()=> window.location.href = d.redirect || 'rides.php', 900);
        } else {
            toast('error', d.msg);
            document.querySelectorAll('#otp-boxes .otp-box').forEach(i=>{ i.value=''; i.style.borderColor='#ef4444'; });
            setTimeout(()=> document.querySelectorAll('#otp-boxes .otp-box').forEach(i=>{ i.style.borderColor=''; }), 1200);
            setBtn('btn-verify-otp', false, 'Verify & Login <i class="fa-solid fa-check ml-2"></i>');
        }
    } catch(e) {
        setBtn('btn-verify-otp', false, 'Verify & Login <i class="fa-solid fa-check ml-2"></i>');
        toast('error', 'Network error. Please try again.');
    }
}

/* ── OTP box navigation ── */
function otpNext(inp) {
    inp.value = inp.value.replace(/\D/g,'');
    const boxes = [...document.querySelectorAll('#otp-boxes .otp-box')];
    const idx = boxes.indexOf(inp);
    if (inp.value && idx < boxes.length-1) boxes[idx+1].focus();
    if (idx===boxes.length-1 && inp.value) doVerifyOtp();
}
function otpBack(e, inp) {
    if (e.key==='Backspace' && !inp.value) {
        const boxes = [...document.querySelectorAll('#otp-boxes .otp-box')];
        const idx = boxes.indexOf(inp);
        if (idx>0) boxes[idx-1].focus();
    }
}

/* ── Resend timer ── */
function startTimer() {
    let s = 30;
    const btn = document.getElementById('resend-btn');
    const tmr = document.getElementById('resend-timer');
    btn.classList.add('hidden'); tmr.classList.remove('hidden');
    clearInterval(timerInterval);
    timerInterval = setInterval(()=>{
        s--;
        tmr.textContent = `00:${s.toString().padStart(2,'0')}`;
        if (s<=0) { clearInterval(timerInterval); tmr.classList.add('hidden'); btn.classList.remove('hidden'); }
    }, 1000);
}
</script>
</body>
</html>
