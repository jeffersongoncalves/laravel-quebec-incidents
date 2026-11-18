<?php

namespace JeffersonGoncalves\QuebecIncidents\Enums;

/**
 * Whether the confidentiality incident presents a risk of serious injury (s. 3.7): weigh the
 * sensitivity of the information, the anticipated consequences of its use and the likelihood
 * that it will be used for injurious purposes.
 */
enum Risk: string
{
    /** No risk of serious injury: record it in the register, no notice owed. */
    case NotSerious = 'not_serious';

    /** Risk of serious injury: promptly notify the Commission d'accès à l'information and the persons concerned (s. 3.5). */
    case Serious = 'serious';

    public function requiresNotices(): bool
    {
        return $this === self::Serious;
    }
}
