# Changelog

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
