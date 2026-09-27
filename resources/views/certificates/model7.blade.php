<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Attestation - Modèle 7</title>
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
    top: 5mm; left: 5mm; right: 5mm; bottom: 5mm;
    border: 8px solid #9dbd5e;
    z-index: 2;
  }
  .border-inner {
    position: absolute;
    top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
    border: 2px solid #bddf9e;
    z-index: 3;
  }

  /* ── CONTENU PRINCIPAL ── */
  .content-wrapper {
    position: absolute;
    top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
    background-color: #dbffdb;
    z-index: 10;
    padding: 10px 15px;
  }

  /* ── HEADER : Ref | Logo | QR ── */
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
  }
  .header-table td {
    vertical-align: middle;
  }

  /* ── TITRE DU TYPE D'ATTESTATION ── */
  .main-title {
    text-align: center;
    color: #000000;
    font-size: 24px;
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    text-transform: uppercase;
    margin: 5px auto 10px auto;
  }

  /* ── TEXTE PRINCIPAL ── */
  .main-text-block {
    font-size: 19px;
    font-family: 'Arial', sans-serif;
    line-height: 1.15;
    text-align: justify;
    margin-bottom: 8px;
  }

  .para {
    text-indent: 1cm;
    margin-bottom: 6px;
  }

  .attestation-footer-block {
    font-size: 19px;
    font-family: 'Arial', sans-serif;
    margin-top: 5px;
    margin-bottom: 15px;
    text-indent: 0;
  }

  .date-block {
    font-size: 19px;
    font-family: 'Arial', sans-serif;
    margin-bottom: 10px;
    text-indent: 0;
  }

  /* ── PROGRAMME DE FORMATION ── */
  .programme-title {
    font-size: 17px;
    font-family: 'Arial', sans-serif;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 10px;
  }
  .programme-body {
    font-size: 14px;
    font-family: 'Arial', sans-serif;
    line-height: 1.2;
  }
  /* Catégorie : surlignée (fond jaune) + soulignée */
  .category-title {
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    margin-top: 5px;
    margin-bottom: 2px;
    background-color: #FFD700;
    text-decoration: underline;
    display: inline-block;
  }
  .programme-body ol {
    margin: 0;
    padding-left: 0.5cm;
    margin-bottom: 2px;
    list-style-type: decimal;
  }
  .programme-body ol li {
    font-weight: bold;
    font-family: 'Arial', sans-serif;
    font-size: 14px;
    margin-bottom: 0px;
    line-height: 1.1;
  }

  /* ── SIGNATURE ── */
  .signature-block {
    position: absolute;
    bottom: 170px;
    right: 40px;
    text-align: center;
    width: 200px;
  }
  .signature-title {
    font-size: 16px;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 5px;
  }

  /* ── FOOTER ── */
  .footer {
    position: absolute;
    bottom: 40px;
    left: 20px;
    right: 20px;
    text-align: center;
    font-size: 10px;
    font-family: 'Arial', sans-serif;
    color: #4049ad;
    font-style: italic;
    line-height: 1.1;
    z-index: 10;
  }
  .footer-link {
    color: #4f4fff;
    text-decoration: underline;
  }

  .signature-img { max-height: 150px; }

</style>
</head>
<body>

    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <div class="content-wrapper">

        {{-- ── HEADER : Réf | Logo | QR Code ── --}}
        <table class="header-table">
            <tr>
                <td style="width: 30%; text-align: left; vertical-align: top;">
                    <div style="font-size: 14px; font-weight: bold; line-height: 1.4;">
                        Réf. : <span style="font-weight: normal;">{{ $certificate->reference }}</span>
                    </div>
                </td>
                <td style="width: 40%; text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/logo_fec.png') }}" style="max-height: 110px; max-width: 150px;" alt="Logo FEC" />
                </td>
                <td style="width: 30%; text-align: right; vertical-align: top;">
                    <div style="display: inline-block; background: #fff; padding: 2px; border: 1px solid #ccc;">
                        <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="90" height="90" style="display:block;" />
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── TITRE PRINCIPAL ── --}}
        <div class="main-title">
            ATTESTATION DE PRESENCE
        </div>

        {{-- ── CORPS : TEXTE D'ATTESTATION ── --}}
        @php
            $dateDebut = \Carbon\Carbon::parse($session->date_debut)->locale('fr');
            $dateFin = \Carbon\Carbon::parse($session->date_fin)->locale('fr');
            $dateFaitA = $dateFin->copy();

            // Formatage spécifique des dates (ex: 03 & 04 Mai 2016)
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
        @endphp

        <div class="main-text-block">
            <div class="para">
                Nous FREE ENGINERING CONSULTANCY Centre de Formation en Qualité, Sécurité & Environnement (QSE) attestons que :
            </div>
            <div class="para">
                {{ $participant->genre ?? 'Mr / Mme' }} <span style="font-weight: bold;">{{ mb_strtoupper($participant->full_name) }}</span> Employé à <span style="font-weight: bold;">{{ $participant->company ?? 'l\'entreprise' }}</span><br>
                a participé à la session de formation sur le thème ‘’<span style="font-weight: bold;">{{ mb_strtoupper($session->formation->intitule) }}</span>’’
                référence <span style="font-weight: bold;">SMS055</span>
                en dates du <span style="font-weight: bold;">{{ $formattedDates }}</span>.
            </div>
        </div>

        <div class="attestation-footer-block">
            Attestation établie pour servir et valoir ce que de droit.
        </div>

        <div class="date-block">
            Fait à {{ $certificate->city }} le {{ $dateFaitA->format('d') }} {{ ucfirst($dateFaitA->translatedFormat('F Y')) }}
        </div>

        {{-- ── PROGRAMME ET PICTOGRAMME ── --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div class="programme-title">Programme de Formation :</div>
                    <div class="programme-body">
                        @if(isset($objectifsGroups) && count($objectifsGroups) > 0)
                            @foreach($objectifsGroups as $category => $objectifs)
                                <div class="category-title">{{ $category }} :</div>
                                <ol>
                                    @foreach($objectifs as $obj)
                                        <li>{{ $obj->intitule }}</li>
                                    @endforeach
                                </ol>
                            @endforeach
                        @else
                            {{--  vide  --}}
                        @endif
                    </div>
                </td>
                <td style="width: 30%; vertical-align: middle; text-align: center;">
                    @if(!empty($pictoBase64))
                        <img src="{{ $pictoBase64 }}" alt="Pictogramme" style="max-height:220px; max-width:250px;" />
                    @endif
                </td>
            </tr>
        </table>

        {{-- ── SIGNATURE ── --}}
        <div class="signature-block">
            <div class="signature-title">La Direction</div>
            @if(!empty($session->formation->typeAttestation->file_signature))
                <img src="{{ public_path('storage/' . $session->formation->typeAttestation->file_signature) }}" class="signature-img" style="margin-top: 10px;" alt="Signature" />
            @endif
        </div>

    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        2ième et 3ième Etage N°1158 Immeuble BS Face Ancienne Direction NOBRA Rue Pasteur EBOUBE MBENGUE Akwa;<br>
        BP 68 Douala Cameroun ; Tél. : (237) 677145068 / Autorisation d’exercer suivant <span style="font-weight: bold;">Arrêté N° 0002/MINEFOP/SG/DFOP du 28 Février 2007</span><br>
        Site Web: <span class="footer-link">www.feconsultancy.com</span>  / Facebook: <span class="footer-link">www.facebook.com/freengineeringconsultancy</span> Email: <span class="footer-link">infos@feconsultancy.com</span>
    </div>

</body>
</html>
