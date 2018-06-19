#!/bin/sh
# Author:       Valentin Popov
# Email:        info@valentineus.link
# Date:         2018-06-19
# Usage:        /bin/sh ./build.sh
# Description:  Build the final package for installation in Moodle.

# Updating the Environment
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export PATH="$PATH:/usr/local/scripts"

# Current project
PROJECT="local_webhooks"

# Defining directories
DIRECTORY="$(pwd)"
NAMEDIR="$(basename $DIRECTORY)"
TMPDIR="$(mktemp --directory)"

# Creating a Temporary Directory
cp --recursive --verbose "../$NAMEDIR" "$TMPDIR/$PROJECT"
mkdir --parents --verbose "$DIRECTORY/build"
cd "$TMPDIR"

# Creating an archive
zip -9 -r "$DIRECTORY/build/$PROJECT.zip" "$PROJECT" \
    -x "$PROJECT/.git*" \
    -x "$PROJECT/.travis.yml" \
    -x "$PROJECT/build*"

# End of work
exit 0