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

  /* Double bordure bleue pour DomPDF */
  .outer-border {
    position: absolute;
    top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
    border: 1px solid #1a3a8c;
  }

  .inner-border {
    position: absolute;
    top: 14mm; left: 14mm; right: 14mm; bottom: 14mm; /* Calculé depuis la page */
    border: 6px solid #1a3a8c; /* Bordure plus épaisse */
    background: #dce8f5; /* Fond intérieur vert ciel / bleuté */
  }

  .content-wrapper {
    position: absolute;
    top: 18mm; left: 20mm; right: 20mm; bottom: 20mm;
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
    background: #c8e6b4; /* vert ciel */
    border: 3px solid #ffffff; /* cadre epais blanc */
    padding: 6px 9px;
    font-size: 7px; /* Taille 6 demandée */
    text-align: center;
    line-height: 1;
    width: 240px; /* Ajusté pour correspondre à la phrase demandée en taille 6 */
    margin-left: 0; /* Aligné à gauche, au niveau du Ref */
  }

  /* ── TITRE PRINCIPAL : 18px, gras ── */
  .main-title {
    text-align: center;
    font-size: 25px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 16px 0 14px 0;
    line-height: 1;
  }

  /* ── CORPS : 21px ── */
  .body-text {
    font-style: normal;
    font-size: 19px;
    line-height: 1;
    text-align: justify;
    margin-bottom: 12px;
  }

  .fec-name { font-weight: bold; font-style: italic; font-size: 14px; }
  .person-name { font-weight: bold; font-style: italic; text-decoration: underline; color: #7faa5a; /* Vert ciel foncé pour lisibilité */ }
  .permit { font-weight: bold; font-style: italic; text-decoration: underline; color: #000; }
  .employer { font-weight: bold; font-style: italic; text-decoration: underline; color: #7faa5a; /* Vert ciel demandé */ }

  /* ── TITRE FORMATION : 14px, gras ── */
  .formation-title {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 12px 0 10px 0;
    line-height: 1;
  }

  /* ── LISTE DÉTAILS : 14px, normal ── */
  .details-list {
    list-style: none;
    margin: 0 0 8px 14px;
    font-size: 14px;
    line-height: 1;
  }
  .details-list li { position: relative; padding-left: 12px; }
  .details-list li::before { content: '-'; position: absolute; left: 0; }

  /* ── PÉDAGOGIQUE : 14px, gras ── */
  .pedagogique-title {
    font-size: 15px;
    font-weight: bold;
    font-style: normal;
    margin: 8px 0 6px 0;
  }

  /* ── TITRES MODULES : 12px, gras ── */
  .module-title {
    font-size: 13px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 8px 0 4px 0;
  }

  .module-table {
    width: 100%;
    border-collapse: collapse;
  }

  .module-table td {
    vertical-align: top;
  }

  /* ── SIGNATURE ── */
  .signature-section {
    margin-top: 16px;
    padding-left: 26px;
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

  /* ── FOOTER : Arial uniquement, bleu, 6px ── */
  .footer {
    position: absolute;
    bottom: 150px;
    left: 0;
    right: 0;
    font-size: 7px;
    text-align: center;
    font-weight: normal;
    line-height: 1.5;
    color: #1a3a8c;
  }
  .footer a { color: #1a3a8c; text-decoration: none; }
</style>
</head>
<body>
<div class="page">

    <div class="outer-border"></div>
    <div class="inner-border"></div>

    <div class="content-wrapper">

        <!-- RÉFÉRENCE -->
        <div class="header-ref">Ref : {{ $certificate->reference }}</div>

        <!-- LIGNE HEADER -->
        <table class="header-table">
            <tr>
            <td style="text-align: left;">
                <!-- Encadré légal vert -->
                <div class="legal-box">
                Vu l'article R.233-13-19 du Code du Travail ;<br>
                Vu l'arrêté Ministériel du 2 Décembre 1998<br>
                Vu les Recommandations CNAM R/383 modifiées ;<br>
                Vu l'Arrêté N*00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC<br>
                du 19 Novembre 2021 autorisant FEC à Exercer en Qualité de<br>
                Centre de Formation Professionnel Rapide
                </div>
            </td>

            <td style="width: 28%; text-align: center; vertical-align: middle;">
                <!-- Logo FEC -->
                <img src="{{ public_path('logo/logo.png') }}" style="max-height:92px; max-width:135px;" alt="Logo FEC" />
            </td>

            <td style="width: 33%; text-align: right; vertical-align: top; padding-right: 15px;">
                <!-- QR Code SVG -->
                <div style="display: inline-block; text-align: center;">
                <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="96" height="96" style="display:block; margin: 0 auto;" />
                </div>
            </td>
            </tr>
        </table>

        <!-- TITRE PRINCIPAL : 18px gras -->
        <div class="main-title">{{ $session->formation->typeAttestation->name ?? "CERTIFICAT D'APTITUDE A LA CONDUITE EN SECURITE" }}</div>

            <!-- CORPS : 21px -->
            <div class="body-text">
                Nous, soussignons <span class="fec-name">FREE ENGINEERING CONSULTANCY (FEC), Centre de Formation Professionnelle et Rapide en Qualité, Sécurité &nbsp;&nbsp; &amp; Environnement (QSE)</span> certifions que <span class="person-name">{{ $participant->full_name }}</span> @if(!empty($participant->numero_permis) || ($participant->license_categories && $participant->license_categories !== 'Non renseigné')) détenteur de Permis de <span class="permit">@if(!empty($participant->numero_permis)) N° {{ $participant->numero_permis }} @endif @if($participant->license_categories && $participant->license_categories !== 'Non renseigné') de type {{ $participant->license_categories }}@endif</span>@endif Salarié de <span class="employer">{{ $participant->company }}</span> a régulièrement suivi l'action de formation suivante :
                {{ $session->formation->intitule ?? "FORMATION INITIALE OU RECYCLAGE UTILISATION SECURITAIRE DES GRUES AUXILIAIRES SMS53 'CACES R390'" }}
            </div>

            <!-- DÉTAILS : 14px normal -->
            <ul class="details-list">
                <li>Dates de la formation : {{ $training->start_date }} @if($training->end_date) au {{ $training->end_date }} @endif</li>
                <li>Durée : {{ $training->duration }}</li>
                <li>Lieu de réalisation de la formation : {{ $training->location }}</li>
            </ul>

            @if(!empty($session->formation->objectifs))
            <!-- PÉDAGOGIQUE : 14px gras -->
            <div class="pedagogique-title">Déroulement pédagogique théorique et pratique :</div>

            <table class="module-table">
                <tr>
                <td style="padding-right: 15px;">
                    <!-- BULLETS : 13px normal -->
                    <div style="font-size: 13px; line-height: 1.75; margin-left: 6px;">
                    {!! nl2br(e($session->formation->objectifs)) !!}
                    </div>
                </td>

                <!-- Pictogramme 1 correspondant au file1 -->
                <td style="width: 192px; text-align: right;">
                    @if(!empty($session->formation->typeAttestation->file1))
                    <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file1) }}" style="max-width:192px;" />
                    @else
                    {{--  vide  --}}
                    @endif
                </td>
                </tr>
            </table>
            @endif

            <!-- SIGNATURE -->
            <div class="signature-section">
                <!-- "Fait à" : normal, ville+date en italique rouge -->
                <div class="fait-a">
                Fait à <span class="city">{{ $certificate->city }}</span> le {{ \Carbon\Carbon::parse($attestation->date_delivrance)->format('d') }} <span class="dateval">{{ ucfirst(\Carbon\Carbon::parse($attestation->date_delivrance)->translatedFormat('F Y')) }}</span>
                </div>
                <!-- "Fin de Validité" : gras italique rouge -->
                <div class="validite">Fin de Validité : {{ \Carbon\Carbon::parse($attestation->date_delivrance)->addYears(1)->format('d') }} {{ ucfirst(\Carbon\Carbon::parse($attestation->date_delivrance)->addYears(1)->translatedFormat('F Y')) }}</div>
                <!-- "La Direction" : gras souligné -->
                <div class="direction">La Direction</div>
                @if(!empty($session->formation->typeAttestation->file_signature))
                <div style="text-align:center; margin-top: 10px;">
                    <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" style="max-height: 80px;" />
                </div>
                @endif
            </div>

            <div class="footer">
                2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa ; BP 68 Douala ; Tél. : (237) 677145068 ; 692664980 ; 691902153 ;<br>
                670407656 (WhatSapp) / RC N° A/033328 ; Arrêté N°00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021<br>
                N° de Contribuable P016800300941S / Email : <a href="mailto:infos@feconsultancy.com">infos@feconsultancy.com</a> &amp; <a href="mailto:guyalain.ngounou@feconsultancy.com">guyalain.ngounou@feconsultancy.com</a>/
            </div>

        </div> <!-- Fin de content-wrapper -->
    </div>

</body>
</html>
