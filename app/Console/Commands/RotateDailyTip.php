<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tip;

class RotateDailyTip extends Command
{
    protected $signature = 'tips:rotate';
    protected $description = 'Rotate daily active tip';

    public function handle()
    {
        // Set all to inactive
        Tip::where('is_active', true)->update(['is_active' => false]);
        
        // Pick one randomly
        $tip = Tip::inRandomOrder()->first();
        if ($tip) {
            $tip->is_active = true;
            $tip->save();
            $this->info("Tip ID {$tip->id} is now active.");
        } else {
            $this->error("No tips found.");
        }
    }
}