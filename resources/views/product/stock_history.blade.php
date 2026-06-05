@extends('layouts.app')
@section('title', __('lang_v1.product_stock_history'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.product_stock_history')</h1>
</section>

<!-- Main content -->
<section class="content">
<div class="row">
    <div class="col-md-12">
    @component('components.widget', ['title' => $product->name])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
        @if($product->type == 'variable')
            <div class="col-md-3">
                <div class="form-group">
                    <label for="variation_id">@lang('product.variations'):</label>
                    <select class="select2 form-control" name="variation_id" id="variation_id">
                        @foreach($product->variations as $variation)
                            <option value="{{$variation->id}}">{{$variation->product_variation->name}} - {{$variation->name}} ({{$variation->sub_sku}})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" id="variation_id" name="variation_id" value="{{$product->variations->first()->id}}">
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('stock_history_start_date', __('business.start_date') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('stock_history_start_date', null, ['placeholder' => __('business.start_date'), 'class' => 'form-control', 'readonly', 'id' => 'stock_history_start_date']); !!}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('stock_history_end_date', __('business.end_date') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('stock_history_end_date', null, ['placeholder' => __('business.end_date'), 'class' => 'form-control', 'readonly', 'id' => 'stock_history_end_date']); !!}
                </div>
            </div>
        </div>
    @endcomponent
    @component('components.widget')
        <div id="product_stock_history" style="display: none;"></div>
    @endcomponent
    </div>
</div>

</section>
<!-- /.content -->
@endsection

@section('javascript')
   <script type="text/javascript">
        $(document).ready( function(){
            $('#stock_history_start_date, #stock_history_end_date').datepicker({
                autoclose: true,
                todayHighlight: true,
                format: datepicker_date_format,
            }).on('changeDate clearDate', function() {
                load_stock_history($('#variation_id').val(), $('#location_id').val());
            });

            load_stock_history($('#variation_id').val(), $('#location_id').val());
        });

        function get_stock_history_date_param(date_input) {
            var date_val = $(date_input).val();
            if (!date_val) {
                return null;
            }

            return moment(date_val, moment_date_format).format('YYYY-MM-DD');
        }

       function load_stock_history(variation_id, location_id) {
            $('#product_stock_history').fadeOut();

            var url = '/products/stock-history/' + variation_id + "?location_id=" + location_id;

            var start_date = get_stock_history_date_param('#stock_history_start_date');
            var end_date = get_stock_history_date_param('#stock_history_end_date');

            if (start_date) {
                url += '&start_date=' + start_date;
            }
            if (end_date) {
                url += '&end_date=' + end_date;
            }

            $.ajax({
                url: url,
                dataType: 'html',
                success: function(result) {
                    if ($.fn.DataTable.isDataTable('#stock_history_table')) {
                        $('#stock_history_table').DataTable().destroy();
                    }

                    $('#product_stock_history')
                        .html(result)
                        .fadeIn();

                    __currency_convert_recursively($('#product_stock_history'));

                    $('#stock_history_table').DataTable({
                        searching: false,
                        ordering: false
                    });
                },
            });
       }

       $(document).on('change', '#variation_id, #location_id', function(){
            load_stock_history($('#variation_id').val(), $('#location_id').val());
       });
   </script>
@endsection