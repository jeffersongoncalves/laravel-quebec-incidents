<?php

return [

    'table' => 'quebec_incidents',

    /*
     * The act asks to notify the Commission d'accès à l'information and the persons concerned
     * "promptly" when an incident presents a risk of serious injury (s. 3.5). It sets no hours:
     * this is your own target, used by quebec:incidents-overdue. null disables it.
     */
    'notice_target' => '72 hours',

    /*
     * Keep each register entry for at least 5 years after you became aware of the incident
     * (Regulation respecting confidentiality incidents, s. 11).
     */
    'keep_for' => '5 years',

];
