<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keluar - {{ $surat->perihal }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; }
        .container { width: 80%; margin: auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h3 { margin: 0; }
        .header p { margin: 0; }
        .content { margin-top: 20px; }
        .signature { margin-top: 50px; text-align: right; }
        .signature p { margin: 0; }
        .footer { text-align: center; margin-top: 50px; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>KOP SURAT KAMPUS ANDA</h3>
            <p>{{ $kampus_nama }}</p>
            <p>{{ $kampus_alamat }}</p>
            <hr style="border: 1px solid black;">
        </div>

        <p style="text-align: right;">{{ $kampus_nama_kota }}, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d F Y') }}</p>
        <p>Nomor: {{ $surat->nomor_surat }}</p>
        <p>Sifat: {{ $surat->sifat->nama_sifat ?? 'Biasa' }}</p>
        <p>Perihal: {{ $surat->perihal }}</p>
        <br>
        <p>Yth. {{ $surat->penerima }}</p>
        <p>Di tempat</p>
        <br>

        <div class="content">
            {{-- Isi surat bisa berupa teks biasa atau HTML jika perlu formatting --}}
            <pre style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; white-space: pre-wrap;">{{ $surat->isi_surat }}</pre>
        </div>

        <div class="signature">
            <p>Hormat kami,</p>
            <br><br><br> {{-- Ruang untuk tanda tangan --}}
            <p>(Nama Pimpinan)</p>
            <p>Jabatan Pimpinan</p>
        </div>

        <div class="footer">
            <p>Telp: (021)xxxxxx | Email: info@kampus.com | Website: kampus.com</p>
        </div>
    </div>
</body>
</html>