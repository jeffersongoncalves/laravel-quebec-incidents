<?php

namespace JeffersonGoncalves\QuebecIncidents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\QuebecIncidents\Enums\Risk;

/**
 * A confidentiality incident: access, use or communication not authorized by law, loss, or any other
 * breach of the protection of personal information (s. 3.6). Every incident goes in the register,
 * whether it presents a risk of serious injury or not (s. 3.8).
 *
 * @property string $title
 * @property string|null $description
 * @property string|null $circumstances
 * @property Carbon|null $occurred_at
 * @property Carbon $discovered_at when the business became aware
 * @property list<string>|null $data_categories
 * @property int|null $affected_persons
 * @property Risk|null $risk
 * @property string|null $sensitivity
 * @property string|null $consequences
 * @property string|null $likelihood
 * @property string|null $measures
 * @property Carbon|null $notice_due_at
 * @property Carbon|null $cai_notified_at
 * @property string|null $cai_reference
 * @property Carbon|null $persons_notified_at
 * @property string|null $persons_notice_delay_reason
 * @property Carbon|null $public_notice_at
 * @property string|null $public_notice_reason
 * @property Carbon|null $closed_at
 * @property Carbon $keep_until
 * @property Carbon $created_at
 */
class Incident extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('quebec-incidents.table', 'quebec_incidents');
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'discovered_at' => 'datetime',
            'data_categories' => 'array',
            'affected_persons' => 'integer',
            'risk' => Risk::class,
            'notice_due_at' => 'datetime',
            'cai_notified_at' => 'datetime',
            'persons_notified_at' => 'datetime',
            'public_notice_at' => 'datetime',
            'closed_at' => 'datetime',
            'keep_until' => 'datetime',
        ];
    }

    /** Not assessed yet, or a risk of serious injury and the CAI not notified. */
    public function owesCaiNotice(): bool
    {
        return $this->closed_at === null
            && $this->cai_notified_at === null
            && ($this->risk === null || $this->risk->requiresNotices());
    }

    /** A risk of serious injury and the persons concerned neither notified, publicly told, nor deferred. */
    public function owesPersonsNotice(): bool
    {
        return $this->closed_at === null
            && $this->risk?->requiresNotices() === true
            && $this->persons_notified_at === null
            && $this->public_notice_at === null
            && $this->persons_notice_delay_reason === null;
    }

    public function isOverdue(): bool
    {
        return $this->owesCaiNotice() && $this->notice_due_at !== null && $this->notice_due_at->isPast();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('closed_at')->where(fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->whereNull('cai_notified_at')->where(fn (Builder $q) => $q->whereNull('risk')->orWhere('risk', Risk::Serious->value)))
            ->orWhere(fn (Builder $q) => $q->where('risk', Risk::Serious->value)
                ->whereNull('persons_notified_at')->whereNull('public_notice_at')->whereNull('persons_notice_delay_reason')));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('closed_at')
            ->whereNull('cai_notified_at')
            ->where(fn (Builder $q) => $q->whereNull('risk')->orWhere('risk', Risk::Serious->value))
            ->whereNotNull('notice_due_at')
            ->where('notice_due_at', '<', now());
    }

    /**
     * Entries past their 5-year keeping period (s. 11 of the regulation).
     *
     * @param  Builder<self>  $query
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('keep_until', '<', now());
    }
}
