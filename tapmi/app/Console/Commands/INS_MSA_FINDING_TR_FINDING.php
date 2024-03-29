<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class INS_MSA_FINDING_TR_FINDING extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Kafka:INS_MSA_FINDING_TR_FINDING';

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
        $exe = app('App\Http\Controllers\KafkaController')->RUN_INS_MSA_FINDING_TR_FINDING();
		echo $exe;
    }
}
