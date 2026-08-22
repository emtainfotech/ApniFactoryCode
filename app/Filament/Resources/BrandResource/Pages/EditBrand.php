<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\WhatsappTraits;  
use App\Models\Brand;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Livewire\WithFileUploads; 

class EditBrand extends EditRecord
{
       use WhatsappTraits;
       use WithFileUploads;
    protected static string $resource = BrandResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
      protected function afterSave(): void
    {
        // Check if the saved record's type is 'Registered'
        if ($this->record->type === 'Registered') {
            $this->sendnotification_onmultipledevice('brandregistred',$this->record->id);
        }
    }
    
    
    ///////////////////////new code according table
    public array $newBrandRowData = [];
    public array $allBrandsData = [];
     
    // 1. Declare the public property for Livewire data binding
    public string $brandSearch = ''; 

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
  protected function afterFill(): void
    {
        // 1. Initialize the new row data structure explicitly
        $this->newBrandRowData = [
            'name' => '',
            'category_id' => '',
            'type' => 'Processing',
            'adminresponse' => '',
            'image' => '' 
        ];

        // 2. Load existing inline brands
        $this->refreshInlineTableData();
    }

    /**
     * Reusable helper to pull brand records into the table array
     */
    protected function refreshInlineTableData(): void
    {
        $targetCompanyId = $this->record->company_id;
        $brands = Brand::where('company_id', $targetCompanyId)->get();

        $this->allBrandsData = [];
        foreach ($brands as $brand) {
            $this->allBrandsData[$brand->id] = [
                'name' => $brand->name,
                'type' => $brand->type,
                'adminresponse' => $brand->adminresponse,
                  'trademarkno' => $brand->trademarkno, // Added to map with database column
                'image' => $brand->image, 
            ];
        }
    }
    /**
     * Inline Update Callback Action Triggered from Blade Buttons
     */
     public $uploadedLogos = [
        'image' => null
    ];
    public function updateBrandInline(int $brandId): void
    {
        $data = $this->allBrandsData[$brandId] ?? null;

        if ($data) {
            $brand = Brand::find($brandId);
            
            if ($brand) {
                // $brand->update([
                //     'name' => $data['name'],
                //     'type' => $data['type'], 
                //     'adminresponse' => $data['adminresponse'],
                //     'trademarkno' => $data['trademarkno'], 
                // ]);
                   $updateData = [
                    'name' => $data['name'],
                    'type' => $data['type'], 
                    'adminresponse' => $data['adminresponse'],
                    'trademarkno' => $data['trademarkno'], // Saves text number directly
                ];

               if (isset($this->uploadedLogos[$brandId])) {
                    // Stores inside 'storage/app/public/brand' directory
                    $logoPath = $this->uploadedLogos[$brandId]->store('brand', 'public');
                    $updateData['image'] = $logoPath;
                }

                // Apply update payload to DB
                $brand->update($updateData);
                Notification::make()
                    ->title('Brand Record Updated Successfully!')
                    ->success()
                    ->send();
                    
                if ($brandId === $this->record->id) {
                    $this->fillForm();
                }
            }
        }
    }
    
      public function addNewBrandInline(): void
    {    // 1. Validate that a brand name was supplied
    if (empty(trim($this->newBrandRowData['name'] ?? ''))) {
        \Filament\Notifications\Notification::make()
            ->title('Brand Name is required.')
            ->danger()
            ->send();
        return;
    }
        //dd($this->newBrandRowData);
    // Convert the extracted parameter securely to a clean integer
    $finalCompanyId = (int) $this->record->company_id;
    // Safety check to block saving if the ID couldn't be extracted
    if ($finalCompanyId === 0) {
        \Filament\Notifications\Notification::make()
            ->title('Could not resolve a valid Company ID from the URL.')
            ->danger()
            ->send();
        return;
    }
// --- NEW: Handle Image Upload Processing ---
    $imagePath = null;
    if (!empty($this->uploadedLogos['image'])) {
        // Validate that the uploaded file is an actual image and under 2MB
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['image' => $this->uploadedLogos['image']],
            ['image' => 'image|max:2048']
        );

        if ($validator->fails()) {
            \Filament\Notifications\Notification::make()
                ->title('Invalid Image file. Max size allowed is 2MB.')
                ->danger()
                ->send();
            return;
        }

        /**
         * Stores image in: storage/app/public/brands
         * $imagePath will hold a relative string like: "brands/xyz123.jpg"
         */
        $imagePath = $this->uploadedLogos['image']->store('brand', 'public');
    }
    // 3. Create the brand entry using the exact URL company identifier
    Brand::create([
        'company_id'    => $this->record->company_id, // Pulled straight from edit URL path
        'mid'           => $this->record->mid ?: $finalCompanyId, 
        'user_id'       => $this->record->user_id ?: null,
        'status'        => $this->record->status ?? 1,
        'trademarkno'   => $this->record->trademarkno ?? '',
        //'addby'         => auth()->id(),

        // Dynamic user input elements captured from the table footer row
        'name'          => $this->newBrandRowData['name'],
        'category_id'   => $this->newBrandRowData['category_id'] ?: null,
        'type'          => $this->newBrandRowData['type'] ?? 'Processing',
        'adminresponse' => $this->newBrandRowData['adminresponse'] ?? '',// Assigned the upload path securely saved to storage disk
        'image'         => $imagePath,
    ]);

    // 4. Reset the footer form values back to blank status
    $this->newBrandRowData = [
        'name' => '',
        'category_id' => '',
        'type' => 'Processing',
        'adminresponse' => '',
    ];
    $this->uploadedLogos = [
                'image' => null
            ];
    // 5. Instantly refresh the sub-table UI listing
    $this->refreshInlineTableData();

    \Filament\Notifications\Notification::make()
        ->title('New Brand Added Successfully!')
        ->success()
        ->send();
    }
    
    public function deleteBrandInline(int $brandId): void
{
    // 1. Block the admin from deleting the primary record they are currently editing
    if ($brandId === $this->record->id) {
        \Filament\Notifications\Notification::make()
            ->title('Cannot Delete Active Record')
            ->body('You cannot delete the primary brand entry you are currently actively editing.')
            ->danger()
            ->send();
        return;
    }

    // 2. Locate and delete the record safely from the database
    $brand = Brand::find($brandId);
    
    if ($brand) {
        $brand->delete();

        // 3. Re-sync the dynamic table data array states to immediately reflect changes
        $this->refreshInlineTableData();

        \Filament\Notifications\Notification::make()
            ->title('Brand Record Deleted!')
            ->success()
            ->send();
    }
}
}
