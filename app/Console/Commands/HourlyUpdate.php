<?php

namespace App\Console\Commands;

use App\Logic\PmcTestLogic;
use Illuminate\Console\Command;

class HourlyUpdate extends Command

{

    /**

     * The name and signature of the console command.

     *

     * @var string

     */
    protected $signature = 'hour:update';

    /**

     * The console command description.

     *

     * @var string

     */

    protected $description = "task to extract information apis pmc";


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
        try {
            PmcTestLogic::CasesRecordsLists();
            PmcTestLogic::InsertAllCaseWithRecordId();
            PmcTestLogic::UpdateAllCaseWithRecordId();
            //PmcTestLogic::GetCaseWithRecordId();
        } catch (\Exception $e) {
            return $this->info('Error' . $e);           
        }
        $this->info('The task update was completed successfully');
    }
}
