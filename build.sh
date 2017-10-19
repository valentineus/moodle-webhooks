#!/bin/sh
# Author:       Valentin Popov
# Email:        info@valentineus.link
# Date:         2017-10-19
# Usage:        /bin/sh build.sh
# Description:  Build the final package for installation in Moodle.

# Updating the Environment
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export PATH="$PATH:/usr/local/scripts"

# Build the package
cd ..
mv "./moodle-webhooks" "./local_webhooks"
zip -9 -r "local_webhooks.zip" "local_webhooks"  \
        -x "local_webhooks/.git*"       \
        -x "local_webhooks/.travis.yml" \
        -x "local_webhooks/build.sh"

# End of work
exit 0