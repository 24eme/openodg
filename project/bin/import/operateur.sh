#!/bin/bash

. bin/config.inc

cat data/import/extravitis/operateur/EVV.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 1,1 > /tmp/EVV.utf8.csv




