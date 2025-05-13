<?php

namespace App\Filament\Resources\Shop\ProductResource\Pages;

use App\Filament\Resources\Shop\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array {
        if($data['thumbnail_type'] == 'image')
            $data['thumbnail_image'] = asset($data['thumbnail']);
        if($data['thumbnail_type'] == 'country')
            $data['thumbnail_country'] = $data['thumbnail'];
        // dd($data);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array {

        if($data['thumbnail_type'] == 'image')
            $data['thumbnail'] = Storage::url($data['thumbnail_image']);
        if($data['thumbnail_type'] == 'country')
            $data['thumbnail'] = $data['thumbnail_country'];
        return $data;
    }
}
