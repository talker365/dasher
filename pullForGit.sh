#!/bin/bash
start=$(date +'%s')
echo -e '\nTransfering files...'
rsync -ar --info=progress2 /Volumes/sigmon/var/www/html/ src/var/www/html
echo -e "\n\nFile Transfer Completed! in $(($(date +'%s') - $start)) seconds."