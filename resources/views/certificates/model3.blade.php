<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Attestation - Modèle 3</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @page { size: A4 landscape; margin: 0; }

  /* ── POLICE HELVETICA ── */
  @font-face {
    font-family: 'Helvetica';
    src: url('{{ public_path("fonts/helvetica/Helvetica.ttf") }}') format("truetype");
    font-weight: normal;
    font-style: normal;
  }
  @font-face {
    font-family: 'Helvetica';
    src: url('{{ public_path("fonts/helvetica/helvetica-bold.otf") }}') format("opentype");
    font-weight: bold;
    font-style: normal;
  }

  body {
    margin: 0; padding: 0;
    width: 297mm; height: 210mm;
    background: #ffffff;
    font-family: 'Helvetica', Arial, sans-serif;
    color: #000;
    position: relative;
    overflow: hidden;
  }

  /* ── TRIPLE BORDURE NOIRE (similaire aux autres modèles) ── */
  .border-outer {
    position: absolute;
    top: 12mm; left: 12mm; right: 12mm; bottom: 12mm;
    border: 1px solid #000;
    z-index: 2;
  }
  .border-middle {
    position: absolute;
    top: 13mm; left: 13mm; right: 13mm; bottom: 13mm;
    border: 6px solid #000;
    z-index: 2;
  }
  .border-inner {
    position: absolute;
    top: 17mm; left: 17mm; right: 17mm; bottom: 17mm;
    border: 1px solid #000;
    z-index: 2;
  }

  /* ── WATERMARK LOGO LARGE ── */
  .watermark {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
    opacity: 0.30;
    width: 80%;
    max-height: 98%;
    object-fit: contain;
  }

  /* ── CONTENU PRINCIPAL ── */
  .content-wrapper {
    position: absolute;
    top: 12mm; left: 20mm; right: 20mm; bottom: 20mm;
    z-index: 10;
  }

  /* ── HEADER ── */
  .header-row {
    width: 100%;
    display: table;
    margin-bottom: 8px;
  }
  .header-row td { vertical-align: middle; }

  /* ── CORPS ── */
  .main-content {
    text-align: center;
    margin-top: 6px;
    margin-bottom: 6px;
  }

  .main-text-block {
    width: 90%;
    margin: 0 auto;
    font-size: 19px;
    line-height: 1.2;
    text-align: center;
  }

  .name-line {
    font-size: 19px;
    font-weight: bold;
    text-decoration: underline;
  }

  .formation-line {
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.2;
  }

  /* ── QR + SIGNATURE ── */
  .bottom-bar {
    width: 100%;
    display: table;
    margin-bottom: 6px;
  }
  .bottom-bar td { vertical-align: bottom; }

  /* ── FOOTER CARD VERT FORÊT ── */
  .footer-card {
    position: absolute;
    bottom: 20mm;
    left: 20mm;
    right: 20mm;
    background-color: #70c840;
    border: 2px solid #000;
    padding: 5px 6px;
    text-align: center;
    font-size: 15px;
    line-height: 1;
    font-family: 'Helvetica', Arial, sans-serif;
    color: #000000;
    z-index: 11;
  }
</style>
</head>
<body>

    <!-- WATERMARK : logo_large_size.png toujours en fond -->
    <img src="{{ public_path('logo/logo_large_size.png') }}" class="watermark" alt="Watermark" />

    <!-- TRIPLE BORDURE -->
    <div class="border-outer"></div>
    <div class="border-middle"></div>
    <div class="border-inner"></div>

    <div class="content-wrapper">

        <!-- Conteneur interne position:relative pour ancrer le footer en bas -->
        <div style="position: relative; height: 100%;">

            <!-- HEADER : Logo | Titre | Numéro -->
            <table class="header-row">
                <tr>
                    <td style="width: 28%; text-align: left;">
                        <img src="{{ public_path('logo/logo.png') }}" style="max-height: 170px; max-width: 260px;" alt="Logo FEC" />
                    </td>
                    <td style="width: 44%; text-align: center; padding: 0 10px;">
                        <div style="font-size: 30px; font-weight: bold; text-transform: uppercase; line-height: 1.2; font-family: 'Helvetica', Arial, sans-serif;">
                            {{ $session->formation->typeAttestation->name ?? 'ATTESTATION DE FORMATION' }}
                        </div>
                    </td>
                    <td style="width: 28%; text-align: right; font-size: 16px; font-weight: bold; font-family: 'Helvetica', Arial, sans-serif;">
                        {{ $certificate->reference }}
                    </td>
                </tr>
            </table>

            <!-- CORPS DU TEXTE CENTRÉ -->
            <div class="main-content">
                <div class="main-text-block">
                    @php
                        $dateDebut = \Carbon\Carbon::parse($session->date_debut)->locale('fr');
                        $dateFin   = \Carbon\Carbon::parse($session->date_fin)->locale('fr');
                        $dateValidite = $session->formation->typeAttestation
                            ? $session->formation->typeAttestation->computeExpirationDate($dateFin)
                            : $dateFin->copy()->addYears(1);
                        $dateValidite->locale('fr');
                    @endphp

                    Nous soussignés cabinet <strong>Free Engineering Consultancy (FEC)</strong>, attestons que<br>
                    <span class="name-line">M./Mme {{ $participant->full_name }}</span><br>
                    permis de conduire n°<strong>{{ $participant->numero_permis ?? 'LT-248554-14' }}</strong> de type(s) <strong>{{ $participant->license_categories ?? 'B & C' }}</strong> Employé à <strong>{{ $participant->company ?? 'ATES Ltd Cameroun' }}</strong> a suivi avec succès une FORMATION en<br>
                    <span class="formation-line">CONDUITE DÉFENSIVE / PRÉVENTIVE DES POIDS LOURDS</span><br>
                    à <strong>{{ $certificate->city ?? 'Douala' }}</strong> en date du <strong>{{ $dateDebut->translatedFormat('Y-m-d') }}</strong>.<br>
                    En foi de quoi nous lui délivrons cette attestation N°<strong>{{ $certificate->reference ?? '260525-0001' }}</strong> valable jusqu’au <strong>{{ $dateValidite->translatedFormat('Y-m-d') }}</strong> pour servir et valoir ce que de droit
                </div>
            </div>

            <!-- QR CODE (gauche) + SIGNATURE (droite, grande) -->
            <table class="bottom-bar" style="margin-bottom: 6px;">
                <tr>
                    <td style="width: 50%; text-align: left; padding-left: 2%; padding-bottom: 4px;">
                        <div style="display: inline-block; background:#fff; border: 2px solid #fff; padding: 2px;">
                            <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="140" height="140" style="display:block;" />
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right; padding-right: 10%; padding-bottom: 4px;">
                        <div style="display: inline-block; text-align: center;">
                            @if(!empty($session->formation->typeAttestation->file_signature))
                                <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" style="max-height: 150px;" />
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

        </div><!-- fin position:relative -->
    </div><!-- fin content-wrapper -->

    <!-- FOOTER CARD VERT FORÊT — positionné par rapport au body (DomPDF) -->
    <div class="footer-card">
        2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa<br>
        BP 68 Douala &nbsp;|&nbsp; Tél. : (237) 677145068 ; 692664980 ; 691902153 ; 670407656 (WhatsApp) &nbsp;|&nbsp; RC N° A/033328<br>
        Arrêté N°00000481/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC du 19 Novembre 2021 &nbsp;|&nbsp; N° de Contribuable P016800300941S<br>
        Email : infos@feconsultancy.com &nbsp;|&nbsp; guyalain.ngounou@feconsultancy.com
    </div>

</body>
</html>
