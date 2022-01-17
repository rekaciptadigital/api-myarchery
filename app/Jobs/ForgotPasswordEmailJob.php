<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordEmail;
use Log;

class ForgotPasswordEmailJob extends Job
{
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->data['email'])->send(new ForgotPasswordEmail($this->data));
        Log::channel('forget-password')->info("Email sent to " . $this->data['email']);
    }

    public function failed($exception)
    {
        $exception->getMessage();
        Log::channel('forget-password')->error($exception->getMessage());
    }
}