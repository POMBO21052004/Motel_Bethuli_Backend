<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FEC — API Gateway | Free Engineering Consultancy</title>
    <meta name="description" content="API Backend du système de gestion des attestations et formations de Free Engineering Consultancy.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #0b1120; color: #f1f5f9; margin: 0; }
        html { scroll-behavior: smooth; }

        .hero-bg {
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(59,130,246,0.22), transparent),
                radial-gradient(ellipse 60% 40% at 80% 60%, rgba(16,185,129,0.10), transparent),
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
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.07), transparent);
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
        .method-post { background:rgba(59,130,246,0.12);  color:#60a5fa; border:1px solid rgba(59,130,246,0.25); }
        .method-del  { background:rgba(239,68,68,0.12);   color:#f87171; border:1px solid rgba(239,68,68,0.25); }
        .nav-link { transition:color .2s; }
        .nav-link:hover { color:#60a5fa; }
    </style>
</head>
<body>

<!-- ══════════════════ TOP NAV ══════════════════ -->
<nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-[#0b1120]/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="/logo/logo_fec.png" alt="FEC Logo" class="h-9 w-auto object-contain rounded-xl">
            <span class="text-sm font-bold text-white tracking-tight hidden sm:inline">Free Engineering <span class="text-blue-400">Consultancy</span></span>
        </div>
        <div class="hidden sm:flex items-center gap-6 text-xs font-medium text-slate-400">
            <a href="#overview"  class="nav-link">Vue d'ensemble</a>
            <a href="#legal"     class="nav-link">Légal</a>
            <a href="#contact"   class="nav-link">Contact</a>
            <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors text-xs font-semibold">
                Accéder à l'app →
            </a>
        </div>
        <!-- Mobile CTA -->
        <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
           class="sm:hidden px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold">
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

        <!-- Split layout: left text + right icon card -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left -->
            <div>
                <!-- Logo -->
                <div class="mb-6">
                    <img src="/logo/logo_fec.png" alt="FEC Logo" class="h-16 w-auto object-contain rounded-2xl">
                </div>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight mb-4">
                    FEC <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">API Gateway</span>
                </h1>
                <p class="text-base text-slate-400 leading-relaxed mb-6">
                    Interface de programmation backend du <strong class="text-slate-200">Système de Gestion des Attestations et Formations</strong>
                    de Free Engineering Consultancy. Réservée aux applications autorisées.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ config('app.frontend_url', '#') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all text-sm shadow-lg shadow-blue-600/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Accéder à l'application
                    </a>
                    <a href="{{ config('app.frontend_url', '#') }}/verify" target="_blank"
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 glass hover:bg-white/5 text-slate-200 font-semibold rounded-xl transition-all text-sm">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Vérifier une attestation
                    </a>
                </div>
            </div>

            <!-- Right: info cards -->
            <div class="grid grid-cols-2 gap-3">
                <div class="glass rounded-2xl p-5">
                    <div class="text-2xl font-black text-blue-400 mb-1">REST</div>
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
                    <p class="text-xs text-slate-500 mt-2">Admin · Gestionnaire</p>
                </div>
                <div class="glass rounded-2xl p-5">
                    <div class="text-2xl font-black text-amber-400 mb-1">PHP 8.3</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Runtime</div>
                    <p class="text-xs text-slate-500 mt-2">Laravel 11 · MySQL 8</p>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- ══════════════════ LÉGAL ══════════════════ -->
<section id="legal" class="py-16 px-4 sm:px-6 bg-slate-900/25">
    <div class="max-w-7xl mx-auto">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">Informations légales</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 mb-8">Politique & Conditions</h2>

        <!-- Two-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <!-- Conditions d'utilisation -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/15 border border-blue-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">CGU</span>
                        <h3 class="font-bold text-white text-sm leading-tight">Conditions d'utilisation</h3>
                    </div>
                </div>
                <ul class="text-xs text-slate-400 space-y-2 leading-relaxed">
                    <li>· Cette API est <strong class="text-slate-300">strictement réservée</strong> aux applications internes de FEC et partenaires autorisés par écrit.</li>
                    <li>· Toute tentative d'accès non autorisé est interdite et passible de poursuites judiciaires.</li>
                    <li>· L'exploitation commerciale sans accord préalable de FEC est prohibée.</li>
                    <li>· FEC se réserve le droit de révoquer tout accès sans préavis en cas d'usage abusif.</li>
                    <li>· Les tokens d'accès sont personnels et confidentiels — ne pas les partager.</li>
                </ul>
            </div>

            <!-- Politique de confidentialité -->
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
                    <li>· Les données personnelles (nom, email, fonction) sont collectées uniquement dans le cadre de la gestion des formations.</li>
                    <li>· Elles ne sont <strong class="text-slate-300">jamais revendues</strong> ni cédées à des tiers.</li>
                    <li>· Droit d'accès, de rectification et de suppression garanti pour chaque utilisateur.</li>
                    <li>· Données hébergées sur des serveurs sécurisés en Europe (OVH).</li>
                    <li>· Contact DPO : <a href="mailto:infos@feconsultancy.com" class="text-blue-400 hover:underline">infos@feconsultancy.com</a></li>
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
                    <li>· Ce logiciel est la <strong class="text-slate-300">propriété exclusive</strong> de Free Engineering Consultancy (FEC). © {{ date('Y') }}.</li>
                    <li>· Tous droits réservés. Reproduction interdite sans autorisation écrite de FEC.</li>
                    <li>· Développé sur Laravel (MIT) · React.js (MIT) · TailwindCSS (MIT).</li>
                    <li>· Toute décompilation, rétro-ingénierie ou redistribution est strictement interdite.</li>
                    <li>· Usage à des fins d'audit de sécurité soumis à autorisation préalable.</li>
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
                    <li>· <strong class="text-slate-300">30 req/min</strong> sur la vérification publique d'attestations.</li>
                    <li>· Communications chiffrées en TLS 1.2+.</li>
                    <li>· Mots de passe hachés avec Bcrypt (12 rounds). Tokens Sanctum à durée de vie limitée.</li>
                </ul>
            </div>
        </div>

        <!-- Responsabilité — full width -->
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
                FEC s'engage à maintenir ce service opérationnel avec un objectif de disponibilité de <strong class="text-slate-300">99 %</strong>.
                Des maintenances programmées peuvent temporairement interrompre le service. FEC décline toute responsabilité pour les
                dommages indirects liés à une interruption de service. Les données générées (QR codes, PDFs) sont conservées conformément
                aux obligations légales camerounaises. Toute réclamation doit être adressée à
                <a href="mailto:infos@feconsultancy.com" class="text-blue-400 hover:underline">infos@feconsultancy.com</a>.
                Juridiction compétente : Tribunaux de Douala, Cameroun.
            </p>
        </div>
    </div>
</section>

<hr class="section-divider">

<!-- ══════════════════ CONTACT ══════════════════ -->
<section id="contact" class="py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Contact</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 mb-8">Free Engineering Consultancy</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-xs text-slate-400 leading-relaxed">
                    2ième et 3ième Étage N°1158<br>Immeuble BS, Face Ancienne Direction NOBRA<br>
                    Rue Pasteur EBOUBE MBENGUE Akwa<br>
                    <strong class="text-slate-300">BP 68 Douala, Cameroun</strong>
                </p>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <p class="text-xs text-slate-400 leading-relaxed">
                    <strong class="text-slate-300">Tél :</strong> (237) 677 145 068<br>
                    692 664 980 &nbsp;·&nbsp; 691 902 153<br>
                    <span class="text-emerald-400">670 407 656 (WhatsApp)</span>
                </p>
            </div>
            <div class="glass rounded-2xl p-5 text-center">
                <svg class="w-5 h-5 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <a href="mailto:infos@feconsultancy.com" class="text-blue-400 hover:underline block text-xs mb-1">infos@feconsultancy.com</a>
                <a href="mailto:guyalain.ngounou@feconsultancy.com" class="text-blue-400 hover:underline block text-xs">guyalain.ngounou@feconsultancy.com</a>
                <div class="mt-3 border-t border-white/5 pt-3 space-y-1">
                    <p class="text-[10px] text-slate-500">RC N° A/033328</p>
                    <p class="text-[10px] text-slate-500">Contribuable P016800300941S</p>
                    <p class="text-[10px] text-slate-500">Arrêté N°00000481/MINEFOP</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="py-6 px-4 border-t border-white/5 bg-[#070d1a]">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
        <span>© {{ date('Y') }} <strong class="text-slate-500">Free Engineering Consultancy (FEC)</strong>. Tous droits réservés.</span>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ config('app.frontend_url', '#') }}" target="_blank" class="hover:text-slate-400 transition-colors">Application</a>
            <a href="{{ config('app.frontend_url', '#') }}/verify" target="_blank" class="hover:text-slate-400 transition-colors">Vérification</a>
            <span class="text-slate-800">·</span>
            <span class="font-mono text-slate-700">{{ config('app.url') }}</span>
        </div>
    </div>
</footer>

</body>
</html>
