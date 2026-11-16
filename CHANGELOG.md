# Changelog

## 2.5.0 - 2026-11-16

### What's Changed

* Support RoadRunner 3 and temporal/sdk 2.20 by @agoalofalife in https://github.com/keepsuit/laravel-temporal/pull/80
* Drop Laravel 11 support and re-enable composer security advisories by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/82
* Register schedule and operator clients, stop reporting activity interruptions by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/83

#### Breaking changes

- **Laravel 11 is no longer supported.** The package requires Laravel 12 or 13 and Carbon 3. Composer keeps Laravel 11 apps on 2.4.x.
- **RoadRunner 3 is required.** The package now depends on `temporal/sdk ~2.20.0`, `roadrunner/roadrunner ^3.0` and `roadrunner/cli ^3.0`. `temporal:install` and the automatic download install RoadRunner `3.0.x`. If your project has a RoadRunner `2025.1.x` binary, replace it.
- **Read-only workflow callbacks (temporal/sdk 2.20).** A query handler, an update validator, an await condition or a `Workflow::sideEffect()` callback can no longer send commands (start an activity or a timer, signal a workflow, and similar). Such a call now throws `IllegalStateException`: "Workflow calls that send commands are not allowed inside read-only callbacks." These commands broke replay. To keep the previous behaviour while you move the calls, set `\Temporal\Worker\FeatureFlags::$readOnlyWorkflowCallbacks = false;` in a service provider.

#### Added

- `ScheduleClientInterface` and `OperatorClientInterface` are registered in the container. They use the same `temporal.address` and `temporal.tls` config as the workflow client.

#### Changed

- Activities stopped through a heartbeat (canceled, paused or reset) are no longer reported to Laravel's exception handler. The exceptions are still rethrown, so Temporal behaviour is unchanged. If you relied on these errors in your error tracker, they will stop appearing.

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.4.0...2.5.0

## v2.4.0 - 2026-11-16

### What's Changed

* Support temporal/sdk 2.18 and 2.19 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/81

#### Fixes

- `rr get-binary` now downloads RoadRunner 3 by default, which this release does not support. Both `temporal:install` and the automatic download therefore install the latest RoadRunner `2025.1.x`. If you download the binary manually, use `./vendor/bin/rr get-binary --filter='^2025.1'`.
- RoadRunner `3.x` binaries get config version `3` instead of `2.7`.
- `temporal:work --watch` reloads the workers again. Before, `rr reset` failed because the config version was missing.
- `temporal:work` also shows worker output that is logged with an uppercase `INFO` level.

#### Upgrade notes

temporal/sdk 2.18 and 2.19 report no breaking changes, but they change some behaviour:

- **2.18:** a method-level `#[MethodRetry]` is now merged with the options passed to the call, and the call options win (temporalio/sdk-php#783).
- **2.18:** when no update ID is given, the client generates one (UUID v4) (temporalio/sdk-php#773).
- **2.18 and 2.19:** they add two feature flags, both disabled by default: `FeatureFlags::$propagateCancellationToNewScopes` (2.18) and `FeatureFlags::$settleAwaitOnFirstSettledCondition` (2.19). This package leaves them disabled.

This release is tested with RoadRunner `2025.1.x` only. If your project already has a RoadRunner `3.x` binary, replace it with `2025.1.x`. Support for RoadRunner 3 and temporal/sdk 2.20 will come in the next release.

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.3.2...2.4.0

## v2.3.2 - 2026-08-07

### What's Changed

* fix: workflows crash when spatie data is not installed by @andrewbroberg in https://github.com/keepsuit/laravel-temporal/pull/79

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.3.1...2.3.2

## v2.3.1 - 2026-07-20

### What's Changed

* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/75
* Bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/77
* feat: graceful worker shutdown on SIGINT/SIGTERM by @kyle-leasecake in https://github.com/keepsuit/laravel-temporal/pull/78

### New Contributors

* @kyle-leasecake made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/78

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.3.0...2.3.1

## v2.3.0 - 2026-03-06

### What's Changed

* Laravel 13.x Compatibility by @laravel-shift in https://github.com/keepsuit/laravel-temporal/pull/73
* support temporal sdk 2.17 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/74

### New Contributors

* @laravel-shift made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/73

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.2.1...2.3.0

## 2.2.1 - 2025-12-21

### What's Changed

* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/70
* time testing utilities by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/72

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.2.0...2.2.1

## v2.2.0 - 2025-10-26

### What's Changed

* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/67
* Bump temporal sdk to 2.16 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/69

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.1.3...2.2.0

## 2.1.3 - 2025-09-30

### What's Changed

* Fix typo in  README.md by @agoalofalife in https://github.com/keepsuit/laravel-temporal/pull/64
* fix: unable to use php binary with space in the path TemporalTestingWorker by @andrewbroberg in https://github.com/keepsuit/laravel-temporal/pull/65

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.1.2...2.1.3

## v2.1.2 - 2025-08-20

### What's Changed

* Logging fix by @challapradyumna in https://github.com/keepsuit/laravel-temporal/pull/60

### New Contributors

* @challapradyumna made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/60

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.1.1...2.1.2

## v2.1.1 - 2025-08-19

### What's Changed

* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/63
* fix: unable to use php binary with space in the path by @andrewbroberg in https://github.com/keepsuit/laravel-temporal/pull/62

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.1.0...2.1.1

## v2.1.0 - 2025-07-28

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/keepsuit/laravel-temporal/pull/58
* feat: add buildRunning method for already running workflows by @andrewbroberg in https://github.com/keepsuit/laravel-temporal/pull/57

### New Contributors

* @andrewbroberg made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/57

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/2.0.0...2.1.0

## v2.0.0 - 2025-07-19

### What's Changed

- Drop support for php 8.1, laravel 10, temporal/sdk < 2.15
- Drop registration of workflows/activities from config
- Discover workflows/activities from whole `app` directory
- Pass laravel logger to temporal worker
- Enabled `workflowDeferredHandlerStart` feature flag (this enforce correct behaviour of `signal with start` [see release](https://github.com/temporalio/sdk-php/releases/tag/v2.11.0)

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.2.1...2.0.0

## v1.2.1 - 2025-06-18

### What's Changed

* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot in https://github.com/keepsuit/laravel-temporal/pull/54
* Update temporal.php config after update version by @agoalofalife in https://github.com/keepsuit/laravel-temporal/pull/55

### New Contributors

* @agoalofalife made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/55

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.2.0...1.2.1

## v1.2.0 - 2025-06-15

### What's Changed

* Updated eloquent model serialization by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/50
* dropped support for `temporal/sdk:2.7` and added test for each temporal version by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/52
* added support for `temporal/sdk:2.14` by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/51

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.1.1...1.2.0

## v1.1.1 - 2025-04-09

### What's changed

- Fix requirement of `spatie/laravel-data`
- Fix interface signature changed
- Fix ci tests

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.1.0...1.1.1

## v1.1.0 - 2025-02-23

### What's changed

- Allow `temporal/sdk` 2.13
- Support `laravel` 12

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.0.3...1.1.0

## v.1.0.3 - 2025-02-21

### What's Changed

* Support `thecodingmachine/safe` 3.0
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/keepsuit/laravel-temporal/pull/45

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.0.2...1.0.3

## v1.0.2 - 2025-01-18

### What's Changed

* Phpstan 2 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/44

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.0.1...1.0.2

## v1.0.1 - 2025-01-18

### What's changed

- Allow `temporal/sdk` `2.12`

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.0.0...1.0.1

## 1.0.0-beta2 - 2024-03-15

### What's Changed

* Bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/keepsuit/laravel-temporal/pull/34
* Drop laravel data v3 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/36
* Added support for `temporal/sdk` `v2.8`

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/1.0.0-beta1...1.0.0-beta2

## v0.6.14 - 2024-01-03

### What's Changed

* Fixed builders phpdoc

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.13...0.6.14

## v0.6.13 - 2024-01-02

### What's Changed

* Conflict with `temporal/sdk:2.7`
* Added `temporal:install` command
* Ignored communication exceptions in worker (thrown when the worker is killed)
* Fixed child workflow result when not mocked in testing environment
* Improved tests
* Bump aglipanci/laravel-pint-action from 2.3.0 to 2.3.1 by @dependabot in https://github.com/keepsuit/laravel-temporal/pull/30

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.12...0.6.13

## v0.6.12 - 2023-12-15

### What's Changed

* Support for namespaces config when starting child workflows and activities. by @slnw in https://github.com/keepsuit/laravel-temporal/pull/28

### New Contributors

* @slnw made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/28

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.11...0.6.12

## v0.6.11 - 2023-11-17

### What's Changed

- Support project using type "module"

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.10...0.6.11

## v0.6.10 - 2023-10-05

### What's changed

- Catch all instantiation errors in `LaravalPayloadConverter` and fallback to `JsonConverter`

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.9...0.6.10

## v0.6.9 - 2023-09-25

### What's Changed

- Fix commands in ReadMe 📚 by @michael-rubel in https://github.com/keepsuit/laravel-temporal/pull/15
- Remove issue template config by @michael-rubel in https://github.com/keepsuit/laravel-temporal/pull/17
- Temporal root namespace by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/18

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.8...0.6.9

## v0.6.8 - 2023-09-19

### What's changed

- Fixed required wrong temporal sdk version

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.7...0.6.8

## v0.6.7 - 2023-09-19

### What's Changed

- Fix testing with `temporal/sdk:2.6.0`

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.6...0.6.7

## v0.6.6 - 2023-04-28

### What's changed

- Set correct roadrunner config version when using v3

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.5...0.6.6

## v0.6.5 - 2023-04-27

### What's changed

- Support roadrunner v3

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.4...0.6.5

## v0.6.4 - 2023-04-12

### What's Changed

- Ensure roadrunner binary is executable
- Improved error messages for testing processes fail

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.3...0.6.4

## v0.6.3 - 2023-04-05

### What's Changed

- Add more verbose message for the user if worker crashes https://github.com/keepsuit/laravel-temporal/pull/12

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.6.2...0.6.3

## v0.6.2 - 2023-03-24

### What's Changed

- Improve testing speed using only local cache when testing environment is not configured

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/v0.6.1...0.6.2

## v0.6.1 - 2023-03-21

### What's Changed

- Add interface for `Temporal` class 🔧 (https://github.com/keepsuit/laravel-temporal/pull/10)

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/v0.6.0...v0.6.1

## v0.6.0 - 2023-03-15

### What's Changed

- Improved phpstan types (https://github.com/keepsuit/laravel-temporal/pull/6)
- Added phpstan extension to resolve temporal proxy classes methods and return types
- Improved test worker handling
- Update trait name in ReadMe 📚  (https://github.com/keepsuit/laravel-temporal/pull/8)

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.5.3...v0.6.0

## v0.5.3 - 2023-01-25

### What's Changed

- Fix installation step in ReadMe 📝 by @michael-rubel in https://github.com/keepsuit/laravel-temporal/pull/4
- Support laravel 10 by @cappuc in https://github.com/keepsuit/laravel-temporal/pull/5

### New Contributors

- @michael-rubel made their first contribution in https://github.com/keepsuit/laravel-temporal/pull/4

**Full Changelog**: https://github.com/keepsuit/laravel-temporal/compare/0.5.2...0.5.3

## v0.5.2 - 2023-01-05

- Allow to pass only workflow/activity name for mock

## v0.5.1 - 2022-12-06

- Improved eloquent integration with dirty tracking (globally not attribute specific)

## v0.5.0 - 2022-11-29

- Discover activities and workflows classes (without interface)
- Refactor make commands to generate activities and workflow classes without interface (by default) or only the interface (useful for remote execution)

## v0.4.2 - 2022-10-20

- Support serialization of Enums

## v0.4.1 - 2022-10-13

- Allow to mock workflows without a running temporal server and worker
- Allow to mock local activities

## v0.4

- Added support for `Eloquent` models serialization/deserialization
- Updated configuration file with eloquent serialization options

## v0.3

- Added config option for changing the temporal namespace
- Added config options to allow customization of default retry options for workflows and activities
- Added `--scoped` option to `make` commands to allow generating Workflow/Activity inside a scoped namespace
- Added `--for-workflow` option to `make:activity` command to allow generating Activity inside a Workflow namespace
- Improved app testing performance and added experimental support for parallel testing

## v0.2

- Added testing helpers: activity/workflows mocks, dispatches assertions, automatic test server and worker

## v0.1

- Initial release
