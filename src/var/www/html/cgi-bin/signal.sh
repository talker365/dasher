#!/bin/bash

clear

curl -s -c cookie.txt -X POST http://$1/login -d '{username: "guest", password: "guest"}'

while true; do
json=$(curl -s -b cookie.txt -X POST http://$1/API/application -d '{"application":"ApplicationTuner","path":"skylark/Tu
ner","method":"getOnddStatus2","arguments":null}')

echo -e "$json\n"

snr=$(echo $json | awk -F: '{print $10'} | awk -F, '{print $1}')
rssi=$(echo $json | awk -F: '{print $11'} | awk -F, '{print $1}')

#echo -e "\e[1;34m$snr $rssi \e[0m"

sleep 1

read -n 1 -t 1 input
if test "$input" = 'q'; then
break
fi
done
rm -f cookie.txt
