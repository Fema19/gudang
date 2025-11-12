<!DOCTYPE html>
<html>
<head>
    <title>Info Barang Masuk - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="print-date">
        Dicetak pada: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
    </div>
    
    <h1>Daftar Barang Masuk</h1>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Stok Sebelum</th>
                <th>Stok Setelah</th>
                <th>Keterangan</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['nama'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['stok_before'] }}</td>
                <td>{{ $row['stok_after'] }}</td>
                <td>{{ $row['type'] }}</td>
                <td>{{ $row['created_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
