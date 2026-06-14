<?php

namespace Modules\Order\Filament\Resources\Orders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Order\Filament\Resources\Orders\Pages\EditOrder;
use Modules\Order\Filament\Resources\Orders\Pages\ListOrders;
use Modules\Order\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use Modules\Order\Filament\Resources\Orders\Schemas\OrderForm;
use Modules\Order\Filament\Resources\Orders\Tables\OrdersTable;
use Modules\Order\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function getNavigationGroup(): ?string
    {
        return 'Orders';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        // Orders are placed through the storefront, so the admin panel only
        // lists and manages them — there is no "create order" page here.
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
