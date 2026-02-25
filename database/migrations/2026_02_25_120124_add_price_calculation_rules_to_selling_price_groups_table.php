<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceCalculationRulesToSellingPriceGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('selling_price_groups', function (Blueprint $table) {
            $table->integer('base_price_group_id')->unsigned()->nullable();
            $table->string('calc_type')->nullable(); // 'percentage_discount', 'percentage_markup' dst
            $table->decimal('calc_amount', 22, 4)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('selling_price_groups', function (Blueprint $table) {
            $table->dropColumn(['base_price_group_id', 'calc_type', 'calc_amount']);
        });
    }
}
