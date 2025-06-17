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
    const lastCheck = "{{ \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s') }}";  // ✅ Sama persis dengan format di DB

    setInterval(() => {
        fetch(`/sendToBlynk/${encodeURIComponent(lastCheck)}`)
            .then(response => response.json())
            .then(data => {
                if (data.ada_data_baru) {
                    console.log("📡 Data baru terdeteksi, mengirim ke Blynk...");
                    fetch('/sendToBlynk')
                        .then(() => {
                            location.reload();
                        });
                } else {
                    console.log("⏳ Tidak ada data baru.");
                }
            })
            .catch(error => console.error('❌ Gagal cek data baru:', error));
    }, 10000);
</script>
