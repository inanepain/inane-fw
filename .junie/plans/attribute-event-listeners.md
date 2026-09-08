---
sessionId: session-260908-122149-1vip
---

# Requirements

### Overview & Goals
Add declarative listener registration to `inanepain/event`: a caller supplies an existing listener object, and the provider discovers its attributed public methods and registers them as PSR-14 callables.

### Functional Requirements
- Add a repeatable method attribute (planned as `Inane\Event\Attribute\Listener`) accepting an event `class-string<object>` and an optional integer priority defaulting to `0`.
- Add an attributed-listener registration method to the built-in providers. It will reflect the supplied object, register each annotated public method for every declared event, and remain fluent.
- Preserve manual `addListener()` registration and all current provider semantics.
- The default and randomised providers retain insertion order for attributed methods; `PrioritisedListenerProvider` uses attribute priority, highest first.
- Do not instantiate listener classes or introduce container coupling.

### Scope
**In scope:** listener-method attributes, reflection-based registration, priority mapping, package tests and documentation.

**Out of scope:** event-class metadata, automatic class scanning, dependency injection/container integration, and changes to `EventDispatcher` or PSR-14 contracts.

# Technical Design

### Current Implementation
- `src/EventDispatcher.php` already dispatches the `iterable` supplied by `Psr\EventDispatcher\ListenerProviderInterface`; it should remain unchanged.
- `src/Provider/ListenerProvider.php` and `src/Provider/RandomizedListenerProvider.php` share `ListenerProviderTrait`, whose `addListener()` keeps insertion order and removes exact duplicate callables.
- `src/Provider/PrioritisedListenerProvider.php` maintains its own `array<string, array<int, callable[]>>` registry and sorts priorities descending.
- `tests/Provider/ListenerProviderTest.php` and `tests/Provider/PrioritisedListenerProviderTest.php` establish these distinct ordering and duplicate behaviours.

### Proposed Changes
- Add `src/Attribute/Listener.php` as a strict, readonly native PHP attribute restricted to methods and marked repeatable. Its constructor exposes the declared event class and `priority = 0`.
- Add a focused provider trait/helper (planned under `src/Provider/`) that reflects a caller-provided object, reads only `Listener` attributes from its public methods, and turns each declaration into the callable pair `[$object, $methodName]`.
- Expose a fluent attributed-object registration API consistently from `ListenerProvider` and `PrioritisedListenerProvider`; adapt the default provider registration signature to accept the priority argument but intentionally preserve its existing insertion-order semantics. `RandomizedListenerProvider` receives the API through inheritance.
- Route each discovered binding through the provider’s existing `addListener()` logic rather than maintaining another listener store. This preserves clearing, duplicate behaviour, aggregate compatibility, and dispatching unchanged.
- Retain native reflection errors for invalid attribute construction and add clear validation for registrations that cannot form an invocable public listener method or name a usable event class.

### Files
- Add: `lib/inanepain/event/src/Attribute/Listener.php`.
- Add: a narrowly scoped provider discovery trait/helper in `lib/inanepain/event/src/Provider/`.
- Modify: `src/Provider/ListenerProviderTrait.php`, `src/Provider/ListenerProvider.php` as needed for the common registration API, and `src/Provider/PrioritisedListenerProvider.php` for priority-aware registration.
- Update: provider tests, `doc/readme/part/class/listener-provider.adoc`, `doc/readme/part/class/prioritised-listener-provider.adoc`, usage includes as needed, generated `README.adoc`, and the release changelog source/generated `CHANGELOG.adoc` if this package’s release convention requires it.

### Risks
- Reflection must never register inaccessible methods; discovery will limit candidates to public methods before creating callables.
- The two provider implementations intentionally differ in duplicate treatment; discovery will delegate to their existing registration methods so no new inconsistency is introduced.
- Attribute priority must not accidentally reorder the non-prioritised provider; it is accepted there only for a uniform attributed API and ignored for ordering.

# Testing

### Validation Approach
Run the Event submodule PHPUnit suite using `lib/inanepain/event/phpunit.xml` (and the root aggregated suite when practical) after adding focused tests.

### Key Scenarios
- An attributed public method is invoked when its declared event is dispatched.
- A repeatable attribute registers one method for multiple event classes.
- A caller-supplied object retains its state when its annotated method runs.
- Default provider discovery keeps declaration/registration order; randomised provider remains usable; priority provider executes higher attribute priorities first and preserves equal-priority order.
- Existing explicit registration, clearing, duplicate behaviour, aggregate composition, and stoppable-event dispatch tests remain passing.

### Failure Scenarios
- Assert the defined failure for an invalid event class and any non-invocable attributed method.
- Verify that unannotated methods produce no registrations.

# Delivery Steps

### ✓ Step 1: Introduce the listener attribute and shared discovery registration
Attributed public methods can be discovered from a caller-supplied listener object.

- Add the repeatable, method-targeted `Inane\Event\Attribute\Listener` attribute with event-class and optional priority metadata.
- Implement the focused provider-side reflection discovery helper under `src/Provider/`.
- Provide the fluent attributed-object registration API while preserving strict types, typed contracts, and existing manual listener registration.

### ✓ Step 2: Integrate attributes with each built-in provider’s ordering model
Discovered listener methods enter the existing provider registries with correct ordering semantics.

- Update `ListenerProviderTrait.php` / `ListenerProvider.php` to accept discovered bindings without changing insertion-order or duplicate prevention behaviour.
- Update `PrioritisedListenerProvider.php` so attribute priority is passed into its existing priority buckets.
- Ensure `RandomizedListenerProvider` inherits the new registration capability and `AggregateProvider` continues to operate via the unchanged PSR-14 provider contract.

### ✓ Step 3: Cover attributed registration and document the public API
The feature is tested and documented as part of the Event package.

- Extend provider PHPUnit tests with annotated listener fixtures covering dispatch, repeatable bindings, object state, ordering, priority, ignored methods, and invalid declarations.
- Re-run the Event package suite and relevant aggregated tests to protect existing explicit registration and stoppable-event behaviour.
- Update listener-provider AsciiDoc reference and usage material, then regenerate package README/changelog artefacts according to the existing documentation workflow.