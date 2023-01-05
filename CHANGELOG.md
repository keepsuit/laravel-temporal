# Changelog

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
