#!/usr/bin/env bash

exit 0;

if [[ -z "$TRAVIS" ]]; then
	echo "Script is only to be run by Travis CI" 1>&2
	exit 1
fi

if [[ -z "$WP_ORG_USERNAME" ]]; then
    echo "WordPress.org username not set" 1>&2
    exit 1
fi

if [[ -z "$WP_ORG_PASSWORD" ]]; then
    echo "WordPress.org password not set" 1>&2
    exit 1
fi

if [[ -z "$TRAVIS_TAG" ]]; then
    echo "Build must be tag" 1>&2
    exit 0
fi

PLUGIN="woocommerce-gateway-ebanx"
SLUG="ebanx-payment-gateway-for-woocommerce"
TRAVIS_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
PROJECT_ROOT="$TRAVIS_ROOT/$PLUGIN"
PLUGIN_BUILDS_PATH="$PROJECT_ROOT/build"
SVN_ROOT_PATH="$PLUGIN_BUILDS_PATH/svn"
VERSION=$TRAVIS_TAG
ZIP_FILE="$PROJECT_ROOT/$SLUG.zip"

# Ensure the zip file for the current version has been built
if [ -f "$ZIP_FILE" ]; then
    echo "Found zip file $ZIP_FILE ... discarding."
    rm -f $ZIP_FILE
fi

# Clean up any previous svn dir
rm -fR $SVN_ROOT_PATH

echo "Performing Checkout from http://svn.wp-plugins.org/$SLUG"
# Checkout the SVN repo
svn co -q "http://svn.wp-plugins.org/$SLUG" $SVN_ROOT_PATH

# Erase trunk
rm -fR "$SVN_ROOT_PATH/trunk"
# Create trunk directory
mkdir $SVN_ROOT_PATH/trunk

echo "Synchronizing trunk"
# Copy our new version of the plugin into trunk
rsync -r -p --exclude "build" $PROJECT_ROOT/* $SVN_ROOT_PATH/trunk

# Erase possible broken version
rm -fR "$SVN_ROOT_PATH/tags/$VERSION"
# Add new version tag
mkdir $SVN_ROOT_PATH/tags/$VERSION

echo "Synchronizing tag $VERSION"
# Copy our new version of the plugin into tag directory
rsync -r -p --exclude "build" $PROJECT_ROOT/* $SVN_ROOT_PATH/tags/$VERSION

echo "Generating diff"
# Add new files to SVN
svn stat $SVN_ROOT_PATH | grep '^?' | awk '{print $2}' | xargs -I x svn add x@
# Remove deleted files from SVN
svn stat $SVN_ROOT_PATH | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@

echo "Uploading to SVN"
# Commit to SVN
svn ci --no-auth-cache --username $WP_ORG_USERNAME --password $WP_ORG_PASSWORD $SVN_ROOT_PATH -m "Deploy version $VERSION"

echo "Cleaning temp dirs"
# Remove SVN temp dir
rm -fR $SVN_ROOT_PATH
