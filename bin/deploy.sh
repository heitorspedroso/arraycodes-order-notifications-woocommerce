#!/bin/bash
set -e

VERSION="$1"

if [ -z "$VERSION" ]; then
  echo "Uso: $0 <versão>  (ex: 1.0.1)"
  exit 1
fi

PLUGIN_FILE="arraycodes-order-notifications-woocommerce.php"
README_FILE="readme.txt"

echo "Bumping version to $VERSION..."
sed -i "s/^ \* Version:.*/ * Version: $VERSION/" "$PLUGIN_FILE"
sed -i "s/^Stable tag:.*/Stable tag: $VERSION/" "$README_FILE"

echo "Building JS/CSS..."
npm run build

echo "Verifying distribution build..."
npm run build-market

echo "Committing..."
git add "$PLUGIN_FILE" "$README_FILE" assets/build/
git commit -m "Version $VERSION"
git push

echo "Tagging v$VERSION..."
git tag "v$VERSION"
git push --tags

echo "Done — CI will deploy to SVN and create the GitHub Release."