# Project Playbook (macOS, local dev)

## Environment

### Prerequisites

- `php` 8.5+ (validated: `PHP 8.5.6`)
- `composer` (validated: `Composer 2.10.0`)
- `git` (validated: `2.50.1`)

### Optional tools (only needed for specific tasks)

- `asciidoctor` + `asciidoctor-reducer` (validated present; required for documentation build)
- `just` (validated: `1.51.0`; alternative UX for docs/CSS)
- `node` (present: `v26.0.0`; used for frontend playgrounds / not validated here)

### Variables

- `ENV_PHP_VENDOR`: Used by `public/serve.php` as a fallback autoloader path if `vendor/autoload.php` is not present.

---

## Modules

### Root project (`skeleton/inane-fw`)

#### Summary

- PHP project using Composer autoloading.
- Contains a console application entry point wired through `public/index.php` and `bin/inane-fw`.
- Tests are aggregated via root `phpunit.xml` across several internal libraries and `source/lib/view`.

#### How to Build

Builds project documentation (regenerates `README.adoc` and `CHANGELOG.adoc` from `source/doc/...`):

`cd /Users/philip/Sites/inane-fw && composer run --no-interaction build`

#### How to Run Tests

Runs the aggregated PHPUnit suite configured in `phpunit.xml`:

`cd /Users/philip/Sites/inane-fw && ./vendor/bin/phpunit -c phpunit.xml`

#### How to Run Single Test

Example using PHPUnit `--filter` (runs the `HtmlBuilderTest` tests from the aggregated suite):

`cd /Users/philip/Sites/inane-fw && ./vendor/bin/phpunit -c phpunit.xml --filter HtmlBuilderTest`

#### Run / Check

- CLI app (shows available commands when run without args):
  `cd /Users/philip/Sites/inane-fw && php bin/inane-fw`

- CLI app via a web entry file (behaves as console app when executed via CLI):
  `cd /Users/philip/Sites/inane-fw && php public/index.php`

- Web app via PHP built-in server:
  `TBD`

- WebSocket server (`public/serve.php`) (long-running):
  `TBD`

#### Notes

- Submodules: internal libraries live under `lib/inanepain/*` (git submodules). Initial setup typically includes:
  - `git submodule update --init --recursive`
  - (optional) track `develop` for submodules (see `README.adoc`)
- Root PHPUnit config (`phpunit.xml`) aggregates tests from:
  - `lib/inanepain/*/tests` (several)
  - `source/lib/view/tests`
- Documentation build uses `asciidoctor-reducer` and `asciidoctor` (installed locally in this environment).

---

## Tools

- Validated versions in this environment:
  - `php -v` → `PHP 8.5.6`
  - `composer --version` → `2.10.0`
  - `just --version` → `1.51.0`
  - `git --version` → `2.50.1`

## Notes

- Commands marked `TBD` were not executed in this session (per validation rules) and should be filled in after selecting/validating a run strategy for long-running services (e.g. PHP built-in server or websocket server).
