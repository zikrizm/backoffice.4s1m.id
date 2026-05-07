@extends('layouts.app')
@section('title', __( 'lang_v1.all_sales'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang( 'sale.sells')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
        @if($is_woocommerce)
            <div class="col-md-3">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                          {!! Form::checkbox('only_woocommerce_sells', 1, false, 
                          [ 'class' => 'input-icheck', 'id' => 'synced_from_woocommerce']); !!} {{ __('lang_v1.synced_from_woocommerce') }}
                        </label>
                    </div>
                </div>
            </div>
        @endif
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')])
        @can('direct_sell.access')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('SellController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        @if(auth()->user()->can('direct_sell.view') ||  auth()->user()->can('view_own_sell_only') ||  auth()->user()->can('view_commission_agent_sell'))
        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
         @endphp
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('messages.date')</th>
                        <th>@lang('sale.invoice_no')</th>
                        <th>@lang('sale.products')</th>
                        <th>@lang('lang_v1.quantity')</th>
                        <th>@lang('sale.unit_price')</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('lang_v1.contact_no')</th>
                        <th>@lang('sale.location')</th>
                        <th>@lang('sale.payment_status')</th>
                        <th>@lang('lang_v1.payment_method')</th>
                        <th>@lang('sale.total_amount')</th>
                        <th>@lang('sale.total_paid')</th>
                        <th>@lang('lang_v1.sell_due')</th>
                        <th>@lang('lang_v1.sell_return_due')</th>
                        <th>@lang('lang_v1.shipping_status')</th>
                        <th>@lang('lang_v1.total_items')</th>
                        <th>@lang('lang_v1.types_of_service')</th>
                        <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}</th>
                        <th>@lang('lang_v1.added_by')</th>
                        <th>@lang('sale.sell_note')</th>
                        <th>@lang('sale.staff_note')</th>
                        <th>@lang('sale.shipping_details')</th>
                        <th>@lang('restaurant.table')</th>
                        <th>@lang('restaurant.service_staff')</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="9"><strong>@lang('sale.total'):</strong></td>
                        <td class="footer_payment_status_count"></td>
                        <td class="payment_method_count"></td>
                        <td class="footer_sale_total"></td>
                        <td class="footer_total_paid"></td>
                        <td class="footer_total_remaining"></td>
                        <td class="footer_total_sell_return_due"></td>
                        <td colspan="2"></td>
                        <td class="service_type_count"></td>
                        <td colspan="7"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });

    var export_buttons = [
        {
            extend: 'csv',
            text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG.export_to_csv,
            className: 'btn-sm',
            exportOptions: {
                columns: ':visible',
            },
            footer: true,
        },
        {
            extend: 'excel',
            text: '<i class="fa fa-file-excel" aria-hidden="true"></i> ' + LANG.export_to_excel,
            className: 'btn-sm',
            exportOptions: {
                columns: ':visible, .export-only',
                stripNewlines: false
            },
            footer: true,
            customize: function(xlsx) {
                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                var sheetData = sheet.getElementsByTagName('sheetData')[0];
                var rows = listToArray(sheetData.getElementsByTagName('row'));
                
                function listToArray(list) {
                    var array = [];
                    for (var i = 0; i < list.length; i++) {
                        array.push(list[i]);
                    }
                    return array;
                }
                
                function getCellText(cell) {
                    if (!cell) return "";
                    var isElem = cell.getElementsByTagName('is')[0];
                    if (isElem) {
                        var tElem = isElem.getElementsByTagName('t')[0];
                        if (tElem) return tElem.textContent || "";
                    }
                    var vElem = cell.getElementsByTagName('v')[0];
                    if (vElem) return vElem.textContent || "";
                    return "";
                }
                
                // Clear the sheetData element first
                while (sheetData.firstChild) {
                    sheetData.removeChild(sheetData.firstChild);
                }
                
                var excelRowIndex = 1;
                var excelMergeCells = [];
                
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    var rowNum = parseInt(row.getAttribute('r'));
                    
                    if (rowNum < 3) {
                        // Title/Header rows: copy as is, but ensure row reference is correct
                        row.setAttribute('r', excelRowIndex);
                        var cells = row.getElementsByTagName('c');
                        for (var c = 0; c < cells.length; c++) {
                            var colLetter = cells[c].getAttribute('r').replace(/[0-9]/g, '');
                            cells[c].setAttribute('r', colLetter + excelRowIndex);
                        }
                        sheetData.appendChild(row);
                        excelRowIndex++;
                        continue;
                    }
                    
                    // This is a data row. Let's find Products, Qty, and Price cells.
                    var cells = listToArray(row.getElementsByTagName('c'));
                    var prodCell = null, qtyCell = null, priceCell = null;
                    
                    for (var c = 0; c < cells.length; c++) {
                        var cell = cells[c];
                        var colLetter = cell.getAttribute('r').replace(/[0-9]/g, '');
                        // Map our columns based on the design:
                        // Col D is Products, Col E is Quantity, Col F is Price.
                        if (colLetter === 'D') prodCell = cell;
                        if (colLetter === 'E') qtyCell = cell;
                        if (colLetter === 'F') priceCell = cell;
                    }
                    
                    var prods = getCellText(prodCell).split('\n');
                    var qtys = getCellText(qtyCell).split('\n');
                    var prices = getCellText(priceCell).split('\n');
                    
                    var maxLines = Math.max(prods.length, qtys.length, prices.length);
                    
                    if (maxLines > 1) {
                        var startRow = excelRowIndex;
                        
                        for (var j = 0; j < maxLines; j++) {
                            var newRow = sheet.createElement('row');
                            newRow.setAttribute('r', excelRowIndex);
                            
                            for (var c = 0; c < cells.length; c++) {
                                var cell = cells[c];
                                var colLetter = cell.getAttribute('r').replace(/[0-9]/g, '');
                                
                                if (colLetter === 'D') {
                                    var newCell = sheet.createElement('c');
                                    newCell.setAttribute('r', colLetter + excelRowIndex);
                                    newCell.setAttribute('t', 'inlineStr');
                                    var isElem = sheet.createElement('is');
                                    var tElem = sheet.createElement('t');
                                    tElem.textContent = prods[j] || "";
                                    isElem.appendChild(tElem);
                                    newCell.appendChild(isElem);
                                    newRow.appendChild(newCell);
                                } else if (colLetter === 'E') {
                                    var newCell = sheet.createElement('c');
                                    newCell.setAttribute('r', colLetter + excelRowIndex);
                                    newCell.setAttribute('t', 'inlineStr');
                                    var isElem = sheet.createElement('is');
                                    var tElem = sheet.createElement('t');
                                    tElem.textContent = qtys[j] || "";
                                    isElem.appendChild(tElem);
                                    newCell.appendChild(isElem);
                                    newRow.appendChild(newCell);
                                } else if (colLetter === 'F') {
                                    var newCell = sheet.createElement('c');
                                    newCell.setAttribute('r', colLetter + excelRowIndex);
                                    newCell.setAttribute('t', 'inlineStr');
                                    var isElem = sheet.createElement('is');
                                    var tElem = sheet.createElement('t');
                                    tElem.textContent = prices[j] || "";
                                    isElem.appendChild(tElem);
                                    newCell.appendChild(isElem);
                                    newRow.appendChild(newCell);
                                } else {
                                    // Other columns: keep value only in the first split row, make empty in subsequent rows
                                    if (j === 0) {
                                        var newCell = cell.cloneNode(true);
                                        newCell.setAttribute('r', colLetter + excelRowIndex);
                                        newRow.appendChild(newCell);
                                    } else {
                                        var newCell = sheet.createElement('c');
                                        newCell.setAttribute('r', colLetter + excelRowIndex);
                                        newRow.appendChild(newCell);
                                    }
                                }
                            }
                            sheetData.appendChild(newRow);
                            excelRowIndex++;
                        }
                        
                        // Register merge cells for non-item columns
                        for (var c = 0; c < cells.length; c++) {
                            var cell = cells[c];
                            var colLetter = cell.getAttribute('r').replace(/[0-9]/g, '');
                            if (colLetter !== 'D' && colLetter !== 'E' && colLetter !== 'F') {
                                excelMergeCells.push(colLetter + startRow + ':' + colLetter + (excelRowIndex - 1));
                            }
                        }
                    } else {
                        // Single item: just append as is with updated row index
                        var newRow = row.cloneNode(true);
                        newRow.setAttribute('r', excelRowIndex);
                        var rowCells = newRow.getElementsByTagName('c');
                        for (var c = 0; c < rowCells.length; c++) {
                            var colLetter = rowCells[c].getAttribute('r').replace(/[0-9]/g, '');
                            rowCells[c].setAttribute('r', colLetter + excelRowIndex);
                        }
                        sheetData.appendChild(newRow);
                        excelRowIndex++;
                    }
                }
                
                // Add mergeCells to the sheet
                if (excelMergeCells.length > 0) {
                    var mergeCells = sheet.getElementsByTagName('mergeCells');
                    
                    if (mergeCells.length === 0) {
                        var worksheet = sheet.getElementsByTagName('worksheet')[0];
                        var newMergeCells = sheet.createElement('mergeCells');
                        newMergeCells.setAttribute('count', excelMergeCells.length);
                        
                        for (var m = 0; m < excelMergeCells.length; m++) {
                            var newMergeCell = sheet.createElement('mergeCell');
                            newMergeCell.setAttribute('ref', excelMergeCells[m]);
                            newMergeCells.appendChild(newMergeCell);
                        }
                        
                        worksheet.insertBefore(newMergeCells, sheetData.nextSibling);
                    } else {
                        var existingMergeCells = mergeCells[0];
                        for (var m = 0; m < excelMergeCells.length; m++) {
                            var newMergeCell = sheet.createElement('mergeCell');
                            newMergeCell.setAttribute('ref', excelMergeCells[m]);
                            existingMergeCells.appendChild(newMergeCell);
                        }
                        var currentCount = parseInt(existingMergeCells.getAttribute('count')) || 0;
                        existingMergeCells.setAttribute('count', currentCount + excelMergeCells.length);
                    }
                }
            }
        },
        {
            extend: 'print',
            text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG.print,
            className: 'btn-sm',
            exportOptions: {
                columns: ':visible',
                stripHtml: true,
            },
            footer: true,
            customize: function ( win ) {
                if ($('.print_table_part').length > 0 ) {
                    $($('.print_table_part').html()).insertBefore($(win.document.body).find( 'table' ));
                }
                if ($(win.document.body).find( 'table.hide-footer').length) {
                    $(win.document.body).find( 'table.hide-footer tfoot' ).remove();
                }
                __currency_convert_recursively($(win.document.body).find( 'table' ));
            }
        },
        {
            extend: 'colvis',
            text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG.col_vis,
            className: 'btn-sm',
        },
    ];

    var pdf_btn = {
        extend: 'pdf',
        text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG.export_to_pdf,
        className: 'btn-sm',
        exportOptions: {
            columns: ':visible',
        },
        footer: true,
    };

    if (typeof non_utf8_languages !== 'undefined' && typeof app_locale !== 'undefined' && non_utf8_languages.indexOf(app_locale) == -1) {
        export_buttons.push(pdf_btn);
    }

    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        buttons: export_buttons,
        "ajax": {
            "url": "/sells",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.is_direct_sale = 1;

                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.service_staffs = $('#service_staffs').val();

                if($('#shipping_status').length) {
                    d.shipping_status = $('#shipping_status').val();
                }
                
                @if($is_woocommerce)
                    if($('#synced_from_woocommerce').is(':checked')) {
                        d.only_woocommerce_sells = 1;
                    }
                @endif

                if($('#only_subscriptions').is(':checked')) {
                    d.only_subscriptions = 1;
                }

                d = __datatable_ajax_callback(d);
            }
        },
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        columns: [
            { data: 'action', name: 'action', orderable: false, "searchable": false},
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'sell_items_names', name: 'sell_items_names', orderable: false, searchable: false, visible: false, className: 'export-only' },
            { data: 'sell_items_qtys', name: 'sell_items_qtys', orderable: false, searchable: false, visible: false, className: 'export-only' },
            { data: 'sell_items_prices', name: 'sell_items_prices', orderable: false, searchable: false, visible: false, className: 'export-only' },
            { data: 'conatct_name', name: 'conatct_name'},
            { data: 'mobile', name: 'contacts.mobile'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'payment_status', name: 'payment_status'},
            { data: 'payment_methods', orderable: false, "searchable": false},
            { data: 'final_total', name: 'final_total'},
            { data: 'total_paid', name: 'total_paid', "searchable": false},
            { data: 'total_remaining', name: 'total_remaining'},
            { data: 'return_due', orderable: false, "searchable": false},
            { data: 'shipping_status', name: 'shipping_status'},
            { data: 'total_items', name: 'total_items', "searchable": false},
            { data: 'types_of_service_name', name: 'tos.name', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'service_custom_field_1', name: 'service_custom_field_1', @if(empty($is_types_service_enabled)) visible: false @endif},
            { data: 'added_by', name: 'u.first_name'},
            { data: 'additional_notes', name: 'additional_notes'},
            { data: 'staff_note', name: 'staff_note'},
            { data: 'shipping_details', name: 'shipping_details'},
            { data: 'table_name', name: 'tables.name', @if(empty($is_tables_enabled)) visible: false @endif },
            { data: 'waiter', name: 'ss.first_name', @if(empty($is_service_staff_enabled)) visible: false @endif },
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#sell_table'));
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            for (var r in data){
                footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;
                footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due').data('orig-value') ? parseFloat($(data[r].return_due).find('.sell_return_due').data('orig-value')) : 0;
            }

            $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due));
            $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));

            $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
            $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
            var payment_method_sums = {};
            var payment_method_counts = {};
            for (var r in data) {
                var paymentMethodSpan = $(data[r].payment_methods);
                var details = paymentMethodSpan.data('payment-details');
                
                if (details) {
                    for (var i = 0; i < details.length; i++) {
                        var m = details[i].method || "Other";
                        var amt = parseFloat(details[i].amount) || 0;
                        if (!payment_method_sums[m]) {
                            payment_method_sums[m] = 0;
                        }
                        payment_method_sums[m] += amt;
                    }
                }
                
                var origValue = paymentMethodSpan.data('orig-value');
                if (origValue) {
                    if (!payment_method_counts[origValue]) {
                        payment_method_counts[origValue] = 0;
                    }
                    payment_method_counts[origValue] += 1;
                }
            }
            
            var pm_html = '<p class="text-left"><small>';
            for (var m in payment_method_counts) {
                var count = payment_method_counts[m];
                var sum = payment_method_sums[m] || 0;
                pm_html += m + ' - ' + count + ' (' + __currency_trans_from_en(sum, true) + ')</br>';
            }
            pm_html += '</small></p>';
            $('.payment_method_count').html(pm_html);
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status',  function() {
        sell_table.ajax.reload();
    });
    @if($is_woocommerce)
        $('#synced_from_woocommerce').on('ifChanged', function(event){
            sell_table.ajax.reload();
        });
    @endif

    $('#only_subscriptions').on('ifChanged', function(event){
        sell_table.ajax.reload();
    });
});
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection