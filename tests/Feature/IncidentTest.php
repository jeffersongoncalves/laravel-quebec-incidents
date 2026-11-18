<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use JeffersonGoncalves\QuebecIncidents\Enums\Risk;
use JeffersonGoncalves\QuebecIncidents\Events\IncidentOpened;
use JeffersonGoncalves\QuebecIncidents\Events\IncidentOverdue;
use JeffersonGoncalves\QuebecIncidents\Facades\QuebecIncidents;
use JeffersonGoncalves\QuebecIncidents\Models\Incident;

it('registers an incident with a notice target and a 5-year keeping period', function () {
    Event::fake([IncidentOpened::class]);
    $this->freezeSecond();

    $incident = QuebecIncidents::open('Backup exposed', now()->subHours(10), ['affected_persons' => 1200]);

    expect($incident->notice_due_at->equalTo(now()->addHours(62)))->toBeTrue()
        ->and($incident->keep_until->equalTo(now()->subHours(10)->addYears(5)))->toBeTrue()
        ->and($incident->owesCaiNotice())->toBeTrue()
        ->and(Incident::query()->scopes('pending')->count())->toBe(1);

    Event::assertDispatched(IncidentOpened::class);
});

it('owes nothing without a risk of serious injury, but keeps the entry', function () {
    $incident = QuebecIncidents::assess(QuebecIncidents::open('Lost encrypted laptop'), Risk::NotSerious, 'Low', 'None expected', 'Key not exposed');

    expect($incident->owesCaiNotice())->toBeFalse()
        ->and($incident->likelihood)->toBe('Key not exposed')
        ->and(Incident::query()->scopes('pending')->count())->toBe(0)
        ->and(fn () => QuebecIncidents::notifyCai($incident))->toThrow(LogicException::class, 'serious injury');
});

it('owes the CAI and the persons a notice for a risk of serious injury', function () {
    $incident = QuebecIncidents::assess(QuebecIncidents::open('Passwords leaked'), Risk::Serious);

    QuebecIncidents::notifyCai($incident, 'CAI-1');

    expect($incident->owesCaiNotice())->toBeFalse()
        ->and($incident->owesPersonsNotice())->toBeTrue()
        ->and(Incident::query()->scopes('pending')->count())->toBe(1);

    QuebecIncidents::notifyPersons($incident);

    expect($incident->fresh()->owesPersonsNotice())->toBeFalse()
        ->and(Incident::query()->scopes('pending')->count())->toBe(0);
});

it('accepts a public notice or a deferral instead of individual notices', function () {
    $public = QuebecIncidents::notifyPublicly(QuebecIncidents::assess(QuebecIncidents::open('A'), Risk::Serious), 'No contact details');
    $deferred = QuebecIncidents::deferPersonsNotice(QuebecIncidents::assess(QuebecIncidents::open('B'), Risk::Serious), 'Police investigation');

    expect($public->owesPersonsNotice())->toBeFalse()
        ->and($deferred->owesPersonsNotice())->toBeFalse();
});

it('needs an assessment before closing', function () {
    QuebecIncidents::close(QuebecIncidents::open('Unknown'));
})->throws(LogicException::class, 'Assess');

it('dispatches an event per overdue incident', function () {
    Event::fake([IncidentOverdue::class]);
    QuebecIncidents::open('A');
    QuebecIncidents::notifyCai(QuebecIncidents::assess(QuebecIncidents::open('B'), Risk::Serious), 'REF');
    QuebecIncidents::assess(QuebecIncidents::open('C'), Risk::NotSerious);

    $this->travel(73)->hours();
    Artisan::call('quebec:incidents-overdue');

    Event::assertDispatchedTimes(IncidentOverdue::class, 1);
});

it('has no overdue incidents without a notice target', function () {
    config()->set('quebec-incidents.notice_target', null);

    expect(QuebecIncidents::open('A')->notice_due_at)->toBeNull();

    $this->travel(1)->years();

    expect(Incident::query()->scopes('overdue')->count())->toBe(0);
});

it('finds entries past their keeping period', function () {
    QuebecIncidents::open('Old', now()->subYears(6));
    QuebecIncidents::open('Recent');

    expect(Incident::query()->scopes('expired')->pluck('title')->all())->toBe(['Old']);
});
