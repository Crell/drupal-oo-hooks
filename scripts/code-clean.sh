#!/bin/sh
find . -name "*~" -type f | xargs rm -f
find . -name "DEADJOE" -type f | xargs rm -f
find . -name "*" -type f | grep -v ".psp" | grep -v ".gif" | grep -v ".jpg" | grep -v ".png" | grep -v ".tgz" | xargs perl -wi -pe 's/\s+$/\n/'
find . -name "*" -type f | grep -v ".psp" | grep -v ".gif" | grep -v ".jpg" | grep -v ".png" | grep -v ".tgz" | xargs perl -wi -pe 's/\t/  /g'
