<?php

namespace JeffersonGoncalves\QuebecIncidents\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\QuebecIncidents\Events\IncidentOverdue;
use JeffersonGoncalves\QuebecIncidents\Models\Incident;

class OverdueCommand extends Command
{
    protected $signature = 'quebec:incidents-overdue';

    protected $description = 'Dispatch IncidentOverdue for every incident not assessed, or not notified to the CAI, by your notice target';

    public function handle(): int
    {
        $count = 0;

        Incident::query()->scopes('overdue')->lazyById()->each(function (Incident $incident) use (&$count): void {
            event(new IncidentOverdue($incident));
            $count++;
        });

        $count === 0
            ? $this->info('No overdue incidents.')
            : $this->warn("{$count} incident(s) past your notice target: the act asks to notify promptly (s. 3.5).");

        return self::SUCCESS;
    }
}
