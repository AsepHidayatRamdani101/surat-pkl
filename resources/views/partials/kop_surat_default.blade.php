@php
    $kopLogo = $kopLogo ?? null;

    if (empty($kopLogo)) {
        $candidateLogoPaths = [
            public_path('LogoJabar.png'),
            public_path('logojabar.png'),
            public_path('vendor/adminlte/dist/img/AdminLTELogo.png'),
        ];

        foreach ($candidateLogoPaths as $candidatePath) {
            if (file_exists($candidatePath)) {
                $kopLogo = $candidatePath;
                break;
            }
        }
    }

    if (!empty($kopLogo) && !str_starts_with((string) $kopLogo, 'data:')) {
        $resolvedPath = null;

        if (str_starts_with((string) $kopLogo, 'file:///')) {
            $resolvedPath = preg_replace('/^file:\/\//', '', (string) $kopLogo);
        } elseif (str_starts_with((string) $kopLogo, 'http://') || str_starts_with((string) $kopLogo, 'https://')) {
            $resolvedPath = null;
        } else {
            $resolvedPath = (string) $kopLogo;
            if (!file_exists($resolvedPath)) {
                $resolvedPath = public_path(ltrim((string) $kopLogo, '/'));
            }
        }

        if (!empty($resolvedPath) && file_exists($resolvedPath)) {
            $extension = strtolower(pathinfo($resolvedPath, PATHINFO_EXTENSION));
            $mimeMap = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
            ];

            $mimeType = $mimeMap[$extension] ?? 'image/png';
            $kopLogo = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($resolvedPath));
        }
    }
@endphp

<div style="{{ $kopOuterStyle ?? 'margin-top: -50px' }}">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 110px; vertical-align: top;">
                @if (!empty($kopLogo))
                    <img src="{{ $kopLogo }}" alt="logo" width="{{ $kopLogoWidth ?? 100 }}"
                        style="margin-right: 20px;">
                @endif
            </td>
            <td style="vertical-align: top;">
                <div style="text-align: center; margin: 0;">
                    <h4 style="margin: 0; line-height: 1.2;">PEMERINTAH DAERAH PROVINSI JAWA BARAT <br>
                        DINAS PENDIDIKAN <br>
                        CABANG DINAS PENDIDIKAN WILAYAH XI</h4>
                    <h2 style="margin-top: -2px; margin-bottom: -2px;"><strong>SMK NEGERI 8 GARUT</strong></h3>
                        <p style="margin-top: 0px; margin-bottom: 0;font-size: 12pt;">JL. RAYA LIMBANGAN-SELAWI KM
                            12
                            GARUT </p>
                        <p style="margin:0; font-size: 7pt;">
                            <i>Website:</i>www.smkn8-garut.sch.id , <i>E-mail:</i> smknegeri8grt@gmail.com <br>
                        </p>
                </div>
            </td>
        </tr>
    </table>
</div>

<hr style="{{ $kopHrStyle ?? 'margin-top: 10px; border: 1px solid black;' }}">
