<div class="box box-solid product_row" id="product_{{ $product->id }}">
    <div class="box-header">
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool remove_product_row" data-product-id="{{ $product->id }}">
                <i class="fa fa-times fa-lg text-danger"></i>
            </button>
        </div>
        <h4 class="box-title" style="color: #3b3a6e; margin-bottom: 5px;">Produk: {{ $product->name }}
            ({{ $product->sku }})</h4>
    </div>
    <div class="box-body" style="padding-top: 0;">
        <div class="table-responsive">
            <table class="table table-condensed table-bordered table-th-green text-center table-striped">
                <thead>
                    <tr>
                        @if ($product->type == 'variable')
                            <th>@lang('lang_v1.variation')</th>
                        @endif
                        <th>@lang('lang_v1.default_selling_price_inc_tax')</th>
                        @foreach ($price_groups as $price_group => $price_group_name)
                            <th>{{ $price_group_name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->variations as $variation)
                        @include('product.partials.bulk_price_update_variation_row')
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
