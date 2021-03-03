<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class INS_IDMS_TM_ROAD_UPDATE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Kafka:INS_IDMS_TM_ROAD_UPDATE';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exe = app('App\Http\Controllers\KafkaController')->RUN_INS_IDMS_TM_ROAD_UPDATE();
		echo $exe;
    }
}
