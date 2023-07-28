<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
//use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('first_name')
                             ->maxLength(255)
                             ->required(),
                    TextInput::make('last_name')
                             ->maxLength(255)
                             ->required(),
                    DatePicker::make('birth_date'),
                ])->columns(2),
                Card::make()->schema([
                    Select::make('department_id')
                          ->relationship('department', 'name')
                          ->required(),
                    DatePicker::make('date_hired')
                              ->required(),
                ])->columns(2),
                Card::make()->schema([
//                    Select::make('country_id')
//                          ->relationship('country', 'name')
//                          ->required(),
//                    Select::make('state_id')
//                          ->relationship('state', 'name'),
//                    Select::make('city_id')
//                          ->relationship('city', 'name'),
                    Select::make('country_id')
                          ->label('Country')
                          ->options(Country::all()->pluck('name', 'id')->toArray())
                          ->reactive()
//                          ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),
                          ->afterStateUpdated(function (callable $set) {
                              $set('state_id', null);
                              $set('city_id', null);
                          })
                          ->required(),
                    Select::make('state_id')
                          ->label('State/County')
                          ->options(function (callable $get) {
                              $country = Country::find($get('country_id'));
                              if (! $country) {
                                  return State::all()->pluck('name', 'id');
                              }
                              return $country->states->pluck('name', 'id');
                          })
                          ->reactive()
                          ->afterStateUpdated(fn (callable $set) => $set('city_id', null))
                          ->required(),
                    Select::make('city_id')
                          ->label('City')
                          ->options(function (callable $get) {
                              $state = State::find($get('state_id'));
                              if (! $state) {
                                  return City::all()->pluck('name', 'id');
                              }
                              return $state->cities->pluck('name', 'id');
                          })
                          ->required(),
                    TextInput::make('address')
                             ->label('Street')
                             ->maxLength(255),
                    TextInput::make('postcode')
                             ->maxLength(255),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable()->searchable(),
                TextColumn::make('country.name')->sortable()->searchable(),
                TextColumn::make('date_hired')->date()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('department')->relationship('department', 'name'),
                SelectFilter::make('country')->relationship('country', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
