@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Rekap hari ini</h1>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <x-card title="Jalur A" :value="$rekap['A']" type="up" />
            <x-card title="Jalur B" :value="$rekap['B']" type="down" />
            <x-card title="Jalur C" :value="$rekap['C']" type="neutral" />
            <x-card title="Total Hari ini" :value="$rekap['total']" type="up" />
            <x-card title="Jalur Terpadat" :value="$rekap['terpadat']" type="down" />
            <x-card title="Rata-rata per jam" :value="$rekap['rata_rata']" type="neutral" />
        </div>

        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="text-lg font-semibold mb-2">Tren Lalu Lintas</h2>
            <canvas id="chart"></canvas>
        </div>

        <div class="bg-white rounded shadow p-4 overflow-x-auto">
            <h2 class="text-lg font-semibold mb-2">Log Aktivitas</h2>
            <table class="w-full text-sm border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-center px-4 py-2 border border-gray-300">Waktu</th>
                        <th class="text-center px-4 py-2 border border-gray-300">Jalur</th>
                        <th class="text-center px-4 py-2 border border-gray-300">Jumlah Kendaraan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($todayLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="text-center px-4 py-2 border border-gray-300">{{ $log->waktu }}</td>
                            <td class="text-center px-4 py-2 border border-gray-300">{{ $log->jalur }}</td>
                            <td class="text-center px-4 py-2 border border-gray-300">{{ $log->jumlah_kendaraan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                datasets: [
                    {
                        label: 'Jalur A',
                        data: @json($grafik->where('Jalur', 'A')->pluck('total', 'hari')->toArray()),
                        borderColor: 'blue',
                        fill: true,
                    },
                    {
                        label: 'Jalur B',
                        data: @json($grafik->where('Jalur', 'B')->pluck('total', 'hari')->toArray()),
                        borderColor: 'orange',
                        fill: true,
                    },
                    {
                        label: 'Jalur C',
                        data: @json($grafik->where('Jalur', 'C')->pluck('total', 'hari')->toArray()),
                        borderColor: 'red',
                        fill: true,
                    },
                ]
            }
        });
    </script>
@endsection