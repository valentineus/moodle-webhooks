#!/bin/sh
# Build an installable local_webhooks package from any working directory.
set -eu

DIRECTORY=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
STAGING=$(mktemp -d)
trap 'rm -rf "$STAGING"' EXIT HUP INT TERM

mkdir -p "$STAGING/webhooks" "$DIRECTORY/build"
# Only runtime files belong in the Moodle installation package.
cp -R "$DIRECTORY/classes" "$DIRECTORY/db" "$DIRECTORY/lang" "$STAGING/webhooks/"
cp "$DIRECTORY"/*.php "$DIRECTORY/LICENSE.txt" "$STAGING/webhooks/"
rm -f "$DIRECTORY/build/local_webhooks.zip"
cd "$STAGING"
zip -q -9 -r "$DIRECTORY/build/local_webhooks.zip" webhooks
