<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $penggajian->kode_penggajian }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            margin: 0;
            padding: 24px;
            font-size: 13px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #2b6cb0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0 0;
            color: #718096;
            font-size: 11px;
        }
        .title-badge {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-badge span {
            background-color: #ebf8ff;
            color: #2b6cb0;
            font-weight: bold;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 14px;
            border: 1px solid #bee3f8;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
            width: 25%;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .salary-table th {
            background-color: #2b6cb0;
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        .salary-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .salary-table tr.total-row td {
            background-color: #f7fafc;
            font-weight: bold;
            font-size: 14px;
            color: #2b6cb0;
            border-top: 2px solid #cbd5e0;
            border-bottom: 2px solid #2b6cb0;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
        }
        .note {
            margin-top: 30px;
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sistem Etos Kerja (SEK)</h1>
        <p>Aplikasi Manajemen SDM, Presensi, dan Penggajian Karyawan Terpadu</p>
    </div>

    <div class="title-badge">
        <span>SLIP GAJI RESMI</span>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Kode Slip Gaji</td>
            <td>: <strong>{{ $penggajian->kode_penggajian }}</strong></td>
            <td class="info-label">Tanggal Transaksi</td>
            <td>: {{ \Carbon\Carbon::parse($penggajian->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama Karyawan</td>
            <td>: {{ optional($penggajian->karyawan)->nama ?? '-' }}</td>
            <td class="info-label">Kode Karyawan</td>
            <td>: {{ optional($penggajian->karyawan)->kode_karyawan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Email</td>
            <td>: {{ optional($penggajian->karyawan)->email ?? '-' }}</td>
            <td class="info-label">Role / Status</td>
            <td>: {{ ucfirst(optional($penggajian->karyawan)->role ?? 'Karyawan') }} / {{ optional($penggajian->karyawan)->status ?? 'Aktif' }}</td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th>Deskripsi Komponen Finansial</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Pokok Harian / Bulanan</td>
                <td class="text-right">Rp {{ number_format($penggajian->gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bonus Performa &amp; Kehadiran (Otomatis)</td>
                <td class="text-right">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
                <td class="text-right">Rp {{ number_format($totalGaji, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Penerima,</p>
            <div class="signature-space"></div>
            <p><strong>{{ optional($penggajian->karyawan)->nama ?? 'Karyawan' }}</strong></p>
        </div>
        <div class="signature-box">
            <p>Bagian Keuangan / HRD,</p>
            <div class="signature-space"></div>
            <p><strong>Manajemen Etos Kerja</strong></p>
        </div>
    </div>

    <div class="note">
        * Dokumen ini dibuat dan divalidasi secara otomatis oleh Sistem Etos Kerja (SEK). Sah tanpa tanda tangan basah.
    </div>

</body>
</html>

