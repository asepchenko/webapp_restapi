<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeTripsInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix TRP-000001
        //6 digit counter, max 999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_trips_insert
            BEFORE INSERT ON `trips` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(trip_number,6) FROM trips ORDER BY trip_number DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('00000',CAST(temp AS CHAR)),6);	
                SET NEW.trip_number = CONCAT('TRP-', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_trips_insert` ");
    }
}
