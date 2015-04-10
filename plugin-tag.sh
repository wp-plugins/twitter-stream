#!/bin/bash

if [ $# -lt 1 ]; then
	echo 'usage: plugin-tag 1.2.3'
	exit
fi

TAG_NAME=$1

git tag $TAG_NAME
git push
git push --tags

sh ./plugin-deploy.sh "tagging version $TAG_NAME" tags/$TAG_NAME