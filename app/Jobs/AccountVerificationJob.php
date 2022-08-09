<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordEmail;
use App\Libraries\Logging;
use App\Mail\AccountVerificationEmail;

class AccountVerificationJob extends Job
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
        Mail::to($this->data['email'])->send(new AccountVerificationEmail($this->data));
        $this->log("success", "Email sent to " . $this->data['email']);
    }

    public function failed($exception)
    {
        $this->log("error", $exception->getMessage());
    }

    private function log($status, $message)
    {
        Logging::setFileName("account-verification-" . date("Y-m-d"))->setLogPath("email-log")->add([
            "status" => $status,
            "message" => $message,
            "email" => $this->data['email'],
        ]);
    }
}
