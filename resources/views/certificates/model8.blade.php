<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attestation - Modèle 8</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @page {
    size: A4 portrait;
    margin: 0;
  }

  /* ── POLICES ── */
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

  body {
    margin: 0;
    padding: 0;
    width: 210mm;
    height: 297mm;
    background: #ffffff;
    font-family: 'Comic Sans MS', sans-serif;
    font-style: normal;
  }

  .page {
    background: #ffffff;
    width: 210mm;
    height: 297mm;
    position: relative;
    color: #000;
    overflow: hidden;
  }

  /* ── BORDURES COMME MODELE 1 ── */
  .outer-border {
    position: absolute;
    top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
    border: 1px solid #7b89a3;
  }

  .inner-border {
    position: absolute;
    top: 13mm; left: 13mm; right: 13mm; bottom: 13mm;
    border: 6px solid #44587d;
    background: #f5f9fc;
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
    background: #ccffcc;
    border: 4px solid #ffffff;
    padding: 6px 9px;
    font-weight: bold;
    font-size: 7.5px;
    text-align: center;
    line-height: 0.8;
    width: 260px;
    margin-left: 0;
  }

  /* ── TITRE PRINCIPAL ── */
  .main-title {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    font-style: normal;
    text-transform: uppercase;
    margin: 16px 0 14px 0;
    line-height: 1;
  }

  /* ── CORPS ── */
  .body-text {
    font-style: normal;
    font-size: 15px;
    line-height: 1.1;
    text-align: justify;
    margin-bottom: 12px;
  }

  .details-list {
    list-style: none;
    margin: 0 0 10px 0;
    font-size: 14px;
    line-height: 1.2;
  }

  .pedagogique-title {
    font-size: 16px;
    font-weight: bold;
    font-style: normal;
    margin: 8px 0 8px 0;
    text-decoration: underline;
  }

  /* ── OBJECTIFS EN ARIAL ── */
  .objectives-container {
    font-family: 'Arial', sans-serif;
  }
  .obj-category {
    font-size: 14px;
    font-weight: bold;
    margin-top: 6px;
    margin-bottom: 2px;
  }
  .obj-text {
    font-size: 13px;
    line-height: 1.15;
    margin-bottom: 4px;
  }
  .obj-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
  }
  .obj-list li {
    font-size: 13px;
    line-height: 1.15;
    position: relative;
    padding-left: 10px;
    margin-bottom: 2px;
  }
  .obj-list li::before {
    content: '-';
    position: absolute;
    left: 0;
  }

  /* ── SIGNATURE ── */
  .signature-section {
    margin-top: 15px;
    line-height: 1.2;
  }
  .fait-a {
    font-size: 15px;
    font-style: normal;
  }
  .fait-a .city { font-style: italic; font-weight: bold; color: #008000; }
  .fait-a .dateval { font-style: italic; font-weight: bold; color: #008000; }

  .validite {
    font-size: 15px;
    font-style: italic;
    font-weight: bold;
    color: #cc0000;
  }
  .direction {
    font-size: 15px;
    font-weight: bold;
    text-decoration: underline;
  }

  /* ── FOOTER ── */
  .footer {
    position: absolute;
    bottom: 120px;
    left: 0;
    right: 0;
    font-size: 7.5px;
    text-align: center;
    font-weight: normal;
    line-height: 1;
    color: #000080;
  }
  .footer a { color: #000080; text-decoration: none; }
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
            <td style="width: 33%; text-align: left; vertical-align: top;">
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

            <td style="width: 34%; text-align: center; vertical-align: top;">
                <!-- Logo FEC -->
                <img src="{{ public_path('logo/logo_fec.png') }}" style="max-height:130px; max-width:130px; margin-top: 0px; position: relative; left: -30px;" alt="Logo FEC" />
            </td>

            <td style="width: 33%; text-align: right; vertical-align: top; padding-right: 15px;">
                <!-- QR Code SVG encadré blanc -->
                  <div style="display: inline-block; text-align: center; background:#ffffff; border: 4px solid #ffffff; padding: 4px;">
                  <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="96" height="96" style="display:block; margin: 0 auto;" />
                  </div>
            </td>
            </tr>
        </table>

        <!-- TITRE PRINCIPAL -->
        <div class="main-title">ATTESTATION DE FORMATION</div>

        @php
            $dateDebut = \Carbon\Carbon::parse($session->date_debut)->locale('fr');
            $dateFin = \Carbon\Carbon::parse($session->date_fin)->locale('fr');
            $dateFaitA = $dateFin->copy();

            $formattedDates = "";
            if ($session->date_debut && $session->date_fin && $session->date_debut !== $session->date_fin) {
                if ($dateDebut->format('m Y') === $dateFin->format('m Y')) {
                    $formattedDates = $dateDebut->format('d') . ' & ' . $dateFin->format('d') . ' ' . ucfirst($dateFin->translatedFormat('F Y'));
                } else {
                    $formattedDates = 'du ' . $dateDebut->format('d') . ' ' . ucfirst($dateDebut->translatedFormat('F Y')) . ' au ' . $dateFin->format('d') . ' ' . ucfirst($dateFin->translatedFormat('F Y'));
                }
            } else {
                $formattedDates = 'le ' . $dateFin->format('d') . ' ' . ucfirst($dateFin->translatedFormat('F Y'));
            }

            $dateValidite = $session->formation->typeAttestation
                ? $session->formation->typeAttestation->computeExpirationDate($dateFaitA)
                : $dateFaitA->copy()->addYears(1);
        @endphp

        <!-- CORPS -->
        <div class="body-text">
            Nous, soussignons <span style="font-weight: bold;">FREE ENGINEERING CONSULTANCY (FEC)</span>, Cabinet Spécialisé en Conseil, Formation & Assistance Technique en Qualité, Sécurité & Environnement (QSE) certifions que <span style="font-weight: bold;">{{ $participant->genre ?? 'Mr/Mme' }} {{ mb_strtoupper($participant->full_name) }}</span> en qualité de <span style="font-weight: bold;">{{ $participant->fonction ?? 'Employé' }}</span> à <span style="font-weight: bold;">{{ $participant->company ?? 'l\'entreprise' }} {{ $participant->location ?? '' }}</span> a régulièrement suivi l'action de formation suivante :
        </div>
        <div style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 18px; margin-bottom: 12px;">
            ‘’{{ mb_strtoupper($session->formation->intitule) }}’’
        </div>

        <ul class="details-list" style="margin-left: 30px;">
            <li>-Dates de la formation : {{ $formattedDates }}</li>
            <li>-Durée : {{ $training->duration }}</li>
            <li>-Lieu de réalisation de la formation : {{ $session->location ?? 'Base MCI Dibamba - Douala' }}</li>
        </ul>

        <div class="pedagogique-title">Déroulement pédagogique théorique et pratique :</div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; vertical-align: top; padding-right: 10px;">
                    <div class="objectives-container">
                        @if(isset($objectifsGroups) && count($objectifsGroups) > 0)
                            @foreach($objectifsGroups as $category => $objectifs)
                                <div class="obj-category">{{ $category }}</div>
                                <ul class="obj-list">
                                    @foreach($objectifs as $obj)
                                        <li>{{ $obj->intitule }}</li>
                                    @endforeach
                                </ul>
                            @endforeach
                        @else
                            <div class="obj-category">Objectif général</div>
                            <div class="obj-text">Renforcer les capacités des participants en leadership et en communication afin d’améliorer leur efficacité professionnelle et leur impact au sein de l’entreprise.</div>

                            <div class="obj-category">Objectifs spécifiques</div>
                            <div class="obj-text">À l’issue de la formation, les participants seront capables de :</div>
                            <ul class="obj-list">
                                <li>Comprendre les fondamentaux du leadership moderne ;</li>
                                <li>Identifier les styles de leadership adaptés au contexte professionnel ;</li>
                                <li>Améliorer leur communication interpersonnelle et opérationnelle ;</li>
                                <li>Développer des techniques d’écoute active et de gestion des conflits ;</li>
                                <li>Optimiser la communication d’équipe pour une meilleure coordination opérationnelle.</li>
                            </ul>
                        @endif
                    </div>
                </td>
                <td style="width: 30%; vertical-align: middle; text-align: center;">
                    @if(!empty($pictoBase64))
                        <img src="{{ $pictoBase64 }}" alt="Pictogramme" style="max-height:180px; max-width:200px;" />
                    @endif
                </td>
            </tr>
        </table>

        <!-- SIGNATURE -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 60px;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div class="fait-a">Fait à <span class="city">{{ $certificate->city }}</span> le <span class="dateval">{{ $dateFaitA->format('d') }} {{ ucfirst($dateFaitA->translatedFormat('F Y')) }}</span></div>
                    <div class="validite">Fin de Validité : {{ $dateValidite->format('d') }} {{ ucfirst($dateValidite->translatedFormat('F Y')) }}</div>
                </td>
                <td style="width: 50%; vertical-align: bottom; text-align: center;">
                    <div class="direction">La Direction</div>
                    @if(!empty($session->formation->typeAttestation->file_signature))
                        <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" style="max-width: 150px; margin-top: 5px;" alt="Signature" />
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer">
            2ieme et 3ieme Etage N*1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa ; BP 68 Douala ; Tél. : (237) 677145068 ; 692664980 ; 691902153 ; 670407656 (WhatSapp) / RC N° A/033328 ; Arrêté N*00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021<br>
            N° de Contribuable P016800300941S / Email: infos@feconsultancy.com & guyalain.ngounou@feconsultancy.com/
        </div>

    </div>
</div>
</body>
</html>
