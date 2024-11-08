<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeCustomersInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix CUST-000001
        //6 digit counter, max 999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_customers_insert
            BEFORE INSERT ON `customers` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(customer_code,6) FROM customers ORDER BY customer_code DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('00000',CAST(temp AS CHAR)),6);	
                SET NEW.customer_code = CONCAT('CUST-', result);
            END          
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER `trigger_before_customers_insert` ");
    }
}
