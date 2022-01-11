<?php

namespace Rongu\Sms\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rongu\Sms\Dhiraagu\SmsStatusChecker;

class SmsStatusCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $smsNotification)
    {
        //
        $this->delay(Carbon::now()->addSeconds(90));
    }

    public function backoff()
    {
        return [30*60, 60*60, 60*60];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new SmsStatusChecker)->check($this->smsNotification);
        
    }
}
