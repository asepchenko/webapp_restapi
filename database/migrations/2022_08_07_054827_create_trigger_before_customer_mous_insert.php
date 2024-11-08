<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeCustomerMousInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix LKE-YYYY00001
        //5 digit counter, max 99.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_customer_mous_insert
            BEFORE INSERT ON `customer_mous` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(mou_number,5) FROM customer_mous ORDER BY mou_number DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('00000',CAST(temp AS CHAR)),5);	
                SET NEW.mou_number = CONCAT('LKE/', CAST(year(now()) AS CHAR), '/', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_customer_mous_insert` ");
    }
}
