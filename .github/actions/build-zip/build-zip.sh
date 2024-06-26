#!/bin/bash

# Define the plugin slug and the zip file name
set -eo

# Allow some ENV variables to be customized
if [[ -z "$SLUG" ]]; then
	SLUG=${GITHUB_REPOSITORY#*/}
fi
echo "ℹ︎ SLUG is $SLUG"

PLUGIN_SLUG=$SLUG
ZIP_FILE="${PLUGIN_SLUG}.zip"

# Remove any existing zip file
rm -f $ZIP_FILE

# Create a zip archive of the plugin directory
zip -r $ZIP_FILE . -x "*.git*" -x "*.github*" -x "*tests*" -x "*.DS_Store" -x "*.gitignore" -x "*.gitattributes" -x "build-zip.sh"

echo "Zip archive ${ZIP_FILE} created successfully."