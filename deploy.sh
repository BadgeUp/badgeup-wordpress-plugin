#!/bin/bash -e

# Syncs latest git tag to SVN (indeded to be run in CI on every git commit) and sets the stable version to the new tag
# This is a heavily modified version of https://gist.githubusercontent.com/kloon/6487562/raw/f4d2d5b07cefc8bf1ca829922fd7d13c0c03b35d/deploy.sh

# main config
PLUGINSLUG="badgeup"
CURRENTDIR=`pwd`
MAINFILE="badgeup.php" # this should be the name of your main php file in the wordpress plugin

if [ -z "$SVN_USER" ]; then
	echo 'SVN_USER variable must be provided'
	exit 1
fi

if [ -z "$SVN_PASSWORD" ]; then
	echo 'SVN_PASSWORD variable must be provided'
	exit 1
fi

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="https://plugins.svn.wordpress.org/$PLUGINSLUG"

echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

echo "Ignoring git files and deployment script"
svn propset svn:ignore "deploy.sh
README.md
.git
.gitignore" "$SVNPATH/trunk/"

# get the latest tag on master
LATEST_TAG=$(git describe master --tags --abbrev=0)

# Write the stable version to readme.txt
sed -i '' "s/{{STABLE_TAG}}/$LATEST_TAG/g" "./$PLUGINSLUG/readme.txt"

# Write the current version to badgeup.php
sed -i '' "s/{{CURRENT_VERSION}}/$LATEST_TAG/g" "./$PLUGINSLUG/$MAINFILE"

echo "Copying from git repo to SVN"
cp -rf ./badgeup/ $SVNPATH/trunk/

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn commit --username="$SVN_USER" --password="$SVN_PASSWORD" -m "release $LATEST_TAG"

if [ ! -d "$SVNPATH/tags/$LATEST_TAG" ]; then
	echo "Creating new SVN tag & committing it"
	cd $SVNPATH
	svn copy trunk/ tags/$LATEST_TAG/
	cd $SVNPATH/tags/$LATEST_TAG
	svn commit --username="$SVN_USER" --password="$SVN_PASSWORD" -m "tagging version $LATEST_TAG"
fi

echo "Removing $SVNPATH..."
rm -rf $SVNPATH/

echo "DONE"
