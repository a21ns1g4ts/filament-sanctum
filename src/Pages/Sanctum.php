<?php

namespace Devtical\Sanctum\Pages;

use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Sanctum extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static string $view = 'filament-sanctum::pages.sanctum';

    protected static ?string $slug = 'sanctum';

    protected function getTitle(): string
    {
        return trans('Sanctum');
    }

    protected static function getNavigationLabel(): string
    {
        return trans('Sanctum');
    }

    protected function getTableQuery(): Builder
    {
        return Auth::user()->tokens()->getQuery();
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(trans('Name'))
                ->sortable()
                ->searchable(),
            Tables\Columns\TagsColumn::make('abilities')
                ->label(trans('Abilities')),
            Tables\Columns\TextColumn::make('last_used_at')
                ->label(trans('Last used at'))
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label(trans('Created at'))
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('new')
                ->label(trans('Create a new Token'))
                ->action(function (array $data) {
                    $user = Auth::user();
                    $token = $user->createToken($data['name'], $data['abilities'])->plainTextToken;
                    request()->session()->flash('sanctum-token', $token);
                    Notification::make()
                        ->title(trans('Saved successfully'))
                        ->success()
                        ->icon('heroicon-o-finger-print')
                        ->title(trans('Token was created successfully'))
                        ->send();

                    return redirect(config('filament.path').'/sanctum');
                })
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label(trans('Token Name'))
                        ->required(),
                    Forms\Components\CheckboxList::make('abilities')
                        ->label(trans('Abilities'))
                        ->options(config('filament-sanctum.abilities'))
                        ->columns(config('filament-sanctum.columns')),
                ]),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('revoke')
                ->label(trans('Revoke'))
                ->action(fn (Collection $records) => $records->each->delete())
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }
}
