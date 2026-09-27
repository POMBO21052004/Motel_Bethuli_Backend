<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attestation - Modèle 5</title>
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
    font-family: 'Comic Sans MS', sans-serif;
    font-style: normal;
    color: #000;
  }

  .page {
    background: #ffffff;
    width: 210mm;
    height: 297mm;
    position: relative;
    overflow: hidden;
  }

  /* Bordures : bleu ciel demandé (#87ceeb) */
  .outer-border {
    position: absolute;
    top: 5mm; left: 5mm; right: 5mm; bottom: 5mm;
    border: 9px solid #2987ca;
    z-index: 1;
  }

  .inner-border {
    position: absolute;
    top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
    border: 3px solid #7db6de;
    z-index: 2;
  }

  .content-wrapper {
    position: absolute;
    top: 11mm; left: 11mm; right: 11mm; bottom: 11mm;
    background-color: #d9e2f3;
    padding: 25px;
    z-index: 3;
    display: flex;
    flex-direction: column;
  }

  /* HEADER */
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
  }
  .header-table td {
    vertical-align: top;
  }
  .header-ref {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 5px;
  }

  /* TITRE PRINCIPAL */
  .main-title {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    margin: 15px 0 10px 0;
    line-height: 1.2;
  }

  /* CORPS */
  .body-text {
    font-size: 17px;
    line-height: 1.3;
    text-align: justify;
    margin-bottom: 12px;
  }

  .fec-name { font-weight: bold; }

  .var-highlight {
    color: #3f694a;
    font-style: italic;
    text-decoration: underline;
    font-weight: normal;
  }
  .name-highlight {
    color: #3697b8;
    font-style: italic;
    text-decoration: underline;
    font-weight: bold;
  }

  .formation-title {
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    text-transform: uppercase;
    margin: 10px 0;
    color: #000000;
  }

  .details-list {
    list-style: none;
    margin: 0 0 10px 15px;
    font-size: 15px;
    line-height: 1.2;
  }
  .details-list li { position: relative; padding-left: 10px; margin-bottom: 2px; }
  .details-list li::before { content: '-'; position: absolute; left: 0; }

  /* PÉDAGOGIQUE (ARIAL) */
  .pedagogique-section {
    font-family: Arial, Helvetica, sans-serif;
  }
  .pedagogique-title {
    font-size: 15px;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 6px;
  }
  .pedagogique-subtitle {
    font-size: 14px;
    margin-bottom: 6px;
  }

  /* SIGNATURE */
  .signature-section {
    margin-top: 15px;
    font-family: 'Comic Sans MS', sans-serif;
  }
  .fait-a {
    font-size: 15px;
  }
  .validite {
    font-size: 15px;
  }
  .direction {
    font-size: 15px;
    font-weight: bold;
    text-decoration: underline;
  }

  /* ── FOOTER ── */
  .footer {
    position: absolute;
    bottom: 100px;
    left: 0;
    right: 0;
    font-size: 7.5px;
    text-align: center;
    font-weight: normal;
    line-height: 1;
    color: #000080;
  }
  .footer a { color: #000080; text-decoration: none; }
  .signature-img { max-height: 150px; }
</style>
</head>
<body>
<div class="page">
    <div class="outer-border"></div>
    <div class="inner-border"></div>
    <div class="content-wrapper">

        <!-- HEADER -->
        <div class="header-ref" style="margin-bottom: 10px;">Ref : {{ $certificate->reference }}</div>

        <table class="header-table">
            <tr>
                <td style="width: 30%; text-align: left;">
                    @if(!empty($entrepriseLogoBase64))
                        <img src="{{ $entrepriseLogoBase64 }}" style="max-height:100px; max-width:140px;" alt="Logo Entreprise" />
                    @endif
                </td>
                <td style="width: 40%; text-align: center;">
                    <img src="{{ public_path('logo/logo_fec.png') }}" style="max-height:110px; max-width:150px;" alt="Logo FEC" />
                </td>
                <td style="width: 30%; text-align: right;">
                    <div style="display: inline-block; text-align: center; background:#ffffff; border: 2px solid #ffffff; padding: 2px;">
                        <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="100" height="100" style="display:block; margin: 0 auto;" />
                    </div>
                </td>
            </tr>
        </table>

        <!-- TITRE PRINCIPAL -->
        <div class="main-title">ATTESTATION DE FORMATION DE FORMATEUR INTERNE</div>

        <!-- CORPS -->
        <div class="body-text">
            Nous, soussignons <span class="fec-name">FREE ENGINEERING CONSULTANCY (FEC), Centre de Formation Professionnelle et Rapide en Qualité́, Sécurité́ & Environnement (QSE)</span> certifions que <span class="name-highlight">Mr/Mme {{ $participant->full_name }}</span>
            @if(!empty($participant_model->matricule)) Matricule <span class="var-highlight">{{ $participant_model->matricule }}</span> @endif
            @if(!empty($participant->numero_permis)) détenteur de Permis de Conduire N* <span class="var-highlight">{{ $participant->numero_permis }}</span> @endif
            @if(!empty($participant->license_categories) && $participant->license_categories !== 'Non renseigné') de Catégorie <span class="var-highlight">{{ $participant->license_categories }}</span> @endif
            en service à <span class="var-highlight">{{ $participant->company }}</span>
            @if(!empty($participant_model->fonction)) en qualité de <span class="var-highlight">{{ $participant_model->fonction }}</span> @endif
            a régulièrement suivi l’action de Formateur Interne sur le thème :
        </div>

        <div class="formation-title">
            {{ $session->formation->intitule ?? "FORMATION" }}
        </div>

        <ul class="details-list">
            <li>Date de la formation : {{ $training->start_date }} @if($training->end_date) au {{ $training->end_date }} @endif</li>
            <li>Durée : {{ $training->duration }}</li>
            <li>Lieu de réalisation de la formation : {{ $training->location }}</li>
        </ul>

        <!-- PÉDAGOGIQUE (Arial) -->
        <div class="pedagogique-section">
            <div class="pedagogique-title">Déroulement pédagogique théorique et pratique autour des Objectifs Spécifiques ci-dessous :</div>
            <div class="pedagogique-subtitle">À l'issue de la formation, les participants sont désormais capables de :</div>

            @if(isset($objectifsGroups) && count($objectifsGroups) > 0)
                @php
                    $allObjectives = collect();
                    foreach($objectifsGroups as $cat => $objs) {
                        foreach($objs as $obj) {
                            $allObjectives->push($obj);
                        }
                    }

                    function toRoman($num) {
                        $n = intval($num);
                        $res = '';
                        $roman_numerals = array(
                            'm'  => 1000, 'cm' => 900, 'd'  => 500, 'cd' => 400,
                            'c'  => 100, 'xc' => 90, 'l'  => 50, 'xl' => 40,
                            'x'  => 10, 'ix' => 9, 'v'  => 5, 'iv' => 4, 'i'  => 1);
                        foreach ($roman_numerals as $roman => $number) {
                            $matches = intval($n / $number);
                            $res .= str_repeat($roman, $matches);
                            $n = $n % $number;
                        }
                        return $res;
                    }
                @endphp
                <table style="width: 100%; border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
                    @foreach($allObjectives as $index => $obj)
                        <tr>
                            <td style="width: 25px; vertical-align: top; text-align: right; padding-right: 5px;">{{ toRoman($index + 1) }}.</td>
                            <td style="vertical-align: top;">{{ $obj->intitule }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <!-- DATES BLOCK -->
        @php
            $dateFaitA = \Carbon\Carbon::parse($session->date_fin);
            $dateValidite = $session->formation->typeAttestation
                ? $session->formation->typeAttestation->computeExpirationDate($dateFaitA)
                : $dateFaitA->copy()->addYears(1);
        @endphp
        <div style="margin-top: 5mm; font-family: 'Comic Sans MS', sans-serif; font-size: 15px; line-height: 1.1;">
            Fait à <span style="font-weight:bold; font-style:italic; color:#cc0000;">{{ $certificate->city }}</span> le <span style="font-weight:bold; font-style:italic; color:#cc0000;">{{ $dateFaitA->format('d') }} {{ ucfirst($dateFaitA->locale('fr')->translatedFormat('F Y')) }}</span><br>
            Fin de Validité : <span style="font-weight:bold; font-style:italic; color:#cc0000;">{{ $dateValidite->format('d') }} {{ ucfirst($dateValidite->locale('fr')->translatedFormat('F Y')) }}</span>
        </div>

        <!-- SIGNATURE BLOCK (ABSOLUTE BACKGROUND) -->
        <div style="position: absolute; top: 68%; right: 10%; text-align: center; font-family: 'Comic Sans MS', sans-serif; z-index: 0; width: 35%;">
            <div style="font-size: 15px; font-weight: bold; text-decoration: underline; margin-bottom: 5px;">La Direction</div>
            @if(!empty($session->formation->typeAttestation->file_signature))
                <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" class="signature-img" style="display: inline-block; opacity: 0.8;" />
            @endif
        </div>

        <div class="footer">
            2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa ; BP 68 Douala ; Tél. : (237) 677145068; 692664980; 691902153 ;<br>
            670407656 (WhatSapp) / RC N° A/033328 ; Arrêté N°00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021<br>
            N° de Contribuable P016800300941S / Email : <a href="mailto:infos@feconsultancy.com" style="text-decoration: underline;">infos@feconsultancy.com</a> &amp; <a href="mailto:guyalain.ngounou@feconsultancy.com" style="text-decoration: underline;">guyalain.ngounou@feconsultancy.com</a>/
        </div>

    </div>
</div>
</body>
</html>
