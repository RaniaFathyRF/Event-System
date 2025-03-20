<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    /**
     * @var string
     */
    protected static string $resource = TicketResource::class;

    /**
     * @return array|\Filament\Actions\Action[]|\Filament\Actions\ActionGroup[]
     */
    protected function getActions(): array
    {
        return [
           // Actions\EditAction::make(),
        ];
    }
}
