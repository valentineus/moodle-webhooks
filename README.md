# Moodle WebHook's
[![GitHub Release](https://img.shields.io/github/release/valentineus/moodle-webhooks.svg)](https://github.com/valentineus/moodle-webhooks/releases)

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

## Documentation

* [Install the plugin](docs/getting-started.md#installation).
* [User guide](docs/getting-started.md#user-guide).
* [Request format](docs/getting-started.md#request-format).

## License

<img height="256px" alt="GNU Banner" src="https://www.gnu.org/graphics/runfreegnu.png" />

[GNU GPLv3](LICENSE.txt).
Copyright (c)
[Valentin Popov](mailto:info@valentineus.link).
