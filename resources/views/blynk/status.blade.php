<h3>Status Kirim Data ke Blynk</h3>

<p>⏰ Terakhir diperbarui: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>

<ul>
@foreach ($hasil as $row)
    @if (isset($row['error']))
        <li>Jalur {{ $row['jalur'] }} - ❌ {{ $row['error'] }}</li>
    @else
        <li>Jalur {{ $row['jalur'] }} -
            {{ $row['jumlah_kendaraan'] ? '✅ jumlah_kendaraan_rt' : '❌ jumlah_kendaraan_rt' }}
            |
            {{ $row['durasi_lampu_hijau'] ? '✅ durasi_lampu_hijau' : '❌ durasi_lampu_hijau' }}
        </li>
    @endif
@endforeach
</ul>

<script>
    // Auto reload setiap 120 detik (120000 milidetik)
    setTimeout(function () {
        location.reload();
    }, 120000);
</script>