<?php

namespace JeffersonGoncalves\QuebecIncidents\Events;

use JeffersonGoncalves\QuebecIncidents\Models\Incident;

/** Start the response: the 72-hour clock is running. */
class IncidentOpened
{
    public function __construct(public Incident $incident) {}
}
