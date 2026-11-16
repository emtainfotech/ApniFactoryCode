<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductReviews;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Category;
use Auth;
use DB;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use App\Helper\helper;
use App\Services\BulkProductSpreadsheetService;
class ProductController extends Controller
{
 //  list / store destroy
  public function store(Request $request){ 
      $previousdata = json_decode($request->previousdata);
      $name = $previousdata->name ?? 'Untitled Product';
      $mid = $previousdata->maincategory ?? 0;
      $cid = $previousdata->category ?? 0;
      $description = $previousdata->details ?? '';
      $Brand = $previousdata->brand ?? 0;
      $tax = $previousdata->tax ?? 0;
      $hsncode = $previousdata->hsncode ?? '';
      $delimiter = '-';
      $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $name))))), $delimiter));
      $slug = $slug.'-'.rand(1000,999999);
      $images = [];
      $img = 'product/default.jpg';
      if($files=$request->file('images')){
        $targetDir = storage_path('app/public/product');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }
        foreach($files as $key=>$file){
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $fname = time() . '_' . uniqid() . '.' . $ext;
            $file->move($targetDir, $fname);
            $images[]='product/'.$fname;
            if($key==0){$img = 'product/'.$fname;}
            }
        }
        $proid = Product::orderBy('id','desc')->first();
        $prid = empty($proid) ? 1 : $proid->id + 1;

        // Parse shade groups JSON if provided from Step 2
        $shadeGroupsInput = $request->input('shade_groups_data');
        $parsedGroups = null;
        if (!empty($shadeGroupsInput)) {
            $parsedGroups = is_string($shadeGroupsInput) ? json_decode($shadeGroupsInput, true) : (is_array($shadeGroupsInput) ? $shadeGroupsInput : null);
        }

        // Check if any group or attribute has GST included
        $hasGstIncluded = false;
        if (!empty($parsedGroups) && is_array($parsedGroups)) {
            foreach ($parsedGroups as $grp) {
                if (!empty($grp['is_gst_included']) && ((int)$grp['is_gst_included'] === 1 || $grp['is_gst_included'] === '1' || $grp['is_gst_included'] === true)) {
                    $hasGstIncluded = true;
                    break;
                }
            }
        }
        if (!$hasGstIncluded && $request->has('is_gst_included')) {
            foreach ((array)$request->is_gst_included as $gstVal) {
                if ((int)$gstVal === 1 || $gstVal === '1' || $gstVal === true) {
                    $hasGstIncluded = true;
                    break;
                }
            }
        }

        // Auto-run pending migrations if column has not been added on live database yet
        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'has_gst_included') || !\Illuminate\Support\Facades\Schema::hasColumn('products', 'shade_groups')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                // If migration fails, continue with fallback column checks
            }
        }

        $approvalStatus = $hasGstIncluded ? 'pending_review' : 'approved';
        $productStatus = $hasGstIncluded ? 'Deactive' : 'Active';

        $pary = array(
                      "maincategory_id" => $mid,
                      "category_id"     => $cid,
                      "subcategory_id"  => 0,
                      "name"            => $name,
                      "slug"            => $slug,
                      "title"           => $slug,
                      "description"     => $description,
                      "status"          => $productStatus,
                      "approval_status" => $approvalStatus,
                      "brand_id"        => $Brand,
                      "product_id"      => $prid,
                      "user_id"         => Auth::user()->id,
                      "multipleimages"  => json_encode($images),
                      "image"           => $img,
                      "hsncode"         => $hsncode,
                      "tax"             => $tax
                      );

        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'has_gst_included')) {
            $pary['has_gst_included'] = $hasGstIncluded ? 1 : 0;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'submitted_at')) {
            $pary['submitted_at'] = $hasGstIncluded ? now() : null;
        }
        if (!empty($parsedGroups) && \Illuminate\Support\Facades\Schema::hasColumn('products', 'shade_groups')) {
            $pary['shade_groups'] = json_encode($parsedGroups);
        }

        $pid = Product::insertGetId($pary);
        
        $pricingService = app(\App\Services\PaintPricingService::class);
        $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), null, $cid, null, $mid);

        $hasIsGstColumn = \Illuminate\Support\Facades\Schema::hasColumn('product_attributes', 'is_gst_included');
        $hasGstRateColumn = \Illuminate\Support\Facades\Schema::hasColumn('product_attributes', 'gst_rate');
        $hasGroupNameColumn = \Illuminate\Support\Facades\Schema::hasColumn('product_attributes', 'group_name');
        $hasMrpColumn = \Illuminate\Support\Facades\Schema::hasColumn('product_attributes', 'mrp');

        // 1. Group-based pricing insertion (Asian Paints Style)
        if (!empty($parsedGroups) && is_array($parsedGroups)) {
            foreach ($parsedGroups as $gIndex => $grp) {
                $groupName = trim($grp['name'] ?? ('Group ' . ($gIndex + 1)));
                $isGroupGst = !empty($grp['is_gst_included']) && ((int)$grp['is_gst_included'] === 1 || $grp['is_gst_included'] === '1' || $grp['is_gst_included'] === true);
                $groupGstRate = !empty($grp['gst_rate']) ? (float)$grp['gst_rate'] : (float)($tax ?: 18.00);
                $shades = (array)($grp['shades'] ?? []);
                $packs = (array)($grp['packs'] ?? []);

                foreach ($shades as $shadeId) {
                    foreach ($packs as $packId => $packData) {
                        $sellerPrice = (float)($packData['seller_price'] ?? 0);
                        if (isset($packData['price']) && is_numeric($packData['price']) && (float)$packData['price'] > 0) {
                            $customerPrice = round((float)$packData['price'], 2);
                        } else {
                            $customerPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
                        }
                        $mrp = isset($packData['mrp']) && is_numeric($packData['mrp']) ? round((float)$packData['mrp'], 2) : null;
                        $packLitres = $pricingService->parsePackLitres($packId);

                        $patbu = array(
                            "product_id"      => $pid,
                            "color"           => $shadeId,
                            "quantity"        => $packId,
                            "oldprice"        => $customerPrice,
                            "seller_price"    => $sellerPrice,
                            "commission_rate" => $commissionRate,
                            "pack_litres"     => $packLitres,
                            "price"           => $customerPrice,
                            "status"          => '1',
                        );

                        if ($hasGroupNameColumn) {
                            $patbu['group_name'] = $groupName;
                        }
                        if ($hasIsGstColumn) {
                            $patbu['is_gst_included'] = $isGroupGst ? 1 : 0;
                        }
                        if ($hasGstRateColumn) {
                            $patbu['gst_rate'] = $groupGstRate;
                        }
                        if ($hasMrpColumn) {
                            $patbu['mrp'] = $mrp;
                        }

                        ProductAttributes::insert($patbu);
                    }
                }
            }
        } elseif ($request->has('boxpacking')) {
            // 2. Fallback: Flat array insertion
            foreach($request->boxpacking as $key=>$bxpackg){
                // Factory price
                $sellerPrice = (float)($request->seller_price[$key] ?? ($request->price[$key] ?? 0));
                
                // GST Included
                $isGstIncluded = !empty($request->is_gst_included[$key]) && ((int)$request->is_gst_included[$key] === 1 || $request->is_gst_included[$key] === '1');
                $gstRate = !empty($request->gst_rate[$key]) ? (float)$request->gst_rate[$key] : (float)($tax ?: 18.00);
                $groupName = $request->group_name[$key] ?? 'Group 1';

                // Display price (user-edited or calculated)
                if (isset($request->price[$key]) && is_numeric($request->price[$key]) && (float)$request->price[$key] > 0) {
                    $customerPrice = round((float)$request->price[$key], 2);
                } else {
                    $customerPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
                }

                // MRP (optional)
                $mrp = isset($request->mrp[$key]) && is_numeric($request->mrp[$key]) ? round((float)$request->mrp[$key], 2) : null;

                $packLitres = $pricingService->parsePackLitres($bxpackg);

                $patbu = array(
                                "product_id"      => $pid,
                                "color"           => $request->color[$key] ?? null,
                                "quantity"        => $bxpackg,
                                "oldprice"        => $customerPrice,
                                "seller_price"    => $sellerPrice,
                                "commission_rate" => $commissionRate,
                                "pack_litres"     => $packLitres,
                                "price"           => $customerPrice,
                                "status"          => '1',
                            );

                if ($hasGroupNameColumn) {
                    $patbu['group_name'] = $groupName;
                }
                if ($hasIsGstColumn) {
                    $patbu['is_gst_included'] = $isGstIncluded ? 1 : 0;
                }
                if ($hasGstRateColumn) {
                    $patbu['gst_rate'] = $gstRate;
                }
                if ($hasMrpColumn) {
                    $patbu['mrp'] = $mrp;
                }

                ProductAttributes::insert($patbu);            
            }
        }
        
        // If GST Included, record in ProductReviewHistory
        if ($hasGstIncluded) {
            $company = Company::where('user_id', Auth::id())->first();
            \App\Models\ProductReviewHistory::create([
                'product_id'      => $pid,
                'company_id'      => $company?->id,
                'seller_user_id'  => Auth::id(),
                'reviewer_id'     => null,
                'action'          => 'submitted_for_gst_review',
                'previous_status' => 'draft',
                'new_status'      => 'pending_review',
                'notes'           => 'Product submitted with GST-inclusive pricing. Routed to Admin Review Bucket for Display Price approval.',
                'metadata'        => [
                    'commission_rate' => $commissionRate,
                    'shade_groups'    => $parsedGroups,
                ],
            ]);

            return redirect('/seller/product')->with('success', 'Product submitted successfully! Because GST is included, this product has been sent to the Admin Review Bucket for Display Price review and approval before publishing.');
        }

        return redirect('/seller/product')->with('success', 'Product successfully created and published.');
  }
  public function list(Request $request){
        $data['title'] = 'Product';
        $data['categorylist'] = Category::where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
        $data['Brandlist'] = Brand::where('user_id',Auth::user()->id)->where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
        $query = Product::where('user_id',Auth::user()->id);
        if ($request->filled('Brand')) {    $query = $query->where("brand_id", $request->input('Brand'));           }
        if ($request->filled('srchcategory')) {    $query = $query->where("category_id", $request->input('srchcategory'));  }
        if ($request->filled('product')) {    $query = $query->where("name", 'like', '%' . $request->input('product') . '%');           }
        $data['list'] = $query->orderby("id","desc")->paginate(20)->withQueryString();
             
        return view('product.list',$data);  
  }

  public function bulkUploadView()
  {
      $company = Company::where("user_id", Auth::user()->id)->first();
      if (empty($company)) {
          return redirect('/seller/product')->withErrors(["Please Add Your Company By Admin First"]);
      }
      $data['title'] = 'Bulk Upload Products';
      $data['company'] = $company;
      $data['categorylist'] = Category::where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
      $data['Brandlist'] = Brand::where('user_id', Auth::user()->id)->where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->orderby('name','asc')->get();
      if ($data['Brandlist']->isEmpty()) {
          $data['Brandlist'] = Brand::where(function($q){ $q->where('status', 'Active')->orWhere('status', '1')->orWhere('status', 1); })->take(15)->get();
      }
      $data['shadeCards'] = ShadeCard::whereIn('status', ['Active', '1', 1])->orderBy('name', 'asc')->get();
      $data['boxPackings'] = BoxPacking::whereIn('status', ['Active', '1', 1])->orderBy('id', 'asc')->get();
      return view('product.bulk-upload', $data);
  }

  public function downloadBulkSampleExcel(BulkProductSpreadsheetService $service)
  {
      $tempPath = $service->generateSampleExcel(Auth::id());
      return response()->download($tempPath, 'apnifactory_products_bulk_template.xlsx')->deleteFileAfterSend(true);
  }

  public function downloadBulkSampleCsv(BulkProductSpreadsheetService $service)
  {
      $tempPath = $service->generateSampleCsv();
      return response()->download($tempPath, 'apnifactory_products_bulk_sample.csv', [
          'Content-Type' => 'text/csv; charset=UTF-8'
      ])->deleteFileAfterSend(true);
  }

  public function processBulkUpload(Request $request, BulkProductSpreadsheetService $service)
  {
      $request->validate([
          'spreadsheet_file' => 'nullable|file|max:20480',
          'csv_file'         => 'nullable|file|max:20480',
          'image_files.*'    => 'nullable|image|max:10240',
          'images_zip'       => 'nullable|file|mimes:zip|max:51200',
      ]);

      $company = Company::where("user_id", Auth::user()->id)->first();
      if (empty($company)) {
          return redirect()->back()->withErrors(["Please Add Your Company By Admin First"]);
      }

      $file = $request->file('spreadsheet_file') ?: $request->file('csv_file');
      if (!$file) {
          return redirect()->back()->withErrors(["Please select an Excel (.xlsx) or CSV (.csv) spreadsheet file to upload."]);
      }

      try {
          $imageFiles = $request->file('image_files') ?: [];
          $zipFile = $request->file('images_zip');

          $result = $service->importSpreadsheet($file, $imageFiles, $zipFile, Auth::id());

          $msg = "Bulk Upload Successful! {$result['products_count']} products processed, {$result['variants_count']} size/shade variants saved, and {$result['images_count']} product images linked.";
          if (!empty($result['skipped_rows'])) {
              $msg .= " (" . count($result['skipped_rows']) . " rows skipped).";
          }

          return redirect('/seller/product')->with('success', $msg);

      } catch (\Exception $e) {
          return redirect()->back()->withErrors(["Bulk import failed: " . $e->getMessage()]);
      }
  }
  public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
  public function addform(){
       $company = Company::where("user_id",Auth::user()->id)->first();
       if(empty($company)){return redirect()->back()->withErrors(["Please Add Your Company By Admin"]);}
       $Brand = Brand::where("user_id",Auth::user()->id)->first();
       if(empty($Brand)){return redirect()->back()->withErrors(["Please Add Your Brand First"]);}
        $data['title'] = 'Add Product';
        $data['step1Data'] =session()->get('product_step1', []);
        return view('product.add',$data);
  }
    public function editview($id,Request $request){
        $data['title'] = 'Edit Product';
        $pro = Product::where('id',$id)->firstOrFail();
        $data['detail'] = $pro;
        $data['shadecard'] = ShadeCard::where('category_id',$pro->category_id)->get();
        $query =  ProductAttributes::where("product_id",$id);
        if ($request->filled('srchshade')) { $query = $query->where("color", $request->input('srchshade'));  }
        $data['attributelist'] = $query->get();

        $pricingService = app(\App\Services\PaintPricingService::class);
        $commissionRate = $pricingService->getEffectiveCommissionRate(
            Auth::id(),
            null,
            $pro->category_id,
            null,
            $pro->maincategory_id
        );
        $data['commissionRate'] = $commissionRate;
        $data['companydetail'] = Company::where('user_id', Auth::id())->first();

        // Pass structured shade groups
        $data['shadeGroups'] = $pricingService->formatShadeGroupsForProduct($pro);

        // Pass product review & revision audit histories for both panels
        $data['reviewHistories'] = \App\Models\ProductReviewHistory::where('product_id', $id)
            ->with(['reviewer', 'seller'])
            ->orderBy('id', 'desc')
            ->get();

        // Pass all box packings
        $data['boxPackings'] = BoxPacking::all();

        return view('product.edit',$data);
  }

    /**
     * Seller Action: Update entire Shade Group pricing (Factory Price, Display Price, GST, MRP)
     */
    public function updateGroupPricing(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $groupName = trim($request->input('group_name'));
        $groupId = $request->input('group_id');
        $isGstIncluded = $request->boolean('is_gst_included') || $request->input('is_gst_included') == '1' || $request->input('is_gst_included') === 1 ? 1 : 0;
        $gstRate = (float)$request->input('gst_rate', $product->tax ?: 18.00);
        $packsData = $request->input('packs', []);

        $pricingService = app(\App\Services\PaintPricingService::class);
        $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), null, $product->category_id, null, $product->maincategory_id);

        $groups = $pricingService->formatShadeGroupsForProduct($product);
        $targetGroup = null;
        $targetIndex = null;
        foreach ($groups as $idx => $g) {
            if (($groupId && ($g['group_id'] ?? '') === $groupId) || strcasecmp($g['name'], $groupName) === 0) {
                $targetGroup = $g;
                $targetIndex = $idx;
                break;
            }
        }

        if (!$targetGroup) {
            return back()->with('error', "Group '{$groupName}' not found on this product.");
        }

        $shadeIds = (array)($targetGroup['shades'] ?? []);
        $updatedSkus = 0;

        foreach ($packsData as $packId => $pData) {
            $factoryPrice = (float)($pData['seller_price'] ?? 0);
            $displayPrice = !empty($pData['price']) && is_numeric($pData['price']) 
                ? round((float)$pData['price'], 2)
                : round($factoryPrice * (1 + ($commissionRate / 100)), 2);
            $mrp = !empty($pData['mrp']) && is_numeric($pData['mrp']) ? round((float)$pData['mrp'], 2) : null;

            foreach ($shadeIds as $shadeId) {
                $attr = ProductAttributes::where('product_id', $product->id)
                    ->where('color', $shadeId)
                    ->where('quantity', $packId)
                    ->first();

                $attrData = [
                    'group_name'      => $groupName,
                    'seller_price'    => $factoryPrice,
                    'price'           => $displayPrice,
                    'oldprice'        => $displayPrice,
                    'commission_rate' => $commissionRate,
                    'is_gst_included' => $isGstIncluded,
                    'gst_rate'        => $gstRate,
                    'mrp'             => $mrp,
                ];

                if ($attr) {
                    $attr->update($attrData);
                } else {
                    $attrData['product_id'] = $product->id;
                    $attrData['color'] = $shadeId;
                    $attrData['quantity'] = $packId;
                    $attrData['pack_litres'] = $pricingService->parsePackLitres($packId);
                    $attrData['status'] = '1';
                    ProductAttributes::create($attrData);
                }
                $updatedSkus++;
            }
        }

        // Update groups JSON on product
        $updatedStoredGroups = [];
        foreach ($groups as $idx => $g) {
            if ($idx === $targetIndex) {
                $g['name'] = $groupName;
                $g['is_gst_included'] = $isGstIncluded;
                $g['gst_rate'] = $gstRate;
                $g['packs'] = $packsData;
            }
            $updatedStoredGroups[] = [
                'group_id'        => $g['group_id'] ?? ('group_' . ($idx + 1)),
                'name'            => $g['name'],
                'shades'          => (array)$g['shades'],
                'is_gst_included' => $g['is_gst_included'],
                'gst_rate'        => $g['gst_rate'],
                'packs'           => $g['packs'],
            ];
        }

        $pUpdate = ['shade_groups' => $updatedStoredGroups];
        if ($isGstIncluded) {
            $pUpdate['has_gst_included'] = 1;
            $pUpdate['approval_status'] = 'pending_review';
            $pUpdate['status'] = 'Deactive';
            $pUpdate['submitted_at'] = now();
        }
        $product->update($pUpdate);

        // Audit Log
        \App\Models\ProductReviewHistory::create([
            'product_id'      => $product->id,
            'company_id'      => $product->user?->company?->id ?? 0,
            'seller_user_id'  => Auth::id(),
            'reviewer_id'     => Auth::user()->isAdmin() ? Auth::id() : null,
            'action'          => 'seller_updated_group_pricing',
            'previous_status' => $product->approval_status,
            'new_status'      => $product->approval_status,
            'notes'           => "Updated pricing for Shade Group '{$groupName}' ({$updatedSkus} SKUs affected). GST Included: " . ($isGstIncluded ? "Yes ({$gstRate}%)" : 'No'),
            'metadata'        => [
                'group_name' => $groupName,
                'packs'      => $packsData,
                'gst_rate'   => $gstRate,
            ],
        ]);

        return back()->with('success', "Shade group '{$groupName}' pricing updated successfully! ({$updatedSkus} SKUs updated)");
    }

    /**
     * Seller Action: Add a shade into an existing group (preventing duplicate shades across groups)
     */
    public function addShadeToGroup(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $colorId = (int)$request->input('color_id');
        $groupName = trim($request->input('group_name'));

        if (!$colorId) {
            return back()->with('error', 'Please select a valid shade to add.');
        }

        // Check non-repetition: Shade cannot belong to another group for this product!
        $existingOtherGroup = ProductAttributes::where('product_id', $product->id)
            ->where('color', $colorId)
            ->where('group_name', '!=', $groupName)
            ->whereNotNull('group_name')
            ->first();

        if ($existingOtherGroup) {
            return back()->with('error', "This shade already belongs to group '{$existingOtherGroup->group_name}'. Shades cannot be repeated across multiple groups.");
        }

        $pricingService = app(\App\Services\PaintPricingService::class);
        $groups = $pricingService->formatShadeGroupsForProduct($product);

        $targetGroup = null;
        $targetIndex = null;
        foreach ($groups as $idx => $g) {
            if (strcasecmp($g['name'], $groupName) === 0) {
                $targetGroup = $g;
                $targetIndex = $idx;
                break;
            }
        }

        if (!$targetGroup) {
            return back()->with('error', "Group '{$groupName}' not found.");
        }

        // Inherit group pricing and pack sizes for this shade
        $packs = $targetGroup['packs'] ?? [];
        $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), null, $product->category_id, null, $product->maincategory_id);
        $isGst = !empty($targetGroup['is_gst_included']) ? 1 : 0;
        $gstRate = !empty($targetGroup['gst_rate']) ? (float)$targetGroup['gst_rate'] : ($product->tax ?: 18.00);

        foreach ($packs as $packId => $pData) {
            $sellerPrice = (float)($pData['seller_price'] ?? 0);
            $displayPrice = !empty($pData['price']) && is_numeric($pData['price']) 
                ? (float)$pData['price'] 
                : round($sellerPrice * (1 + ($commissionRate / 100)), 2);
            $mrp = !empty($pData['mrp']) ? (float)$pData['mrp'] : null;

            ProductAttributes::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'color'      => $colorId,
                    'quantity'   => $packId,
                ],
                [
                    'group_name'      => $groupName,
                    'seller_price'    => $sellerPrice,
                    'price'           => $displayPrice,
                    'oldprice'        => $displayPrice,
                    'commission_rate' => $commissionRate,
                    'pack_litres'     => $pricingService->parsePackLitres($packId),
                    'is_gst_included' => $isGst,
                    'gst_rate'        => $gstRate,
                    'mrp'             => $mrp,
                    'status'          => '1',
                ]
            );
        }

        // Update JSON
        if (!in_array($colorId, $groups[$targetIndex]['shades'])) {
            $groups[$targetIndex]['shades'][] = $colorId;
        }

        $product->update([
            'shade_groups' => array_map(function ($g) {
                return [
                    'group_id'        => $g['group_id'] ?? '',
                    'name'            => $g['name'],
                    'shades'          => (array)$g['shades'],
                    'is_gst_included' => $g['is_gst_included'],
                    'gst_rate'        => $g['gst_rate'],
                    'packs'           => $g['packs'],
                ];
            }, $groups)
        ]);

        $shadeName = Helper::getcolorsnamebyid($colorId);

        \App\Models\ProductReviewHistory::create([
            'product_id'      => $product->id,
            'company_id'      => $product->user?->company?->id ?? 0,
            'seller_user_id'  => Auth::id(),
            'reviewer_id'     => null,
            'action'          => 'seller_added_shade_to_group',
            'previous_status' => $product->approval_status,
            'new_status'      => $product->approval_status,
            'notes'           => "Added shade '{$shadeName}' to group '{$groupName}'.",
            'metadata'        => ['color_id' => $colorId, 'group_name' => $groupName],
        ]);

        return back()->with('success', "Shade '{$shadeName}' added to group '{$groupName}' successfully!");
    }

    /**
     * Seller Action: Remove a shade from a group
     */
    public function removeShadeFromGroup(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $colorId = (int)$request->input('color_id');
        $groupName = trim($request->input('group_name'));

        // Delete attributes for this shade under this group
        ProductAttributes::where('product_id', $product->id)
            ->where('color', $colorId)
            ->where('group_name', $groupName)
            ->delete();

        $pricingService = app(\App\Services\PaintPricingService::class);
        $groups = $pricingService->formatShadeGroupsForProduct($product);

        foreach ($groups as &$g) {
            if (strcasecmp($g['name'], $groupName) === 0) {
                $g['shades'] = array_values(array_filter($g['shades'], fn($sid) => (int)$sid !== $colorId));
            }
        }

        $product->update([
            'shade_groups' => array_map(function ($g) {
                return [
                    'group_id'        => $g['group_id'] ?? '',
                    'name'            => $g['name'],
                    'shades'          => (array)$g['shades'],
                    'is_gst_included' => $g['is_gst_included'],
                    'gst_rate'        => $g['gst_rate'],
                    'packs'           => $g['packs'],
                ];
            }, $groups)
        ]);

        $shadeName = Helper::getcolorsnamebyid($colorId);

        \App\Models\ProductReviewHistory::create([
            'product_id'      => $product->id,
            'company_id'      => $product->user?->company?->id ?? 0,
            'seller_user_id'  => Auth::id(),
            'reviewer_id'     => null,
            'action'          => 'seller_removed_shade_from_group',
            'previous_status' => $product->approval_status,
            'new_status'      => $product->approval_status,
            'notes'           => "Removed shade '{$shadeName}' from group '{$groupName}'.",
            'metadata'        => ['color_id' => $colorId, 'group_name' => $groupName],
        ]);

        return back()->with('success', "Shade '{$shadeName}' removed from group '{$groupName}'.");
    }

    /**
     * Seller Action: Create a new Shade Group on an existing product
     */
    public function createShadeGroup(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $groupName = trim($request->input('group_name'));
        if (empty($groupName)) {
            $groupName = 'Group ' . (ProductAttributes::where('product_id', $product->id)->distinct('group_name')->count('group_name') + 1);
        }

        $shadeIds = (array)$request->input('shades', []);
        $packsData = (array)$request->input('packs', []);
        $isGst = $request->boolean('is_gst_included') || $request->input('is_gst_included') == '1' ? 1 : 0;
        $gstRate = (float)$request->input('gst_rate', $product->tax ?: 18.00);

        // Check non-repetition
        $alreadyAssigned = ProductAttributes::where('product_id', $product->id)
            ->whereIn('color', $shadeIds)
            ->whereNotNull('group_name')
            ->pluck('color')
            ->unique()
            ->all();

        if (!empty($alreadyAssigned)) {
            $names = ShadeCard::whereIn('id', $alreadyAssigned)->pluck('name')->implode(', ');
            return back()->with('error', "The following shades are already assigned to another group: {$names}. A shade cannot belong to multiple groups.");
        }

        $pricingService = app(\App\Services\PaintPricingService::class);
        $commissionRate = $pricingService->getEffectiveCommissionRate(Auth::id(), null, $product->category_id, null, $product->maincategory_id);

        foreach ($shadeIds as $colorId) {
            foreach ($packsData as $packId => $pData) {
                $sellerPrice = (float)($pData['seller_price'] ?? 0);
                $displayPrice = !empty($pData['price']) && is_numeric($pData['price']) 
                    ? (float)$pData['price'] 
                    : round($sellerPrice * (1 + ($commissionRate / 100)), 2);
                $mrp = !empty($pData['mrp']) && is_numeric($pData['mrp']) ? (float)$pData['mrp'] : null;

                ProductAttributes::create([
                    'product_id'      => $product->id,
                    'color'           => $colorId,
                    'quantity'        => $packId,
                    'group_name'      => $groupName,
                    'seller_price'    => $sellerPrice,
                    'price'           => $displayPrice,
                    'oldprice'        => $displayPrice,
                    'commission_rate' => $commissionRate,
                    'pack_litres'     => $pricingService->parsePackLitres($packId),
                    'is_gst_included' => $isGst,
                    'gst_rate'        => $gstRate,
                    'mrp'             => $mrp,
                    'status'          => '1',
                ]);
            }
        }

        $newGroups = $pricingService->formatShadeGroupsForProduct($product);
        $product->update([
            'shade_groups' => array_map(function ($g) {
                return [
                    'group_id'        => $g['group_id'] ?? '',
                    'name'            => $g['name'],
                    'shades'          => (array)$g['shades'],
                    'is_gst_included' => $g['is_gst_included'],
                    'gst_rate'        => $g['gst_rate'],
                    'packs'           => $g['packs'],
                ];
            }, $newGroups)
        ]);

        if ($isGst) {
            $product->update([
                'has_gst_included' => 1,
                'approval_status'  => 'pending_review',
                'status'           => 'Deactive',
                'submitted_at'     => now(),
            ]);
        }

        \App\Models\ProductReviewHistory::create([
            'product_id'      => $product->id,
            'company_id'      => $product->user?->company?->id ?? 0,
            'seller_user_id'  => Auth::id(),
            'reviewer_id'     => null,
            'action'          => 'seller_created_shade_group',
            'previous_status' => $product->approval_status,
            'new_status'      => $product->approval_status,
            'notes'           => "Created new shade group '{$groupName}' with " . count($shadeIds) . " shade(s).",
            'metadata'        => [
                'group_name' => $groupName,
                'shades'     => $shadeIds,
                'gst_rate'   => $gstRate,
            ],
        ]);

        return back()->with('success', "New shade group '{$groupName}' created successfully!");
    }
     public function productdetail(Request $request){ 
         $rt=array();$rmn["sellerdetail"]=array();
         $rmn = Product::where("id",$request->get('productid'))->first();
         if (!$rmn) {
             return response()->json(['status' => false, 'message' => 'Product not found'], 404);
         }

         $images = [];
         if (!empty($rmn->multipleimages)) {
             $decoded = is_string($rmn->multipleimages) ? json_decode($rmn->multipleimages, true) : $rmn->multipleimages;
             if (is_array($decoded)) {
                 $images = array_values(array_filter(array_map('strval', $decoded)));
             }
         }
         if (empty($images) && !empty($rmn->image)) {
             $images = [(string)$rmn->image];
         }
         $rmn['multipleimages'] = $images;
             $attribute = ProductAttributes::where("product_id",$request->get('productid'))->where("color",$request->get('colorid'))->get();
              if ($attribute->isEmpty()) {
                  $firstColor = ProductAttributes::where("product_id", $request->get('productid'))->value('color');
                  if ($firstColor) {
                      $attribute = ProductAttributes::where("product_id", $request->get('productid'))->where("color", $firstColor)->get();
                  } else {
                      $attribute = ProductAttributes::where("product_id", $request->get('productid'))->get();
                  }
              }
              $caller = auth('sanctum')->user() ?? auth()->user();
              $canViewInternalPricing = $caller && ($caller->isAdmin() || (int)$caller->id === (int)$rmn->user_id);

              foreach($attribute as $atrbt){
                   $color=ShadeCard::where("id",$atrbt->color)->first();
                   $sbm['color']=$color ? $color->name : '';
                   $sbm['hexcode']=$color ? $color->hexcode : '';
                   $quantity=BoxPacking::where("id",$atrbt->quantity)->first();
                   $sbm['boxpacking']=$quantity ? $quantity->name : '';
                   $sbm['quantity']=$quantity ? $quantity->pcs : 1;
                   $sbm['pack_litres']=$atrbt->pack_litres ?: 1.0;
                   if ($canViewInternalPricing) {
                       $sbm['seller_price'] = $atrbt->seller_price ?: $atrbt->price;
                   }
                   $sbm['price']=(float)$atrbt->price;
                   $sbm['oldprice']=(float)$atrbt->oldprice;
                   $sbm['attributeid']=$atrbt->id;
                   array_push($rt,$sbm);
              }
              $rmn['attributes'] = $rt;
              /////////////////seller detail (confidential contact info restricted to authenticated admin/owner)
              $sellerdetail = Company::where("user_id",$rmn->user_id)->first();
              $srh["company_id"] = $sellerdetail ? (int)$sellerdetail->id : 0;
              $srh["name"] =  $sellerdetail ? $sellerdetail->name : '';
              if ($canViewInternalPricing) {
                  $srh["email"] =  $sellerdetail ? $sellerdetail->email : '';
                  $srh["mobile"] =  $sellerdetail ? $sellerdetail->mobile : '';
              }
              $srh["city"] =  $sellerdetail ? $sellerdetail->city : '';
              $srh["photo"] =  $sellerdetail ? $sellerdetail->photo : '';
              $rmn['sellerdetail'] = $srh;
              
              ////////////////product reviews
              $revary = $related = array();
              $reviewlist = ProductReviews::where("product_id",$request->get('productid'))->whereIn("status", ["Active", "active", "1"])->get();
              foreach($reviewlist as $review){
                  $custmr = Customer::where("id",$review->customer_id)->first();
                  if(!empty($custmr)){
                  $rw["customername"] = $custmr->name;
                  $rw["customerimage"] = $custmr->image;
                  $rw["review"] = $review->review;
                  $rw["rating"] = $review->rating;
                  $rw["date"] = $review->created_at; 
                  array_push($revary,$rw);
                  }
              }
              $rmn["reviewlist"] = $revary;
              ///////////is customer cable to post review
               $rmn["postreview"]=true;
               $rmn["wishlist"]=false;
                $wihl = DB::table("wishlist")->where("userid",$request->get('userid'))->where("productid",$request->get('productid'))->first();
                if(!empty($wihl)){ $rmn["wishlist"]=true; }
                ///////////related product/////
               $relatedpr = Product::where("user_id",$rmn->user_id)->where("id","!=",$rmn->id)->orderby("id","desc")->limit(5)->get();
               foreach($relatedpr as $rel){
                   $rlt['id'] = $rel->id;
                   $rlt['name'] = $rel->name;
                   $rlt['image'] = $rel->image;
                   $price = ProductAttributes::where("product_id",$rel->id)->orderby('price','asc')->first();
                   $rlt['color']= $price ? Helper::getcolorsnamebyid($price->color) : '';
                   $rlt['colorid']= $price ? $price->color : 0;
                   $quantity= $price ? BoxPacking::where("id",$price->quantity)->first() : null;
                   $numPrice = $price ? round((float)$price->price, 2) : 0;
                   $formattedPrice = number_format($numPrice, 2, '.', '');
                   $rlt['price'] = $price ? ('₹' . $formattedPrice . ($quantity ? ' (' . $quantity->name . ')' : '')) : '';
                   array_push($related,$rlt);
               }
              $rmn["relatedproductlist"] = $related;
                if($request->get('colorid')!==''){
                    $list=ShadeCard::where("id",$request->get('colorid'))->first();
                         $rmn['colordetail']= $list;
                }
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
      }
     public function productattributeprice(Request $request){ 
          $attribute = ProductAttributes::where("product_id",$request->get('productid'));
          if($request->get('color')!=''){
             $attribute = $attribute->where("color",$request->get('color'));
          }
          if($request->get('quantity')!=''){
             $attribute = $attribute->where("quantity",$request->get('quantity'));
          }
          $attribute = $attribute->first();
          
          if ($attribute && empty($attribute->seller_price)) {
              $attribute->seller_price = $attribute->price;
          }
          
          return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$attribute]);
      }
     
     public function update(Request $request, $id)
{ 
    // 1. Find your product/detail record
    $detail = Product::findOrFail($id);

    // 2. Initialize array with existing images that the user DID NOT delete
    $finalImages = $request->input('current_images', []);

    // Optional: If you want to delete removed files completely from your server disk storage:
    if (!empty($detail->multipleimages)) {
        $oldImages = json_decode($detail->multipleimages, true) ?: [];
        // Find which images were deleted by comparing arrays
        $deletedImages = array_diff($oldImages, $finalImages);
        
        foreach ($deletedImages as $deletedImage) {
            // Adjust the disk and path depending on your setup
            // if (Storage::disk('public')->exists($deletedImage)) {
            //     Storage::disk('public')->delete($deletedImage);
            // }
        }
    }

    // 3. Process and upload NEW images if any were added
    if ($request->hasFile('new_images')) {
        $targetDir = storage_path('app/public/product');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }
        foreach ($request->file('new_images') as $file) {
            if ($file->isValid()) {
                // Save image to universal storage path
                $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($targetDir, $filename);
                
                // Add new filename to our final tracking array
                $finalImages[] = 'product/'.$filename;
                $fnlimgs = 'product/'.$filename;
            }
        }
    }else{
        $fnlimgs = $detail->image;
    }

    // 4. Save back to the database as a JSON string
    $detail->multipleimages = json_encode(array_values($finalImages));
    $detail->image = $fnlimgs;
    $detail->name = $request->name;
    $detail->brand_id = $request->brand;
    $detail->maincategory_id = $request->maincategory;
    $detail->category_id = $request->category;
    $detail->status = $request->status;
    $detail->description = $request->details;
      $detail->hsncode = $request->hsncode;
    $detail->save();

    return redirect()->back()->with('success', 'Product images updated successfully!');
}
 public function destroyFilter($id)
{
        $filter =  DB::table("product_attributes")->find($id);
        DB::table("product_attributes")->where('id', $id)->delete();
         

        // 3. Redirect back with a success message
        return redirect()->back()->with('success', 'Product variant deleted successfully.');

}
}
