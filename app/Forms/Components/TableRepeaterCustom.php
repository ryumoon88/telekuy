<?php

namespace App\Forms\Components;

use Awcodes\TableRepeater\Components\TableRepeater;
use Filament\Forms\Components\Field;

class TableRepeaterCustom extends TableRepeater
{
    protected string $view = 'forms.components.table-repeater-custom';

    protected bool $hasSummary = false;

    protected array $summaryColumns = [];

    public function sum(array $columns): static{
        $this->hasSummary = true;
        $this->summaryColumns = $columns;
        return $this;
    }

    public function hasSummary(): bool{
        return $this->hasSummary;
    }

    public function getSummaryColumns(): array {
        return $this->summaryColumns;
    }

    public function getView(): string
    {
        return 'forms.components.table-repeater-custom';
    }

    public function getSummaryTotal(string $column): string
    {
        $total = 0;
        foreach ($this->getState() as $item) {
            if (isset($item[$column])) {
                $total += intval($item[$column]);
            }
        }

        return $total; // Format as Indonesian currency
    }
}
