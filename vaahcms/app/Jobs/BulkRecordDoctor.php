<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VaahCms\Modules\Appointments\Models\Doctor;
use Illuminate\Support\Facades\Log;


class BulkRecordDoctor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record_count;

    /**
     * Create a new job instance.
     */
    public function __construct($records_count)
    {
        $this->record_count = $records_count;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //
        Log ::info('Starting record creation job for ' . $this->record_count . ' records.');
        $i = 0;
        while ($i<$this->record_count){
            $inputs = Doctor::fillItem(false);

            $item =  new Doctor();
            $item->fill($inputs);
            $item->save();

            $i++;
        }
        Log::info('Record creation job for ' . $this->record_count . ' records completed.');
    }
}
