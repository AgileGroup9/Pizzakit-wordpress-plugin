#!/bin/bash

set -e
find -name "*.php" | while read f
do
	php -d error_reporting=32767 -l $f
done