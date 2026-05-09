<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Top referrers (30 days)</x-slot>

        @php($rows = $this->getRows())

        @if($rows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No external referrers yet.</p>
        @else
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="text-left font-medium py-1">Source</th>
                        <th class="text-right font-medium py-1">Views</th>
                        <th class="text-right font-medium py-1">Uniques</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($rows as $row)
                        <tr>
                            <td class="py-1.5 truncate max-w-xs text-gray-900 dark:text-gray-100">{{ $row->host }}</td>
                            <td class="py-1.5 text-right tabular-nums">{{ number_format($row->views) }}</td>
                            <td class="py-1.5 text-right tabular-nums text-gray-500">{{ number_format($row->uniques) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
