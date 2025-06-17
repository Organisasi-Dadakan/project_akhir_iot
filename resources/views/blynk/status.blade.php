<h3>Status Kirim Data ke Blynk</h3>

<p>⏰ Terakhir diperbarui: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>

<ul>
@foreach ($hasil as $row)
    @if (isset($row['error']))
        <li>Jalur {{ $row['jalur'] }} - ❌ {{ $row['error'] }}</li>
    @elseif ($row['jalur'] === 'Semua Jalur')
        <li>Total Kendaraan per Jam ({{ $row['jam'] }}) - 
            {{ $row['status'] ? '✅ terkirim ke V3' : '❌ gagal kirim ke V3' }} |
            Jumlah: {{ $row['jumlah_kendaraan_per_jam'] }}
        </li>
    @else
        <li>Jalur {{ $row['jalur'] }} - 
            {{ $row['jumlah_kendaraan'] ? '✅ jumlah_kendaraan_rt' : '❌ jumlah_kendaraan_rt' }} |
            {{ $row['jumlah_chart'] ? '✅ chart' : '❌ chart' }} |
            {{ $row['durasi_lampu_hijau'] ? '✅ durasi_lampu_hijau' : '❌ durasi_lampu_hijau' }} |
            {{ isset($row['durasi_lampu_merah']) ? 
                ($row['durasi_lampu_merah'] ? '✅ durasi_lampu_merah (' . 
                    ($row['jalur'] == 'A' ? 'V8' : ($row['jalur'] == 'B' ? 'V9' : 'V10')) . ')' 
                    : '❌ durasi_lampu_merah') : '' }}
        </li>
    @endif
@endforeach

@if (isset($status_terpadat))
    <li>Jalur Terpadat (V4) - 
        {{ $status_terpadat ? '✅ sukses dikirim' : '❌ gagal dikirim' }}
    </li>
@endif
</ul>

<script>
    // Auto reload setiap 10 detik
    setTimeout(function () {
        location.reload();
    }, 20000);
</script>
