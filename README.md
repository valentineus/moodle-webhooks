# Moodle WebHook's
[![GitHub Release](https://img.shields.io/github/release/valentineus/moodle-webhooks.svg)](https://github.com/valentineus/moodle-webhooks/releases)
[![Tests and installation package](https://github.com/valentineus/moodle-webhooks/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/valentineus/moodle-webhooks/actions/workflows/ci.yml)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/376ab669f2d3436884015554f686a915)](https://www.codacy.com/app/valentineus/moodle-webhooks)
[![Gitter Badge](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/valentineus/moodle-webhooks)

Adding Webhooks to Moodle.
The Webhooks feature allows applications to receive real-time notification of changes to certain data.

Using Webhooks, your application will be able to receive notifications of changes to selected topics and their fields.
Because of this, you do not have to rely on continuous or even periodic requests to check for updates.
Notifications about Webhooks updates are sent as POST requests to the callback URL you specified.
Notifications can indicate the very fact of a field change or include a new value.

Features:

* Use any number of services for notification;
* Customizing each external service;
* Interception of all events in the Moodle system;
* Use a secret phrase to authenticate requests;
* [JSON](https://en.wikipedia.org/wiki/JSON) - Format of outgoing data;

## Installation

Get the installation package in any of the available methods:

* [GitHub Releases](https://github.com/valentineus/moodle-webhooks/releases).
* [Compilation from the source code](#build).
* Tested development packages from [GitHub Actions](https://github.com/valentineus/moodle-webhooks/actions/workflows/ci.yml): open a successful run and download the `local_webhooks-<commit SHA>` artifact. Extract the artifact download, then upload the contained `local_webhooks.zip` in Moodle’s plugin installer.

## Build

Self-assembly package is as follows:

* Clone the repository:
```bash
git clone https://github.com/valentineus/moodle-webhooks.git moodle-webhooks
```

* Run the build script:
```bash
cd ./moodle-webhooks
/bin/sh build.sh
```

The build requires `zip` and produces `build/local_webhooks.zip`. The package
contains a single `webhooks/` directory, suitable for installation under
`local/webhooks`. Tests, CI configuration and build output are excluded.

## Continuous integration

GitHub Actions runs on pushes to `master`, pull requests targeting `master`, and
manual dispatch. The matrix tests Moodle 4.5 LTS on PHP 8.1 and 8.3 with both
MySQL 8.0 (`mysqli`) and PostgreSQL 15 (`pgsql`). This is the tested CI matrix;
it does not change the minimum Moodle version declared in `version.php`.

[Moodle Plugin CI](https://github.com/moodlehq/moodle-plugin-ci) installs Moodle
and the plugin, checks PHP syntax, validates plugin metadata and database
savepoints, and runs the plugin’s PHPUnit integration tests. These cover service
creation/update/deletion, pagination, subscriptions, backup/restore and webhook
dispatch filtering. HTTP responses are mocked; the tests do not call external
webhook endpoints.

The package job runs only after **all** matrix jobs pass. It builds and verifies
the installation ZIP and retains the downloadable artifact for 30 days. This is
a test package for the checked-out commit, not a published GitHub release.

## License

<img height="256px" alt="GNU Banner" src="https://www.gnu.org/graphics/runfreegnu.png" />

[GNU GPLv3](LICENSE.txt).
Copyright (c)
[Valentin Popov](mailto:info@valentineus.link).