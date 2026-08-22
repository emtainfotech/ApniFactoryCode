<div>
    <x-filament::widget>
        <x-filament::card>
            <div class="flex items-center justify-between">
                {{ $this->form }} {{-- Render the filters form here --}}
            </div>
            <div>
                <canvas id="usersChart"></canvas>
            </div>
        </x-filament::card>
    </x-filament::widget>
</div>
