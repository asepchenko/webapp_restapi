<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeInvoicesInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix INV-0000000001
        //10 digit counter, max 9.999.999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_invoices_insert
            BEFORE INSERT ON `invoices` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(invoice_number,10) FROM invoices ORDER BY invoice_number DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('000000000',CAST(temp AS CHAR)),10);	
                SET NEW.invoice_number = CONCAT('INV-', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_invoices_insert` ");
    }
}
