@extends('layouts.app')
@section('title', __('lang_v1.add_selling_price_group_prices'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('lang_v1.add_selling_price_group_prices')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-xs-12">
                <div class="form-group">
                    {!! Form::text('search_product', null, [
                        'class' => 'form-control',
                        'placeholder' => __('lang_v1.search_product_to_edit'),
                        'id' => 'search_product',
                    ]) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">Kalkulasi Massal</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('bulk_price_group', 'Pilih Grup Harga:') !!}
                                {!! Form::select('bulk_price_group', $price_groups, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => 'Pilih Grup Harga',
                                    'style' => 'width: 100%;',
                                    'id' => 'bulk_price_group',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('bulk_calc_type', 'Jenis Kalkulasi:') !!}
                                {!! Form::select(
                                    'bulk_calc_type',
                                    [
                                        'percentage_discount' => 'Diskon Persentase (%)',
                                        'percentage_markup' => 'Markup Persentase (%)',
                                        'fixed_discount' => 'Diskon Tetap',
                                        'fixed_markup' => 'Markup Tetap',
                                    ],
                                    null,
                                    ['class' => 'form-control select2', 'placeholder' => 'Pilih Jenis', 'id' => 'bulk_calc_type'],
                                ) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('bulk_calc_amount', 'Nilai:') !!}
                                {!! Form::text('bulk_calc_amount', null, [
                                    'class' => 'form-control input_number',
                                    'placeholder' => 'Nilai',
                                    'id' => 'bulk_calc_amount',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success" id="apply_bulk_calc"
                                style="margin-top: 25px;">Terapkan ke Semua</button>
                            <p class="help-block">Kalkulasi dihitung dari harga <strong>grup yang terpilih</strong> saat
                                ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        {!! Form::open([
            'url' => action('ProductBulkPriceUpdateController@bulkUpdate'),
            'method' => 'post',
            'id' => 'bulk_price_update_form',
        ]) !!}
        <div class="row">
            <div class="col-xs-12" id="product_list_body">
                @foreach ($products as $product)
                    @include('product.partials.bulk_price_update_row')
                @endforeach
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('#search_product').length) {
                $('#search_product').autocomplete({
                    source: function(request, response) {
                        $.getJSON(
                            '/products/list-no-variation', {
                                term: request.term,
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 0) {
                            toastr.error(LANG.no_products_found);
                            $('input#search_product').select();
                        }
                    },
                    select: function(event, ui) {
                        addProductRow(ui.item.product_id);
                    },
                }).autocomplete('instance')._renderItem = function(ul, item) {
                    var string = '<li>' + item.name + ' (' + item.sku + ')' + '</li>';
                    return $(string).appendTo(ul);
                }
            }
        });

        function addProductRow(product_id) {
            if ($('#product_' + product_id).length == 0) {
                $.ajax({
                    url: '/products/get-product-for-price-update/' + product_id,
                    dataType: 'html',
                    success: function(result) {
                        if (result) {
                            $('#product_list_body').prepend(result);
                        }
                    },
                });
            }
        }

        // Load price group rules from server
        var price_groups_rules = {!! json_encode($price_groups_json) !!};

        $(document).on('click', '.remove_product_row', function() {
            $(this).closest('.product_row').remove();
        });

        $(document).on('submit', 'form#bulk_price_update_form', function(e) {
            if ($('#product_list_body .product_row').length == 0) {
                e.preventDefault();
                toastr.error('Silakan pilih setidaknya satu produk.');
                return false;
            }
        });

        // Find which price groups depend on a given base group
        function getDependents(base_group_id) {
            var dependents = [];
            $.each(price_groups_rules, function(id, rule) {
                if (rule.base_price_group_id !== null && parseInt(rule.base_price_group_id) === parseInt(
                        base_group_id)) {
                    dependents.push(rule);
                }
            });
            return dependents;
        }

        // Calculate price from base and rule
        function applyCalcRule(base_price, calc_type, calc_amount) {
            if (!calc_type || !calc_amount) return base_price;
            var amount = parseFloat(calc_amount);
            if (calc_type === 'percentage_discount') {
                return base_price * (1 - amount / 100);
            } else if (calc_type === 'percentage_markup') {
                return base_price * (1 + amount / 100);
            } else if (calc_type === 'fixed_discount') {
                return base_price - amount;
            } else if (calc_type === 'fixed_markup') {
                return base_price + amount;
            }
            return base_price;
        }

        // When any price group input changes, recalculate all dependents
        $(document).on('change', 'input.price_group_input', function() {
            var changed_group_id = $(this).data('group-id');
            var tr = $(this).closest('tr');
            var base_price = __read_number($(this));

            var dependents = getDependents(changed_group_id);
            $.each(dependents, function(i, rule) {
                var new_price = applyCalcRule(base_price, rule.calc_type, rule.calc_amount);
                var target = tr.find('input[data-group-id="' + rule.id + '"]');
                __write_number(target, new_price);
                // Trigger change again in case this newly set price is also a base for another group
                target.trigger('change');
            });
        });

        // Bulk calculation logic
        $(document).on('click', '#apply_bulk_calc', function() {
            var selected_group_id = $('#bulk_price_group').val();
            var calc_type = $('#bulk_calc_type').val();
            var calc_amount = $('#bulk_calc_amount').val();

            if (!selected_group_id || !calc_type || !calc_amount) {
                toastr.error('Silakan lengkapi semua input kalkulasi massal.');
                return;
            }

            $('.product_row').each(function() {
                var box = $(this);
                box.find('tbody tr').each(function() {
                    var tr = $(this);

                    // Set the value to the selected group input
                    var target_input = tr.find('input[data-group-id="' + selected_group_id + '"]');
                    if (target_input.length) {
                        // Get current price of the selected group (price lama)
                        var current_price = __read_number(target_input);

                        // Calculate new price based on the current price
                        var new_price = applyCalcRule(current_price, calc_type, calc_amount);

                        __write_number(target_input, new_price);
                        // Trigger change so dependent groups are updated
                        target_input.trigger('change');
                    }
                });
            });

            toastr.success('Kalkulasi massal berhasil diterapkan.');
        });
    </script>
@endsection
