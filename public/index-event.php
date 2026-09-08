<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Event\Event;
use Inane\Event\EventDispatcher;
use Inane\Event\LimitedEvent;
use Inane\Event\Provider\AggregateProvider;
use Inane\Event\Provider\ListenerProvider;
use Inane\Event\Provider\PrioritisedListenerProvider;
use Inane\Event\Provider\RandomizedListenerProvider;
use Inane\Event\StoppableEvent;

$pen = new Pencil();

// Use directly
$event = new Event();
Cli::line($event->name);

// Extend to create a named domain event
class UserRegistered extends Event {
    public function __construct(
        public readonly string $username,
    ) {}
}

$event = new UserRegistered('alice');
Cli::line($event->name);
Cli::line($event->username);

$pen->divider();

class Odd extends StoppableEvent {
    public function __construct(
        public readonly string $message = '',
    ) {}
}

$provider = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);

$provider->addListener(Odd::class, function(Odd $event) {
    Cli::line('Listener 1: ' . $event->message);
    if ($event->message > 5) $event->stopPropagation();
});

$provider->addListener(Odd::class, function(Odd $event) {
    Cli::line('Listener 2: ' . $event->message);
});

$dispatcher->dispatch(new Odd('3')); // both listeners fire
$dispatcher->dispatch(new Odd('7')); // only listener 1 fires

$pen->divider();

// $provider = new ListenerProvider();
// $dispatcher = new EventDispatcher($provider);

$provider->addListener(Event::class, function(Event $event) {
    Cli::line('Received: ' . $event->name);
});
$dispatcher->dispatch(new Event());

$pen->divider();

// $provider = new ListenerProvider();
// $dispatcher = new EventDispatcher($provider);

$provider->addListener(Event::class, fn(Event $e) => Cli::line('First'))
    ->addListener(Event::class, fn(Event $e) => Cli::line('Second'))
;

$dispatcher->dispatch(new Event());

$pen->divider();

$prioritisedProvider = new PrioritisedListenerProvider();
$dispatcher = new EventDispatcher($prioritisedProvider);

$prioritisedProvider->addListener(Event::class, fn(Event $e) => Cli::line('Low'), priority: -10)
    ->addListener(Event::class, fn(Event $e) => Cli::line('Highest'), priority: 100)
    ->addListener(Event::class, fn(Event $e) => Cli::line('Default'))
    ->addListener(Event::class, fn(Event $e) => Cli::line('High'), priority: 10)
;

$dispatcher->dispatch(new Event());
// High
// Default
// Low

$pen->divider();

$randomProvider = new RandomizedListenerProvider();
$dispatcher = new EventDispatcher($randomProvider);

$provider->addListener(Event::class, fn(Event $e) => Cli::line('A'))
    ->addListener(Event::class, fn(Event $e) => Cli::line('B'))
    ->addListener(Event::class, fn(Event $e) => Cli::line('C'))
;

// Order of A, B, C is random on each dispatch
$dispatcher->dispatch(new Event());

$pen->divider();

// $basic      = new ListenerProvider();
// $prioritized = new PrioritisedListenerProvider();

$provider->addListener(Event::class, fn(Event $e) => Cli::line('Basic listener'));
$provider->addListener(LimitedEvent::class, fn(Event $e) => Cli::line('Limited: ' . $e->count));
$prioritisedProvider->addListener(Event::class, fn(Event $e) => Cli::line('Priority listener'), priority: 5);

$aggregateProvider = new AggregateProvider();
$aggregateProvider->addProvider($provider)
    ->addProvider($prioritisedProvider)
;

$dispatcher = new EventDispatcher($aggregateProvider);

$dispatcher->dispatch(new Event());
$pen->divider('*', 40, 1);
$dispatcher->dispatch(new Odd('3'));
$pen->divider('*', 40, 1);
$dispatcher->dispatch(new Odd('2'));
$pen->divider('*', 40, 1);
$le = new LimitedEvent();
$dispatcher->dispatch($le);
$dispatcher->dispatch($le);
$dispatcher->dispatch($le);

$pen->divider();
