<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <x-heroicon-o-calendar-days style="width: 1.25rem; height: 1.25rem; color: rgb(107 114 128);" />
                Your Activity
            </div>
        </x-slot>

        <style>
            .contrib-grid {
                display: grid;
                grid-template-columns: repeat({{ $weekCount }}, 1fr);
                grid-template-rows: repeat(7, 1fr);
                gap: 3px;
                width: 100%;
            }

            .contrib-cell {
                aspect-ratio: 1 / 1;
                border-radius: 2px;
                transition: box-shadow 0.15s;
                cursor: default;
            }

            .contrib-cell:hover {
                box-shadow: 0 0 0 1px rgb(156 163 175);
            }

            .dark .contrib-cell:hover {
                box-shadow: 0 0 0 1px rgb(75 85 99);
            }

            .contrib-legend {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.5rem;
                margin-top: 0.75rem;
                font-size: 0.7rem;
                color: rgb(107 114 128);
            }

            .dark .contrib-legend {
                color: rgb(156 163 175);
            }

            .contrib-legend-scale {
                display: flex;
                gap: 2px;
            }

            .contrib-legend-cell {
                width: 8px;
                height: 8px;
                border-radius: 2px;
            }

            .contrib-completed { background-color: rgb(16 185 129); }
            .contrib-partial { background-color: rgb(167 243 208); }
            .dark .contrib-partial { background-color: rgb(6 95 70); }
            .contrib-skipped { background-color: rgb(254 202 202); }
            .dark .contrib-skipped { background-color: rgb(127 29 29); }
            .contrib-pending { background-color: rgb(229 231 235); }
            .dark .contrib-pending { background-color: rgb(55 65 81); }
            .contrib-none { background-color: rgb(243 244 246); }
            .dark .contrib-none { background-color: rgb(31 41 55); }
        </style>

        <div class="contrib-grid">
            @foreach ($weeks as $weekIndex => $week)
                @foreach ($week as $dayIndex => $day)
                    <div
                        class="contrib-cell {{ $day['color'] }}"
                        style="grid-row: {{ $dayIndex + 1 }}; grid-column: {{ $weekIndex + 1 }};"
                        title="{{ $day['label'] }}"
                    ></div>
                @endforeach
            @endforeach
        </div>

        <div class="contrib-legend">
            <span>Less</span>
            <div class="contrib-legend-scale">
                <div class="contrib-legend-cell contrib-pending"></div>
                <div class="contrib-legend-cell contrib-partial"></div>
                <div class="contrib-legend-cell contrib-completed"></div>
            </div>
            <span>More</span>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
