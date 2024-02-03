#!/bin/bash
start=$(date +'%s')
echo -e '\nTransfering files...'
rsync -ar --info=progress2 src/var/www/html/ /Volumes/sigmon/var/www/html 
echo -e "\n\nFile Transfer Completed! in $(($(date +'%s') - $start)) seconds."