<?php

namespace JeffersonGoncalves\QuebecIncidents\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\QuebecIncidents\IncidentManager;

/**
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident open(string $title, ?\DateTimeInterface $discoveredAt = null, array<string, mixed> $attributes = [])
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident assess(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, \JeffersonGoncalves\QuebecIncidents\Enums\Risk $risk, ?string $sensitivity = null, ?string $consequences = null, ?string $likelihood = null)
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident notifyCai(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, ?string $reference = null, ?\DateTimeInterface $at = null)
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident notifyPersons(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, ?\DateTimeInterface $at = null)
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident notifyPublicly(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, string $reason, ?\DateTimeInterface $at = null)
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident deferPersonsNotice(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, string $reason)
 * @method static \JeffersonGoncalves\QuebecIncidents\Models\Incident close(\JeffersonGoncalves\QuebecIncidents\Models\Incident $incident, ?string $measures = null)
 *
 * @see IncidentManager
 */
class QuebecIncidents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IncidentManager::class;
    }
}
