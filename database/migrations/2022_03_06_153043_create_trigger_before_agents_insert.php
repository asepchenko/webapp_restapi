<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerBeforeAgentsInsert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //using prefix AGT-000001
        //6 digit counter, max 999.999
        DB::unprepared("
            CREATE TRIGGER trigger_before_agents_insert
            BEFORE INSERT ON `agents` FOR EACH ROW
            BEGIN
                DECLARE result 		VARCHAR(20);
                DECLARE temp  	    INT;
                    
                SET temp = (SELECT RIGHT(agent_code,6) FROM agents ORDER BY agent_code DESC LIMIT 1);
                SET temp = COALESCE(temp,0)+1;
                SET result = RIGHT(CONCAT('00000',CAST(temp AS CHAR)),6);	
                SET NEW.agent_code = CONCAT('AGT-', result);
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
        DB::unprepared("DROP TRIGGER `trigger_before_agents_insert` ");
    }
}
