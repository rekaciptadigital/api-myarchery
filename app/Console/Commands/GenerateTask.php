<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\User;
class GenerateTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'untuk runing query generate dat bnyak';

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
        $p = ArcheryEventParticipant::where("event_id",21)->where("status",1)->get();
        foreach ($p as $key => $value) {
            $u = User::find($value->user_id);
            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($value->event_category_id, $u->gender), $value->id);
        }
    }
}
