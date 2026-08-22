<?php
/*
namespace App\Filament\Widgets;
use Filament\Widgets\LineChartWidget;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;
 class UsersChart extends ChartWidget implements HasForms
{
    use InteractsWithForms;

    protected static ?string $heading = 'Users Registered (Filterable - Without Carbon)';

    public ?array $filterFormData = [];
   protected static string $view = 'filament.widgets.users-chart';

    // The form property is needed to initialize the form
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('filterFormData');
    }

    protected function getFormSchema(): array
    {
        $currentYear = (int) date('Y');

        return [
            Select::make('month')
                ->options(function () {
                    $months = [];
                    for ($m = 1; $m <= 12; $m++) {
                        $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
                    }
                    return $months;
                })
                ->default((int) date('n'))
                ->afterStateUpdated(fn () => $this->updateChartData()), // Use afterStateUpdated
            Select::make('year')
                ->options(function () use ($currentYear) {
                    $years = [];
                    for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++) {
                        $years[$year] = $year;
                    }
                    return $years;
                })
                ->default($currentYear)
                ->afterStateUpdated(fn () => $this->updateChartData()), // Use afterStateUpdated
        ];
    }

    protected function getData(): array
    {
        $months = [];
        $userCounts = [];

        // Loop through the last 12 months to gather data
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y'); // e.g., "Jan 2025"
            $months[] = $monthName;

            $count = User::whereYear('created_at', $month->year)
                         ->whereMonth('created_at', $month->month)
                         ->count();
            $userCounts[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sellers',
                    'data' => $userCounts,
                    'borderColor' => '#9BD0F5', // Optional: customize line color
                    'backgroundColor' => 'rgba(155, 208, 245, 0.2)', // Optional: customize fill color
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public  function updateChartData(): void
    {
        $this->emitSelf('refresh'); // Tell Livewire to refresh the component
    }

}
*/
namespace App\Filament\Widgets;
use Filament\Widgets\LineChartWidget;
use App\Models\User;
class UsersChart extends LineChartWidget
{
    protected static ?string $heading = 'Sellers';

    protected function getData(): array
    {
        $months = [];
        $userCounts = [];

        // Loop through the last 12 months to gather data
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y'); // e.g., "Jan 2025"
            $months[] = $monthName;

            $count = User::whereYear('created_at', $month->year)
                         ->whereMonth('created_at', $month->month)
                         ->count();
            $userCounts[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sellers',
                    'data' => $userCounts,
                    'borderColor' => '#9BD0F5', // Optional: customize line color
                    'backgroundColor' => 'rgba(155, 208, 245, 0.2)', // Optional: customize fill color
                ],
            ],
            'labels' => $months,
        ];
    }
}

