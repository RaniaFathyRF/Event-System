<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\RestoreAction;




use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @return bool
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('super_admin');
    }

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    /**
     * @param $record
     * @return bool
     */
    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    /**
     * @param $record
     * @return bool
     */
    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('super_admin');
    }

    /**
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ticket_id')
                    ->label(__('Ticket ID'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ticket_name')
                    ->label(__('Ticket Title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->label(__('Ticket Status'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->searchable()
                    ->preload()
                    ->options(User::all()->pluck('name', 'id')) // Fetch roles from the database
                    ->relationship('user', 'name')
                    ->required(),

            ]);
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label(__('Ticket ID'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_name')
                    ->label(__('Ticket Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label(__('Phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(
                fn(Ticket $record): string => Pages\ViewTicket::getUrl([$record->id]),
            )
            ->filters([
                // Add a filter for soft-deleted rows
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(), // Delete
                RestoreAction::make()->label('Restore') // Add Restore Action
                ->visible(fn($record) => $record->trashed()), // Show only for trashed records
            ])
            ->bulkActions([

            ]);
    }

    /**
     * @param Infolist $infolist
     * @return Infolist
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        $infolist
            ->schema([
                TextEntry::make('ticket_id')
                    ->label(__('Ticket ID:')),
                TextEntry::make('ticket_name')
                    ->label(__('Ticket Name:')),
                TextEntry::make('status')
                    ->label(__('Ticket Status:')),
                TextEntry::make('user.name')
                    ->label(__('User Name:')),
                TextEntry::make('user.email')
                    ->label(__('Email:')),
                TextEntry::make('user.phone')
                    ->label(__('Phone:')),
            ]);
        return $infolist;
    }

    /**
     * @return array|\class-string[]|\Filament\Resources\RelationManagers\RelationGroup[]|\Filament\Resources\RelationManagers\RelationManagerConfiguration[]
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    /**
     * @return array|\Filament\Resources\Pages\PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
            'view' => Pages\ViewTicket::route('/{record}'), // Add this line

        ];
    }
}
