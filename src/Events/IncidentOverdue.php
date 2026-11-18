<?php

namespace JeffersonGoncalves\QuebecIncidents\Events;

use JeffersonGoncalves\QuebecIncidents\Models\Incident;

/** The supervisory authority was not notified within 72 hours. */
class IncidentOverdue
{
    public function __construct(public Incident $incident) {}
}
