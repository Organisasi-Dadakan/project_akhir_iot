@extends('layouts.app')

@section('content')
<div class="py-6 px-4 max-w-7xl mx-auto space-y-6">
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">
        Dashboard Lalu Lintas
    </h2>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach (['A', 'B', 'C'] as $lane)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">
                Jumlah Kendaraan Jalur {{ $lane }}
            </h3>
            <p class="text-3xl font-bold text-blue-600">
                {{ $latestLogs[$lane]->vehicle_count ?? 0 }}
            </p>
        </div>
        @endforeach
    </div>

    {{-- Total & Stat --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Total Hari Ini</h3>
            <p class="text-3xl font-bold text-green-600">{{ $todayTotal }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Jalur Terpadat</h3>
            <p class="text-3xl font-bold text-red-600">{{ $busiestLane->lane ?? '-' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Rata-rata Kendaraan/jam</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $avgPerHour }}</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Grafik Kendaraan per Jam (Hari Ini)</h3>
        <canvas id="trafficChart" height="100"></canvas>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Log Kendaraan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-gray-700 dark:text-gray-300">
                        <th class="px-4 py-2">Waktu</th>
                        <th class="px-4 py-2">Jalur</th>
                        <th class="px-4 py-2">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $log->recorded_at }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $log->lane }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $log->vehicle_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Ambil data dari Blade ke variabel JS
    const chartLabels = @json($chartData->pluck('hour'));
    const chartData = @json($chartData->pluck('total'));

    const ctx = document.getElementById('trafficChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Total Kendaraan',
                data: chartData,
                backgroundColor: 'rgba(59, 130, 246, 0.6)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush