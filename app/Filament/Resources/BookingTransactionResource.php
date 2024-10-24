<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Workshop;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\BookingTransaction;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Product and Price')
                    ->schema([
                        Forms\Components\Select::make('workshop_id')
                        ->relationship( 'workshop','name' )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(Function ($state, callable $set){
                            $workshop = Workshop::find($state);
                            $set('price', $workshop ? $workshop->price : 0);
                        })
                        ->afterStateUpdated(Function ($state, callable $get, callable $set){
                            $workshop = Workshop::find($state);
                            $set('price', $workshop ? $workshop->price : 0);
                        }),
                        Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->prefix('Qty People')
                        ->live()
                        ->afterStateUpdated(Function ($state, callable $get, callable $set){
                            $price = $get('price');
                            $subTotal = $price  * $state;
                            $totalPpn = $subTotal * 0.11;
                            $totalAmount = $subTotal + $totalPpn;

                            $set('total_amount', $totalAmount);

                            $participants = $get('participants', 0, $state) ?? [];
                            $currentCount = count($participants);

                            if ($state > $currentCount) {
                                for ($i = $currentCount; $i < $state; $i++) { 
                                    $participants[] = ['name' => '', 'occupation' => '', 'email' => ''];
                                }
                            }else {
                                $participants =array_slice($participants, 0, $state);
                            }
                            $set ('participants', $participants);
                        })
                        ->afterStateHydrated(Function ($state, callable $get, callable $set){
                            // calculate total amount when  the form  is loaded  with existing data
                           $price = $get('price');
                           $subTotal = $price  * $state;
                           $totalPpn = $subTotal * 0.11;
                           $totalAmount = $subTotal + $totalPpn;

                           $set('total_amount', $totalAmount);
                        }),
                        Forms\Components\TextInput::make('total_amount' )
                        ->required()
                        ->numeric()
                        ->prefix('IDR')
                        ->readOnly()
                        ->helperText('Harga sudah include PPN 11%'),

                        Repeater::make( 'Participants' )
                        ->schema([
                            Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                ->label('Participant Name')
                                ->required(),

                                Forms\Components\TextInput::make('occupation')
                                ->label('Occupation')
                                ->required(),

                                Forms\Components\TextInput::make('email')
                                ->label('Email')
                                ->required(),
                            ]),
                        ])
                        ->column(1)
                        ->label('Participant Detail')    
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
