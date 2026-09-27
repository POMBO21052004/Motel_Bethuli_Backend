<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Attestation - Modèle 4</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @page {
    size: A4 portrait;
    margin: 0;
  }

  /* ── POLICE ARIAL ── */
  @font-face {
    font-family: 'Arial';
    src: url('{{ public_path("fonts/arial/arial.ttf") }}') format("truetype");
    font-weight: normal;
    font-style: normal;
  }
  @font-face {
    font-family: 'Arial';
    src: url('{{ public_path("fonts/arial/arialbd.ttf") }}') format("truetype");
    font-weight: bold;
    font-style: normal;
  }
  @font-face {
    font-family: 'Arial';
    src: url('{{ public_path("fonts/arial/ariali.ttf") }}') format("truetype");
    font-weight: normal;
    font-style: italic;
  }
  @font-face {
    font-family: 'Arial';
    src: url('{{ public_path("fonts/arial/arialbi.ttf") }}') format("truetype");
    font-weight: bold;
    font-style: italic;
  }

  body {
    margin: 0;
    padding: 0;
    width: 210mm;
    height: 297mm;
    background: #ffffff;
    font-family: 'Arial', sans-serif;
    color: #000;
    position: relative;
    overflow: hidden;
  }

  /* ── BORDURES ── */
  .border-outer {
    position: absolute;
    top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
    border: 5px solid #cc0000;
    z-index: 2;
  }
  .border-inner {
    position: absolute;
    top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
    border: 1px solid #cc0000;
    z-index: 3;
  }

  /* ── IMAGE TRIANGLE ROUGE ── */
  .red-triangle-img {
    position: absolute;
    top: -5mm;
    left: -9mm;
    width: 220mm;
    height: auto;
    z-index: 1;
  }

  /* ── CONTENU PRINCIPAL ── */
  .content-wrapper {
    position: absolute;
    top: 15mm; left: 15mm; right: 15mm; bottom: 15mm;
    z-index: 10;
  }

  /* ── HEADER : Ref | Logo | QR ── */
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;  /* espace header ↔ reste du contenu */
    padding-bottom: 6px;
  }
  .header-table td {
    vertical-align: middle;
  }

  /* ── TITRE DU TYPE D'ATTESTATION — NOIR ── */
  .main-title {
    text-align: center;
    color: #000000;
    font-size: 24px;
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    text-transform: uppercase;
    margin: -10px auto 20px auto;
  }

  /* ── TEXTE PRINCIPAL — paragraphes retrait 3cm ── */
  .main-text-block {
    font-size: 19px;
    font-family: 'Arial', sans-serif;
    line-height: 1.2;
    text-align: justify;
    margin-bottom: 6px;
  }
  .main-text-block .para {
    text-indent: 1.3cm;
    margin-bottom: 4px;
  }
  .main-text-block .participant-name {
    font-weight: bold;
    text-decoration: underline;
  }
  .main-text-block .formation-title {
    font-weight: bold;
    text-transform: uppercase;
  }
  .main-text-block .ref-bold {
    font-weight: bold;
  }

  .attestation-footer-block {
    font-size: 19px;
    font-family: 'Arial', sans-serif;
    margin-top: 18px;
    margin-bottom: 14px;  /* espace haut→milieu */
  }
  /* "Fait à" et "Fin de validité" : Comic Sans, décalage 1.3cm comme les paragraphes */
  .date-validity-block {
    font-size: 15px;
    font-family: 'Comic Sans MS', 'Comic Sans', cursive;
    margin-left: 1.3cm;
    margin-top: 0;
    line-height: 1.0;  /* colle Fait à et Fin Validité ensemble */
  }
  .date-validity-block .city-green {
    color: #228B22;
    font-weight: bold;
  }
  .date-validity-block .date-green {
    color: #228B22;
    font-weight: bold;
  }
  .date-validity-block .fin-validite-line {
    color: #cc0000;
    font-weight: bold;
  }

  /* ── SECTION SIGNATURE (Tableau 2 colonnes) ── */
  .signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
    margin-bottom: 0;
  }
  .signature-table td {
    vertical-align: top;
  }
  .signature-table .col-sign {
    width: 40%;
    text-align: left;
    padding-left: 30px;
  }

  /* ── PROGRAMME DE FORMATION ── */
  .programme-title {
    font-size: 17px;
    font-family: 'Arial', sans-serif;
    font-weight: bold;
    text-decoration: underline;
    margin-top: -50px;  /* même espace milieu→bas que haut→milieu */
    margin-bottom: 2px;
  }
  .programme-body {
    font-size: 13px;
    font-family: 'Arial', sans-serif;
    line-height: 1.15;
  }
  /* Catégorie : surlignée (fond jaune) + soulignée, sans bordure */
  .category-title {
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    margin-top: 2px;
    margin-bottom: 0px;
    background-color: #FFD700; /* surligneur jaune */
    text-decoration: underline;
    padding: 0px 3px;
    display: inline-block;
  }
  .programme-body ol {
    margin: 0;
    padding-left: 1cm;
    margin-bottom: 1px;
  }
  .programme-body ol li {
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    font-size: 15px;  /* +1 soit 15px */
    margin-bottom: 0;
    line-height: 1.1;
    text-align: justify;
  }

  .pictos-row {
    width: 60%;
    margin: 22px auto 5px auto;  /* descend les pictos */
    border-collapse: collapse;
  }
  .pictos-row td {
    text-align: center;
    vertical-align: middle;
    padding: 0 20px;
  }

  /* ── FOOTER ── */
  .footer {
    position: absolute;
    bottom: 15mm;
    left: 12mm;
    right: 12mm;
    text-align: center;
    font-size: 8px;
    font-family: 'Arial', sans-serif;
    color: #000080;
    line-height: 1.2;
    z-index: 10;
  }
  /* Emails soulignés en bleu */
  .footer a {
    color: #0000EE;
    text-decoration: underline;
    font-family: 'Arial', sans-serif;
  }

</style>
</head>
<body>

    {{-- ── BORDURES ── --}}
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    {{-- ── IMAGE TRIANGLE ROUGE ── --}}
    <img src="{{ public_path('images/header_red_4.png') }}" class="red-triangle-img" alt="" />

    <div class="content-wrapper">


        {{-- ── RÉFÉRENCE : collée au coin sup. gauche du cadre intérieur ── --}}
        <div style="position: absolute; top: 0; left: 0;
                    font-size: 13px; font-weight: bold; color: #000;
                    line-height: 1.4; font-family: 'Arial', sans-serif;">
            Réf. : <span style="font-size: 14px;">{{ $certificate->reference }}</span>
        </div>

        {{-- ── HEADER : Logo | QR Code ── --}}
        <table class="header-table">
            <tr>
                <td style="width: 25%; text-align: left; vertical-align: middle;">

                </td>

                {{-- Logo à gauche (centré sur 60%) --}}
                <td style="width: 50%; text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/logo_fec.png') }}" style="max-height: 150px; max-width: 150px;" alt="Logo FEC" />
                </td>

                {{-- QR Code à droite — même taille que le logo --}}
                <td style="width: 25%; text-align: right; vertical-align: middle;">
                    <div style="display: inline-block; background: #fff; padding: 3px;">
                        <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="120" height="120" style="display:block;" />
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── TITRE DU TYPE D'ATTESTATION — NOIR ── --}}
        <div class="main-title">
            {{ $session->formation->typeAttestation->name ?? "ATTESTATION DE FORMATION" }}
        </div>

        {{-- ── CORPS : TEXTE D'ATTESTATION ── --}}
        @php
            $dateDebut    = \Carbon\Carbon::parse($session->date_debut)->locale('fr');
            $dateFin      = \Carbon\Carbon::parse($session->date_fin)->locale('fr');
            $dateValidite = $session->formation->typeAttestation
                ? $session->formation->typeAttestation->computeExpirationDate($dateFin)
                : $dateFin->copy()->addYears(1);
            $dateValidite->locale('fr');
            $dateFaitA = $dateFin->copy();
        @endphp

        {{-- Paragraphe 1 — retrait 3cm --}}
        <div class="main-text-block">
            <div class="para">
                Nous <span class="ref-bold" style="color: #000000;">FREE ENGINEERING CONSULTANCY (FEC)</span> Centre de Formation en Qualité, Sécurité &amp; Environnement (QSE) attestons que :
            </div>
            {{-- Paragraphe 2 — retrait 3cm --}}
            <div class="para">
                <span class="participant-name">{{ $participant->genre ?? 'Mr/Mme' }} {{ $participant->full_name }}</span>
                a participé en Qualité de Salarié à l'entreprise <span class="ref-bold">{{ $participant->company }}</span>
                à la session de formation sur le thème <span class="formation-title">"{{ $session->formation->intitule }}"</span>
                référence <span class="ref-bold">SMS025</span>
                en date du <span class="ref-bold">{{ $dateDebut->translatedFormat('d F Y') }}</span>
                dans les locaux de l'Entreprise.
            </div>
        </div>

        {{-- ── "Attestation établie..." même taille corps ── --}}
        <div class="attestation-footer-block">
            Attestation établie pour servir et valoir ce que de droit.
        </div>

        {{-- ── Fait à / Fin de Validité : poussés à droite (3cm), taille -2 ── --}}
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <div class="date-validity-block">
                        Fait à <span class="city-green">{{ $certificate->city }}</span>
                        le <span class="date-green">{{ $dateFin->translatedFormat('d F Y') }}</span><br>
                        <span class="fin-validite-line">Fin de Validité : {{ $dateValidite->translatedFormat('d F Y') }}</span>
                    </div>
                </td>
                <td class="col-sign">
                    @if(!empty($session->formation->typeAttestation->file_signature))
                    <img
                        src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}"
                        style="max-height: 140px; max-width: 220px;"
                        alt="Signature"
                    />
                    @endif
                </td>
            </tr>
        </table>

        {{-- ── PROGRAMME DE FORMATION ── --}}
        <div class="programme-title">Programme de Formation :</div>
        <div class="programme-body">
            @if(isset($objectifsGroups) && count($objectifsGroups) > 0)
                @foreach($objectifsGroups as $category => $objectifs)
                    {{-- Catégorie encadrée de bordures jaune --}}
                    <div class="category-title">{{ $category }} :</div>
                    <ol>
                        @foreach($objectifs as $obj)
                            <li>{{ $obj->intitule }}</li>
                        @endforeach
                    </ol>
                @endforeach
            @else
                <div class="category-title">Partie théorique :</div>
                <ol>
                    <li>Rappel sur la sécurité incendie.</li>
                    <li>Identifier les moyens de secours (Extincteurs ; RIA).</li>
                    <li>Les classes de feux.</li>
                    <li>Les causes d'incendie.</li>
                </ol>
                <div class="category-title">Partie Pratique :</div>
                <ol>
                    <li>Exercices d'extinction sur feux réels.</li>
                    <li>(Types de feux suivant demande pour les risques spécifiques)</li>
                    <li>Manipulation pratique d'extincteurs et RIA par chaque stagiaire.</li>
                </ol>
            @endif
        </div>

        {{-- ── PICTOGRAMMES ── --}}
        @if(!empty($pictoBase64) || !empty($picto2Base64))
        <table class="pictos-row">
            <tr>
                @if(!empty($pictoBase64))
                <td>
                    <img src="{{ $pictoBase64 }}" alt="Pictogramme 1" style="max-height:160px; max-width:180px;" />
                </td>
                @endif
                @if(!empty($picto2Base64))
                <td>
                    <img src="{{ $picto2Base64 }}" alt="Pictogramme 2" style="max-height:160px; max-width:180px;" />
                </td>
                @endif
            </tr>
        </table>
        @endif

    </div>{{-- fin content-wrapper --}}

    {{-- ── FOOTER — emails soulignés en bleu ── --}}
    <div class="footer">
        2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa ; BP 68 Douala ; Tél. : (237) 677145068; 692664980; 691902153 ;<br>
        670407656 (WhatSapp) / RC N° A/033328 ; Arrêté N°00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021<br>
        N° de Contribuable P016800300941S / Email : <a href="mailto:infos@feconsultancy.com">infos@feconsultancy.com</a> &amp; <a href="mailto:guyalain.ngounou@feconsultancy.com">guyalain.ngounou@feconsultancy.com</a>/
    </div>

</body>
</html>
