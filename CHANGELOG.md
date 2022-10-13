# Changelog

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
