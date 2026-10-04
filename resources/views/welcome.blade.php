<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Motel Bethuli — API Gateway</title>
    <meta name="description" content="Interface API backend du Système de Réservation du Motel Bethuli. Réservée aux applications autorisées.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-serif-display { font-family: 'Playfair Display', serif; }
        body { background-color: #0b1120; color: #f1f5f9; margin: 0; }
        html { scroll-behavior: smooth; }

        .hero-bg {
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(245,158,11,0.18), transparent),
                radial-gradient(ellipse 60% 40% at 80% 60%, rgba(245,158,11,0.07), transparent),
                #0b1120;
        }
        .glass {
            background: rgba(15,23,42,0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.07);
        }
        .section-divider {
            border: none; height: 1px;
            background: linear-gradient(to right, transparent, rgba(245,158,11,0.15), transparent);
            margin: 0;
        }
        .pulse-dot {
            width:10px; height:10px; background:#10b981; border-radius:50%;
            display:inline-block; box-shadow:0 0 8px #10b981;
            animation: pulse-anim 2s infinite;
        }
        @keyframes pulse-anim {
            0%  { box-shadow: 0 0 0 0 rgba(16,185,129,0.7); }
            70% { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
            100%{ box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }
        .method-get  { background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(16,185,129,0.25); }
        .method-post { background:rgba(245,158,11,0.12);  color:#fbbf24; border:1px solid rgba(245,158,11,0.25); }
        .method-del  { background:rgba(239,68,68,0.12);   color:#f87171; border:1px solid rgba(239,68,68,0.25); }
        .nav-link { transition:color .2s; }
        .nav-link:hover { color:#f59e0b; }
        .amber-glow { box-shadow: 0 0 30px rgba(245,158,11,0.15); }
    </style>
</head>
<body>

<!-- ══════════════════ TOP NAV ══════════════════ -->
<nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-[#0b1120]/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="/logo/logo.png" alt="Motel Bethuli" class="h-9 w-auto object-contain">
            <span class="text-sm font-bold text-white tracking-tight hidden sm:inline">
                Motel <span class="text-amber-400">Bethuli</span>
            </span>
        </div>
        <div class="hidden sm:flex items-center gap-6 text-xs font-medium text-slate-400">
            <a href="#overview" class="nav-link">Vue d'ensemble</a>
            <a href="#endpoints" class="nav-link">Endpoints</a>
            <a href="#legal" class="nav-link">Légal</a>
            <a href="#contact" class="nav-link">Contact</a>
            <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
               class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-white rounded-lg transition-colors text-xs font-semibold">
                Accéder à l'app →
            </a>
        </div>
        <!-- Mobile CTA -->
        <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
           class="sm:hidden px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-semibold">
            App →
        </a>
    </div>
</nav>

<!-- ══════════════════ HERO ══════════════════ -->
<section id="overview" class="hero-bg pt-28 pb-20 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto">

        <!-- Status badge -->
        <div class="flex justify-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-semibold text-emerald-400 border border-emerald-500/20">
                <span class="pulse-dot"></span>
                Service opérationnel &nbsp;·&nbsp; v1.0.0
            </div>
        </div>

        <!-- Split layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left -->
            <div>
                <!-- Logo -->
                <div class="mb-6">
                    <img src="/logo/logo.png" alt="Motel Bethuli" class="h-20 w-auto object-contain">
                </div>
                <h1 class="font-serif-display text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight mb-4">
                    Motel Bethuli <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">API Gateway</span>
                </h1>
                <p class="text-base text-slate-400 leading-relaxed mb-6">
                    Interface de programmation backend du <strong class="text-slate-200">Système de Réservation Hôtelière</strong>
                    du Motel Bethuli. Réservée aux applications autorisées.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl transition-all text-sm shadow-lg shadow-amber-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Accéder à l'application
                    </a>
                    <a href="#endpoints"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 glass hover:bg-white/5 text-slate-200 font-semibold rounded-xl transition-all text-sm">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        Voir les endpoints
                    </a>
                </div>
            </div>

            <!-- Right: info cards -->
            <div class="grid grid-cols-2 gap-3">
                <div class="glass rounded-2xl p-5 amber-glow">
                    <div class="text-2xl font-black text-amber-400 mb-1">REST</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Architecture</div>
                    <p class="text-xs text-slate-500 mt-2">JSON · Laravel Sanctum · Bearer Token</p>
                </div>
                <div class="glass rounded-2xl p-5">
                    <div class="text-2xl font-black text-emerald-400 mb-1">HTTPS</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Protocole</div>
                    <p class="text-xs text-slate-500 mt-2">TLS 1.2+ · Chiffrement de bout en bout</p>
                </div>
                <div class="glass rounded-2xl p-5">
                    <div class="text-2xl font-black text-purple-400 mb-1">RBAC</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Autorisations</div>
                    <p class="text-xs text-slate-500 mt-2">Admin · Réceptionniste · Client</p>
                </div>
                <div class="glass rounded-2xl p-5">
                    <div class="text-2xl font-black text-amber-400 mb-1">PHP 8</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Runtime</div>
                    <p class="text-xs text-slate-500 mt-2">Laravel 11 · MySQL 8</p>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="section-divider">

<!-- ══════════════════ ENDPOINTS ══════════════════ -->
<section id="endpoints" class="py-16 px-4 sm:px-6 bg-slate-900/25">
    <div class="max-w-7xl mx-auto">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">API Routes</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 mb-8">Principaux Endpoints</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Auth -->
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Authentification
                </h3>
                <div class="space-y-2 text-xs font-mono">
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">POST</span>
                        <span class="text-slate-400">/api/login</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">POST</span>
                        <span class="text-slate-400">/api/register</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">POST</span>
                        <span class="text-slate-400">/api/logout</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/me</span>
                    </div>
                </div>
            </div>

            <!-- Chambres -->
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Chambres (Public)
                </h3>
                <div class="space-y-2 text-xs font-mono">
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/rooms</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/rooms/{id}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/rooms/check-availability</span>
                    </div>
                </div>
            </div>

            <!-- Réservations Admin -->
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Réservations (Admin)
                </h3>
                <div class="space-y-2 text-xs font-mono">
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/admin/reservations</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">POST</span>
                        <span class="text-slate-400">/api/admin/reservations</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-del px-2 py-0.5 rounded text-[10px] font-bold">DEL</span>
                        <span class="text-slate-400">/api/admin/reservations/{id}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">PATCH</span>
                        <span class="text-slate-400">/api/admin/reservations/{id}/status</span>
                    </div>
                </div>
            </div>

            <!-- Clients Admin -->
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Clients (Admin)
                </h3>
                <div class="space-y-2 text-xs font-mono">
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/admin/clients</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-get px-2 py-0.5 rounded text-[10px] font-bold">GET</span>
                        <span class="text-slate-400">/api/admin/clients/{id}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="method-post px-2 py-0.5 rounded text-[10px] font-bold">POST</span>
                        <span class="text-slate-400">/api/admin/clients/{id}/toggle-cni-verified</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="section-divider">

<!-- ══════════════════ LÉGAL ══════════════════ -->
<section id="legal" class="py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">Informations légales</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 mb-8">Politique & Conditions</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <!-- Conditions d'utilisation -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">CGU</span>
                        <h3 class="font-bold text-white text-sm leading-tight">Conditions d'utilisation</h3>
                    </div>
                </div>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li>· Cette API est <strong class="text-slate-300">strictement réservée</strong> aux applications internes du Motel Bethuli et partenaires autorisés par écrit.</li>
                    <li>· Toute tentative d'accès non autorisé est interdite et passible de poursuites judiciaires.</li>
                    <li>· L'exploitation commerciale sans accord préalable est prohibée.</li>
                    <li>· Le Motel Bethuli se réserve le droit de révoquer tout accès sans préavis en cas d'usage abusif.</li>
                    <li>· Les tokens d'accès sont personnels et confidentiels — ne pas les partager.</li>
                </ul>
            </div>

            <!-- Confidentialité -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">Privacy</span>
                        <h3 class="font-bold text-white text-sm leading-tight">Politique de confidentialité</h3>
                    </div>
                </div>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li>· Les données personnelles (nom, email, téléphone, CNI) sont collectées uniquement dans le cadre des réservations.</li>
                    <li>· Elles ne sont <strong class="text-slate-300">jamais revendues</strong> ni cédées à des tiers.</li>
                    <li>· Droit d'accès, de rectification et de suppression garanti pour chaque client.</li>
                    <li>· Données hébergées sur des serveurs sécurisés.</li>
                    <li>· Contact : <a href="mailto:motelbethuli@gmail.com" class="text-amber-400 hover:underline">motelbethuli@gmail.com</a></li>
                </ul>
            </div>

            <!-- Licence -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/15 border border-purple-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Licence</span>
                        <h3 class="font-bold text-white text-sm leading-tight">Licence logicielle</h3>
                    </div>
                </div>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li>· Ce logiciel est la <strong class="text-slate-300">propriété exclusive</strong> du Motel Bethuli. © {{ date('Y') }}.</li>
                    <li>· Tous droits réservés. Reproduction interdite sans autorisation écrite.</li>
                    <li>· Développé sur Laravel (MIT) · React.js (MIT) · TailwindCSS (MIT).</li>
                    <li>· Toute décompilation, rétro-ingénierie ou redistribution est strictement interdite.</li>
                </ul>
            </div>

            <!-- Sécurité -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-red-500/15 border border-red-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Sécurité</span>
                        <h3 class="font-bold text-white text-sm leading-tight">Sécurité & Rate Limiting</h3>
                    </div>
                </div>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li>· <strong class="text-slate-300">5 req/min</strong> sur les routes d'authentification (anti-bruteforce).</li>
                    <li>· <strong class="text-slate-300">200 req/min</strong> pour les utilisateurs authentifiés.</li>
                    <li>· Communications chiffrées en TLS 1.2+.</li>
                    <li>· Mots de passe hachés avec Bcrypt. Tokens Sanctum à durée de vie limitée.</li>
                    <li>· OTP par email pour la vérification du compte.</li>
                </ul>
            </div>
        </div>

        <!-- Disponibilité -->
        <div class="mt-5 glass rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">SLA</span>
                    <h3 class="font-bold text-white text-sm leading-tight">Responsabilité & Disponibilité</h3>
                </div>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">
                Le Motel Bethuli s'engage à maintenir ce service opérationnel avec un objectif de disponibilité de <strong class="text-slate-300">99 %</strong>.
                Des maintenances programmées peuvent temporairement interrompre le service. Toute réclamation doit être adressée à
                <a href="mailto:motelbethuli@gmail.com" class="text-amber-400 hover:underline">motelbethuli@gmail.com</a>.
                Juridiction compétente : Tribunaux de Yaoundé/Douala, Cameroun.
            </p>
        </div>
    </div>
</section>

<hr class="section-divider">

<!-- ══════════════════ CONTACT ══════════════════ -->
<section id="contact" class="py-16 px-4 sm:px-6 bg-slate-900/25">
    <div class="max-w-7xl mx-auto">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Contact</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 mb-8">Motel Bethuli</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-xs text-slate-400 leading-relaxed">
                    <strong class="text-slate-300">Motel Bethuli</strong><br>
                    Cameroun
                </p>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <p class="text-xs text-slate-400 leading-relaxed">
                    <strong class="text-slate-300">Tél :</strong> +237 673 44 56 82<br>
                    <span class="text-emerald-400">WhatsApp disponible</span>
                </p>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <a href="mailto:motelbethuli@gmail.com" class="text-amber-400 hover:underline block text-xs mb-1">motelbethuli@gmail.com</a>
                <div class="mt-3 border-t border-white/5 pt-3">
                    <p class="text-[10px] text-slate-500">Système de Réservation en ligne</p>
                    <p class="text-[10px] text-slate-500 font-mono mt-1">{{ config('app.url') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="py-6 px-4 border-t border-white/5 bg-[#070d1a]">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
        <div class="flex items-center gap-3">
            <img src="/logo/logo.png" alt="Motel Bethuli" class="h-6 w-auto object-contain opacity-60">
            <span>© {{ date('Y') }} <strong class="text-slate-500">Motel Bethuli</strong>. Tous droits réservés.</span>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ config('app.frontend_url', '#') }}" target="_blank" class="hover:text-amber-400 transition-colors">Application</a>
            <a href="#endpoints" class="hover:text-amber-400 transition-colors">API Docs</a>
            <span class="text-slate-800">·</span>
            <span class="font-mono text-slate-700">{{ config('app.url') }}</span>
        </div>
    </div>
</footer>

</body>
</html>
