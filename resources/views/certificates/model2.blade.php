<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attestation</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @page {
    size: A4 portrait;
    margin: 0;
  }

  @font-face {
    font-family: 'Comic Sans MS';
    src: url('{{ public_path("fonts/comic/comic.ttf") }}') format("truetype");
    font-weight: normal;
    font-style: normal;
  }

  @font-face {
    font-family: 'Comic Sans MS';
    src: url('{{ public_path("fonts/comic/comicbd.ttf") }}') format("truetype");
    font-weight: bold;
    font-style: normal;
  }

  @font-face {
    font-family: 'Comic Sans MS';
    src: url('{{ public_path("fonts/comic/comici.ttf") }}') format("truetype");
    font-weight: normal;
    font-style: italic;
  }

  @font-face {
    font-family: 'Comic Sans MS';
    src: url('{{ public_path("fonts/comic/comicz.ttf") }}') format("truetype");
    font-weight: bold;
    font-style: italic;
  }

  /* ── POLICE ARIAL pour la section pédagogique ── */
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
    font-family: 'Comic Sans MS';
    font-style: normal;
  }

  .page {
    background: #ffffff; /* Extérieur blanc */
    width: 210mm;
    height: 297mm;
    position: relative; /* Gras par défaut sur tout le document */
    color: #000;
    overflow: hidden;
  }

  /* Double bordure verte pour DomPDF */
  .outer-border {
    position: absolute;
    top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
    border: 1px solid #279427; /* Vert foncé */
  }

  .inner-border {
    position: absolute;
    top: 13mm; left: 13mm; right: 13mm; bottom: 13mm;
    border: 6px solid #279427; /* Bordure verte épaisse */
    background: #f0fff0; /* Fond intérieur vert très léger */
  }

  .content-wrapper {
    position: absolute;
    top: 16mm; left: 18mm; right: 18mm; bottom: 16mm;
    z-index: 3;
  }

  /* ── HEADER REF ── */
  .header-ref {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 8px;
  }

  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
  }

  .header-table td {
    vertical-align: top;
  }

  /* Encadré légal vert */
  .legal-box {
    background: #84D699; /* vert ciel */
    border: 4px solid #ffffff; /* cadre epais blanc */
    padding: 6px 9px;
    font-weight: bold;
    font-size: 7.5px; /* Taille 7.5 demandée */
    text-align: center;
    line-height: 0.8;
    width: 260px; /* Ajusté pour correspondre à la phrase demandée en taille 6 */
    margin-left: 0; /* Aligné à gauche, au niveau du Ref */
  }

  /* ── TITRE PRINCIPAL gras ── */
  .main-title {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 16px 0 14px 0;
    line-height: 1;
  }

  /* ── CORPS  ── */
  .body-text {
    font-style: normal;
    font-size: 19px;
    line-height: 1;
    text-align: justify;
    margin-bottom: 12px;
  }

  .fec-name { font-weight: bold; font-style: italic; }
  .person-name { font-weight: bold; font-style: italic; text-decoration: underline; color: #00634F; /* Vert ciel foncé pour lisibilité */ }
  .permit { font-weight: bold; font-style: italic; text-decoration: underline; color: #000; }
  .employer { font-weight: bold; font-style: italic; text-decoration: underline; color: #00634F; /* Vert ciel demandé */ }

  /* ── TITRE FORMATION : 18px, gras ── */
  .formation-title {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 12px 0 10px 0;
    line-height: 1;
  }

  /* ── LISTE DÉTAILS : 14px, normal ── */
  .details-list {
    list-style: none;
    margin: 0 0 8px 22px;   /* 22px = aligne le texte avec les objectifs */
    font-size: 14px;
    line-height: 1.4;       /* +0.4 espacement */
  }
  .details-list li { position: relative; padding-left: 12px; }
  .details-list li::before { content: '-'; position: absolute; left: 0; }

  /* ── PÉDAGOGIQUE : taille réduite ── */
  .pedagogique-title {
    font-size: 14px;
    font-weight: bold;
    font-style: normal;
    margin: 8px 0 6px 0;
  }

  /* ── TITRES MODULES : 12px, gras ── */
  .module-title {
    font-size: 6px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 8px 0 4px 0;
    line-height: 0.6;
  }

  .module-table {
    width: 100%;
    border-collapse: collapse;
    line-height: 0.9;  /* +0.3 pour espacer les objectifs */
    font-size: 6px;
  }

  .module-table td {
    vertical-align: top;
    line-height: 0.9;  /* +0.3 */
    font-size: 6px;
  }

  /* ── SIGNATURE ── */
  .signature-section {
    margin-top: 16px;
    padding-left: 26px;
    line-height: 1;
  }
  .fait-a {
    font-size: 15px;
    font-weight: bold;
    font-style: normal;
    margin-bottom: 2px;
  }
  .fait-a .city    { font-style: italic; font-weight: bold; color: #cc0000; }
  .fait-a .dateval { font-style: italic; font-weight: bold; color: #cc0000; }

  .validite {
    font-size: 15px;
    font-weight: bold;
    font-style: italic;
    color: #cc0000;
  }

  .direction {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    font-style: normal;
    text-decoration: underline;
    letter-spacing: 1px;
  }

  /* ── FOOTER : Arial uniquement, bleu ── */
  .footer {
    position: absolute;
    bottom: 140px;
    left: 0;
    right: 0;
    font-size: 7.5px;
    text-align: center;
    font-weight: normal;
    line-height: 1;
    color: #000080;
  }
  .footer a { color: #000080; text-decoration: none; }
  .signature-img { max-height: 150px; width: auto; }
</style>
</head>
<body>
<div class="page">

    <div class="outer-border"></div>
    <div class="inner-border"></div>

    <div class="content-wrapper">

        <!-- LIGNE HEADER -->
        <table class="header-table" style="margin-bottom: 0;">
            <tr>
            <td style="width: 28%; text-align: left; vertical-align: top; padding-top: 15px;">
                <!-- RÉFÉRENCE -->
                <div class="header-ref" style="color: #000000;">Ref : {{ $certificate->reference }}</div>
            </td>

            <td style="width: 50%; text-align: center; vertical-align: top;">
                <!-- Logo FEC et Picto 1 Centrés sur la même ligne avec table -->
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                    <tr>
                        <td style="text-align: @if(isset($pictoBase64) && $pictoBase64) right @else center @endif; vertical-align: middle; width: 50%;">
                            <img src="{{ public_path('logo/logo_fec.png') }}" style="max-height:130px; max-width:130px;" alt="Logo FEC" />
                        </td>
                        @if(isset($pictoBase64) && $pictoBase64)
                        <td style="text-align: left; vertical-align: middle; padding-left: 15px; width: 50%;">
                            <img src="{{ $pictoBase64 }}" style="max-height:110px; max-width:110px; opacity: 0.9;" />
                        </td>
                        @endif
                    </tr>
                </table>
            </td>

            <td style="width: 22%; text-align: right; vertical-align: top; padding-right: 15px;">
                <!-- QR Code SVG encadré blanc -->
                <div style="display: inline-block; text-align: center; background:#ffffff; border: 4px solid #ffffff; padding: 4px; margin-top: 15px;">
                    <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="100" height="100" style="display:block; margin: 0 auto;" />
                </div>
            </td>
            </tr>
        </table>

        <!-- TITRE PRINCIPAL : 18px gras, sans card vert -->
        <div class="main-title" style="margin: 5px auto 10px auto;">
            {{ $session->formation->typeAttestation->name ?? "CERTIFICAT D'APTITUDE A LA CONDUITE EN SECURITE" }}
        </div>

            <!-- CORPS : 21px -->
            <div class="body-text">
                Nous, soussignons <span class="fec-name">FREE ENGINEERING CONSULTANCY (FEC), Centre de Formation Professionnelle et Rapide en Qualité, Sécurité &amp; Environnement (QSE)</span> certifions que <span class="person-name">Mr/Mme {{ $participant->full_name }}</span> Personnel de <span class="employer">{{ $participant->company }}</span> régulièrement suivi l’action de formation suivante :
            </div>

            <div class="formation-title">
                {{ $session->formation->intitule ?? "FORMATION INITIALE OU RECYCLAGE UTILISATION SECURITAIRE DES GRUES AUXILIAIRES SMS53 'CACES R390'" }}
            </div>

            <ul class="details-list">
                <li>Dates de la formation : {{ $training->start_date }} @if($training->end_date) au {{ $training->end_date }} @endif</li>
                <li>Durée : {{ $training->duration }}</li>
                <li>Lieu de réalisation de la formation : {{ $training->location }}</li>
            </ul>

            @if(isset($objectifsGroups) && count($objectifsGroups) > 0)
            @php
                $totalItems = 0;
                foreach($objectifsGroups as $cat => $objs) {
                    $totalItems += 1;
                    $totalItems += count($objs);
                }

                // Tailles dynamiques pour que ça rentre sur la page A4
                $titleSize = '14px';
                $objSize = '13px';
                if ($totalItems > 12) {
                    $titleSize = '13px';
                    $objSize = '12px';
                }
                if ($totalItems > 20) {
                    $titleSize = '12px';
                    $objSize = '11px';
                }
                if ($totalItems > 28) {
                    $titleSize = '9px';
                    $objSize = '8px';
                }
            @endphp
            <!-- PÉDAGOGIQUE -->
            <div class="pedagogique-title" style="font-family: 'Arial', sans-serif;">Déroulement pédagogique théorique et pratique :</div>

            <table class="module-table" style="position: relative;">
                <tr>
                <td style="padding-right: 12px; font-family: 'Arial', sans-serif;">
                    <!-- GROUPES D'OBJECTIFS : numérotation globale, sans titre de catégorie -->
                    @php $globalCounter = 1; @endphp
                    @foreach($objectifsGroups as $category => $objectifs)
                        {{-- Catégorie masquée intentionnellement --}}
                        <ul style="list-style-type: none; margin: 0; padding: 0 0 0 12px;">
                        @foreach($objectifs as $obj)
                            <li style="font-size: {{ $objSize }}; font-family: 'Arial', sans-serif; position: relative; padding-left: 22px; margin-bottom: 2px;">
                                <span style="position: absolute; left: 0; font-family: 'Arial', sans-serif;">{{ $globalCounter }}- </span> {{ $obj->intitule }}
                            </li>
                            @php $globalCounter++; @endphp
                        @endforeach
                        </ul>
                    @endforeach
                </td>

                <!-- Pictogramme 2 correspondant au file2 en background de cette zone (côté droit centree) -->
                <td style="width: 192px; text-align: right; vertical-align: center;">
                    @if(isset($picto2Base64) && $picto2Base64)
                    <img src="{{ $picto2Base64 }}" style="max-width:192px; opacity: 0.9;" />
                    @endif
                </td>
                </tr>
            </table>
            @endif

            <!-- SIGNATURE -->
            <table style="width: 100%; margin-top: 5px;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-left: 34px;"> {{-- 34px = aligne avec le texte des objectifs (ul 12px + li 22px) --}}
                        @php
                            $dateFaitA = \Carbon\Carbon::parse($session->date_fin);
                            $dateValidite = $session->formation->typeAttestation
                                ? $session->formation->typeAttestation->computeExpirationDate($dateFaitA)
                                : $dateFaitA->copy()->addYears(1);
                        @endphp
                        <!-- "Fait à" : normal, ville+date en italique rouge -->
                        <div class="fait-a">
                            Fait à <span class="city">{{ $certificate->city }}</span> le {{ $dateFaitA->format('d') }} <span class="dateval">{{ ucfirst($dateFaitA->locale('fr')->translatedFormat('F Y')) }}</span>
                        </div>
                        <!-- "Fin de Validité" : gras italique rouge -->
                        <div class="validite">Fin de Validité : {{ $dateValidite->format('d') }} {{ ucfirst($dateValidite->locale('fr')->translatedFormat('F Y')) }}</div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: bottom;">
                        <div style="display: inline-block; text-align: center;">
                            <!-- "La Direction" : gras souligné -->
                            <div class="direction" style="margin-bottom: 5px;">La Direction</div>
                            @if(!empty($session->formation->typeAttestation->file_signature))
                                <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" class="signature-img" />
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <div class="footer">
                2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa ; BP 68 Douala ; Tél. : (237) 677145068; 692664980; 691902153 ;<br>
                670407656 (WhatSapp) / RC N° A/033328 ; Arrêté N°00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021<br>
                N° de Contribuable P016800300941S / Email : <a href="mailto:infos@feconsultancy.com" style="text-decoration: underline;">infos@feconsultancy.com</a> &amp; <a href="mailto:guyalain.ngounou@feconsultancy.com" style="text-decoration: underline;">guyalain.ngounou@feconsultancy.com</a>/
            </div>

        </div> <!-- Fin de content-wrapper -->
    </div>

</body>
</html>
