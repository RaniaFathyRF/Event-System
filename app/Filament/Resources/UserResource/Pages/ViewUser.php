<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    /**
     * @var string
     */
    protected static string $resource = UserResource::class;

    /**
     * @return array
     */
    protected function getActions(): array {
        return [
           // Actions\EditAction::make(),
        ];
    }

}
