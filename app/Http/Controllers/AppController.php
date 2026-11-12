<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCatgory;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Slider;
use App\Models\Product;
use App\Models\ShadeCard;
use App\Models\ProductAttributes;
use App\Models\Brand;
use App\Models\Company;
use DB;
use App\Models\Advertisement;
use App\Helper\Helper;
use DateTime;
class AppController extends Controller
{
      public function notificationreadbyuser(Request $request){
            $brrmn = DB::table("notifications")->where("id",$request->notificationid)->update(["msgread"=>1]);
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Updated"]);
    }
    public function notificationlist(Request $request){
            $brrmn = DB::table("notifications")->where("customer_id",$request->userid)->get();
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brrmn]);
    }
    public function branddetail(Request $request){
            $brrmn = Brand::where("id",$request->brandid)->first();
            $cmp = Company::where("id",$brrmn->company_id)->first();
            $brrmn["Company"] = $cmp;
            return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brrmn]);
    }
     public function search(Request $request){ 
         $search = $request->get('word');
         $category  = Category::select(['id', 'name', 'image','maincategory_id as relatedid'])->whereIn("status", ["Active", "active", "1"])->where("title","like","%{$search}%")->get()->map(function ($category) {
                         $category->{'screen'} = 'category';
                         return $category;
                        });
         $company  = Company::select(['id', 'name', 'photo as image', 'user_id'])->whereIn("status", ["Active", "active", "1"])->where("name","like","%{$search}%")->get()->map(function ($company) {
                         $company->{'screen'} = 'company';
                         $categid = Product::select(['category_id as cat'])->where("user_id",$company->user_id)->first();
                         if(!empty($categid)){
                              $company->{'relatedid'} = $categid->cat;
                         }else{
                             $company->{'relatedid'} = 0;
                         }
                         unset($company->user_id);
                         return $company;
                        });
         $product  = Product::select(['id', 'name', 'image', 'user_id'])->whereIn("status", ["Active", "active", "1"])->where(function($q) use ($search) { $q->where("name","like","%{$search}%")->orWhere("description","like","%{$search}%"); })->get();
         $shadecard = ShadeCard::where("name","like","%{$search}%")->pluck('id');
         $productattribute = ProductAttributes::select('product_id')->whereIn("color",$shadecard)->groupby('product_id')->pluck('product_id');
         $productbyshade  = Product::select(['id', 'name', 'image', 'user_id'])->whereIn("status", ["Active", "active", "1"])->whereIn("id",$productattribute)->get();

         $allSearchProdIds = $product->pluck('id')->merge($productbyshade->pluck('id'))->unique()->values()->toArray();
         $firstAttrs = empty($allSearchProdIds)
             ? collect()
             : ProductAttributes::whereIn('product_id', $allSearchProdIds)->groupBy('product_id')->selectRaw('product_id, MIN(id) as attr_id')->pluck('attr_id', 'product_id');
         $allSearchUserIds = $product->pluck('user_id')->merge($productbyshade->pluck('user_id'))->filter()->unique()->values()->toArray();
         $searchCompaniesByUser = $this->resolveCompaniesByUserId($allSearchUserIds);

         $mapSearchProduct = function ($p) use ($firstAttrs, $searchCompaniesByUser) {
             $comp = isset($searchCompaniesByUser[(int)$p->user_id]) ? $searchCompaniesByUser[(int)$p->user_id] : null;
             $p->{'screen'} = 'product';
             $p->{'relatedid'} = $firstAttrs->get($p->id, 0);
             $p->{'company_id'} = $comp ? (int)$comp->id : 0;
             $p->{'company_name'} = $comp ? (string)$comp->name : '';
             unset($p->user_id);
             return $p;
         };
         $product = $product->map($mapSearchProduct);
         $productbyshade = $productbyshade->map($mapSearchProduct);
         $mergedCollection = collect();
         // Merge the results, some of which might be empty
         $mergedCollection = $mergedCollection
             ->merge($category)->merge($company)->merge($product)->merge($productbyshade);
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$mergedCollection]);
     }
     public function homescreen(Request $request){ 
         $rmn['company'] = array();
         $maincategory = MainCategory::whereIn("status", ["Active", "active", "1"])->orderby("sequence","asc")->get();
         $slider1 = Slider::whereIn("status", ["Active", "active", "1"])
             ->where(function($q) { $q->whereNull("enddate")->orWhere("enddate", ">=", date("Y-m-d")); })
             ->where("screen","homescreen")->orderby("id","desc")->pluck('image');
         $cntnotification = DB::table("notifications")->where("customer_id",$request->userid)->count('id');
         
         if ($slider1->count() >= 2) {
             $twoArrays = $slider1->split(2)->values();
             $slider = array_values($twoArrays->get(0) ? $twoArrays->get(0)->values()->toArray() : []); 
             $advertisement = array_values($twoArrays->get(1) ? $twoArrays->get(1)->values()->toArray() : []); 
         } elseif ($slider1->count() == 1) {
             $slider = array_values($slider1->toArray());
             $advertisement = [];
         } else {
             $slider = [];
             $advertisement = [];
         }
        // 1. Fetch Random Products (Trending Now)
        $randomProductsRaw = Product::whereIn("status", ["Active", "active", "1"])
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $prodIds = $randomProductsRaw->pluck('id')->toArray();
        $randomProducts = $this->formatProductCollection($randomProductsRaw);

        // 2. Fetch Popular Brands
        $brandsRaw = Brand::whereIn("status", ["Active", "active", "1"])
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->inRandomOrder()
            ->get(['id', 'name', 'image', 'company_id', 'category_id'])
            ->unique('name')
            ->values()
            ->take(12);

        $brands = [];
        foreach ($brandsRaw as $b) {
            $brands[] = [
                'id' => $b->id,
                'name' => $b->name,
                'image' => $b->image ?: 'default.png',
                'company_id' => $b->company_id ?: 0,
                'category_id' => $b->category_id ?: 0,
            ];
        }

        // 3. Blinkit-Style Category Carousels
        $carouselDefs = [
            [
                'title' => 'Wall Primers & Undercoats',
                'subtitle' => 'Interior & Exterior Base Protection',
                'badge' => 'Direct Factory',
                'categories' => ['Interior Wall Primer', 'Exterior Wall Primer', 'Oil Based Primer']
            ],
            [
                'title' => 'Industrial & PU Protective Finishes',
                'subtitle' => 'Heavy-Duty 2K & 1K PU Protective Coatings',
                'badge' => 'Industrial Grade',
                'categories' => ['2K PU Metal Finish', '2K PU Wood Finish', 'Polyurethane Coating PU', 'Hammertone Finish']
            ],
            [
                'title' => 'High Gloss & Silk Emulsions',
                'subtitle' => 'Platinum Luxury Sheen & Metallic Emulsions',
                'badge' => 'Top Rated',
                'categories' => ['Metallic Paints', 'Shine Emulsion', 'Interior Emulsion Water Based', 'Exterior Emulsion Water Based']
            ],
            [
                'title' => 'Enamel & Automotive Coatings',
                'subtitle' => 'Quick Drying, Synthetic & NC Lacquers',
                'badge' => 'High Durability',
                'categories' => ['Enamel Paints', 'Synthetic Enamel Oil Paint', 'Fast Drying Enamel', 'NC Paints']
            ],
            [
                'title' => 'Formulation Bases & Distemper',
                'subtitle' => 'Wholesale Tinting Bases & Economy Packs',
                'badge' => 'Best Value',
                'categories' => ['Bases', 'Distemper', 'Distemper Bucket', 'Wood Finishes', 'Furniture Enamel']
            ]
        ];

        $categoryCarousels = [];
        foreach ($carouselDefs as $cDef) {
            $catIds = Category::whereIn('name', $cDef['categories'])->pluck('id')->toArray();
            $cProds = Product::whereIn('status', ['Active', 'active', '1'])
                ->whereIn('category_id', $catIds)
                ->get();
            if ($cProds->isNotEmpty()) {
                $categoryCarousels[] = [
                    'title' => $cDef['title'],
                    'subtitle' => $cDef['subtitle'],
                    'badge' => $cDef['badge'],
                    'products' => $this->formatProductCollection($cProds)
                ];
            }
        }

        // 4. Fetch More Products (Initial batch for Infinite Scroll Feed)
        $moreProductsRaw = Product::whereIn("status", ["Active", "active", "1"])
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $moreProducts = $this->formatProductCollection($moreProductsRaw);

        // 5. Fetch Verified Factory Companies
        $companiesRaw = Company::whereIn("status", ["Active", "active", "1"])
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->limit(15)
            ->get(['id', 'name', 'photo', 'city', 'state', 'minordervalue']);
        $companies = [];
        foreach ($companiesRaw as $comp) {
            $companies[] = [
                'id' => $comp->id,
                'name' => $comp->name,
                'photo' => $comp->photo ?: '',
                'city' => $comp->city ?: 'India',
                'state' => $comp->state ?: '',
                'minordervalue' => $comp->minordervalue ? (float)$comp->minordervalue : 0.0,
            ];
        }

        // 6. Fetch Categories and active 4th-tier Subcategories for sliders
        $allCategories = Category::whereIn("status", ["Active", "active", "1"])
            ->orderBy("name")
            ->get(['id', 'name', 'image', 'maincategory_id'])
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'scid' => 0,
                    'name' => $cat->name,
                    'image' => $cat->image,
                    'maincategory_id' => $cat->maincategory_id,
                ];
            })
            ->values();

        $catMainMap = $allCategories->pluck('maincategory_id', 'id');
        $activeSubCats = SubCatgory::whereIn("status", ["Active", "active", "1"])
            ->orderBy("name")
            ->get()
            ->map(function ($sc) use ($catMainMap) {
                $parentCatId = (int)($sc->category_id ?: $sc->cid ?: 0);
                $mainId = (int)($sc->maincategory_id ?: $sc->mid ?: ($catMainMap->get($parentCatId) ?? 0));
                return [
                    'id' => $parentCatId,
                    'scid' => $sc->id,
                    'name' => $sc->name,
                    'image' => $sc->image ?: 'default.png',
                    'maincategory_id' => $mainId,
                ];
            });

        $mergedCategories = $allCategories->concat($activeSubCats)->values();

        $rmn = [
            "slider" => $slider,
            "maincategory" => $maincategory,
            "subcategories" => $mergedCategories,
            "advertisement" => $advertisement,
            "notification" => $cntnotification,
            "random_products" => $randomProducts,
            "brands" => $brands,
            "companies" => $companies,
            "category_carousels" => $categoryCarousels,
            "more_products" => $moreProducts,
        ];
        return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }

     private function resolveCompaniesByUserId(array $userIds): array {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($cleanIds)) {
            return [];
        }
        $companies = Company::whereIn('user_id', $cleanIds)
            ->orderByDesc('id')
            ->get(['id', 'user_id', 'name', 'photo', 'city', 'state', 'minordervalue']);
        $map = [];
        foreach ($companies as $comp) {
            $uid = (int)$comp->user_id;
            if (!isset($map[$uid])) {
                $map[$uid] = $comp;
            }
        }
        return $map;
     }

     private function formatProductCollection($collection) {
        if ($collection->isEmpty()) return [];
        $prodIds = $collection->pluck('id')->toArray();
        $userIds = $collection->pluck('user_id')->filter()->unique()->toArray();
        $companiesByUserId = $this->resolveCompaniesByUserId($userIds);

        $rawAttrs = DB::table('product_attributes')
            ->leftJoin('box_packings', function($join) {
                $join->on('box_packings.id', '=', DB::raw('CAST(product_attributes.quantity AS UNSIGNED)'));
            })
            ->select(
                'product_attributes.id as attribute_id',
                'product_attributes.product_id',
                'product_attributes.color',
                'box_packings.name as pack_size',
                DB::raw('CAST(product_attributes.price AS DECIMAL(12,2)) as numeric_price'),
                DB::raw('CAST(product_attributes.oldprice AS DECIMAL(12,2)) as numeric_oldprice')
            )
            ->whereIn('product_attributes.product_id', $prodIds)
            ->where(function($q) {
                $q->whereNull('product_attributes.status')
                  ->orWhereIn('product_attributes.status', ['active', 'Active', '1']);
            })
            ->whereRaw('CAST(product_attributes.price AS DECIMAL(12,2)) > 0')
            ->orderBy('numeric_price', 'asc')
            ->get();

        $lowestAttrsByProduct = [];
        foreach ($rawAttrs as $attrRow) {
            $pid = (int)$attrRow->product_id;
            if (!isset($lowestAttrsByProduct[$pid])) {
                $lowestAttrsByProduct[$pid] = $attrRow;
            }
        }

        $out = [];
        foreach ($collection as $p) {
            $attr = isset($lowestAttrsByProduct[$p->id]) ? $lowestAttrsByProduct[$p->id] : null;
            $price = $attr ? (float)$attr->numeric_price : 0.0;
            if ($price <= 0 && $p->price) {
                $price = (float)$p->price;
            }
            $oldPrice = ($attr && $attr->numeric_oldprice > 0) ? (float)$attr->numeric_oldprice : ($price > 0 ? round($price * 1.25, 2) : 0.0);
            $colorId = $attr && $attr->color ? (string)$attr->color : '0';
            $packSize = $attr && $attr->pack_size ? (string)$attr->pack_size : '';
            $comp = isset($companiesByUserId[(int)$p->user_id]) ? $companiesByUserId[(int)$p->user_id] : null;

            $out[] = [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image ?: 'default.png',
                'category_id' => $p->category_id ?: 0,
                'category_name' => Helper::getcatname($p->category_id),
                'brand_id' => $p->brand_id ?: 0,
                'brand_name' => Helper::getbrandname($p->brand_id),
                'company_id' => $comp ? (int)$comp->id : 0,
                'company_name' => $comp ? (string)$comp->name : '',
                'company_city' => $comp ? ($comp->city ?: 'India') : 'India',
                'company_photo' => $comp ? ($comp->photo ?: '') : '',
                'price' => $price,
                'oldprice' => $oldPrice,
                'color_id' => $colorId,
                'pack_size' => $packSize,
                'attribute_id' => $attr ? (int)$attr->attribute_id : 0,
            ];
        }
        return $out;
     }

     public function randomproducts(Request $request){
        $limit = (int)($request->get('limit') ?: 10);
        $page = (int)($request->get('page') ?: 1);
        $exclude = $request->get('exclude');
        $excludeIds = is_array($exclude) ? $exclude : (is_string($exclude) && strlen($exclude) > 0 ? explode(',', $exclude) : []);

        $query = Product::whereIn("status", ["Active", "active", "1"]);
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $productsRaw = $query->paginate($limit, ['*'], 'page', $page);
        $products = $this->formatProductCollection(collect($productsRaw->items()));

        return response()->json([
            "status" => true,
            "code" => 100,
            "msg" => "Successfully Show",
            "has_more" => $productsRaw->hasMorePages(),
            "total" => $productsRaw->total(),
            "current_page" => $productsRaw->currentPage(),
            "data" => $products
        ]);
     }
     public function secondscreen_category_company(Request $request){
         $rmn['category']=$rmn['company']=$rmn['advertisement']=array();
         $category  = Category::whereIn("status", ["Active", "active", "1"])->where("maincategory_id",$request->get('mid'))->orderby("sequence","asc")->get();
          foreach($category as $cat){
              $ct['name']=$cat->name;
              $ct['image']=$cat->image;
              $ct['cid']=$cat->id;
              array_push($rmn['category'],$ct);
          }
          $company = Company::whereIn("status", ["Active", "active", "1"])->where("maincategory_id",$request->get('mid'))->orderby("id","desc")->get();
          foreach($company as $cmp){
              $cm['name']=$cmp->name;
              $cm['image']=$cmp->photo;
              $cm['cmpid']=$cmp->id;  
              $userid = Company::where("id",$cmp->id)->first();
              $count = $userid ? Product::where('user_id', $userid->user_id)->distinct()->count('category_id') : 0;
              $cm['count']=$count;
              $cm['city']=$cmp->city;
              array_push($rmn['company'],$cm);
          }
           $sliderPluck = Advertisement::whereIn("status", ["Active", "active", "1"])->where("screen","CategoryScreen")->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->orderby("sequence","asc")->pluck('file');
           $rmn['slider'] = array_values($sliderPluck->toArray());
           $adPluck = Slider::whereIn("status", ["Active", "active", "1"])->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->where("screen","CategoryScreen")->orderby("id","desc")->pluck('image');
           $rmn['advertisement'] = array_values($adPluck->toArray());
           return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     private function resolveScopedUserId(int $cmpid, int $bid): int
     {
          if ($cmpid > 0) {
              $uid = (int)(Company::where("id", $cmpid)->value('user_id') ?: 0);
              if ($uid > 0) {
                  return $uid;
              }
          }
          if ($bid > 0) {
              $uid = (int)(Brand::where("id", $bid)->value('user_id') ?: 0);
              if ($uid > 0) {
                  return $uid;
              }
              return (int)(Product::whereIn("status", ["Active", "active", "1"])->where("brand_id", $bid)->value('user_id') ?: 0);
          }
          return 0;
     }
     public function brandlist_bycompanyid(Request $request){
        $cmpid = (int)$request->get('cmpid', 0);
        $cid = (int)$request->get('cid', 0);
        $comp = Company::where("id", $cmpid)->first();
        $companyUserId = $comp ? (int)$comp->user_id : 0;

        $baseQuery = Brand::whereIn("status", ["Active", "active", "1"])->where("company_id", $cmpid);
        $allBrandsRaw = (clone $baseQuery)->get();

        if ($cid > 0) {
            $rmnBrand = (clone $baseQuery)->where("category_id", $cid)->get();
            if ($rmnBrand->isEmpty() && $companyUserId > 0) {
                $brandIdsInCat = Product::whereIn("status", ["Active", "active", "1"])
                    ->where("user_id", $companyUserId)
                    ->where("category_id", $cid)
                    ->distinct()
                    ->pluck('brand_id');
                if ($brandIdsInCat->isNotEmpty()) {
                    $rmnBrand = (clone $baseQuery)->whereIn("id", $brandIdsInCat)->get();
                }
            }
            if ($rmnBrand->isEmpty()) {
                $rmnBrand = $allBrandsRaw;
            }
        } else {
            $rmnBrand = $allBrandsRaw;
        }

        // Hydrate full single-company storefront: all company brands, categories, and products
        $companyProductsRaw = $companyUserId > 0
            ? Product::whereIn("status", ["Active", "active", "1"])
                ->where("user_id", $companyUserId)
                ->orderByDesc('id')
                ->get()
            : collect();
        $companyProducts = $this->formatProductCollection($companyProductsRaw);

        $companyCatIds = $companyProductsRaw->pluck('category_id')
            ->merge($allBrandsRaw->pluck('category_id'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $companyCategories = empty($companyCatIds)
            ? []
            : Category::whereIn('id', $companyCatIds)
                ->whereIn("status", ["Active", "active", "1"])
                ->orderBy('name')
                ->get(['id', 'name', 'image', 'maincategory_id'])
                ->toArray();

        $rmn['Brand'] = collect($rmnBrand)->unique('name')->values()->toArray();
        $rmn['all_brands'] = collect($allBrandsRaw)->unique('name')->values()->toArray();
        $rmn['categories'] = $companyCategories;
        $rmn['products'] = $companyProducts;
        $rmn['bannerimage'] = empty($comp) ? '' : ($comp->photo ?: '');
        $rmn['companyname'] = Helper::getcompanyname($cmpid);
        $rmn['cmpid'] = $cmpid;
        $rmn['company'] = empty($comp) ? null : [
            'id' => (int)$comp->id,
            'name' => (string)$comp->name,
            'photo' => (string)($comp->photo ?: ''),
            'city' => (string)($comp->city ?: 'India'),
            'state' => (string)($comp->state ?: ''),
            'minordervalue' => $comp->minordervalue ? (float)$comp->minordervalue : 0.0,
        ];
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function companylist_bycategoryid(Request $request){
         $cid = (int)$request->get('cid', 0);
         $scid = (int)$request->get('scid', 0);
         $prodQuery = Product::select('user_id')->whereIn("status", ["Active", "active", "1"]);
         if ($scid > 0) {
             $prodQuery->where(function ($q) use ($scid) {
                 $q->where("subcategory_id", $scid)->orWhere("sid", $scid);
             });
         } elseif ($cid > 0) {
             $prodQuery->where("category_id", $cid);
         }
         $sellerid = $prodQuery->groupby('user_id')->get('user_id');
         $prid = [];
         foreach ($sellerid as $seller) {
             $prid[] = $seller->user_id;
         }
         $rmn['company'] = empty($prid)
             ? []
             : Company::whereIn("status", ["Active", "active", "1"])->whereIn("user_id", $prid)->orderby("id","desc")->get();
         $sliderPluck = Advertisement::whereIn("status", ["Active", "active", "1"])->where("screen","CompanyScreen")->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->orderby("sequence","asc")->pluck('file');
         $rmn['slider'] = array_values($sliderPluck->toArray());
         $adPluck = Slider::whereIn("status", ["Active", "active", "1"])->where(function($q){$q->whereNull("enddate")->orWhere("enddate",">=",date("Y-m-d"));})->where("screen","CompanyScreen")->orderby("id","desc")->pluck('image');
         $rmn['advertisement'] = array_values($adPluck->toArray());
         $rmn['categoryname'] = $scid > 0
             ? (SubCatgory::where('id', $scid)->value('name') ?: Helper::getcatname($cid))
             : Helper::getcatname($cid);
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     private function hydrateCategoryProducts($productlist, string $catName = ''): array
     {
          if ($productlist->isEmpty()) {
              return [];
          }
          $pids = $productlist->pluck('id')->toArray();
          $attributes = DB::table('product_attributes')
              ->select('product_id', 'color', DB::raw('MIN(CAST(price AS DECIMAL(12,2))) as min_price'))
              ->whereIn('product_id', $pids)
              ->whereRaw('CAST(price AS DECIMAL(12,2)) > 0')
              ->groupBy('product_id', 'color')
              ->get();

          $colorIds = $attributes->pluck('color')->unique()->filter()->values()->toArray();
          $shadeCards = ShadeCard::whereIn('id', $colorIds)->get()->keyBy('id');
          $attrsByProduct = $attributes->groupBy('product_id');

          $items = [];
          foreach ($productlist as $prod) {
              $prodAttrs = $attrsByProduct->get($prod->id, collect());
              $resolvedCatName = $catName ?: Helper::getcatname($prod->category_id);
              if ($prodAttrs->isEmpty()) {
                  $items[] = [
                      'name' => $prod->name,
                      'image' => $prod->image,
                      'price' => (float)($prod->price ?: 0),
                      'cid' => $prod->category_id,
                      'categoryname' => $resolvedCatName,
                      'pid' => $prod->id,
                      'color' => '',
                      'hexcode' => '',
                      'hexcode_image' => '',
                      'colorid' => '0',
                  ];
              } else {
                  foreach ($prodAttrs as $pat) {
                      $sc = $shadeCards->get($pat->color);
                      $colorName = $sc ? $sc->name : '';
                      $hexcode = $sc ? $sc->hexcode : '';
                      $items[] = [
                          'name' => $colorName ? $prod->name . ' (' . $colorName . ')' : $prod->name,
                          'image' => $prod->image,
                          'price' => (float)$pat->min_price,
                          'cid' => $prod->category_id,
                          'categoryname' => $resolvedCatName,
                          'pid' => $prod->id,
                          'color' => $colorName,
                          'hexcode' => $hexcode,
                          'hexcode_image' => ($hexcode == '' && $sc) ? ($sc->image ?? '') : '',
                          'colorid' => $pat->color ?: '0',
                      ];
                  }
              }
          }
          return $items;
     }

     private function buildCategorySidebar(int $cid, int $bid, $userid, int $scid = 0, int $cmpid = 0): array
     {
          $sbct = [];
          $scopedUid = (int)$userid;
          if ($scopedUid <= 0 && ($cmpid > 0 || $bid > 0)) {
              $scopedUid = $this->resolveScopedUserId($cmpid, $bid);
          }
          $resolvedCmpId = $cmpid > 0
              ? $cmpid
              : ($scopedUid > 0 ? (int)(Company::where('user_id', $scopedUid)->value('id') ?: 0) : 0);

          // 1. Include active 4th-tier sub_categories belonging to $cid (scoped to this company if inside a store)
          if ($cid > 0) {
              $parentCat = Category::find($cid);
              $subCatQuery = SubCatgory::where(function ($q) use ($cid) {
                      $q->where('category_id', $cid)->orWhere('cid', $cid);
                  })
                  ->whereIn('status', ['Active', 'active', '1'])
                  ->orderBy('name');

              if ($scopedUid > 0) {
                  $usedScids = Product::whereIn('status', ['Active', 'active', '1'])
                      ->where('user_id', $scopedUid)
                      ->where('category_id', $cid)
                      ->when($bid > 0, fn ($q) => $q->where('brand_id', $bid))
                      ->get(['subcategory_id', 'sid'])
                      ->flatMap(fn ($p) => [(int)$p->subcategory_id, (int)$p->sid])
                      ->filter(fn ($id) => $id > 0)
                      ->unique()
                      ->values()
                      ->toArray();
                  $childSubCats = empty($usedScids)
                      ? collect()
                      : $subCatQuery->whereIn('id', $usedScids)->get();
              } else {
                  $childSubCats = $subCatQuery->get();
              }

              if ($childSubCats->isNotEmpty()) {
                  if ($parentCat) {
                      $sbct[] = [
                          'highlight' => ($scid === 0),
                          'name' => 'All ' . $parentCat->name,
                          'image' => $parentCat->image,
                          'cid' => $parentCat->id,
                          'scid' => 0,
                          'bid' => $bid ?: 0,
                          'cmpid' => $resolvedCmpId,
                      ];
                  }
                  foreach ($childSubCats as $sc) {
                      $sbct[] = [
                          'highlight' => ($scid === (int)$sc->id),
                          'name' => $sc->name,
                          'image' => $sc->image ?: 'default.png',
                          'cid' => $cid,
                          'scid' => (int)$sc->id,
                          'bid' => $bid ?: 0,
                          'cmpid' => $resolvedCmpId,
                      ];
                  }
              }
          }

          // 2. When inside a Company storefront (Swiggy store mode), list ONLY that company's active categories
          if ($scopedUid > 0) {
              $companyProdQuery = Product::whereIn("status", ["Active", "active", "1"])
                  ->where("user_id", $scopedUid);
              $catIds = (clone $companyProdQuery)->distinct()->pluck('category_id')->toArray();

              // Categories where current $bid specifically has active products, so same-brand stays highlighted cleanly
              $brandCatIds = $bid > 0
                  ? (clone $companyProdQuery)->where("brand_id", $bid)->distinct()->pluck('category_id')->flip()
                  : collect();

              $categories = empty($catIds)
                  ? collect()
                  : Category::whereIn("status", ["Active", "active", "1"])
                      ->whereIn("id", $catIds)
                      ->orderBy('sequence', 'asc')
                      ->get();

              $existingKeys = collect($sbct)->map(fn ($item) => $item['cid'] . ':' . $item['scid'])->flip();
              foreach ($categories as $category) {
                  $key = $category->id . ':0';
                  if ($existingKeys->has($key)) {
                      continue;
                  }
                  $sbct[] = [
                      'highlight' => ($scid === 0 && (int)$category->id === $cid),
                      'name' => $category->name,
                      'image' => $category->image,
                      'cid' => (int)$category->id,
                      'scid' => 0,
                      'bid' => ($bid > 0 && $brandCatIds->has($category->id)) ? $bid : 0,
                      'cmpid' => $resolvedCmpId,
                  ];
              }
              return $sbct;
          }

          // 3. Global fallback (only when NO company/brand scope is active)
          if ($cid > 0) {
              $alreadyHasCats = collect($sbct)->contains(fn ($item) => (int)$item['scid'] === 0 && (int)$item['cid'] !== $cid);
              if (!$alreadyHasCats) {
                  $currentCat = Category::find($cid);
                  if ($currentCat) {
                      $relatedCats = Category::where('maincategory_id', $currentCat->maincategory_id)
                          ->whereIn('status', ['Active', 'active', '1'])
                          ->orderBy('sequence', 'asc')
                          ->get();
                      $existingKeys = collect($sbct)->map(fn ($item) => $item['cid'] . ':' . $item['scid'])->flip();
                      foreach ($relatedCats as $rc) {
                          $key = $rc->id . ':0';
                          if (!$existingKeys->has($key)) {
                              $sbct[] = [
                                  'highlight' => ($scid === 0 && (int)$rc->id === $cid),
                                  'name' => $rc->name,
                                  'image' => $rc->image,
                                  'cid' => (int)$rc->id,
                                  'scid' => 0,
                                  'bid' => 0,
                                  'cmpid' => 0,
                              ];
                          }
                      }
                  }
              }
          }

          return $sbct;
     }

     private function buildProductListResponse(int $bid, int $cid, int $scid, int $cmpid, string $defaultBanner = '')
     {
          $scopedUserId = $this->resolveScopedUserId($cmpid, $bid);
          $resolvedCmpId = $cmpid > 0
              ? $cmpid
              : ($scopedUserId > 0 ? (int)(Company::where('user_id', $scopedUserId)->value('id') ?: 0) : 0);

          $query = Product::whereIn("status", ["Active", "active", "1"]);
          if ($scopedUserId > 0) {
              $query->where("user_id", $scopedUserId);
          }
          if ($bid > 0) {
              $query->where("brand_id", $bid);
          }
          if ($scid > 0) {
              $query->where(function ($q) use ($scid) {
                  $q->where("subcategory_id", $scid)->orWhere("sid", $scid);
              });
          } elseif ($cid > 0) {
              $query->where("category_id", $cid);
          }
          $productlist = $query->get();

          $catName = $scid > 0
              ? (SubCatgory::where('id', $scid)->value('name') ?: Helper::getcatname($cid))
              : Helper::getcatname($cid);

          $rmn['product'] = $this->hydrateCategoryProducts($productlist, $catName);
          $userid = $scopedUserId > 0
              ? $scopedUserId
              : ($productlist->isNotEmpty() ? $productlist->first()->user_id : '');

          $rmn['subcategory'] = $this->buildCategorySidebar($cid, $bid, $userid, $scid, $resolvedCmpId);
          $slider = Slider::where("screen","CategoryScreen")->orderby("id","desc")->first();
          $rmn['bannerimage'] = $slider ? $slider->image : $defaultBanner;
          $rmn['brandname'] = Helper::getbrandname($bid);
          $rmn['company_id'] = $resolvedCmpId;
          $rmn['company_name'] = $resolvedCmpId > 0 ? Helper::getcompanyname($resolvedCmpId) : '';
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }

     public function productcategorylist_bybrandid(Request $request){
          $bid = (int)$request->get('bid', 0);
          $cid = (int)($request->get('cid') ?: $request->get('subcatid') ?: 0);
          $scid = (int)$request->get('scid', 0);
          $cmpid = (int)$request->get('cmpid', 0);
          return $this->buildProductListResponse($bid, $cid, $scid, $cmpid, '');
     }
     
     public function productlistby_bybrandidandsid(Request $request){
          $bid = (int)$request->get('bid', 0);
          $sid = (int)($request->get('sid') ?: $request->get('categoryid') ?: 0);
          $scid = (int)$request->get('scid', 0);
          $cmpid = (int)$request->get('cmpid', 0);
          return $this->buildProductListResponse($bid, $sid, $scid, $cmpid, 'storage/app/public/slider/banner.jpg');
     }

     public function statecitypincode(Request $request){ 
          $get = $request->get("listof");
          $id = $request->get("name");
          $cacheKey = "pincode_{$get}_" . md5($id ?? 'all');
          $list = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function() use ($get, $id) {
              if($get=='state'){
                  return DB::table("india_pincode")->groupBy("state")->pluck('state');
              }
              if($get=='city'){
                  return DB::table("india_pincode")->where("state",$id)->groupBy("city")->pluck('city');
              }
              if($get=='pincode'){
                  return DB::table("india_pincode")->where("city",$id)->groupBy("pincode")->pluck('pincode');
              }
              return [];
          });
          return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$list]);
     }
     ////////////////////////////////////////////////////////old functions
     public function category(Request $request){ 
         $rmn = MainCategory::where("status","active")->get();
         if(!empty($request->get('mid'))){
             $rmn = Category::where("status","active")->where("mid",$request->get('mid'))->get();
         }
         if(!empty($request->get('cid'))){
             $rmn = SubCatgory::where("status","active")->where("cid",$request->get('cid'))->get();
         }
         if(!empty($request->get('sid'))){
             $rmn = Product::where("status","active")->where("sid",$request->get('sid'))->get();
         }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
     public function colourdata(Request $request){ 
         $rt=array();
             $rmn = Product::where("status","active")->where("mid",$request->get('id'))->get();
             foreach($rmn as $row){
                 if($row->colorcode==null){
                     $dt["mid"] = $row->mid;
                     $dt["cid"] = $row->cid;
                     $dt["sid"] = $row->sid;
                     $dt["color"] = $row->colorimage;
                     $dt["type"] = "colorimage";
                 }else{
                     $dt["mid"] = $row->mid;
                     $dt["cid"] = $row->cid;
                     $dt["sid"] = $row->sid;
                     $dt["color"] = $row->colorcode;
                     $dt["type"] = "colorcode";
                 }
                 array_push($rt,$dt);
             }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rt]);
     }
       public function shardecard(Request $request){ 
         $rmn = ShadeCard::where("status","active");
         if(!empty($request->get('cid'))){
             $rmn = $rmn->where("subcategoryid",$request->get('cid'));
         }
             $rmn = $rmn->orderby('id','desc')->get();
        
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
        public function brandaccordingsharcardid(Request $request){ 
         $attrib = ProductAttributes::select('product_id')->where("color",$request->get('shadecardid'))->get('product_id');
         $prid=array();foreach($attrib as $att){array_push($prid,$att->product_id);}
         $rmn = Product::select('brand')->whereIN("product_id",$prid)->groupby('brand')->get();
          $brid=array();foreach($rmn as $rmnb){
              $brrmn = Product::where("brand",$rmnb->brand)->first();
              $bridw['shadeid']=$brrmn->id;
              $bridw['image']=config('app.url').'/'.$brrmn->image;
              $Brand = Brand::where("id",$rmnb->brand)->first();
              $bridw['name']=$Brand->name;
              $bridw['brandid']=$rmnb->brand;
              array_push($brid,$bridw);
          }
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brid]);
     } 
     public function productlistaccordingbrandandshade(Request $request){ 
            $brrmn = Product::where("brand",$request->brandid)->get();
              $brid=array();foreach($brrmn as $rmnb){
              $brrmn1 = ProductAttributes::where("color",$request->shadeid)->where("product_id",$rmnb->product_id)->first();
              if(!empty($brrmn1)){
                   array_push($brid,$brrmn);
              }else{}
               return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$brid]);
              }
              
         return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
     }
}
