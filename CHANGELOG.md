# Changelog

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
