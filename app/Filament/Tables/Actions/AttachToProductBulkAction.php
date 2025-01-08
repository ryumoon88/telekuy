<?php
namespace App\Filament\Tables\Actions;

use App\Enums\ProductType;
use App\Filament\Resources\Shop\ProductResource;
use App\Filament\Resources\Telegram\AccountResource\Pages\ListAccounts;
use App\Models\Shop\Product;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bundle;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;

class AttachToProductBulkAction extends BulkAction {

    public static function getDefaultName(): ?string
    {
        return 'attach_to_product';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->label($this->getLabel());

        $this->successNotificationTitle('Selected records attached to the product');

        $this->fetchSelectedRecords();

        $this->form([
            Forms\Components\Select::make('product_id')
                ->options(fn(ListRecords $livewire) => match($livewire->getModel()) {
                    Account::class => Product::where('type', ProductType::Account)->pluck('name', 'id'),
                    Bundle::class => Product::where('type', ProductType::Bundle)->pluck('name', 'id'),
                })
                ->createOptionForm(fn(Forms\Form $form, ListRecords $livewire) => match($livewire->getModel()) {
                    Account::class => ProductResource::form($form)->operation('createOptionAccount'),
                    Bundle::class => ProductResource::form($form)->operation('createOptionBundle'),
                })
                ->createOptionUsing(fn(array $data, ListRecords $livewire) => match($livewire->getModel()) {
                    Account::class => Product::create(array_merge($data, ['type' => ProductType::Account]))->getKey(),
                    Bundle::class => Product::create(array_merge($data, ['type' => ProductType::Bundle]))->getKey()
                })
                ->preload()
                ->dehydrated()
        ]);

        $this->before(function(Collection $records){
            if($records->every(fn($record) => !!$record->product)){
                $this->failureNotification(Notification::make()->title('Failed to attach account to product')->body('Some of the selected account has been attached to a product.')->danger()->color('danger'));
                $this->failure();
                $this->cancel();
            }
        });

        $this->action(function(array $data, Collection $records, ListRecords $livewire) {
            switch($livewire->getModel()){
                case Account::class:
                    $product = Product::find($data['product_id']);
                    $product->accounts()->attach($records);
                    $this->success();
                    return;
                case Bundle::class:
                    $product = Product::find($data['product_id']);
                    $product->bundles()->attach($records);
                    $this->success();
                    return;
            }
        });

        $this->deselectRecordsAfterCompletion();

        // $this->hidden(function (HasTable $livewire): bool {
        //     // dd($livewire);
        //     // $trashedFilterState = $livewire->getTableFilterState(TrashedFilter::class) ?? [];

        //     // if (! array_key_exists('value', $trashedFilterState)) {
        //     //     return false;
        //     // }

        //     // if ($trashedFilterState['value']) {
        //     //     return false;
        //     // }
        //     $selectedTableRecords = $livewire->getSelectedTableRecords(true);
        //     if($selectedTableRecords->count()) {
        //         dd("AA");
        //     }
        //     return false;
        //     // return filled($trashedFilterState['value']);
        // });
    }
}
