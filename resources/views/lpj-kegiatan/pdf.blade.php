<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LPJ {{ $lpj->nomor_lpj }}</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .kop-surat h2 {
            margin: 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }
        
        .kop-surat p {
            margin: 3px 0;
            font-size: 11pt;
        }
        
        .title {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        
        .title h3 {
            text-decoration: underline;
            font-size: 14pt;
            margin: 5px 0;
        }
        
        .content {
            text-align: justify;
            margin: 20px 0;
        }
        
        .info-table {
            width: 100%;
            margin: 20px 0;
        }
        
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 35%;
        }
        
        .info-table td:nth-child(2) {
            width: 5%;
        }
        
        .budget-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .budget-table th,
        .budget-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .budget-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .budget-table .text-right {
            text-align: right;
        }
        
        .budget-table .text-center {
            text-align: center;
        }
        
        .signature-section {
            margin-top: 40px;
        }
        
        .signature-box {
            width: 45%;
            float: right;
            text-align: center;
        }
        
        .signature-box p {
            margin: 5px 0;
        }
        
        .signature-space {
            height: 80px;
        }
        
        .signature-image {
            height: 70px;
            margin: 10px 0;
        }
        
        .underline {
            text-decoration: underline;
            font-weight: bold;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .laporan-section {
            margin: 20px 0;
        }
        
        .laporan-section h4 {
            margin: 10px 0;
            font-size: 12pt;
        }
        
        .laporan-content {
            text-align: justify;
            white-space: pre-wrap;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- KOP Surat -->
    <div class="kop-surat">
        <h2>YAYASAN JAKARTA ISLAMIC CENTRE</h2>
        <h2>JAKARTA ISLAMIC CENTRE</h2>
        <p>Jl. Raya Condet No. 30, Batu Ampar, Kramat Jati, Jakarta Timur 13520</p>
        <p>Telp: (021) 8090140 | Email: info@jic.co.id | Website: www.jic.co.id</p>
    </div>

    <!-- Title -->
    <div class="title">
        <h3>LAPORAN PERTANGGUNGJAWABAN KEGIATAN</h3>
        <p>{{ $lpj->nomor_lpj }}</p>
    </div>

    <!-- Informasi Kegiatan -->
    <table class="info-table">
        <tr>
            <td>Nama Kegiatan</td>
            <td>:</td>
            <td><strong>{{ $lpj->anggaranKegiatan->nama_kegiatan ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Kode Kegiatan</td>
            <td>:</td>
            <td>{{ $lpj->anggaranKegiatan->kode_kegiatan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Periode Pelaksanaan</td>
            <td>:</td>
            <td>
                @if($lpj->anggaranKegiatan)
                    {{ \Carbon\Carbon::parse($lpj->anggaranKegiatan->tanggal_mulai)->format('d F Y') }} 
                    s/d 
                    {{ \Carbon\Carbon::parse($lpj->anggaranKegiatan->tanggal_selesai)->format('d F Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td>Penanggung Jawab</td>
            <td>:</td>
            <td>{{ $lpj->creator->name ?? '-' }}</td>
        </tr>
    </table>

    <!-- Anggaran dan Realisasi -->
    <table class="budget-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Keterangan</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Anggaran yang Disetujui</td>
                <td class="text-right">{{ number_format($lpj->anggaranKegiatan->anggaran_disetujui ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Total Realisasi</td>
                <td class="text-right">{{ number_format($lpj->total_realisasi, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td><strong>Sisa Anggaran</strong></td>
                <td class="text-right"><strong>{{ number_format($lpj->sisa_anggaran, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Laporan Kegiatan -->
    <div class="laporan-section">
        <h4>LAPORAN PELAKSANAAN KEGIATAN</h4>
        <div class="laporan-content">{{ $lpj->laporan_kegiatan }}</div>
    </div>

    @if($lpj->catatan)
    <div class="laporan-section">
        <h4>CATATAN</h4>
        <div class="laporan-content">{{ $lpj->catatan }}</div>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section clearfix">
        <div class="signature-box">
            <p>Jakarta, {{ \Carbon\Carbon::parse($lpj->approved_at ?? $lpj->created_at)->isoFormat('D MMMM Y') }}</p>
            <p>Menyetujui,</p>
            <p><strong>{{ $lpj->approver ? ($lpj->approver->hasRole('kepala_jic') ? 'Kepala JIC' : 'Kadiv Umum') : 'Pimpinan' }}</strong></p>
            
            @if($lpj->approver && $lpj->approver->signature)
                <img src="{{ public_path('assets/signatures/' . $lpj->approver->signature) }}" alt="Signature" class="signature-image">
            @else
                <div class="signature-space"></div>
            @endif
            
            <p class="underline">{{ $lpj->approver->name ?? '___________________' }}</p>
        </div>
    </div>

    <div class="clearfix" style="margin-top: 100px;"></div>

    <!-- Footer -->
    <div style="margin-top: 50px; font-size: 10pt; color: #666;">
        <p><em>Dokumen ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</em></p>
    </div>
</body>
</html>