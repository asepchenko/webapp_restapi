<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeBillsInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix INVS-000000001
        //9 digit counter, max 999.999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_bills_insert
            BEFORE INSERT ON `bills` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(bill_number,9) FROM bills ORDER BY bill_number DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('00000000',CAST(temp AS CHAR)),9);	
                SET NEW.bill_number = CONCAT('INVS-', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_bills_insert` ");
    }
}
