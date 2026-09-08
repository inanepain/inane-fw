---
sessionId: session-260908-144219-5b6c
---

# Requirements

### Overview & Goals
Expand `lib/inanepain/event/doc/readme/part/listener/attribute/listener.adoc` from its current title-only placeholder into practical documentation for `Inane\Event\Attribute\Listener`.

### Scope
- Explain that `#[Listener]` annotates public listener methods with an event class and optional priority.
- Include one simple end-to-end example using `ListenerProvider`, `addAttributedListener()`, and `EventDispatcher`.
- Include one comprehensive example using `PrioritisedListenerProvider`, explicit priorities, and repeatable attributes on a single public method.
- State the registration constraints and priority behaviour relevant to the examples.

### Out of Scope
- No changes to `Listener.php`, provider implementation, runtime behaviour, or generated root documentation.

# Technical Design

### Current Implementation
- `lib/inanepain/event/src/Attribute/Listener.php` declares a repeatable PHP method attribute with `string $event` (documented as `class-string<object>`) and `int $priority = 0`.
- `lib/inanepain/event/src/Provider/AttributedListenerProviderTrait.php` reflects annotated methods when `addAttributedListener(object $listener)` is called; it rejects non-public methods and nonexistent event classes, then registers each attribute separately.
- `ListenerProvider` uses this trait but discards the attribute priority and keeps registration order. `PrioritisedListenerProvider` retains it and returns higher numeric priorities first, keeping insertion order for equal priorities.
- `EventDispatcher` obtains listeners from the selected PSR-14 provider and invokes each with the dispatched event.

### Proposed Changes
- Retain `= Listener Attribute` as the page title and add concise prose describing attribute placement, `event`, and the default priority.
- Add a `Simple example` AsciiDoc source block showing a strictly typed PHP file: define an event and a listener with one `#[Listener(Event::class)]` public method, register its object on `ListenerProvider`, and dispatch the event through `EventDispatcher`.
- Add a `Comprehensive example` source block showing a strictly typed PHP file that registers one listener object on `PrioritisedListenerProvider`; demonstrate multiple attributes on one method and a higher-priority method so the ordering and repeatability are meaningful.
- Follow the repository’s established AsciiDoc convention (`[source,php]` and `----` delimiters), seen in `source/lib/app/doc/readme/part/example/card-image.adoc`.
- Clarify that the default provider does not apply the declared priority, so callers choose `PrioritisedListenerProvider` when execution order matters.

# Validation

### Validation Approach
- Review both snippets against the signatures and reflection rules in `Listener.php`, `AttributedListenerProviderTrait.php`, `ListenerProvider.php`, `PrioritisedListenerProvider.php`, and `EventDispatcher.php`.
- Check AsciiDoc section and source-block syntax against the project’s existing documentation fragments.

### Key Scenarios
- The simple example registers a public annotated method for an existing event class and dispatches that event.
- The comprehensive example uses repeatable attributes and demonstrates that higher priorities run first only with `PrioritisedListenerProvider`.

# Delivery Steps

### ✓ Step 1: Document the basic listener-attribute workflow
The target page explains and demonstrates registering one annotated listener with the default provider.

- Update `lib/inanepain/event/doc/readme/part/listener/attribute/listener.adoc` with concise attribute-contract guidance.
- Add a simple, strictly typed PHP source block defining an event and a public `#[Listener(...)]` method.
- Show registration via `ListenerProvider::addAttributedListener()` and dispatch via `EventDispatcher`.
- State that the event argument must name an existing event class and attributed methods must be public.

### ✓ Step 2: Add the advanced priority and repeatability example
The target page demonstrates multi-event registration and deterministic priority ordering.

- Add a comprehensive, strictly typed PHP source block using `PrioritisedListenerProvider`.
- Apply repeatable `#[Listener]` attributes to one public method for distinct event classes.
- Include listener priorities that make the highest-first execution rule clear.
- Explain that equal priorities retain registration order and that `ListenerProvider` intentionally ignores declared priority.
- Validate the prose and snippets against the provider reflection and dispatch behaviour, plus existing AsciiDoc source-block formatting.