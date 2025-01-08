<?php
namespace App\Filament\Tables\Actions;

use App\Models\Telegram\Account;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Tapp\FilamentCountryCodeField\Forms\Components\CountryCodeSelect;

class ImportAccountsAction extends Action {
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->modalHeading("Import Accounts");
        $this->visible(Gate::allows('import_telegram::account'));
        $this->form([
            Forms\Components\Section::make('Account Detail')
                ->schema([
                    CountryCodeSelect::make('country_code'),
                    TextInput::make('purchase_price')
                        ->prefix('Rp')
                        ->mask(RawJs::make('$money($input)'))
                        ->label('Purchase Price (per account)')
                        ->stripCharacters(','),
                    TextInput::make('selling_price')
                        ->prefix('Rp')
                        ->label('Selling Price (per account)')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                ])->collapsible(),
            Forms\Components\Section::make('Upload Accounts')
                ->schema([
                    FileUpload::make('accounts')
                        ->directory('uploads')
                        ->disk('local')
                        // ->acceptedFileTypes(['application/zip', 'application/x-rar-compressed'])
                        // ->saveUploadedFileUsing(function (FileUpload $component, TemporaryUploadedFile $file) {
                        //     $folder_names = extractFirstFolderFromArchive($file);
                        //     return $folder_names;
                        // })
                ])
        ]);

        $this->action(function($data) {
            $data['accounts'] = extractAccounts($data['accounts']);
            if(Account::ImportAccounts($data)){
                $this->success();
                return;
            }
            $this->failure();
        });
    }
}
