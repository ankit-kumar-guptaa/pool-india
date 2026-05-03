<?php require_once __DIR__ . '/config.php';
$isLoggedIn = isLoggedIn();
$userName = h(currentUser()['name'] ?? ''); ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pool India | Premium Ride Sharing & Booking</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts (Plus Jakarta Sans) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Tailwind Config - Matched Exactly with Uploaded Logo -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            green: '#1b8036',  /* Logo Green - 'India' */
                            blue: '#1d3a70',   /* Logo Blue - 'POOL' */
                            orange: '#f3821a', /* Logo Orange - Ring */
                            light: '#f8fafc',  /* Soft Slate Light Background */
                            white: '#ffffff',
                        }
                    },
                    boxShadow: {
                        'soft': '0 20px 40px -15px rgba(0,0,0,0.05)',
                        'glow-green': '0 0 40px rgba(27, 128, 54, 0.2)',
                        'glow-blue': '0 0 40px rgba(29, 58, 112, 0.2)',
                        'float': '0 30px 60px rgba(0, 0, 0, 0.1)',
                        'widget': '0 10px 40px -10px rgba(29, 58, 112, 0.15)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f8fafc;
            /* Very light slate */
        }

        /* Light Theme Premium Hero Background */
        .hero-light {
            background: radial-gradient(circle at 80% -20%, rgba(243, 130, 26, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 20% 80%, rgba(27, 128, 54, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(29, 58, 112, 0.05) 0%, transparent 60%),
                #ffffff;
            position: relative;
        }

        .hero-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(29, 58, 112, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(29, 58, 112, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
        }

        /* Phone Mockup Frame */
        .phone-mockup {
            border: 10px solid #f1f5f9;
            border-radius: 40px;
            overflow: hidden;
            position: relative;
            background-color: #fff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), inset 0 0 0 2px #e2e8f0;
            display: inline-block;
        }

        .phone-mockup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 24px;
            background-color: #f1f5f9;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            z-index: 20;
            border: 2px solid #e2e8f0;
            border-top: none;
        }

        /* Floating Animations */
        @keyframes float-smooth {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(1deg);
            }
        }

        .anim-float {
            animation: float-smooth 6s ease-in-out infinite;
        }

        /* Light Glassmorphism */
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Dynamic Image Transition */
        .dynamic-img {
            transition: opacity 0.5s ease-in-out, transform 0.8s ease-out;
        }

        .img-hide {
            opacity: 0;
            transform: scale(0.95);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #1b8036;
            border-radius: 10px;
        }

        /* Animated hero headline */
        @keyframes heroTextIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes heroTextOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .hero-text-in {
            animation: heroTextIn 0.55s cubic-bezier(.4, 0, .2, 1) both;
        }

        .hero-text-out {
            animation: heroTextOut 0.45s cubic-bezier(.4, 0, .2, 1) both;
        }

        /* Viksit Bharat stats */
        .vb-stat {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.2rem;
            padding: 20px 16px;
            text-align: center;
        }

        .vb-card {
            background: #fff;
            border-radius: 1.5rem;
            padding: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: scale(.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .count-anim {
            animation: countUp 0.6s ease both;
        }

        /* ===== MOBILE RESPONSIVE OVERRIDES ===== */
        @media (max-width: 639px) {
            /* Hero */
            .hero-light { min-height: auto; padding-top: 80px; padding-bottom: 30px; }
            .hero-light h1 { font-size: 1.75rem !important; line-height: 1.2; }
            .hero-light #hero-sub { font-size: 14px; margin-bottom: 20px; }

            /* Hero widget */
            .hero-light .bg-white.rounded-\[2rem\] { padding: 16px; border-radius: 1.2rem; }
            #ride-tabs { gap: 4px; padding: 6px; }
            #ride-tabs button { font-size: 11px; padding: 8px 4px; min-width: 60px; gap: 2px; }
            #ride-tabs button i { font-size: 14px; }

            /* Hero image / right visual */
            .hero-light .lg\:w-\[55\%\] { display: none; }

            /* Stats bar */
            .max-w-6xl.-mt-10 { margin-top: -20px; }
            .max-w-6xl.-mt-10 .bg-white { padding: 16px 12px; border-radius: 1.2rem; gap: 12px; }
            .max-w-6xl.-mt-10 .text-4xl { font-size: 1.4rem; }
            .max-w-6xl.-mt-10 .text-xs { font-size: 9px; letter-spacing: 0.05em; }

            /* Services section */
            #services { padding: 40px 0; }
            #services h2 { font-size: 1.6rem; }
            #services p.text-lg { font-size: 14px; }
            #services .rounded-\[2\.5rem\] { border-radius: 1.2rem; padding: 20px; }
            #services h3 { font-size: 1.3rem; }
            #services .phone-mockup { width: 140px !important; height: 300px !important; border-width: 6px; border-radius: 24px; }
            #services .phone-mockup::before { width: 60px; height: 16px; }

            /* Viksit Bharat */
            #mission { padding: 40px 0; }
            #mission h2 { font-size: 1.6rem; }
            #mission .vb-stat { padding: 12px 8px; border-radius: 0.8rem; }
            #mission .vb-stat .text-3xl { font-size: 1.2rem; }
            #mission .vb-stat .text-xs { font-size: 9px; }
            #mission .vb-card { padding: 16px; border-radius: 1rem; }
            #mission .vb-card h3 { font-size: 1rem; }

            /* Download section */
            #download { padding: 40px 0; }
            #download .bg-brand-light { padding: 20px; border-radius: 1.5rem; }
            #download h2 { font-size: 1.5rem; }
            #download .h-\[500px\] { height: 320px; }
            #download .phone-mockup { border-width: 6px; border-radius: 24px; }
            #download .phone-mockup:first-child { width: 150px !important; height: 310px !important; right: 0 !important; }
            #download .phone-mockup:last-child,
            #download .phone-mockup:nth-child(2) { width: 160px !important; height: 330px !important; left: 0 !important; }

            /* Contact section */
            #contact { padding: 40px 0; }
            #contact h2 { font-size: 1.5rem; }
            #contact form { padding: 20px; border-radius: 1.2rem; }

            /* Footer */
            footer { padding: 40px 0; }
            footer .text-3xl { font-size: 1.3rem; }
            footer .w-20.h-20 { width: 50px; height: 50px; }
            footer .grid { gap: 24px; }
            footer h4 { margin-bottom: 12px; }
        }

        @media (min-width: 640px) and (max-width: 1023px) {
            .hero-light { min-height: auto; padding-top: 100px; }
            .hero-light h1 { font-size: 2.2rem !important; }
            #services .phone-mockup { width: 160px !important; height: 340px !important; }
            #download .h-\[500px\] { height: 380px; }
        }
    </style>
</head>

<body class="text-gray-800 antialiased overflow-x-hidden">

    <!-- ========== NAVBAR ========== -->
    <?php require __DIR__ . '/header.php'; ?>

    <!-- ========== DYNAMIC HERO SECTION (INTERACTIVE WIDGET) ========== -->
    <section id="home" class="hero-light min-h-[100vh] flex items-center relative pt-32 pb-12 overflow-hidden">
        <div class="hero-grid"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full mt-4">
            <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-20">

                <!-- Left Content: Dynamic Booking Widget -->
                <div class="lg:w-[45%] w-full" data-aos="fade-right" data-aos-duration="1000">
                    <div id="hero-headline" class="mb-4">
                        <h1 class="text-4xl lg:text-5xl font-black leading-tight tracking-tight text-brand-blue">
                            <span id="hero-line1">Your Commute,</span><br>
                            <span class="text-brand-green" id="hero-line2">Your Choice.</span>
                        </h1>
                    </div>
                    <p class="text-gray-600 mb-8 font-medium" id="hero-sub">Search for daily rides, intercity cabs, or
                        shuttles. Verified peers, cheaper fares.</p>

                    <!-- Interactive Widget Box -->
                    <div class="bg-white rounded-[2rem] shadow-widget p-6 border border-gray-100">
                        <!-- Dynamic Tabs -->
                        <div class="flex flex-wrap gap-2 p-1.5 bg-gray-50 rounded-2xl mb-6 border border-gray-100"
                            id="ride-tabs">
                            <button onclick="switchMode('carpool')" id="tab-carpool"
                                class="flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm bg-white shadow-sm text-brand-blue transition-all flex flex-col items-center gap-1">
                                <i class="fa-solid fa-car-side text-lg"></i> Carpool
                            </button>
                            <button onclick="switchMode('bike')" id="tab-bike"
                                class="flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm text-gray-500 hover:text-brand-blue transition-all flex flex-col items-center gap-1">
                                <i class="fa-solid fa-motorcycle text-lg"></i> Bike
                            </button>
                            <button onclick="switchMode('cab')" id="tab-cab"
                                class="flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm text-gray-500 hover:text-brand-blue transition-all flex flex-col items-center gap-1">
                                <i class="fa-solid fa-taxi text-lg"></i> CabShare
                            </button>
                            <button onclick="switchMode('shuttle')" id="tab-shuttle"
                                class="flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm text-gray-500 hover:text-brand-blue transition-all flex flex-col items-center gap-1">
                                <i class="fa-solid fa-van-shuttle text-lg"></i> Shuttle
                            </button>
                        </div>

                        <!-- Form Area -->
                        <form id="search-form" onsubmit="handleSearch(event)" class="space-y-3 relative" autocomplete="off">

                            <!-- Vertical route line -->
                            <div class="absolute left-[19px] top-[52px] h-[54px] w-0.5 z-0 hidden sm:block" style="background:linear-gradient(#1b8036,#f3821a)"></div>

                            <!-- FROM -->
                            <div class="relative z-10 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-brand-green" style="background:#dcfce7;">
                                    <i class="fa-solid fa-circle-dot text-xs"></i>
                                </div>
                                <div class="flex-1 relative bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 focus-within:ring-2 focus-within:ring-brand-green/40 focus-within:border-brand-green transition-all">
                                    <label class="block text-[10px] uppercase font-black text-gray-400 tracking-wider mb-0.5">Leaving From</label>
                                    <input id="hero-from" type="text" name="from" placeholder="Type a city, area, landmark..."
                                        class="w-full bg-transparent border-none p-0 focus:ring-0 text-brand-blue font-bold text-[15px] outline-none"
                                        required autocomplete="off">
                                    <input type="hidden" id="hero-from-lat" name="from_lat">
                                    <input type="hidden" id="hero-from-lng" name="from_lng">
                                    <!-- Clear btn -->
                                    <button type="button" id="clr-from" onclick="clearField('hero-from','hero-from-lat','hero-from-lng','clr-from')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-red-400 transition hidden text-xs"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>

                            <!-- SWAP BUTTON -->
                            <div class="flex items-center gap-3">
                                <div class="w-8 flex justify-center">
                                    <button type="button" onclick="swapLocations()" id="swap-btn"
                                        class="w-7 h-7 rounded-full border-2 border-gray-200 bg-white hover:border-brand-blue hover:bg-brand-blue hover:text-white text-gray-400 flex items-center justify-center transition-all text-xs shadow-sm">
                                        <i class="fa-solid fa-arrow-up-arrow-down"></i>
                                    </button>
                                </div>
                                <!-- Distance pill (shown after both selected) -->
                                <div id="dist-pill" class="hidden flex-1 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black" style="background:#f0fdf4;color:#1b8036;border:1px solid #bbf7d0">
                                        <i class="fa-solid fa-road text-[10px]"></i>
                                        <span id="dist-text">—</span>
                                        <span class="text-gray-400 font-semibold" id="dur-text"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- TO -->
                            <div class="relative z-10 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-brand-orange" style="background:#fff7ed;">
                                    <i class="fa-solid fa-location-dot text-sm"></i>
                                </div>
                                <div class="flex-1 relative bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 focus-within:ring-2 focus-within:ring-brand-orange/40 focus-within:border-brand-orange transition-all">
                                    <label class="block text-[10px] uppercase font-black text-gray-400 tracking-wider mb-0.5">Going To</label>
                                    <input id="hero-to" type="text" name="to" placeholder="Destination, office, station..."
                                        class="w-full bg-transparent border-none p-0 focus:ring-0 text-brand-blue font-bold text-[15px] outline-none"
                                        required autocomplete="off">
                                    <input type="hidden" id="hero-to-lat" name="to_lat">
                                    <input type="hidden" id="hero-to-lng" name="to_lng">
                                    <button type="button" id="clr-to" onclick="clearField('hero-to','hero-to-lat','hero-to-lng','clr-to')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-red-400 transition hidden text-xs"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>

                            <!-- Date + Seats row -->
                            <div class="flex gap-3 relative z-10 pl-11">
                                <div class="flex-1 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 focus-within:ring-2 focus-within:ring-brand-blue/40 focus-within:border-brand-blue transition-all flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-gray-400 text-sm"></i>
                                    <input type="date" name="date" id="hero-date" min="<?= date('Y-m-d') ?>"
                                        class="w-full bg-transparent border-none p-0 focus:ring-0 text-brand-blue font-bold text-sm outline-none cursor-pointer"
                                        required>
                                </div>
                                <div class="w-24 bg-gray-50 border-2 border-gray-200 rounded-xl px-3 py-3 focus-within:ring-2 focus-within:ring-brand-blue/40 focus-within:border-brand-blue transition-all flex items-center gap-2" id="passenger-count">
                                    <i class="fa-regular fa-user text-gray-400 text-sm"></i>
                                    <input type="number" min="1" max="4" value="1" name="seats"
                                        class="w-full bg-transparent border-none p-0 focus:ring-0 text-brand-blue font-bold text-sm outline-none text-center" required>
                                </div>
                            </div>

                            <button type="submit" id="search-btn"
                                class="w-full mt-1 bg-brand-green text-white font-black text-base py-4 rounded-xl hover:bg-green-700 transition-all shadow-glow-green flex items-center justify-center gap-2">
                                <i class="fa-solid fa-magnifying-glass"></i> Search Rides <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Visuals: Dynamic Image Display -->
                <div class="lg:w-[55%] relative flex justify-center lg:justify-end" data-aos="zoom-in"
                    data-aos-duration="1200" data-aos-delay="200">

                    <!-- Decorative Background for Image -->
                    <div class="absolute inset-0 bg-brand-blue/5 rounded-[3rem] transform rotate-3 scale-105 z-0"></div>

                    <div
                        class="relative w-full h-[400px] sm:h-[500px] lg:h-[600px] rounded-[2.5rem] overflow-hidden shadow-float z-10 bg-gray-100 border-8 border-white">

                        <!-- Dynamic Images Container -->
                        <!-- Carpool Image (Default) -->
                        <img id="img-carpool" src="images/hero_carpool.png" alt="Carpooling"
                            class="absolute inset-0 w-full h-full object-cover dynamic-img"
                            onerror="this.src='https://images.unsplash.com/photo-1549315024-e1d09e73b224?q=80&w=1200'">

                        <!-- Bike Image -->
                        <img id="img-bike" src="images/hero_bike.png" alt="Bike Buddy"
                            class="absolute inset-0 w-full h-full object-cover dynamic-img img-hide"
                            onerror="this.src='https://images.unsplash.com/photo-1558981403-c5f9899a28bc?q=80&w=1200'">

                        <!-- Cab Image -->
                        <img id="img-cab" src="images/hero_cab.png" alt="Cab Share"
                            class="absolute inset-0 w-full h-full object-cover dynamic-img img-hide"
                            onerror="this.src='https://images.unsplash.com/photo-1550291652-6ea9114a47b1?q=80&w=1200'">

                        <!-- Shuttle Image -->
                        <img id="img-shuttle" src="images/hero_shuttle.png" alt="Shuttle"
                            class="absolute inset-0 w-full h-full object-cover dynamic-img img-hide"
                            onerror="this.src='https://images.unsplash.com/photo-1570125909232-eb263c188f7e?q=80&w=1200'">

                        <!-- Overlay Gradient -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-brand-blue/80 via-transparent to-transparent">
                        </div>

                        <!-- Dynamic Text Overlay -->
                        <div class="absolute bottom-8 left-8 right-8 text-white">
                            <div class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold mb-3 uppercase tracking-wider border border-white/30"
                                id="img-tag">GreenCar Carpool</div>
                            <h3 class="text-3xl font-black leading-tight" id="img-title">Share the drive, split the
                                cost.</h3>
                        </div>
                    </div>

                    <!-- Floating Badge -->
                    <div
                        class="absolute top-10 -left-10 bg-white p-4 rounded-2xl flex items-center gap-4 anim-float z-30 shadow-soft border border-gray-100 hidden md:flex">
                        <div
                            class="bg-brand-orange/10 text-brand-orange w-12 h-12 rounded-full flex items-center justify-center text-xl">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="pr-2">
                            <p class="text-xs text-gray-500 font-bold">100% Safe</p>
                            <p class="font-black text-brand-blue text-sm">Verified Profiles</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========== STATS COMPONENT (Light) ========== -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-30 -mt-10" data-aos="fade-up">
        <div
            class="bg-white rounded-[2rem] shadow-soft p-6 sm:p-8 border border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 items-center">
            <div class="text-center px-2 sm:px-4">
                <div class="text-2xl sm:text-4xl font-black text-brand-blue mb-1">5L+</div>
                <div class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-widest">Active Poolers</div>
            </div>
            <div class="text-center px-2 sm:px-4">
                <div class="text-2xl sm:text-4xl font-black text-brand-green mb-1">100+</div>
                <div class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-widest">Cities Live</div>
            </div>
            <div class="text-center px-2 sm:px-4">
                <div class="text-2xl sm:text-4xl font-black text-brand-orange mb-1">₹5Cr</div>
                <div class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-widest">Money Saved</div>
            </div>
            <div class="text-center px-2 sm:px-4">
                <div class="text-2xl sm:text-4xl font-black text-brand-blue mb-1">10K</div>
                <div class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase tracking-widest">Trees Saved</div>
            </div>
        </div>
    </div>

    <!-- ========== SERVICES BENTO GRID (Showcasing App UI) ========== -->
    <section id="services" class="py-24 bg-brand-light relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-16" data-aos="fade-up">
                <h2 class="text-2xl sm:text-4xl md:text-5xl font-black text-brand-blue mb-4">Complete <span
                        class="text-brand-green">Mobility Hub.</span></h2>
                <p class="text-gray-500 text-base sm:text-lg font-medium">One app for all your travel needs. Experience seamless UI
                    and effortless ride matching.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">

                <!-- 1. Carpool (Uses App Screenshot) -->
                <div class="bg-white rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-12 shadow-soft hover:shadow-lg transition-all duration-500 border border-gray-100 flex flex-col md:flex-row items-center gap-6 sm:gap-8 group"
                    data-aos="fade-right">
                    <div class="md:w-1/2 z-10">
                        <div
                            class="w-14 h-14 bg-brand-green/10 text-brand-green rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                        <h3 class="text-3xl font-black text-brand-blue mb-3">Carpool</h3>
                        <p class="text-gray-500 mb-6 font-medium leading-relaxed">Share your daily office ride with
                            verified colleagues. Split fuel costs, enjoy a comfortable commute.</p>
                        <button onclick="window.scrollTo(0,0); switchMode('carpool');"
                            class="inline-flex items-center px-6 py-3 bg-brand-green text-white font-bold rounded-full hover:bg-green-700 transition shadow-md">
                            Search Rides
                        </button>
                    </div>
                    <div class="md:w-1/2 flex justify-center">
                        <div
                            class="phone-mockup w-[200px] h-[420px] transform group-hover:-translate-y-4 transition-transform duration-500 shadow-float">
                            <img src="images/carpool.jpeg" class="phone-screen" alt="Carpool UI"
                                onerror="this.src='images/carpool.jpeg'">
                        </div>
                    </div>
                </div>

                <!-- 2. Bike Buddy (Uses App Screenshot) -->
                <div class="bg-white rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-12 shadow-soft hover:shadow-lg transition-all duration-500 border border-gray-100 flex flex-col md:flex-row items-center gap-6 sm:gap-8 group"
                    data-aos="fade-left" data-aos-delay="100">
                    <div class="md:w-1/2 order-2 md:order-1 flex justify-center">
                        <div
                            class="phone-mockup w-[200px] h-[420px] transform group-hover:-translate-y-4 transition-transform duration-500 shadow-float">
                            <img src="images/bike.jpeg" class="phone-screen" alt="Bike Buddy UI"
                                onerror="this.src='images/bike.jpeg'">
                        </div>
                    </div>
                    <div class="md:w-1/2 order-1 md:order-2 z-10">
                        <div
                            class="w-14 h-14 bg-brand-orange/10 text-brand-orange rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm">
                            <i class="fa-solid fa-motorcycle"></i>
                        </div>
                        <h3 class="text-3xl font-black text-brand-blue mb-3">Bike Buddy</h3>
                        <p class="text-gray-500 mb-6 font-medium leading-relaxed">Zip through city traffic effortlessly.
                            Connect with trusted riders for fast, affordable daily bike pooling.</p>
                        <button onclick="window.scrollTo(0,0); switchMode('bike');"
                            class="inline-flex items-center px-6 py-3 bg-brand-orange text-white font-bold rounded-full hover:bg-orange-600 transition shadow-md">
                            Find a Bike
                        </button>
                    </div>
                </div>

                <!-- 3. CabShare (Uses App Screenshot) -->
                <div class="bg-brand-blue text-white rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-12 shadow-glow-blue hover:shadow-2xl transition-all duration-500 flex flex-col md:flex-row items-center gap-6 sm:gap-8 group relative overflow-hidden"
                    data-aos="fade-right">
                    <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-white/5 rounded-full blur-[50px]"></div>
                    <div class="md:w-1/2 z-10">
                        <div
                            class="w-14 h-14 bg-white/20 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm backdrop-blur-sm border border-white/20">
                            <i class="fa-solid fa-taxi"></i>
                        </div>
                        <h3 class="text-3xl font-black mb-3">CabShare</h3>
                        <p class="text-blue-100 mb-6 font-medium leading-relaxed">Booking a full cab? Find co-passengers
                            heading your way and split the hefty fare instantly. Perfect for intercity!</p>
                        <button onclick="window.scrollTo(0,0); switchMode('cab');"
                            class="inline-flex items-center text-brand-orange font-bold hover:text-orange-300">
                            Share a Cab <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    <div class="md:w-1/2 flex justify-center relative z-10">
                        <div
                            class="phone-mockup w-[200px] h-[420px] transform group-hover:scale-105 transition-transform duration-500 shadow-2xl border-brand-blue">
                            <img src="images/cab.jpeg" class="phone-screen" alt="CabShare UI">
                        </div>
                    </div>
                </div>

                <!-- 4. Shuttle (Uses App Screenshot) -->
                <div class="bg-white rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-12 shadow-soft hover:shadow-lg transition-all duration-500 border border-brand-green/20 flex flex-col md:flex-row items-center gap-6 sm:gap-8 group"
                    data-aos="fade-left" data-aos-delay="100">
                    <div class="md:w-1/2 order-2 md:order-1 flex justify-center">
                        <div
                            class="phone-mockup w-[200px] h-[420px] transform group-hover:scale-105 transition-transform duration-500 shadow-float">
                            <img src="images/shuttle.jpeg" class="phone-screen" alt="Shuttle UI">
                        </div>
                    </div>
                    <div class="md:w-1/2 order-1 md:order-2 z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-14 h-14 bg-green-50 text-brand-green rounded-2xl flex items-center justify-center text-2xl shadow-sm border border-brand-green/20">
                                <i class="fa-solid fa-van-shuttle"></i>
                            </div>
                            <span
                                class="px-3 py-1 bg-brand-orange/10 text-brand-orange text-xs font-black rounded-full uppercase tracking-wider">Coming
                                Soon</span>
                        </div>
                        <h3 class="text-3xl font-black text-brand-blue mb-3">Premium Shuttle</h3>
                        <p class="text-gray-500 mb-6 font-medium leading-relaxed">Pre-register your daily route. Travel
                            comfortably in AC shuttles with guaranteed seating.</p>
                        <button onclick="window.scrollTo(0,0); switchMode('shuttle');"
                            class="inline-flex items-center px-6 py-3 bg-white text-brand-green border-2 border-brand-green font-bold rounded-full hover:bg-brand-green hover:text-white transition shadow-sm">
                            Pre-Register Route
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========== VIKSIT BHARAT SECTION (Rich) ========== -->
    <section id="mission" class="relative py-24 overflow-hidden"
        style="background:linear-gradient(135deg,#0f1e3d 0%,#1d3a70 60%,#0d2252 100%)">
        <!-- BG pattern -->
        <div class="absolute inset-0 opacity-5"
            style="background-image:linear-gradient(rgba(255,255,255,0.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.5) 1px,transparent 1px);background-size:40px 40px;">
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header -->
            <div class="text-center mb-14" data-aos="fade-up">
                <div
                    class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-5 py-2.5 mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/4/41/Flag_of_India.svg/120px-Flag_of_India.svg.png"
                        class="w-8 h-5 object-cover rounded" alt="India">
                    <span class="text-white font-black text-sm uppercase tracking-widest">Viksit Bharat
                        Initiative</span>
                </div>
                <h2 class="text-2xl sm:text-4xl md:text-6xl font-black text-white mb-5 tracking-tight">
                    Driving India <span class="text-brand-orange">Forward.</span>
                </h2>
                <p class="text-blue-200 text-lg font-medium max-w-2xl mx-auto leading-relaxed">
                    Pool India is not just an app — it's a national movement. Every shared ride is a step toward
                    decongested cities, cleaner air, and a stronger economy.
                </p>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14" data-aos="fade-up" data-aos-delay="100">
                <div class="vb-stat">
                    <div class="text-3xl font-black text-brand-green mb-1">2,400+</div>
                    <div class="text-xs font-bold text-blue-300 uppercase tracking-wider">Tonnes CO₂ Saved</div>
                </div>
                <div class="vb-stat">
                    <div class="text-3xl font-black text-brand-orange mb-1">5L+</div>
                    <div class="text-xs font-bold text-blue-300 uppercase tracking-wider">Active Poolers</div>
                </div>
                <div class="vb-stat">
                    <div class="text-3xl font-black text-white mb-1">₹5Cr+</div>
                    <div class="text-xs font-bold text-blue-300 uppercase tracking-wider">Commuters' Savings</div>
                </div>
                <div class="vb-stat">
                    <div class="text-3xl font-black text-brand-green mb-1">100+</div>
                    <div class="text-xs font-bold text-blue-300 uppercase tracking-wider">Cities Live</div>
                </div>
            </div>

            <!-- Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up" data-aos-delay="150">
                <div class="vb-card">
                    <div
                        class="w-12 h-12 bg-green-100 text-brand-green rounded-2xl flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-leaf"></i>
                    </div>
                    <h3 class="font-black text-brand-blue text-lg mb-2">Greener Cities</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed">Carpooling reduces individual vehicle
                        emissions by up to 75%. Pool India commuters have collectively saved 2,400+ tonnes of CO₂ —
                        equivalent to planting 1.1 lakh trees.</p>
                </div>
                <div class="vb-card">
                    <div
                        class="w-12 h-12 bg-orange-100 text-brand-orange rounded-2xl flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-road"></i>
                    </div>
                    <h3 class="font-black text-brand-blue text-lg mb-2">Less Traffic</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed">Every 4-seater carpool removes 3 cars
                        from the road. Pool India's network saves 18 lakh+ vehicle-km monthly, directly reducing
                        peak-hour congestion in metro cities.</p>
                </div>
                <div class="vb-card">
                    <div
                        class="w-12 h-12 bg-blue-100 text-brand-blue rounded-2xl flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </div>
                    <h3 class="font-black text-brand-blue text-lg mb-2">Economic Power</h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed">Poolers save ₹3,000–₹6,000/month on
                        commute costs. That's extra money in the hands of India's working population — fuelling
                        consumption and local growth.</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center mt-12" data-aos="zoom-in">
                <p class="text-blue-200 font-semibold mb-4">Join the movement. Every ride counts.</p>
                <a href="rides.php"
                    class="inline-flex items-center gap-2 bg-brand-orange text-white font-black px-8 py-4 rounded-full hover:bg-orange-600 transition-all shadow-lg text-base">
                    <i class="fa-solid fa-car-side"></i> Start Pooling Today
                </a>
            </div>
        </div>
    </section>

    <!-- ========== APP DOWNLOAD & APP STORE MOCKUPS ========== -->
    <section id="download" class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div
                class="bg-brand-light rounded-[1.5rem] sm:rounded-[3rem] p-6 sm:p-8 md:p-16 flex flex-col lg:flex-row items-center gap-10 sm:gap-16 shadow-soft border border-gray-100">

                <!-- Content Side -->
                <div class="lg:w-1/2" data-aos="fade-right">
                    <div class="inline-block text-brand-orange font-bold tracking-widest uppercase mb-2 text-sm">
                        Download App</div>
                    <h2 class="text-2xl sm:text-4xl md:text-5xl font-black mb-6 text-brand-blue">Available on Play Store & App Store
                    </h2>
                    <p class="text-gray-600 text-lg mb-8 font-medium">Track your rides, verify your profile securely,
                        and join India's fastest-growing mobility community.</p>

                    <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <a href="#"
                            class="bg-brand-blue text-white px-6 py-3.5 rounded-2xl flex items-center gap-4 hover:bg-blue-900 transition-colors shadow-md">
                            <i class="fa-brands fa-google-play text-3xl text-brand-green"></i>
                            <div class="text-left">
                                <p class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Get it on</p>
                                <p class="font-black text-lg leading-tight">Google Play</p>
                            </div>
                        </a>
                        <a href="#"
                            class="bg-brand-blue text-white px-6 py-3.5 rounded-2xl flex items-center gap-4 hover:bg-blue-900 transition-colors shadow-md">
                            <i class="fa-brands fa-apple text-3xl text-white"></i>
                            <div class="text-left">
                                <p class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Download on the
                                </p>
                                <p class="font-black text-lg leading-tight">App Store</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Visual Side -->
                <div class="lg:w-1/2 relative h-[500px] w-full flex justify-center items-center" data-aos="fade-left">
                    <div
                        class="phone-mockup w-[220px] h-[450px] absolute right-4 md:right-12 z-10 transform rotate-6 hover:rotate-0 transition-transform duration-500 shadow-float">
                        <img src="images/ride.jpeg" class="phone-screen" alt="My Rides UI">
                    </div>
                    <div
                        class="phone-mockup w-[240px] h-[480px] absolute left-4 md:left-12 z-20 transform -rotate-3 hover:rotate-0 transition-transform duration-500 shadow-[0_30px_60px_rgba(29,58,112,0.15)]">
                        <img src="images/profile.jpeg" class="phone-screen" alt="Profile UI">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CONTACT / FORM SECTION ========== -->
    <section id="contact" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-16">
                <!-- Contact Info -->
                <div class="lg:w-5/12" data-aos="fade-right">
                    <div class="inline-block text-brand-orange font-bold tracking-widest uppercase mb-2 text-sm">Get in
                        Touch</div>
                    <h2 class="text-2xl sm:text-4xl md:text-5xl font-black text-brand-blue mb-6">Have questions?<br>Let’s connect.
                    </h2>
                    <p class="text-gray-600 mb-8 font-medium">Whether you are looking for enterprise corporate tie-ups,
                        or need support with the app, we are here to help you.</p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-brand-light flex items-center justify-center text-brand-green shrink-0 shadow-sm border border-gray-100">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-brand-blue text-lg">Our Office</h4>
                                <p class="text-gray-600">Sector 3, Noida,<br>Uttar Pradesh, India - 201301</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-brand-light flex items-center justify-center text-brand-orange shrink-0 shadow-sm border border-gray-100">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-brand-blue text-lg">Email Us</h4>
                                <p class="text-gray-600">support@poolindia.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:w-7/12" data-aos="fade-left">
                    <form class="bg-white rounded-3xl shadow-soft border border-gray-100 p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-brand-blue mb-2">Full Name</label>
                                <input type="text"
                                    class="w-full bg-brand-light border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-brand-green focus:border-brand-green block p-3.5 outline-none transition"
                                    placeholder="John Doe" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-brand-blue mb-2">Email Address</label>
                                <input type="email"
                                    class="w-full bg-brand-light border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-brand-green focus:border-brand-green block p-3.5 outline-none transition"
                                    placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-brand-blue mb-2">Phone Number</label>
                            <input type="tel"
                                class="w-full bg-brand-light border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-brand-green focus:border-brand-green block p-3.5 outline-none transition"
                                placeholder="+91 98765 43210" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-brand-blue mb-2">Your Message</label>
                            <textarea rows="4"
                                class="w-full bg-brand-light border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-brand-green focus:border-brand-green block p-3.5 outline-none transition"
                                placeholder="How can we help you?" required></textarea>
                        </div>
                        <button type="submit"
                            class="w-full text-white bg-brand-blue hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-brand-blue/30 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-md">
                            Send Message <i class="fa-solid fa-paper-plane ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== PREMIUM FOOTER ========== -->
    <footer class="bg-brand-blue text-white py-16 border-t-4 border-brand-orange">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 border-b border-white/10 pb-12">
                <div class="flex items-center gap-3 mb-6 md:mb-0">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center p-2">
                        <img src="images/logo.png" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="text-3xl font-black tracking-tight text-white">
                        POOL <span class="text-brand-green">India</span>
                    </div>
                </div>
                <div class="flex gap-4">
                    <a href="#"
                        class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-orange hover:text-white transition-colors text-lg"><i
                            class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#"
                        class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-green hover:text-white transition-colors text-lg"><i
                            class="fa-brands fa-twitter"></i></a>
                    <a href="#"
                        class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-orange hover:text-white transition-colors text-lg"><i
                            class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 mb-12 text-sm font-medium">
                <div>
                    <h4 class="text-brand-orange font-bold mb-6 tracking-wider uppercase">Services</h4>
                    <ul class="space-y-3">
                        <li><button onclick="window.scrollTo(0,0); switchMode('carpool');"
                                class="hover:text-white text-blue-200 transition-colors">GreenCar Carpool</button></li>
                        <li><button onclick="window.scrollTo(0,0); switchMode('bike');"
                                class="hover:text-white text-blue-200 transition-colors">Bike Buddy</button></li>
                        <li><button onclick="window.scrollTo(0,0); switchMode('cab');"
                                class="hover:text-white text-blue-200 transition-colors">CabShare</button></li>
                        <li><button onclick="window.scrollTo(0,0); switchMode('shuttle');"
                                class="hover:text-white text-blue-200 transition-colors">Corporate Shuttle</button></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-brand-orange font-bold mb-6 tracking-wider uppercase">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="hover:text-white text-blue-200 transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white text-blue-200 transition-colors">Trust & Safety</a></li>
                        <li><a href="#mission" class="hover:text-white text-blue-200 transition-colors">Viksit
                                Bharat</a></li>
                        <li><a href="#" class="hover:text-white text-blue-200 transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div class="col-span-2 md:col-span-2 lg:col-span-1">
                    <h4 class="text-brand-orange font-bold mb-6 tracking-wider uppercase">Contact</h4>
                    <ul class="space-y-4 text-blue-200">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-brand-green"></i>
                            Sector 3, Noida, UP, India
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-brand-orange"></i>
                            support@poolindia.com
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col md:flex-row justify-between items-center text-xs text-blue-300 font-bold border-t border-white/10 pt-6">
                <p>&copy; 2026 Pool India. All rights reserved.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Places Autocomplete Helper (loads Google Maps API internally) -->
    <script src="js/places-ac.js"></script>
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // ---- HERO ANIMATED HEADLINES ----
        const heroSlides = [
            { line1: 'Your Commute,', line2: 'Your Choice.', sub: 'Search daily rides, intercity cabs or shuttles. Verified peers, cheaper fares.' },
            { line1: 'Save Money,', line2: 'Save the Planet.', sub: 'Split fuel costs with verified co-passengers. Cut your carbon footprint every day.' },
            { line1: 'For a', line2: 'Viksit Bharat 🇮🇳', sub: 'Pool India is a movement — decongesting cities, cutting emissions, building a smarter India.' },
            { line1: 'Safe Rides,', line2: 'Verified Peers.', sub: 'Aadhaar & DL verified profiles. 100% identity-checked community.' },
        ];
        let heroIdx = 0;
        function cycleHero() {
            const l1 = document.getElementById('hero-line1');
            const l2 = document.getElementById('hero-line2');
            const sub = document.getElementById('hero-sub');
            [l1, l2, sub].forEach(el => { el.classList.remove('hero-text-in'); el.classList.add('hero-text-out'); });
            setTimeout(() => {
                heroIdx = (heroIdx + 1) % heroSlides.length;
                const s = heroSlides[heroIdx];
                l1.textContent = s.line1;
                l2.textContent = s.line2;
                sub.textContent = s.sub;
                [l1, l2, sub].forEach(el => { el.classList.remove('hero-text-out'); el.classList.add('hero-text-in'); });
            }, 450);
        }
        setInterval(cycleHero, 4000);

        // ---- SEARCH FORM ----
        function handleSearch(e) {
            e.preventDefault();
            const from  = document.getElementById('hero-from')?.value || '';
            const to    = document.getElementById('hero-to')?.value || '';
            const date  = document.getElementById('hero-date')?.value || '';
            const seats = document.querySelector('[name=seats]')?.value || 1;
            const fromLat = document.getElementById('hero-from-lat')?.value || '';
            const fromLng = document.getElementById('hero-from-lng')?.value || '';
            const toLat   = document.getElementById('hero-to-lat')?.value || '';
            const toLng   = document.getElementById('hero-to-lng')?.value || '';
            if (!from || !to) return;
            const params = new URLSearchParams({ from, to, date, seats, from_lat: fromLat, from_lng: fromLng, to_lat: toLat, to_lng: toLng });
            window.location.href = 'rides.php?' + params.toString();
        }

        function clearField(inpId, latId, lngId, btnId) {
            document.getElementById(inpId).value = '';
            document.getElementById(latId).value = '';
            document.getElementById(lngId).value = '';
            document.getElementById(btnId).classList.add('hidden');
            document.getElementById('dist-pill').classList.add('hidden');
        }
        function swapLocations() {
            const fi = document.getElementById('hero-from');
            const ti = document.getElementById('hero-to');
            const fl = document.getElementById('hero-from-lat');
            const fn = document.getElementById('hero-from-lng');
            const tl = document.getElementById('hero-to-lat');
            const tn = document.getElementById('hero-to-lng');
            [fi.value, ti.value] = [ti.value, fi.value];
            [fl.value, tl.value] = [tl.value, fl.value];
            [fn.value, tn.value] = [tn.value, fn.value];
            // animate swap btn
            const btn = document.getElementById('swap-btn');
            btn.style.transform = 'rotate(180deg)';
            setTimeout(() => btn.style.transform = '', 400);
            _tryShowDistance();
        }
        async function _tryShowDistance() {
            const flat = document.getElementById('hero-from-lat')?.value;
            const flng = document.getElementById('hero-from-lng')?.value;
            const tlat = document.getElementById('hero-to-lat')?.value;
            const tlng = document.getElementById('hero-to-lng')?.value;
            if (!flat || !tlat) { document.getElementById('dist-pill').classList.add('hidden'); return; }
            try {
                const d = await PI_Places.getDistance({lat:+flat,lng:+flng},{lat:+tlat,lng:+tlng});
                document.getElementById('dist-text').textContent = d.distance_text;
                document.getElementById('dur-text').textContent  = '• ' + d.duration_text;
                document.getElementById('dist-pill').classList.remove('hidden');
            } catch(e) {}
        }

        // Attach autocomplete after DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            // Set today's date as default
            document.getElementById('hero-date').value = new Date().toISOString().split('T')[0];

            PI_Places.initAll([
                {
                    inputId: 'hero-from', latId: 'hero-from-lat', lngId: 'hero-from-lng',
                    onSelect: () => { document.getElementById('clr-from').classList.remove('hidden'); _tryShowDistance(); },
                    onClear:  () => { document.getElementById('dist-pill').classList.add('hidden'); }
                },
                {
                    inputId: 'hero-to', latId: 'hero-to-lat', lngId: 'hero-to-lng',
                    onSelect: () => { document.getElementById('clr-to').classList.remove('hidden'); _tryShowDistance(); },
                    onClear:  () => { document.getElementById('dist-pill').classList.add('hidden'); }
                },
            ]);
            // Show clear btn if value present
            ['hero-from','hero-to'].forEach(id => {
                document.getElementById(id)?.addEventListener('input', function() {
                    const clrId = id === 'hero-from' ? 'clr-from' : 'clr-to';
                    document.getElementById(clrId).classList.toggle('hidden', !this.value);
                });
            });
        });
    </script>
    <script>
        // Initialize AOS Animation
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Light Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 20) {
                nav.classList.add('shadow-md');
                nav.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                nav.classList.remove('shadow-md');
                nav.style.background = 'rgba(255, 255, 255, 0.85)';
            }
        });

        // Dynamic Widget Logic
        const modes = {
            carpool: {
                title: "Share the drive, split the cost.",
                tag: "GreenCar Carpool",
                btnText: "Search Carpools <i class='fa-solid fa-arrow-right ml-2'></i>",
                btnColor: "bg-brand-green",
                btnHover: "hover:bg-green-700",
                shadowColor: "shadow-glow-green"
            },
            bike: {
                title: "Quick rides through city traffic.",
                tag: "Bike Buddy",
                btnText: "Find a Bike <i class='fa-solid fa-arrow-right ml-2'></i>",
                btnColor: "bg-brand-orange",
                btnHover: "hover:bg-orange-600",
                shadowColor: "shadow-md"
            },
            cab: {
                title: "Split intercity cab fares instantly.",
                tag: "CabShare",
                btnText: "Search Cabs <i class='fa-solid fa-arrow-right ml-2'></i>",
                btnColor: "bg-brand-blue",
                btnHover: "hover:bg-blue-900",
                shadowColor: "shadow-glow-blue"
            },
            shuttle: {
                title: "Comfortable daily AC Shuttles.",
                tag: "Premium Shuttle",
                btnText: "Pre-Register Route <i class='fa-solid fa-arrow-right ml-2'></i>",
                btnColor: "bg-teal-600",
                btnHover: "hover:bg-teal-700",
                shadowColor: "shadow-md"
            }
        };

        function switchMode(selectedMode) {
            // Update Active Tab Styling
            const tabs = ['carpool', 'bike', 'cab', 'shuttle'];
            tabs.forEach(mode => {
                const tab = document.getElementById(`tab-${mode}`);
                const img = document.getElementById(`img-${mode}`);

                if (mode === selectedMode) {
                    // Active Tab styling
                    tab.className = "flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm bg-white shadow-sm text-brand-blue transition-all flex flex-col items-center gap-1";
                    // Show Image
                    img.classList.remove('img-hide');
                } else {
                    // Inactive Tab styling
                    tab.className = "flex-1 min-w-[80px] py-2.5 px-3 rounded-xl font-bold text-sm text-gray-500 hover:text-brand-blue transition-all flex flex-col items-center gap-1";
                    // Hide Image
                    img.classList.add('img-hide');
                }
            });

            // Update Dynamic Content (Text and Button)
            const config = modes[selectedMode];
            document.getElementById('img-title').innerText = config.title;
            document.getElementById('img-tag').innerText = config.tag;

            const searchBtn = document.getElementById('search-btn');
            searchBtn.innerHTML = config.btnText;

            // Remove old color classes and add new ones
            searchBtn.className = `w-full mt-2 text-white font-bold text-lg py-4 rounded-xl transition-all flex items-center justify-center gap-2 ${config.btnColor} ${config.btnHover} ${config.shadowColor}`;

            // Optional logic: Hide passenger count for Shuttle
            const paxCount = document.getElementById('passenger-count');
            if (selectedMode === 'shuttle') {
                paxCount.style.opacity = '0.5';
                paxCount.style.pointerEvents = 'none';
            } else {
                paxCount.style.opacity = '1';
                paxCount.style.pointerEvents = 'auto';
            }
        }
    </script>
</body>

</html>