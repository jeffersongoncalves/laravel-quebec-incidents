<?php

namespace JeffersonGoncalves\QuebecIncidents;

use Carbon\CarbonInterval;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\QuebecIncidents\Enums\Risk;
use JeffersonGoncalves\QuebecIncidents\Events\IncidentOpened;
use JeffersonGoncalves\QuebecIncidents\Models\Incident;
use LogicException;

class IncidentManager
{
    /**
     * Enter an incident in the register (s. 3.8) and take reasonable measures to reduce the risk of
     * injury and prevent new incidents (s. 3.5).
     *
     * @param  array<string, mixed>  $attributes  description, circumstances, occurred_at, data_categories, affected_persons, measures
     */
    public function open(string $title, ?DateTimeInterface $discoveredAt = null, array $attributes = []): Incident
    {
        $discoveredAt = Carbon::instance($discoveredAt ?? now());
        $target = config('quebec-incidents.notice_target', '72 hours');

        $incident = Incident::create([
            ...$attributes,
            'title' => $title,
            'discovered_at' => $discoveredAt,
            'notice_due_at' => is_string($target) && $target !== '' ? $discoveredAt->copy()->add(CarbonInterval::make($target)) : null,
            'keep_until' => $discoveredAt->copy()->add(CarbonInterval::make((string) config('quebec-incidents.keep_for', '5 years'))),
        ]);

        event(new IncidentOpened($incident));

        return $incident;
    }

    /**
     * Assess the risk of serious injury (s. 3.7): the sensitivity of the information, the anticipated
     * consequences of its use and the likelihood that it will be used for injurious purposes.
     * Consult the person in charge of the protection of personal information.
     */
    public function assess(Incident $incident, Risk $risk, ?string $sensitivity = null, ?string $consequences = null, ?string $likelihood = null): Incident
    {
        return $this->update($incident, array_filter([
            'risk' => $risk,
            'sensitivity' => $sensitivity,
            'consequences' => $consequences,
            'likelihood' => $likelihood,
        ], fn ($value) => $value !== null));
    }

    /** Notify the Commission d'accès à l'information (s. 3.5; content: s. 3 of the regulation). */
    public function notifyCai(Incident $incident, ?string $reference = null, ?DateTimeInterface $at = null): Incident
    {
        $this->requireSerious($incident);

        return $this->update($incident, ['cai_notified_at' => $at ?? now(), 'cai_reference' => $reference]);
    }

    /** Notify each person whose information is concerned (s. 3.5; content: s. 4 of the regulation). */
    public function notifyPersons(Incident $incident, ?DateTimeInterface $at = null): Incident
    {
        $this->requireSerious($incident);

        return $this->update($incident, ['persons_notified_at' => $at ?? now(), 'persons_notice_delay_reason' => null]);
    }

    /**
     * A public notice instead of individual ones: notifying each person would cause excessive hardship,
     * contact details are missing, or time is short (s. 5 of the regulation).
     */
    public function notifyPublicly(Incident $incident, string $reason, ?DateTimeInterface $at = null): Incident
    {
        $this->requireSerious($incident);

        return $this->update($incident, ['public_notice_at' => $at ?? now(), 'public_notice_reason' => $reason]);
    }

    /** Defer the persons' notice while it could hamper an investigation (s. 3.5, third paragraph). */
    public function deferPersonsNotice(Incident $incident, string $reason): Incident
    {
        return $this->update($incident, ['persons_notice_delay_reason' => $reason]);
    }

    public function close(Incident $incident, ?string $measures = null): Incident
    {
        if ($incident->risk === null) {
            throw new LogicException("Assess the risk of incident [{$incident->getKey()}] before closing it.");
        }

        return $this->update($incident, ['closed_at' => now(), 'measures' => $measures ?? $incident->measures]);
    }

    protected function requireSerious(Incident $incident): void
    {
        if ($incident->risk !== Risk::Serious) {
            throw new LogicException("Incident [{$incident->getKey()}] is not assessed as a risk of serious injury: no notice is owed.");
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function update(Incident $incident, array $attributes): Incident
    {
        $incident->forceFill($attributes)->save();

        return $incident;
    }
}
