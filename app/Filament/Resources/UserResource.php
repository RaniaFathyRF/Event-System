<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Spatie\Permission\Models\Role;



class UserResource extends Resource
{
    /**
     * @var string|null
     */
    protected static ?string $model = User::class;
    /**
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @return bool
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(), // Hash the password,
                Forms\Components\Select::make('roles') // Roles select field
                ->default(function () {
                    // Set default role by name
                    $defaultRole = Role::where('name', 'user')->first(); // Replace 'user' with your default role name
                    return $defaultRole ? [$defaultRole->id] : []; // Return role ID or empty array
                    })
                ->required()
                ->options(Role::all()->pluck('name', 'id')) // Fetch roles from the database
                ->relationship('roles', 'name'), // Use Spatie's relationship
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
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('roles.name') // Display roles in the table
                ->label('Roles')
                    ->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i:s')->hidden(),
                TextColumn::make('updated_at')->dateTime('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_at')->label('Created Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['created_at'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date));
                    }),
                Filter::make('updated_at')
                    ->form([
                        DatePicker::make('updated_at')->label('Updated Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['updated_at'] ?? null, fn ($q, $date) => $q->whereDate('updated_at', $date));
                    }),
            ])
            ->recordUrl(
                fn(User $record): string => Pages\ViewUser::getUrl([$record->id]),
            )
            ->actions([
                EditAction::make(),  // Edit user details
                DeleteAction::make(), // Delete user
            ])
            ->defaultSort('updated_at', 'desc');

    }

    /**
     * @param Infolist $infolist
     * @return Infolist
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        $infolist
            ->schema([
                TextEntry::make('id')
                    ->label(__('ID:')),
                TextEntry::make('name')
                    ->label(__('Name:')),
                TextEntry::make('email')
                    ->label(__('Email:')),
                TextEntry::make('phone')
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
            //
        ];
    }

    /**
     * @return array|\Filament\Resources\Pages\PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),

        ];
    }
}
