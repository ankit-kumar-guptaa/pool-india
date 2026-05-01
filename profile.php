<?php
require_once __DIR__ . '/config.php';
requireLogin();

$sessionUser = currentUser();
$userId = $sessionUser['id'] ?? 0;
$phone = $sessionUser['mobile_No'] ?? '';

// Handle AJAX Requests for OTPs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_aadhar_otp') {
        header('Content-Type: application/json');
        echo json_encode(ProfileService::sendAadharOTP($_POST['aadharNumber']));
        exit;
    }
    if ($_POST['action'] === 'verify_aadhar_otp') {
        header('Content-Type: application/json');
        echo json_encode(ProfileService::verifyAadharOTP($_POST['aadharNumber'], $_POST['otp']));
        exit;
    }
    if ($_POST['action'] === 'send_email_otp') {
        header('Content-Type: application/json');
        $emailToVerify = trim($_POST['email'] ?? '');
        if (empty($emailToVerify) || !filter_var($emailToVerify, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'fails', 'message' => 'Invalid email address']);
            exit;
        }
        // API model field is 'Email' (NOT 'EmailId') — must match SendEmailOtp record
        $resp = ApiService::post('App/SendOTPOnEmail', ['Email' => $emailToVerify]);
        if (!empty($resp['requestid'])) {
            // Store in session: requestid = "GREENCAR{6-digit-OTP}{ddMMyyyy}"
            $_SESSION['email_otp_requestid'] = $resp['requestid'];
            $_SESSION['email_otp_email']     = $emailToVerify;
        }
        echo json_encode($resp ?: ['status' => 'fails', 'message' => 'API error']);
        exit;
    }
    if ($_POST['action'] === 'verify_email_otp') {
        header('Content-Type: application/json');
        $enteredOtp = trim($_POST['otp'] ?? '');
        $reqId      = $_SESSION['email_otp_requestid'] ?? '';
        $savedEmail = $_SESSION['email_otp_email'] ?? '';
        // requestid format: GREENCAR{6-digit-otp}{ddMMyyyy}
        $realOtp = substr($reqId, 8, 6); // skip 'GREENCAR', take 6 digits
        if ($enteredOtp === $realOtp && !empty($realOtp)) {
            // ── CRITICAL: Fetch current profile FIRST so we don't wipe any existing fields ──
            $current = ProfileService::getUserProfile($phone);
            // Build full payload with ALL existing fields merged
            $saveResp = ProfileService::updateProfile([
                'userId'                  => $userId,
                'phoneNumber'             => $phone,
                'name'                    => $current['name']             ?? '',
                'gender'                  => $current['gender']           ?? '',
                'personalEmail'           => $savedEmail,  // the newly verified email
                'officialEmail'           => $current['officialEmail']    ?? '',
                'altPhone'                => $current['altPhone']         ?? '',
                'organisation'            => $current['organisation']     ?? '',
                'homeAddress'             => $current['homeAddress']      ?? '',
                'officeAddress'           => $current['officeAddress']    ?? '',
                'carModel'                => $current['carModel']         ?? '',
                'carNumber'               => $current['carNumber']        ?? '',
                'carColor'                => $current['carColor']         ?? '',
                'linkedin'                => $current['linkedin']         ?? '',
                'instagram'               => $current['instagram']        ?? '',
                'facebook'                => $current['facebook']         ?? '',
                // Preserve existing image — try every possible field name the API returns
                'profileImageUrl'         => $current['profileImageUrl']
                                          ?? $current['photoPath']
                                          ?? $current['profilePicturePath']
                                          ?? $current['photo']
                                          ?? '',
                'isPersonalEmailVerified' => true,   // ← this is what we're setting
                'isOfficialEmailVerified' => $current['isOfficialEmailVerified'] ?? false,
                'IsProfileUpdate'         => 1,
            ]);
            unset($_SESSION['email_otp_requestid'], $_SESSION['email_otp_email']);
            // Update session so page reload reflects verified state
            $_SESSION['user']['personalEmail']           = $savedEmail;
            $_SESSION['user']['isPersonalEmailVerified'] = true;
            echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Wrong OTP. Please try again.']);
        }
        exit;
    }
}

// Handle Profile Save
$toast = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_profile') {
    // ── Fetch live profile first to get latest verified flags ──
    // This prevents save from overwriting verified status with a stale PHP session value
    $liveProfile = ProfileService::getUserProfile($phone);
    $liveEmailVerified    = $liveProfile['isPersonalEmailVerified']  ?? false;
    $liveOfficialVerified = $liveProfile['isOfficialEmailVerified']  ?? false;

    // If the user just typed a NEW email (different from current), reset verified flag
    $submittedEmail = trim($_POST['Email'] ?? '');
    $currentLiveEmail = $liveProfile['personalEmail'] ?? $liveProfile['email'] ?? '';
    if (!empty($submittedEmail) && $submittedEmail !== $currentLiveEmail) {
        $liveEmailVerified = false; // email changed → must re-verify
    }

    $updateData = [
        'userId'                  => $userId,
        'name'                    => trim($_POST['Name'] ?? ''),
        'gender'                  => trim($_POST['Gender'] ?? ''),
        'personalEmail'           => $submittedEmail,
        'phoneNumber'             => $phone,
        'altPhone'                => trim($_POST['AlternatePhone'] ?? ''),
        'organisation'            => trim($_POST['CompanyName'] ?? ''),
        'homeAddress'             => trim($_POST['HomeAddress'] ?? ''),
        'officeAddress'           => trim($_POST['OfficeAddress'] ?? ''),
        'carModel'                => trim($_POST['CarModel'] ?? ''),
        'carNumber'               => trim($_POST['CarNumber'] ?? ''),
        'carColor'                => trim($_POST['CarColor'] ?? ''),
        'linkedin'                => trim($_POST['Linkedin'] ?? ''),
        'instagram'               => trim($_POST['Instagram'] ?? ''),
        'facebook'                => trim($_POST['Facebook'] ?? ''),
        // Profile image: use newly uploaded URL if present, else preserve existing from live API
        'profileImageUrl'         => !empty(trim($_POST['ProfileImageUrl'] ?? ''))
                                     ? trim($_POST['ProfileImageUrl'])
                                     : ($liveProfile['profileImageUrl'] ?? $liveProfile['photoPath'] ?? $liveProfile['photo'] ?? ''),
        'IsProfileUpdate'         => 1,
        // Always use live API verified flags — never trust stale session values
        'isPersonalEmailVerified' => $liveEmailVerified,
        'isOfficialEmailVerified' => $liveOfficialVerified,
    ];
    $updateResp = ProfileService::updateProfile($updateData);
    if (!empty($updateResp) && !isset($updateResp['__curl_error']) && !isset($updateResp['__raw'])) {
        $toast = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
        $_SESSION['user'] = array_merge($_SESSION['user'] ?? [], $updateData);
    } else {
        $toast = ['type' => 'error', 'msg' => $updateResp['message'] ?? 'Failed to update profile.'];
    }
}

// 1. Fetch Live User Profile (Mobile App endpoint)
$profileResp = ProfileService::getUserProfile($phone);
$userProfile = [];
if (!empty($profileResp) && (isset($profileResp['name']) || isset($profileResp['phoneNumber']) || isset($profileResp['mobileNumber']))) {
    $userProfile = $profileResp;
    $_SESSION['user'] = array_merge($sessionUser, $userProfile);
} else {
    $userProfile = $sessionUser;
}

$name = h($userProfile['name'] ?? 'User');
$email = h($userProfile['personalEmail'] ?? $userProfile['email'] ?? '');
$officialEmail = h($userProfile['officialEmail'] ?? '');
$gender = h($userProfile['gender'] ?? 'Not Specified');
$mobileNumber = h($userProfile['phoneNumber'] ?? $userProfile['mobileNumber'] ?? $phone);

// Extracted fields
$altPhone = h($userProfile['altPhone'] ?? '');
$homeAddress = h($userProfile['homeAddress'] ?? '');
$officeAddress = h($userProfile['officeAddress'] ?? '');
$organisation = h($userProfile['organisation'] ?? '');
$designation = h($userProfile['designation'] ?? '');
$aboutMe = h($userProfile['aboutMe'] ?? '');

$vehicleBrand = h($userProfile['vehicleBrand'] ?? '');
$vehicleType = h($userProfile['vehicleType'] ?? '');
$carModel = h($userProfile['carModel'] ?? '');
$carNumber = h($userProfile['carNumber'] ?? '');
$carColor = h($userProfile['carColor'] ?? '');

$linkedin = h($userProfile['linkedin'] ?? '');
$instagram = h($userProfile['instagram'] ?? '');
$facebook = h($userProfile['facebook'] ?? '');

// Safe bool checks
function isVerified($val): bool {
    if ($val === null) return false;
    if (is_bool($val)) return $val;
    if (is_numeric($val)) return $val == 1;
    if (is_string($val)) return in_array(strtolower(trim($val)), ['true', '1', 'yes']);
    return false;
}

$isAadharVerified = isVerified($userProfile['isAadharVerified'] ?? false);
$isPanVerified = isVerified($userProfile['isPanVerified'] ?? false);
$isPersonalEmailVerified = isVerified($userProfile['isPersonalEmailVerified'] ?? false);
$isOfficialEmailVerified = isVerified($userProfile['isOfficialEmailVerified'] ?? false);

// Completeness
$completion = 0;
if (!empty(trim($mobileNumber))) $completion += 20;
if (!empty(trim($email)) && $isPersonalEmailVerified) $completion += 20;
if ($isAadharVerified) $completion += 30;
if (!empty(trim($officialEmail)) && $isOfficialEmailVerified) $completion += 30;
$completion = min(100, $completion);

// 2. Fetch Live Rides
$ridesResp = RideService::getMyRides($userId);
$upcomingRides = [];
$pastRides = [];
if (is_array($ridesResp)) {
    foreach ($ridesResp as $ride) {
        if (!empty($ride['isUpcoming'])) $upcomingRides[] = $ride;
        else $pastRides[] = $ride;
    }
}

// 3. Fetch Connections
$connResp = RideService::getMyConnections($userId);
$pendingRequests = [];
$acceptedConnections = [];

if (isset($connResp['status']) && $connResp['status'] === 1 && isset($connResp['data']['dataset'])) {
    $myInbox = $connResp['data']['dataset']['table'] ?? [];
    $mySendRequest = $connResp['data']['dataset']['table1'] ?? [];
    
    foreach($myInbox as $r) {
        $r['_type'] = 'inbox';
        $isAcc = !empty($r['isaccept']) || strtolower($r['RequestStatus'] ?? '') === 'accepted';
        if ($isAcc) $acceptedConnections[] = $r;
        else $pendingRequests[] = $r;
    }
    foreach($mySendRequest as $r) {
        $r['_type'] = 'sent';
        $isAcc = !empty($r['isaccept']) || strtolower($r['RequestStatus'] ?? '') === 'accepted';
        if ($isAcc) $acceptedConnections[] = $r;
        else $pendingRequests[] = $r;
    }
}

// 4. Fetch CO2/Impact Details
$co2Resp = RideService::getCo2Details($userId);
$impact = [
    'co2' => $co2Resp['co2'] ?? 0,
    'trees' => $co2Resp['treesEquivalent'] ?? 0,
    'fuel' => $co2Resp['fuelSaved'] ?? 0,
    'distance' => $co2Resp['distance'] ?? 0,
];

// Formatting
function formatRideDate($dateStr) {
    if (!$dateStr) return 'Date not specified';
    $time = strtotime($dateStr);
    if (!$time) return h($dateStr);
    return strpos($dateStr, 'T') !== false && strpos($dateStr, ':') !== false 
        ? date('l, M j, Y • H:i', $time) : date('l, M j, Y', $time);
}

$tab = h($_GET['tab'] ?? 'profile');
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
body{background:#f8fafc;font-family:'Plus Jakarta Sans',sans-serif;}
.card{background:#fff;border-radius:1.5rem;border:1.5px solid #e2e8f0;overflow:hidden;}
.tab-btn{padding:10px 18px;border-radius:999px;font-weight:700;font-size:13px;cursor:pointer;transition:all .25s;white-space:nowrap;border:none;}
.tab-btn.active{background:#1d3a70;color:#fff;box-shadow:0 4px 14px rgba(29,58,112,.3);}
.tab-btn:not(.active){color:#64748b;background:transparent;}
.profile-hero{background:linear-gradient(135deg,#1d3a70 0%,#0d2252 100%);border-radius:0 0 2rem 2rem;}
.panel{display:none;}.panel.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.fade-up{animation:fadeUp .4s ease both;}
.field-wrap label{display:block;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.field{width:100%;background:#f1f5f9;border:2px solid #e2e8f0;border-radius:12px;padding:12px 14px;font-size:14px;font-weight:600;color:#1d3a70;outline:none;transition:all .25s;}
.field:focus{border-color:#1b8036;background:#fff;}
textarea.field {min-height: 80px; resize: none;}
.verify-ok{background:#dcfce7;border:1.5px solid #bbf7d0;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:12px;}
.verify-pend{background:#fff7ed;border:1.5px solid #fed7aa;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:12px;}
.progress-bar{height:6px;border-radius:999px;background:rgba(255,255,255,0.2);overflow:hidden;}
.progress-fill{height:100%;border-radius:999px;background:#1b8036;transition:width 1s ease-out;}
.accordion-header{cursor:pointer;user-select:none;transition:background .2s;}
.accordion-header:hover{background:#f8fafc;}
.accordion-content{display:none;}
.accordion-content.open{display:block;animation:fadeUp .3s ease;}

.conn-card {border-left: 4px solid transparent;}
.conn-accepted {border-left-color: #1b8036; background: #f0fdf4;}
.conn-pending {border-left-color: #f3821a; background: #fffbeb;}
.conn-sent {border-left-color: #3b82f6; background: #eff6ff;}
/* Sub-tabs inside Responses */
.sub-tab-btn { color:#64748b; background:transparent; transition:all .2s; }
.sub-tab-btn.active-sub { background:#fff; color:#1d3a70; box-shadow:0 2px 8px rgba(29,58,112,.12); font-weight:900; }
</style>
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<div class="pt-16">
  <!-- Profile Hero -->
  <div class="profile-hero px-4 pt-8 pb-14 relative z-10">
    <div class="max-w-3xl mx-auto flex items-center gap-5">
      <div class="relative shrink-0">
        <label for="profilePicUpload" class="cursor-pointer block relative group">
            <?php
              // API may return different field names — try all
              $profileImg = $userProfile['profileImageUrl']
                         ?? $userProfile['photoPath']
                         ?? $userProfile['profilePicturePath']
                         ?? $userProfile['photo']
                         ?? $userProfile['image']
                         ?? '';
            ?>
            <?php if(!empty($profileImg)): ?>
                <img id="profilePicImg" src="<?= h($profileImg) ?>" class="w-20 h-20 rounded-2xl object-cover border-4 border-white/20 transition group-hover:opacity-75">
            <?php else: ?>
                <div id="profilePicPlaceholder" class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center text-white text-3xl font-black border-4 border-white/20 transition group-hover:bg-white/30">
                  <?= strtoupper(substr($name,0,1)) ?>
                </div>
                <img id="profilePicImg" class="w-20 h-20 rounded-2xl object-cover border-4 border-white/20 transition group-hover:opacity-75 hidden">
            <?php endif; ?>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <i class="fa-solid fa-camera text-white text-xl drop-shadow-md"></i>
            </div>
            <input type="file" id="profilePicUpload" class="hidden" accept="image/*" onchange="uploadProfilePic(this)">
        </label>
      </div>
      <div class="flex-1">
        <h1 class="text-white font-black text-2xl"><?= $name ?></h1>
        <p class="text-blue-300 text-sm font-semibold mb-2"><?= $mobileNumber ?><?= $email ? ' · '.$email : '' ?></p>
        
        <div class="mb-1 flex justify-between items-center">
            <span class="text-xs text-white font-bold">Profile Completion</span>
            <span class="text-xs font-black text-brand-green"><?= $completion ?>%</span>
        </div>
        <div class="progress-bar"><div class="progress-fill" style="width:<?= $completion ?>%"></div></div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="max-w-3xl mx-auto px-4 -mt-5 relative z-20 pb-20">
    <div class="card p-1.5 flex gap-1 overflow-x-auto mb-5" style="border-radius:999px;">
      <button class="tab-btn <?= $tab==='profile'?'active':'' ?>" id="tb-profile" onclick="switchTab('profile')"><i class="fa-solid fa-user-pen mr-1.5"></i>Profile</button>
      <button class="tab-btn <?= $tab==='rides'?'active':'' ?>" id="tb-rides" onclick="switchTab('rides')"><i class="fa-solid fa-car-side mr-1.5"></i>My Rides</button>
      <button class="tab-btn relative <?= $tab==='responses'?'active':'' ?>" id="tb-responses" onclick="switchTab('responses')">
        <i class="fa-solid fa-comments mr-1.5"></i>Responses
        <?php
          $inboxOnlyCount = count(array_filter($pendingRequests, fn($r) => $r['_type'] === 'inbox'));
          if($inboxOnlyCount > 0): ?>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center"><?= $inboxOnlyCount ?></span>
        <?php endif; ?>
      </button>
      <button class="tab-btn <?= $tab==='connections'?'active':'' ?>" id="tb-connections" onclick="switchTab('connections')"><i class="fa-solid fa-people-arrows mr-1.5"></i>Connections</button>
    </div>

    <!-- 1. PROFILE SETTINGS -->
    <div class="panel <?= $tab==='profile'?'active':'' ?> fade-up" id="panel-profile">
      
      <form method="POST" action="profile.php?tab=profile" id="profileForm">
      <input type="hidden" name="action" value="save_profile">
      <input type="hidden" name="ProfileImageUrl" id="hiddenProfileImgUrl" value="<?= h($userProfile['profileImageUrl'] ?? '') ?>">
      
      <!-- Verifications UI -->
      <div class="flex flex-wrap items-center gap-2 mb-4 bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
          <!-- Mobile: always verified -->
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-700">
              <i class="fa-solid fa-mobile-screen"></i> <span class="text-xs font-bold">Mobile ✔️</span>
          </div>
          <!-- Email: show Verify button ONLY when not verified -->
          <?php if($isPersonalEmailVerified): ?>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-700">
              <i class="fa-solid fa-envelope"></i> <span class="text-xs font-bold">Email ✔️</span>
          </div>
          <?php else: ?>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 transition cursor-pointer" onclick="openEmailVerifyModal()">
              <i class="fa-solid fa-envelope"></i> <span class="text-xs font-bold">Email ⏳</span>
              <button type="button" class="ml-1 bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full">Verify</button>
          </div>
          <?php endif; ?>
          <!-- Aadhaar -->
          <?php if($isAadharVerified): ?>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-700">
              <i class="fa-solid fa-fingerprint"></i> <span class="text-xs font-bold">Aadhaar ✔️</span>
          </div>
          <?php else: ?>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 transition cursor-pointer" onclick="openAadhaarVerifyModal()">
              <i class="fa-solid fa-fingerprint"></i> <span class="text-xs font-bold">Aadhaar ⏳</span>
              <button type="button" class="ml-1 bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full">Verify</button>
          </div>
          <?php endif; ?>
          <!-- PAN -->
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg <?= $isPanVerified ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500' ?>">
              <i class="fa-solid fa-id-card"></i> <span class="text-xs font-bold">PAN <?= $isPanVerified?'✔️':'—' ?></span>
          </div>
      </div>

      <!-- Accordion: Personal Info -->
      <div class="card mb-4 shadow-sm border-gray-200">
          <div class="accordion-header p-5 flex justify-between items-center" onclick="toggleAcc('acc-personal')">
              <div class="flex items-center gap-3">
                  <div class="bg-blue-100 text-blue-600 w-10 h-10 flex items-center justify-center rounded-full"><i class="fa-solid fa-user"></i></div>
                  <span class="font-black text-brand-blue text-[15px]">Personal Information</span>
              </div>
              <i class="fa-solid fa-chevron-down text-gray-400"></i>
          </div>
          <div class="accordion-content p-5 pt-0 border-t border-gray-100" id="acc-personal">
              <div class="space-y-4 mt-4">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div class="field-wrap"><label>Full Name</label><input type="text" name="Name" class="field" value="<?= $name ?>"></div>
                      <div class="field-wrap">
                          <label>Gender</label>
                          <select name="Gender" class="field bg-white">
                              <option <?= $gender==='Male'?'selected':'' ?>>Male</option>
                              <option <?= $gender==='Female'?'selected':'' ?>>Female</option>
                              <option <?= $gender==='Other'?'selected':'' ?>>Other</option>
                          </select>
                      </div>
                       <div class="field-wrap sm:col-span-2">
                           <label class="flex justify-between items-center">
                               Personal Email
                               <?php if($isPersonalEmailVerified): ?>
                               <span class="text-green-600 text-[11px] font-black flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Verified</span>
                               <?php else: ?>
                               <span class="text-orange-500 text-[11px] font-semibold">Not Verified</span>
                               <?php endif; ?>
                           </label>
                           <div class="flex gap-2">
                               <input type="email" name="Email" id="profileEmail" class="field flex-1" value="<?= $email ?>" placeholder="Enter your email">
                               <?php if(!$isPersonalEmailVerified): ?>
                               <button type="button" id="btnSendEmailOtp"
                                   onclick="sendEmailOtpInline()"
                                   class="shrink-0 bg-brand-green text-white px-3 py-2 rounded-xl text-xs font-black hover:bg-green-700 transition whitespace-nowrap">
                                   <i class="fa-solid fa-paper-plane mr-1"></i>Send OTP
                               </button>
                               <?php else: ?>
                               <button type="button"
                                   onclick="sendEmailOtpInline()"
                                   class="shrink-0 bg-gray-100 text-gray-500 px-3 py-2 rounded-xl text-xs font-bold hover:bg-gray-200 transition whitespace-nowrap">
                                   <i class="fa-solid fa-rotate-right mr-1"></i>Re-verify
                               </button>
                               <?php endif; ?>
                           </div>
                           <!-- OTP Row: hidden initially, shown after Send OTP -->
                           <div id="emailOtpRow" class="hidden mt-2 flex gap-2 items-center">
                               <input type="text" id="inlineEmailOtp" maxlength="6"
                                   class="field flex-1 text-center tracking-widest text-lg font-black"
                                   placeholder="Enter 6-digit OTP">
                               <button type="button" onclick="verifyEmailOtpInline()"
                                   class="shrink-0 bg-brand-blue text-white px-4 py-2 rounded-xl text-xs font-black hover:bg-blue-900 transition">
                                   <i class="fa-solid fa-check mr-1"></i>Verify
                               </button>
                           </div>
                           <p id="emailOtpMsg" class="text-xs mt-1 font-semibold hidden"></p>
                       </div>
                      <div class="field-wrap">
                          <label class="flex justify-between">Official Email <?= $isOfficialEmailVerified ? '<i class="fa-solid fa-check text-green-500"></i>' : '' ?></label>
                          <input type="email" class="field" value="<?= $officialEmail ?>" readonly>
                      </div>
                      <div class="field-wrap"><label>Mobile Number</label><input type="text" class="field bg-gray-100 text-gray-500" value="<?= $mobileNumber ?>" readonly></div>
                      <div class="field-wrap"><label>Alternate Phone</label><input type="text" name="AlternatePhone" class="field" value="<?= $altPhone ?>"></div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Accordion: Work & Address -->
      <div class="card mb-4 shadow-sm border-gray-200">
          <div class="accordion-header p-5 flex justify-between items-center" onclick="toggleAcc('acc-work')">
              <div class="flex items-center gap-3">
                  <div class="bg-orange-100 text-orange-600 w-10 h-10 flex items-center justify-center rounded-full"><i class="fa-solid fa-building-user"></i></div>
                  <span class="font-black text-brand-blue text-[15px]">Work & Address</span>
              </div>
              <i class="fa-solid fa-chevron-down text-gray-400"></i>
          </div>
          <div class="accordion-content p-5 pt-0 border-t border-gray-100" id="acc-work">
              <div class="space-y-4 mt-4">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div class="field-wrap"><label>Organisation</label><input type="text" name="CompanyName" class="field" value="<?= $organisation ?>"></div>
                      <div class="field-wrap"><label>Designation</label><input type="text" class="field" value="<?= $designation ?>"></div>
                      <div class="field-wrap sm:col-span-2"><label>Home Address</label><textarea name="HomeAddress" class="field"><?= $homeAddress ?></textarea></div>
                      <div class="field-wrap sm:col-span-2"><label>Office Address</label><textarea name="OfficeAddress" class="field"><?= $officeAddress ?></textarea></div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Accordion: Vehicle Details -->
      <div class="card mb-4 shadow-sm border-gray-200">
          <div class="accordion-header p-5 flex justify-between items-center" onclick="toggleAcc('acc-vehicle')">
              <div class="flex items-center gap-3">
                  <div class="bg-gray-100 text-gray-600 w-10 h-10 flex items-center justify-center rounded-full"><i class="fa-solid fa-car"></i></div>
                  <span class="font-black text-brand-blue text-[15px]">Vehicle Details</span>
              </div>
              <i class="fa-solid fa-chevron-down text-gray-400"></i>
          </div>
          <div class="accordion-content p-5 pt-0 border-t border-gray-100" id="acc-vehicle">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                  <div class="field-wrap"><label>Car Model</label><input type="text" name="CarModel" class="field" value="<?= $carModel ?>" placeholder="e.g. Swift, City"></div>
                  <div class="field-wrap"><label>Car Color</label><input type="text" name="CarColor" class="field" value="<?= $carColor ?>"></div>
                  <div class="field-wrap sm:col-span-2"><label>Registration Number</label><input type="text" name="CarNumber" class="field uppercase" value="<?= $carNumber ?>"></div>
              </div>
          </div>
      </div>

      <!-- Accordion: Social Accounts -->
      <div class="card mb-6 shadow-sm border-gray-200">
          <div class="accordion-header p-5 flex justify-between items-center" onclick="toggleAcc('acc-social')">
              <div class="flex items-center gap-3">
                  <div class="bg-pink-100 text-pink-600 w-10 h-10 flex items-center justify-center rounded-full"><i class="fa-solid fa-hashtag"></i></div>
                  <span class="font-black text-brand-blue text-[15px]">Social Accounts</span>
              </div>
              <i class="fa-solid fa-chevron-down text-gray-400"></i>
          </div>
          <div class="accordion-content p-5 pt-0 border-t border-gray-100" id="acc-social">
              <div class="space-y-4 mt-4">
                  <div class="flex items-center gap-3">
                      <i class="fa-brands fa-linkedin text-blue-700 text-2xl w-6"></i>
                      <input type="text" name="Linkedin" class="field flex-1" value="<?= $linkedin ?>">
                  </div>
                  <div class="flex items-center gap-3">
                      <i class="fa-brands fa-instagram text-pink-500 text-2xl w-6"></i>
                      <input type="text" name="Instagram" class="field flex-1" value="<?= $instagram ?>">
                  </div>
                  <div class="flex items-center gap-3">
                      <i class="fa-brands fa-facebook text-blue-600 text-2xl w-6"></i>
                      <input type="text" name="Facebook" class="field flex-1" value="<?= $facebook ?>">
                  </div>
              </div>
          </div>
      </div>

      <button type="submit" class="w-full bg-brand-blue text-white font-black py-4 rounded-xl hover:bg-blue-900 transition shadow-lg mb-4">Save Profile Changes</button>
      </form>
      
      <a href="login.php?action=logout" class="flex items-center justify-center gap-2 w-full py-4 rounded-xl bg-red-50 text-red-500 font-bold border-2 border-red-100 hover:bg-red-500 hover:text-white transition">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </div>

    <!-- 2. MY RIDES -->
    <div class="panel <?= $tab==='rides'?'active':'' ?> fade-up" id="panel-rides">
      <div class="flex justify-between items-center mb-4">
        <p class="font-black text-brand-blue text-lg">My Rides</p>
        <a href="post-ride.php" class="bg-brand-green text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-green-700 transition">+ Post a Ride</a>
      </div>
      
      <?php if(empty($upcomingRides) && empty($pastRides)): ?>
          <div class="text-center py-10">
              <i class="fa-solid fa-car-side text-gray-300 text-5xl mb-3"></i>
              <p class="text-gray-500 font-bold">No rides found.</p>
          </div>
      <?php endif; ?>

      <?php foreach(['Upcoming' => $upcomingRides, 'Past' => $pastRides] as $label => $rideGrp): ?>
          <?php if(!empty($rideGrp)): ?>
          <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3 mt-6"><?= $label ?> Rides</p>
          <div class="space-y-4">
            <?php foreach($rideGrp as $r): ?>
            <div class="card p-4 hover:border-brand-green transition shadow-sm">
              <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-2">
                    <div class="bg-green-100 text-green-700 p-1.5 rounded-lg"><i class="fa-solid fa-calendar-day text-sm"></i></div>
                    <span class="font-bold text-sm text-brand-blue"><?= formatRideDate($r['ride_Date'] ?? '') ?></span>
                </div>
                <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-black uppercase"><?= $label ?></span>
              </div>
              <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl">
                  <div class="flex flex-col items-center">
                      <div class="w-2 h-2 rounded-full bg-brand-green"></div>
                      <div class="w-0.5 h-6 bg-gray-300 my-1"></div>
                      <div class="w-2 h-2 rounded-full bg-brand-orange"></div>
                  </div>
                  <div class="flex-1 space-y-3">
                      <p class="text-sm font-bold text-gray-800 leading-none"><?= h($r['from_Address'] ?? 'Unknown') ?></p>
                      <p class="text-sm font-bold text-gray-800 leading-none"><?= h($r['to_Address'] ?? 'Unknown') ?></p>
                  </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- 3. RESPONSES (Inbox + My Requests) -->
    <?php
      $inboxOnly = array_values(array_filter($pendingRequests, fn($r) => $r['_type'] === 'inbox'));
      $sentOnly  = array_values(array_filter($pendingRequests, fn($r) => $r['_type'] === 'sent'));
    ?>
    <div class="panel <?= $tab==='responses'?'active':'' ?> fade-up" id="panel-responses">

      <!-- Sub-tab bar -->
      <div class="flex gap-2 mb-5 bg-gray-100 p-1 rounded-2xl">
        <button id="stb-inbox"
          onclick="switchSubTab('inbox')"
          class="sub-tab-btn flex-1 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 active-sub">
          <i class="fa-solid fa-inbox"></i> Inbox
          <?php if(count($inboxOnly) > 0): ?>
          <span class="bg-red-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center"><?= count($inboxOnly) ?></span>
          <?php endif; ?>
        </button>
        <button id="stb-sent"
          onclick="switchSubTab('sent')"
          class="sub-tab-btn flex-1 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-paper-plane"></i> My Requests
          <?php if(count($sentOnly) > 0): ?>
          <span class="bg-blue-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center"><?= count($sentOnly) ?></span>
          <?php endif; ?>
        </button>
      </div>

      <!-- SUB-PANEL: INBOX -->
      <div id="subpanel-inbox">
        <?php if(empty($inboxOnly)): ?>
        <div class="text-center py-14">
          <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-inbox text-brand-orange text-3xl"></i>
          </div>
          <p class="text-gray-700 font-black text-lg">No incoming requests</p>
          <p class="text-gray-400 text-sm mt-1">When someone requests to join your ride, it will appear here.</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
        <?php foreach($inboxOnly as $r):
          $rReqId   = $r['rideRequestID'] ?? $r['id'] ?? 0;
          $rRideId  = $r['rideID']        ?? $r['RideID'] ?? 0;
          $peerName = h($r['SenderName']  ?? $r['name'] ?? 'User');
          $peerPic  = !empty($r['senderPhoto']) ? h($r['senderPhoto']) : 'https://ui-avatars.com/api/?name='.urlencode($peerName).'&background=f3821a&color=fff';
          $rDate    = $r['ride_Date']     ?? $r['rideDate'] ?? '';
          $isAcc    = !empty($r['isaccept']) || strtolower($r['RequestStatus'] ?? '') === 'accepted';
        ?>
        <div class="card shadow-sm border-l-4 <?= $isAcc ? 'border-l-brand-green bg-green-50/20' : 'border-l-brand-orange bg-orange-50/20' ?>">
          <div class="p-4 flex items-center gap-4">
            <img src="<?= $peerPic ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-white shadow-sm"
                 onerror="this.src='https://ui-avatars.com/api/?name=U&background=f3821a&color=fff'">
            <div class="flex-1 min-w-0">
              <p class="font-black text-brand-blue text-base leading-tight"><?= $peerName ?></p>
              <p class="text-[11px] <?= $isAcc ? 'text-green-600' : 'text-orange-600' ?> font-bold mt-0.5">
                <?= $isAcc ? '✅ Accepted your ride' : '⏳ Wants to join your ride' ?>
              </p>
              <?php if($rDate): ?>
              <p class="text-[11px] text-gray-400 font-semibold mt-0.5"><?= formatRideDate($rDate) ?></p>
              <?php endif; ?>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase shrink-0 <?= $isAcc ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' ?>">
              <?= $isAcc ? 'Accepted' : 'Pending' ?>
            </span>
          </div>
          <div class="px-4 py-2 bg-gray-50/60 flex items-start gap-3 border-t border-gray-100">
            <div class="flex flex-col items-center mt-1">
              <div class="w-2 h-2 rounded-full bg-brand-green"></div>
              <div class="w-px h-4 bg-gray-300 my-0.5"></div>
              <div class="w-2 h-2 rounded-full bg-brand-orange"></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-gray-600 truncate"><?= h($r['from_Address'] ?? '—') ?></p>
              <p class="text-xs font-bold text-gray-600 truncate mt-1.5"><?= h($r['to_Address'] ?? '—') ?></p>
            </div>
          </div>
          <?php if(!$isAcc): ?>
          <div class="p-3 flex gap-2 border-t border-gray-100">
            <button onclick="acceptRequest(<?= $rReqId ?>, <?= $rRideId ?>)"
              class="flex-1 bg-brand-green text-white py-2.5 rounded-xl text-xs font-black hover:bg-green-700 transition shadow-sm flex items-center justify-center gap-1.5">
              <i class="fa-solid fa-check"></i> Accept
            </button>
            <button onclick="rejectRequest(<?= $rReqId ?>, <?= $rRideId ?>)"
              class="flex-1 bg-red-50 text-red-600 border border-red-200 py-2.5 rounded-xl text-xs font-black hover:bg-red-500 hover:text-white transition flex items-center justify-center gap-1.5">
              <i class="fa-solid fa-xmark"></i> Decline
            </button>
            <button onclick='openViewModal(<?= json_encode($r) ?>)'
              class="bg-brand-blue text-white px-4 py-2.5 rounded-xl text-xs font-black hover:bg-blue-900 transition shadow-sm">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
          <?php else: ?>
          <div class="p-3 flex justify-end border-t border-gray-100">
            <button onclick='openViewModal(<?= json_encode($r) ?>)'
              class="bg-brand-blue text-white px-4 py-2 rounded-xl text-xs font-black hover:bg-blue-900 transition shadow-sm">
              <i class="fa-solid fa-eye mr-1"></i> View Details
            </button>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div><!-- /subpanel-inbox -->

      <!-- SUB-PANEL: SENT REQUESTS -->
      <div id="subpanel-sent" class="hidden">
        <?php if(empty($sentOnly)): ?>
        <div class="text-center py-14">
          <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-paper-plane text-blue-400 text-3xl"></i>
          </div>
          <p class="text-gray-700 font-black text-lg">No sent requests</p>
          <p class="text-gray-400 text-sm mt-1">Requests you've sent to join rides will appear here.</p>
          <a href="rides.php" class="inline-block mt-4 bg-brand-green text-white text-xs font-black px-5 py-2.5 rounded-full hover:bg-green-700 transition">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Find Rides
          </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
        <?php foreach($sentOnly as $r):
          $rReqId   = $r['rideRequestID'] ?? $r['id'] ?? 0;
          $rRideId  = $r['rideID']        ?? $r['RideID'] ?? 0;
          $peerName = h($r['ReceiverName'] ?? $r['name'] ?? 'Ride Owner');
          $peerPic  = !empty($r['receiverPhoto']) ? h($r['receiverPhoto']) : 'https://ui-avatars.com/api/?name='.urlencode($peerName).'&background=1d3a70&color=fff';
          $rDate    = $r['ride_Date']     ?? $r['rideDate'] ?? '';
          $isAcc    = !empty($r['isaccept']) || strtolower($r['RequestStatus'] ?? '') === 'accepted';
          $isRej    = strtolower($r['RequestStatus'] ?? '') === 'rejected';
        ?>
        <div class="card shadow-sm border-l-4 <?= $isAcc ? 'border-l-brand-green bg-green-50/20' : ($isRej ? 'border-l-red-400 bg-red-50/20' : 'border-l-blue-400 bg-blue-50/20') ?>">
          <div class="p-4 flex items-center gap-4">
            <img src="<?= $peerPic ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-white shadow-sm"
                 onerror="this.src='https://ui-avatars.com/api/?name=U&background=1d3a70&color=fff'">
            <div class="flex-1 min-w-0">
              <p class="font-black text-brand-blue text-base leading-tight"><?= $peerName ?></p>
              <p class="text-[11px] font-bold mt-0.5
                <?= $isAcc ? 'text-green-600' : ($isRej ? 'text-red-500' : 'text-blue-500') ?>">
                <?= $isAcc ? '✅ Request Accepted!' : ($isRej ? '❌ Request Declined' : '⏳ Awaiting response') ?>
              </p>
              <?php if($rDate): ?>
              <p class="text-[11px] text-gray-400 font-semibold mt-0.5"><?= formatRideDate($rDate) ?></p>
              <?php endif; ?>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase shrink-0
              <?= $isAcc ? 'bg-green-100 text-green-700' : ($isRej ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-700') ?>">
              <?= $isAcc ? 'Accepted' : ($isRej ? 'Rejected' : 'Pending') ?>
            </span>
          </div>
          <div class="px-4 py-2 bg-gray-50/60 flex items-start gap-3 border-t border-gray-100">
            <div class="flex flex-col items-center mt-1">
              <div class="w-2 h-2 rounded-full bg-brand-green"></div>
              <div class="w-px h-4 bg-gray-300 my-0.5"></div>
              <div class="w-2 h-2 rounded-full bg-brand-orange"></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-gray-600 truncate"><?= h($r['from_Address'] ?? '—') ?></p>
              <p class="text-xs font-bold text-gray-600 truncate mt-1.5"><?= h($r['to_Address'] ?? '—') ?></p>
            </div>
          </div>
          <div class="p-3 flex justify-between items-center border-t border-gray-100">
            <?php if($isAcc): ?>
            <span class="text-xs font-bold text-green-600"><i class="fa-solid fa-phone mr-1"></i><?= h($r['mobile_no'] ?? '—') ?></span>
            <?php else: ?>
            <span class="text-xs text-gray-400 font-semibold">Contact revealed after acceptance</span>
            <?php endif; ?>
            <button onclick='openViewModal(<?= json_encode($r) ?>)'
              class="bg-brand-blue text-white px-4 py-2 rounded-xl text-xs font-black hover:bg-blue-900 transition shadow-sm">
              <i class="fa-solid fa-eye mr-1"></i> View
            </button>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div><!-- /subpanel-sent -->

    </div><!-- /panel-responses -->

    <!-- 4. CONNECTIONS (Accepted only) -->
    <div class="panel <?= $tab==='connections'?'active':'' ?> fade-up" id="panel-connections">
      <p class="font-black text-brand-blue text-lg mb-4">My Connections</p>
      
      <?php if(empty($acceptedConnections)): ?>
          <div class="text-center py-10">
              <i class="fa-solid fa-people-arrows text-gray-300 text-5xl mb-3"></i>
              <p class="text-gray-500 font-bold">No accepted connections found.</p>
          </div>
      <?php else: ?>
          <div class="space-y-4">
              <?php foreach($acceptedConnections as $r): 
                  $isInbox = $r['_type'] === 'inbox';
                  $peerName = h($r['name'] ?? ($isInbox ? ($r['SenderName'] ?? 'User') : ($r['ReceiverName'] ?? 'User')));
                  $mobile = h($r['mobile_no'] ?? '');
                  $reqEmail = h($r['email'] ?? $r['userEmail'] ?? '');
              ?>
              <div class="card shadow-sm conn-card conn-accepted">
                  <div class="p-4 border-b border-gray-100/50 flex items-center gap-4">
                      <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-xl font-black text-brand-blue shadow-sm border border-gray-100">
                          <?= strtoupper(substr($peerName,0,1)) ?>
                      </div>
                      <div class="flex-1">
                          <p class="font-black text-gray-900 text-lg leading-tight"><?= $peerName ?></p>
                          <p class="text-[11px] text-gray-500 font-bold mt-1">Ride Accepted</p>
                      </div>
                      <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-check mr-1"></i> Connected</span>
                  </div>
                  
                  <div class="p-4 bg-gray-50 flex items-center gap-3">
                      <div class="flex flex-col items-center">
                          <div class="w-1.5 h-1.5 rounded-full bg-brand-green"></div>
                          <div class="w-px h-6 bg-gray-300 my-0.5"></div>
                          <div class="w-1.5 h-1.5 rounded-full bg-brand-orange"></div>
                      </div>
                      <div class="flex-1 space-y-3">
                          <p class="text-xs font-bold text-gray-600 leading-none truncate w-56"><?= h($r['from_Address'] ?? '') ?></p>
                          <p class="text-xs font-bold text-gray-600 leading-none truncate w-56"><?= h($r['to_Address'] ?? '') ?></p>
                      </div>
                  </div>

                  <div class="p-4 flex items-center justify-between border-t border-gray-100/50">
                      <div class="flex flex-col gap-1">
                          <div class="flex items-center gap-2">
                              <i class="fa-solid fa-phone text-gray-400 text-xs"></i>
                              <span class="font-black text-gray-800 text-sm"><?= $mobile ?></span>
                          </div>
                          <?php if(!empty($reqEmail)): ?>
                          <div class="flex items-center gap-2">
                              <i class="fa-solid fa-envelope text-gray-400 text-xs"></i>
                              <span class="font-black text-gray-800 text-xs"><?= $reqEmail ?></span>
                          </div>
                          <?php endif; ?>
                      </div>
                      <button class="bg-brand-blue text-white px-4 py-2 rounded-lg text-xs font-black shadow-sm hover:bg-blue-900 transition" onclick='openViewModal(<?= json_encode($r) ?>)'><i class="fa-solid fa-eye mr-1"></i> View</button>
                  </div>
              </div>
              <?php endforeach; ?>
          </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- View Details Modal -->
<div id="viewModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden fade-up">
        <div class="bg-brand-blue p-5 flex justify-between items-center">
            <h3 class="text-white font-black text-lg">User Details</h3>
            <button onclick="closeViewModal()" class="text-white/60 hover:text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex items-center gap-4 mb-2">
                <div id="modalInitials" class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-black text-brand-blue shadow-sm"></div>
                <div>
                    <p id="modalName" class="font-black text-gray-900 text-xl leading-tight"></p>
                    <p id="modalType" class="text-xs text-brand-green font-bold uppercase"></p>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 space-y-3">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Route Information</p>
                <p id="modalFrom" class="text-xs font-bold text-gray-700"></p>
                <div class="flex items-center gap-2 text-gray-300 text-[10px]">
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
                <p id="modalTo" class="text-xs font-bold text-gray-700"></p>
            </div>

            <div class="space-y-3">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Verification Status</p>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen text-gray-400 text-sm w-4"></i>
                        <span class="text-sm font-bold text-gray-700" id="modalMobile">**********</span>
                    </div>
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-gray-400 text-sm w-4"></i>
                        <span class="text-sm font-bold text-gray-700" id="modalEmail">**********</span>
                    </div>
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-fingerprint text-gray-400 text-sm w-4"></i>
                        <span class="text-sm font-bold text-gray-700">Aadhaar / ID</span>
                    </div>
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100 bg-gray-50">
            <button onclick="closeViewModal()" class="w-full bg-brand-blue text-white font-black py-3 rounded-xl shadow-md hover:bg-blue-900 transition">Close</button>
        </div>
    </div>
</div>

<!-- Aadhaar OTP Modal -->
<div id="aadhaarModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden fade-up p-6">
        <h3 class="text-xl font-black text-brand-blue mb-2">Verify Aadhaar</h3>
        <p class="text-sm text-gray-500 mb-5">Enter your 12-digit Aadhaar number to receive an OTP.</p>
        
        <div id="aadhaarStep1">
            <input type="text" id="aadhaarNumber" placeholder="XXXX XXXX XXXX" class="field mb-4 w-full text-center tracking-widest text-lg" maxlength="12">
            <button type="button" onclick="sendAadhaarOTP()" class="w-full bg-brand-green text-white font-black py-3 rounded-xl hover:bg-green-700 transition">Send OTP</button>
        </div>
        
        <div id="aadhaarStep2" class="hidden">
            <input type="text" id="aadhaarOtpInput" placeholder="Enter OTP" class="field mb-4 w-full text-center tracking-widest text-xl" maxlength="6">
            <button type="button" onclick="verifyAadhaarOTP()" class="w-full bg-brand-blue text-white font-black py-3 rounded-xl hover:bg-blue-900 transition">Verify</button>
        </div>
        
        <button type="button" onclick="closeAadhaarModal()" class="w-full mt-3 text-gray-500 text-sm font-bold hover:text-gray-800">Cancel</button>
    </div>
</div>

<!-- Email OTP Modal -->
<div id="emailModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden fade-up p-6">
        <h3 class="text-xl font-black text-brand-blue mb-2">Verify Email</h3>
        <p class="text-sm text-gray-500 mb-5">We've sent an OTP to <span id="verifyEmailText" class="font-bold text-gray-800"></span></p>
        
        <input type="text" id="emailOtpInput" placeholder="Enter OTP" class="field mb-4 w-full text-center tracking-widest text-xl" maxlength="6">
        <button type="button" onclick="verifyEmailOTP()" class="w-full bg-brand-blue text-white font-black py-3 rounded-xl hover:bg-blue-900 transition">Verify Email</button>
        
        <button type="button" onclick="closeEmailModal()" class="w-full mt-3 text-gray-500 text-sm font-bold hover:text-gray-800">Cancel</button>
    </div>
</div>

<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-xl transition-all duration-300 opacity-0 pointer-events-none translate-y-4 z-50"></div>

<?php if($toast): ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        toast('<?= $toast['type'] ?>', '<?= addslashes($toast['msg']) ?>');
    });
</script>
<?php endif; ?>

<script>
function switchTab(name) {
    ['rides','responses','connections','profile'].forEach(t=>{
        document.getElementById('panel-'+t)?.classList.toggle('active', t===name);
        document.getElementById('tb-'+t)?.classList.toggle('active', t===name);
    });
    history.replaceState(null,'','profile.php?tab='+name);
}

// Sub-tab switcher inside Responses panel
function switchSubTab(name) {
    ['inbox','sent'].forEach(s => {
        document.getElementById('subpanel-'+s)?.classList.toggle('hidden', s !== name);
        document.getElementById('stb-'+s)?.classList.toggle('active-sub', s === name);
    });
}

// Accept ride request
async function acceptRequest(reqId, rideId) {
    if(!confirm('Accept this ride request?')) return;
    try {
        const res = await fetch('https://api.greencar.ngo/api/App/AcceptRideRequest', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ rideRequestID: reqId, rideID: rideId, userId: <?= $userId ?> })
        });
        const d = await res.json();
        toast('success','Request Accepted! ✅');
        setTimeout(() => location.reload(), 1200);
    } catch(e){ toast('error','Failed to accept'); }
}

// Reject ride request
async function rejectRequest(reqId, rideId) {
    if(!confirm('Decline this request?')) return;
    try {
        const res = await fetch('https://api.greencar.ngo/api/App/RejectRideRequest', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ rideRequestID: reqId, rideID: rideId, userId: <?= $userId ?> })
        });
        toast('success','Request Declined');
        setTimeout(() => location.reload(), 1200);
    } catch(e){ toast('error','Failed to decline'); }
}

function toggleAcc(id) {
    const el = document.getElementById(id);
    const icon = el.previousElementSibling.querySelector('.fa-chevron-down');
    el.classList.toggle('open');
    if(el.classList.contains('open')){
        icon.style.transform = 'rotate(180deg)';
    }else{
        icon.style.transform = 'rotate(0deg)';
    }
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

function openViewModal(data) {
    const isAcc = data.isaccept || (data.RequestStatus && data.RequestStatus.toLowerCase() === 'accepted');
    const isInbox = data._type === 'inbox';
    const name = data.name || (isInbox ? data.SenderName : data.ReceiverName) || 'User';
    
    document.getElementById('modalInitials').textContent = name.substring(0, 1).toUpperCase();
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalType').textContent = isAcc ? 'Connected User' : (isInbox ? 'Incoming Request' : 'Sent Request');
    
    document.getElementById('modalFrom').textContent = data.from_Address || 'N/A';
    document.getElementById('modalTo').textContent = data.to_Address || 'N/A';
    
    // Privacy masked contact
    const mobile = data.mobile_no || 'N/A';
    const email = data.email || data.userEmail || 'N/A';
    
    if (isAcc) {
        document.getElementById('modalMobile').textContent = mobile;
        document.getElementById('modalEmail').textContent = email;
    } else {
        document.getElementById('modalMobile').textContent = '**********';
        document.getElementById('modalEmail').textContent = '**********';
    }
    
    document.getElementById('viewModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

// PROFILE PICTURE UPLOAD
// API: POST https://api.greencar.ngo/api/App/UploadFile
// Response: { status: 1, url: "https://appdoc.blob.core.windows.net/greencar/..." }
async function uploadProfilePic(input) {
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];

    // Size guard: max 5 MB
    if (file.size > 5 * 1024 * 1024) {
        toast('error', 'Image must be under 5 MB'); return;
    }

    // Show instant preview via FileReader (works offline too)
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('profilePicImg');
        img.src = e.target.result;
        img.classList.remove('hidden');
        document.getElementById('profilePicPlaceholder')?.classList.add('hidden');
    };
    reader.readAsDataURL(file);

    toast('success', '⏳ Uploading...');

    try {
        const formData = new FormData();
        formData.append('file', file);   // API reads Form.Files[0] — any key works

        const res  = await fetch('https://api.greencar.ngo/api/App/UploadFile', {
            method: 'POST',
            body: formData
            // NOTE: DO NOT set Content-Type header — browser sets multipart/form-data boundary automatically
        });

        // API returns JSON: { "status": 1, "url": "https://appdoc.blob..." }
        const data = await res.json();

        if (data.status === 1 && data.url) {
            // Set in hidden field so Save Profile submits it
            document.getElementById('hiddenProfileImgUrl').value = data.url;
            // Update preview with real CDN URL
            document.getElementById('profilePicImg').src = data.url;
            toast('success', '✅ Image uploaded! Click Save Profile.');
        } else {
            toast('error', '❌ Upload failed: ' + (data.message || 'Unknown error'));
        }
    } catch(e) {
        // Network error — preview already shown via FileReader
        toast('error', '❌ Network error during upload. Try again.');
        console.error('Upload error:', e);
    }
}

// AADHAAR OTP FLOW
function openAadhaarVerifyModal() { document.getElementById('aadhaarModal').classList.remove('hidden'); }
function closeAadhaarModal() { document.getElementById('aadhaarModal').classList.add('hidden'); }

async function sendAadhaarOTP() {
    const aadhar = document.getElementById('aadhaarNumber').value.trim();
    if(aadhar.length < 12) { toast('error', 'Enter a valid 12-digit Aadhaar'); return; }
    
    toast('success', 'Sending OTP...');
    const formData = new URLSearchParams();
    formData.append('action', 'send_aadhar_otp');
    formData.append('aadharNumber', aadhar);
    
    try {
        const res = await fetch('profile.php', { method: 'POST', body: formData });
        const json = await res.json();
        // Assuming success
        document.getElementById('aadhaarStep1').classList.add('hidden');
        document.getElementById('aadhaarStep2').classList.remove('hidden');
        toast('success', 'OTP Sent to linked mobile number');
    } catch(e) { toast('error', 'Failed to send OTP'); }
}

async function verifyAadhaarOTP() {
    const aadhar = document.getElementById('aadhaarNumber').value.trim();
    const otp = document.getElementById('aadhaarOtpInput').value.trim();
    if(otp.length < 6) { toast('error', 'Enter valid 6-digit OTP'); return; }
    
    const formData = new URLSearchParams();
    formData.append('action', 'verify_aadhar_otp');
    formData.append('aadharNumber', aadhar);
    formData.append('otp', otp);
    
    try {
        const res = await fetch('profile.php', { method: 'POST', body: formData });
        const json = await res.json();
        toast('success', 'Aadhaar Verified Successfully!');
        closeAadhaarModal();
        setTimeout(() => window.location.reload(), 1500);
    } catch(e) { toast('error', 'Verification Failed'); }
}

// ── EMAIL OTP FLOW (Inline — real API) ──────────────────────────────────────

// Step 1: User clicks "Send OTP" button next to email field
async function sendEmailOtpInline() {
    const email = document.getElementById('profileEmail').value.trim();
    if (!email || !email.includes('@')) {
        toast('error', 'Please enter a valid email address first'); return;
    }
    const btn = document.getElementById('btnSendEmailOtp');
    if(btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Sending...'; }

    const fd = new URLSearchParams();
    fd.append('action', 'send_email_otp');
    fd.append('email', email);
    try {
        const res  = await fetch('profile.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.requestid || data.status === 'ok') {
            // Show OTP input row
            document.getElementById('emailOtpRow').classList.remove('hidden');
            document.getElementById('inlineEmailOtp').focus();
            showEmailMsg('OTP sent to ' + email + '. Check your inbox.', 'green');
            toast('success', '✉️ OTP sent to ' + email);
        } else {
            showEmailMsg('Failed to send OTP. Try again.', 'red');
            toast('error', 'Failed to send OTP');
        }
    } catch(e) {
        showEmailMsg('Network error. Try again.', 'red');
        toast('error', 'Network error');
    } finally {
        if(btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i>Send OTP'; }
    }
}

// Step 2: User enters OTP and clicks "Verify"
async function verifyEmailOtpInline() {
    const otp = document.getElementById('inlineEmailOtp').value.trim();
    if (otp.length < 6) { showEmailMsg('Enter the 6-digit OTP', 'red'); return; }

    const fd = new URLSearchParams();
    fd.append('action', 'verify_email_otp');
    fd.append('otp', otp);
    try {
        const res  = await fetch('profile.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showEmailMsg('✅ ' + data.message, 'green');
            toast('success', '✅ Email Verified!');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showEmailMsg('❌ ' + (data.message || 'Wrong OTP'), 'red');
            toast('error', data.message || 'Wrong OTP');
        }
    } catch(e) {
        showEmailMsg('Network error. Try again.', 'red'); 
    }
}

function showEmailMsg(msg, color) {
    const el = document.getElementById('emailOtpMsg');
    if (!el) return;
    el.textContent = msg;
    el.className = `text-xs mt-1 font-semibold text-${color}-600`;
    el.classList.remove('hidden');
}

// Legacy modal functions (kept for badge-click compatibility)
function openEmailVerifyModal() { sendEmailOtpInline(); }
function closeEmailModal() { document.getElementById('emailModal')?.classList.add('hidden'); }
async function verifyEmailOTP() { verifyEmailOtpInline(); }

document.addEventListener("DOMContentLoaded", () => {
    // Open Personal Info by default
    document.getElementById('acc-personal').classList.add('open');
    document.getElementById('acc-personal').previousElementSibling.querySelector('.fa-chevron-down').style.transform = 'rotate(180deg)';
    
    // Auto switch to tab from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if(tabParam) {
        switchTab(tabParam);
    }
});
</script>
</body>
</html>
