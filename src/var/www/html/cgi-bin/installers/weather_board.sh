#!/bin/bash

# Update package list
sudo apt install i2c-tools -y
sudo apt install lm-sensors -y
sudo apt update

modprobe shtc1
echo shtc1 0x70 > /sys/bus/i2c/devices/i2c-0/new_device

#Installation Complete.