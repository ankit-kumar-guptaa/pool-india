<?php
// header.php — Shared navigation for all Pool India pages
// Assumes config.php already required (session started, helpers available)

$_hIsLoggedIn = isLoggedIn();
$_hUser = currentUser();
$_hName = h($_hUser['name'] ?? 'User');
$_hInitial = strtoupper(substr($_hUser['name'] ?? 'U', 0, 1));
$_hPhone = h($_hUser['mobile_No'] ?? '');
$_hPage = basename($_SERVER['PHP_SELF'] ?? '');

// Active link helper
function _navActive(string $page, string $current): string
{
  return $page === $current ? 'text-brand-green font-black' : 'text-gray-600 hover:text-brand-green font-semibold';
}
?>
<style>
  .glass-nav {
    background: rgba(255, 255, 255, .95);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(0, 0, 0, .06);
  }

  /* User dropdown */
  .user-dd-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 10px);
    background: #fff;
    border-radius: 1.2rem;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 20px 50px rgba(29, 58, 112, .15);
    min-width: 230px;
    z-index: 200;
    transform: translateY(-8px) scale(.97);
    opacity: 0;
    pointer-events: none;
    transition: all .25s cubic-bezier(.4, 0, .2, 1);
  }

  .user-dd-menu.open {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
  }

  .dd-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 11px 16px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    color: #475569;
    cursor: pointer;
    transition: all .18s;
    text-decoration: none;
  }

  .dd-item:hover {
    background: #f0fdf4;
    color: #1b8036;
  }

  .dd-item.danger:hover {
    background: #fff5f5;
    color: #ef4444;
  }

  .dd-item i {
    width: 18px;
    text-align: center;
    font-size: 13px;
  }

  .dd-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 5px 8px;
  }

  .user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1b8036, #22c55e);
    color: #fff;
    font-weight: 900;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #bbf7d0;
  }

  .ham-line {
    display: block;
    width: 22px;
    height: 2.5px;
    background: #1d3a70;
    border-radius: 2px;
    transition: all .3s;
  }

  /* Mobile nav */
  #mobile-nav {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9998;
    flex-direction: column;
  }

  #mobile-nav.open {
    display: flex;
  }

  .mobile-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    backdrop-filter: blur(3px);
  }

  .mobile-sheet {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(320px, 90vw);
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transform: translateX(100%);
    transition: transform .35s cubic-bezier(.4, 0, .2, 1);
  }

  .mobile-sheet.open {
    transform: translateX(0);
  }

  .mob-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 20px;
    font-weight: 700;
    font-size: 15px;
    color: #1d3a70;
    text-decoration: none;
    transition: all .2s;
    border-radius: 12px;
    margin: 2px 8px;
  }

  .mob-link:hover,
  .mob-link.active-mob {
    background: #f0fdf4;
    color: #1b8036;
  }

  .mob-link i {
    width: 20px;
    text-align: center;
    color: #94a3b8;
  }

  .mob-link.active-mob i {
    color: #1b8036;
  }
</style>

<!-- ===== NAVBAR ===== -->
<nav class="glass-nav fixed w-full z-50 transition-all duration-300 py-1.5 shadow-sm" id="main-nav">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 md:h-17 items-center">

      <!-- Logo -->
      <a href="index.php" class="flex-shrink-0 flex items-center gap-2 group">
        <!-- <div class="w-20 h-12 md:w-14 md:h-14 flex items-center justify-center">
          <img src="images/logo.png" alt="Pool India" class="w-full h-full object-contain">
        </div> -->
        <img src="images/logo.png" alt="Pool India" width="180">
        <!-- <span class="font-black text-brand-blue text-lg hidden sm:block">POOL <span class="text-brand-green">India</span></span> -->
      </a>

      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center gap-1 lg:gap-2">
        <a href="rides.php" class="px-3 py-2 rounded-xl transition <?= _navActive('rides.php', $_hPage) ?>">
          <i class="fa-solid fa-magnifying-glass mr-1 text-xs"></i>Find Ride
        </a>
        <a href="post-ride.php" class="px-3 py-2 rounded-xl transition <?= _navActive('post-ride.php', $_hPage) ?>">
          <i class="fa-solid fa-plus mr-1 text-xs text-brand-orange"></i>Post Ride
        </a>
        <a href="index.php#services"
          class="px-3 py-2 rounded-xl text-gray-600 hover:text-brand-green font-semibold transition">Services</a>
        <a href="index.php#mission"
          class="px-3 py-2 rounded-xl text-gray-600 hover:text-brand-green font-semibold transition">Viksit Bharat</a>
        <a href="index.php#contact"
          class="px-3 py-2 rounded-xl text-gray-600 hover:text-brand-green font-semibold transition">Support</a>

        <div class="w-px h-6 bg-gray-200 mx-1"></div>

        <?php if ($_hIsLoggedIn): ?>
          <!-- User Dropdown -->
          <div class="relative" id="user-dd-wrap">
            <button id="user-dd-btn" onclick="toggleDd(event)"
              class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-2xl border-2 border-gray-100 hover:border-brand-green transition-all bg-white">
              <div class="user-avatar"><?= $_hInitial ?></div>
              <div class="text-left hidden lg:block">
                <p class="text-xs text-gray-400 font-semibold leading-none">Welcome back</p>
                <p class="text-sm text-brand-blue font-black leading-tight"><?= $_hName ?></p>
              </div>
              <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-1 transition-transform" id="dd-chevron"></i>
            </button>

            <!-- Dropdown -->
            <div class="user-dd-menu" id="user-dd-menu">
              <!-- User Info Header -->
              <div class="px-4 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                  <div class="user-avatar w-11 h-11 rounded-xl text-lg"><?= $_hInitial ?></div>
                  <div>
                    <p class="font-black text-brand-blue text-sm"><?= $_hName ?></p>
                    <?php if ($_hPhone): ?>
                      <p class="text-xs text-gray-400 font-semibold">+91 <?= $_hPhone ?></p>
                    <?php endif; ?>
                    <span
                      class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700">✓
                      Verified</span>
                  </div>
                </div>
              </div>

              <div class="p-2">
                <a href="profile.php"
                  class="dd-item <?= $_hPage === 'profile.php' ? 'bg-green-50 text-brand-green' : '' ?>">
                  <i class="fa-solid fa-user"></i> My Profile
                </a>
                <a href="profile.php?tab=rides" class="dd-item">
                  <i class="fa-solid fa-car-side"></i> My Rides
                </a>
                <a href="profile.php?tab=connections" class="dd-item">
                  <i class="fa-solid fa-handshake"></i> My Connections
                </a>
                <a href="profile.php?tab=responses" class="dd-item">
                  <i class="fa-solid fa-reply"></i> Responses
                  <span
                    class="ml-auto bg-brand-orange text-white text-[10px] font-black px-2 py-0.5 rounded-full">2</span>
                </a>
                <div class="dd-divider"></div>
                <a href="post-ride.php?mode=carpool" class="dd-item">
                  <i class="fa-solid fa-plus text-brand-green"></i> Post Carpool Ride
                </a>
                <a href="post-ride.php?mode=bike" class="dd-item">
                  <i class="fa-solid fa-motorcycle text-brand-orange"></i> Post Bike Ride
                </a>
                <div class="dd-divider"></div>
                <a href="login.php?action=logout" class="dd-item danger">
                  <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Not logged in -->
          <a href="login.php"
            class="flex items-center gap-2 border-2 border-brand-blue text-brand-blue px-4 py-2 rounded-full font-bold hover:bg-brand-blue hover:text-white transition-all">
            <i class="fa-solid fa-user text-sm"></i> Login
          </a>
        <?php endif; ?>

        <a href="index.php#download"
          class="bg-brand-blue text-white px-5 py-2.5 rounded-full font-bold hover:bg-brand-orange transition-all shadow-md ml-1 text-sm">
          <i class="fa-brands fa-google-play mr-1"></i>Get App
        </a>
      </div>

      <!-- Mobile: Right actions -->
      <div class="flex md:hidden items-center gap-2">
        <?php if ($_hIsLoggedIn): ?>
          <a href="profile.php" class="user-avatar text-base"><?= $_hInitial ?></a>
        <?php else: ?>
          <a href="login.php"
            class="text-brand-blue font-bold text-sm border-2 border-brand-blue px-3 py-1.5 rounded-full">Login</a>
        <?php endif; ?>
        <button onclick="toggleMob()" id="ham-btn"
          class="flex flex-col gap-1.5 justify-center items-center w-10 h-10 rounded-xl bg-gray-100 hover:bg-brand-blue/10 transition"
          aria-label="Menu">
          <span class="ham-line"></span>
          <span class="ham-line" style="width:16px"></span>
          <span class="ham-line"></span>
        </button>
      </div>

    </div>
  </div>
</nav>

<!-- ===== MOBILE NAV ===== -->
<div id="mobile-nav">
  <div class="mobile-overlay" onclick="toggleMob()"></div>
  <div class="mobile-sheet" id="mob-sheet">

    <!-- Sheet Header -->
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
      <div class="flex items-center gap-3">
        <img src="images/logo.png" class="w-10 h-10 object-contain" alt="">
        <span class="font-black text-brand-blue">POOL <span class="text-brand-green">India</span></span>
      </div>
      <button onclick="toggleMob()"
        class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <?php if ($_hIsLoggedIn): ?>
      <!-- User Badge -->
      <div class="mx-4 mt-4 p-4 rounded-2xl" style="background:linear-gradient(135deg,#1d3a70,#0d2252)">
        <div class="flex items-center gap-3">
          <div class="user-avatar w-12 h-12 rounded-xl text-xl"><?= $_hInitial ?></div>
          <div>
            <p class="font-black text-white"><?= $_hName ?></p>
            <?php if ($_hPhone): ?>
              <p class="text-blue-300 text-xs font-semibold">+91 <?= $_hPhone ?></p><?php endif; ?>
          </div>
          <span class="ml-auto text-[10px] bg-green-400/20 text-green-300 font-black px-2 py-1 rounded-full">✓
            Verified</span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Nav Links -->
    <div class="flex-1 py-3 px-2">
      <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-3 py-2">Browse</p>
      <a href="index.php" class="mob-link <?= $_hPage === 'index.php' ? 'active-mob' : '' ?>"><i
          class="fa-solid fa-house"></i> Home</a>
      <a href="rides.php" class="mob-link <?= $_hPage === 'rides.php' ? 'active-mob' : '' ?>"><i
          class="fa-solid fa-magnifying-glass"></i> Find a Ride</a>
      <a href="post-ride.php" class="mob-link <?= $_hPage === 'post-ride.php' ? 'active-mob' : '' ?>"><i
          class="fa-solid fa-plus"></i> Post a Ride</a>
      <a href="index.php#mission" class="mob-link"><i class="fa-solid fa-flag"></i> Viksit Bharat</a>
      <a href="index.php#contact" class="mob-link"><i class="fa-solid fa-headset"></i> Support</a>

      <?php if ($_hIsLoggedIn): ?>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-3 py-2 mt-2">My Account</p>
        <a href="profile.php" class="mob-link <?= $_hPage === 'profile.php' ? 'active-mob' : '' ?>"><i
            class="fa-solid fa-user"></i> My Profile</a>
        <a href="profile.php?tab=rides" class="mob-link"><i class="fa-solid fa-car-side"></i> My Rides</a>
        <a href="profile.php?tab=connections" class="mob-link"><i class="fa-solid fa-handshake"></i> My Connections</a>
        <a href="profile.php?tab=responses" class="mob-link">
          <i class="fa-solid fa-reply"></i> Responses
          <span class="ml-auto bg-brand-orange text-white text-[10px] font-black px-2 py-0.5 rounded-full">2</span>
        </a>
        <div class="my-2 mx-3 h-px bg-gray-100"></div>
        <a href="login.php?action=logout" class="mob-link" style="color:#ef4444;">
          <i class="fa-solid fa-right-from-bracket" style="color:#ef4444;"></i> Logout
        </a>
      <?php else: ?>
        <div class="px-3 pt-3">
          <a href="login.php"
            class="flex items-center justify-center gap-2 w-full py-3 rounded-2xl bg-brand-blue text-white font-black text-base">
            <i class="fa-solid fa-user"></i> Login to Pool India
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- App Download -->
    <div class="p-4 border-t border-gray-100">
      <p class="text-xs font-bold text-gray-400 text-center mb-3">Download the App</p>
      <div class="flex gap-2">
        <a href="#"
          class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl bg-brand-blue text-white text-xs font-bold">
          <i class="fa-brands fa-google-play text-brand-green"></i> Play Store
        </a>
        <a href="#"
          class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl bg-brand-blue text-white text-xs font-bold">
          <i class="fa-brands fa-apple"></i> App Store
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  // ── Dropdown ──────────────────────────────────────────────────────────────────
  function toggleDd(e) {
    e && e.stopPropagation();
    const menu = document.getElementById('user-dd-menu');
    const chev = document.getElementById('dd-chevron');
    menu.classList.toggle('open');
    chev.style.transform = menu.classList.contains('open') ? 'rotate(180deg)' : '';
  }
  document.addEventListener('click', () => {
    const menu = document.getElementById('user-dd-menu');
    const chev = document.getElementById('dd-chevron');
    if (menu) { menu.classList.remove('open'); if (chev) chev.style.transform = ''; }
  });

  // ── Mobile Menu ──────────────────────────────────────────────────────────────
  function toggleMob() {
    const nav = document.getElementById('mobile-nav');
    const sheet = document.getElementById('mob-sheet');
    const isOpen = nav.classList.contains('open');
    if (isOpen) {
      sheet.classList.remove('open');
      setTimeout(() => { nav.classList.remove('open'); document.body.style.overflow = ''; }, 320);
    } else {
      nav.classList.add('open');
      document.body.style.overflow = 'hidden';
      setTimeout(() => sheet.classList.add('open'), 10);
    }
  }

  // ── Scroll shrink ─────────────────────────────────────────────────────────────
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('main-nav');
    if (!nav) return;
    if (window.scrollY > 40) {
      nav.style.boxShadow = '0 4px 24px rgba(29,58,112,.10)';
    } else {
      nav.style.boxShadow = '';
    }
  });
</script>