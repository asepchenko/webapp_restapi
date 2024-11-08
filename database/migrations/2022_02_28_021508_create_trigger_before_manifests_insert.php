<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeManifestsInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix MFT-0000000001
        //10 digit counter, max 9.999.999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_manifests_insert
            BEFORE INSERT ON `manifests` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(manifest_number,10) FROM manifests ORDER BY manifest_number DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('000000000',CAST(temp AS CHAR)),10);	
                SET NEW.manifest_number = CONCAT('MFT-', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_manifests_insert` ");
    }
}
