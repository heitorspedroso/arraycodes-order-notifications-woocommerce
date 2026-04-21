#!/bin/sh

PLUGIN_SLUG="checkout-whatsapp-for-woocommerce"
PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/dist"
DEST_PATH="$BUILD_PATH/$PLUGIN_SLUG"

echo "Create folders"
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

#echo "Build JS"
#npm install
#npm run production
echo "Build Composer"
composer install --no-dev || exit "$?"

echo "Syncing files"
rsync -rc "$PROJECT_PATH/" "$DEST_PATH/" --exclude-from="$PROJECT_PATH/.distignore" --delete --delete-excluded

echo "Build Zip"
cd "$BUILD_PATH" || exit
#sed -i "33,46d" $PLUGIN_SLUG/views/html-admin-page.php
#sed -i "5d" $PLUGIN_SLUG/notifications-with-whatsapp.php
#sed -i "17d" $PLUGIN_SLUG/notifications-with-whatsapp.php

zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"
rm -rf "$PLUGIN_SLUG"

echo "Build Success !!!"
