<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\SellingPriceGroup;
use App\Variation;
use App\VariationGroupPrice;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBulkPriceUpdateController extends Controller
{
    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $selected_products_string = $request->input('selected_products');
        if (!empty($selected_products_string)) {
            $selected_products = explode(',', $selected_products_string);
            $business_id = $request->session()->get('user.business_id');

            $products = Product::where('business_id', $business_id)
                ->whereIn('id', $selected_products)
                ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
                ->get();

            $price_groups_collection = SellingPriceGroup::where('business_id', $business_id)->active()->get();
            $price_groups = $price_groups_collection->pluck('name', 'id');
            $price_groups_json = $price_groups_collection->map(function($pg) {
                return [
                    'id'                  => $pg->id,
                    'name'                => $pg->name,
                    'base_price_group_id' => $pg->base_price_group_id,
                    'calc_type'           => $pg->calc_type,
                    'calc_amount'         => $pg->calc_amount,
                ];
            })->keyBy('id');

            return view('product.bulk-price-update')->with(compact(
                'products',
                'price_groups',
                'price_groups_json'
            ));
        }
    }

    public function bulkUpdate(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $products = $request->input('products');
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();
            foreach ($products as $id => $product_data) {
                //Format variations data
                foreach ($product_data['variations'] as $key => $value) {
                    $variation = Variation::where('product_id', $id)->findOrFail($key);

                    //Update price groups
                    if (!empty($value['group_prices'])) {
                        foreach ($value['group_prices'] as $k => $v) {
                            VariationGroupPrice::updateOrCreate(
                                ['price_group_id' => $k, 'variation_id' => $variation->id],
                                ['price_inc_tax' => $this->productUtil->num_uf($v)]
                            );
                        }
                    }
                }
            }
            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __("lang_v1.updated_success")
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return redirect('products')->with('status', $output);
    }

    public function getProductForPriceUpdate($product_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
            ->findOrFail($product_id);
            
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        return view('product.partials.bulk_price_update_row')->with(compact(
            'product',
            'price_groups'
        ));
    }
}
