<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Riwayat Peminjaman</title>
    <style>
        @media print { .no-print { display: none; } }
        .no-print { margin-bottom: 12px; }
    </style>
</head>
<body onload="window.print()">
<div class="no-print"><button type="button" onclick="window.print()">Cetak</button></div>
@include('admin.riwayat._dokumen')
</body>
</html>
