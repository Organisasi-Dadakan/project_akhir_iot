@props(['title', 'value', 'type' => 'neutral'])

@php
    $commonIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18" />
                   </svg>'; // Ikon jalan (garis paralel)

    $icons = [
        'Jalur A' => $commonIcon,
        'Jalur B' => $commonIcon,
        'Jalur C' => $commonIcon,
        'Total Hari ini' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17v-6h4v6" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17v-10h4v10" />
                           </svg>',
        'Jalur Terpadat' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                           </svg>',
        'Rata-rata per jam' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                               </svg>',
    ];
@endphp

<div class="p-4 bg-white rounded shadow flex items-center space-x-3">
    {!! $icons[$title] ?? '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                         </svg>' !!}
    <div>
        <div class="text-sm text-gray-600">{{ $title }}</div>
        <div class="text-2xl font-bold mt-1">{{ $value }}</div>
    </div>
</div>
