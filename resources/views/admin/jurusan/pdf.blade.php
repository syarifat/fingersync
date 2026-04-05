<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kamus ID Jurusan</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f97316; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #ea580c; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background-color: #fff7ed; color: #ea580c; font-weight: bold; }
        .text-center { text-align: center; }
        .id-badge { font-weight: bold; color: #fff; background-color: #374151; padding: 4px 8px; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Buku Referensi ID Jurusan</h2>
        <p>Gunakan ID ini untuk mengisi kolom <strong>KODE JURUSAN</strong> pada Template Import Excel Siswa.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="20%" class="text-center">KODE JURUSAN</th>
                <th>NAMA PROGRAM KEAHLIAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jurusan as $j)
            <tr>
                <td class="text-center"><span class="id-badge">{{ $j->kode }}</span></td>
                <td><strong>{{ $j->nama }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>