<tr>
    @if ($product->type == 'variable')
        <td>
            {{ $variation->product_variation->name }}
            - {{ $variation->name }} ({{ $variation->sub_sku }})
        </td>
    @endif
    <td><span class="display_currency default_price" data-currency_symbol="true"
            data-default-price="{{ $variation->sell_price_inc_tax }}">{{ $variation->sell_price_inc_tax }}</span></td>
    @foreach ($price_groups as $k => $v)
        @php
            $price_grp = $variation->group_prices
                ->filter(function ($item) use ($k) {
                    return $item->price_group_id == $k;
                })
                ->first();
            $group_name_lower = strtolower($v);
        @endphp
        <td>
            {!! Form::text(
                'products[' . $product->id . '][variations][' . $variation->id . '][group_prices][' . $k . ']',
                !empty($price_grp) ? @num_format($price_grp->price_inc_tax) : 0,
                [
                    'class' => 'form-control input-sm input_number price_group_input',
                    'data-group-name' => $group_name_lower,
                    'data-group-id' => $k,
                ],
            ) !!}
        </td>
    @endforeach
</tr>
