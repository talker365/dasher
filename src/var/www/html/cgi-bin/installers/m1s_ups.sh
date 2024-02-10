#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall m1s_ups and its dependents"
  echo "  -h, --help            Display this help message"
  exit 1
}

# Check for command line options
while [[ "$#" -gt 0 ]]; do
  case $1 in
    -u|--uninstall)
      uninstall=true
      ;;
    -h|--help)
      usage
      ;;
    *)
      echo "Invalid option: $1"
      usage
      ;;
  esac
  shift
done

# Function to check if a command is installed
check_command() {
  if [ ! -x "$(command -v "$1")" ]; then
    echo "$command_to_check does not exist or is not executable"
    if [ "$1" == "wget"       ]; then sudo apt install -y wget; fi
    if [ "$1" == "git"        ]; then sudo apt install -y git; fi
    if [ "$1" == "unzip".     ]; then sudo apt install -y unzip; fi
    if [ "$1" == "bc".        ]; then sudo apt install -y bc; fi    	
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  sudo rm -rf "$INSTALL_DIR"/m1s_ups
  sudo rm -rf /etc/systemd/system/m1s_ups.service
  echo "m1s_ups and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_pypacket
  exit 0
fi

# Otherwise, install m1s_ups and its dependents

# Check if required commands are installed
check_command "unzip"
check_command "git"
check_command "bc"

# Update package list
sudo apt update

# Clone m1s_ups repository
mkdir "$INSTALL_DIR"/m1s_ups
cd  "$INSTALL_DIR"/m1s_ups
wget https://wiki.odroid.com/_media/en/m1s_ups/service.zip
unzip ./service.zip
chmod 777 -R ./service
cd service
chmod a+x ./check_ups.sh
sed -i 's|INSTALL_PATH="/root/m1s_ups"|INSTALL_PATH="/opt/m1s_ups"|g' install_service.sh
sed -i 's|WorkingDirectory=/root/m1s_ups|WorkingDirectory=/opt/m1s_ups|g' m1s_ups.service

./install_service.sh

lsusb | grep 1209:c550
echo "ch55xduino dev node =" `find $(grep -l "PRODUCT=$(printf "%x/%x" "0x1209" "0xc550")" \
                                            /sys/bus/usb/devices/[0-9]*:*/uevent | sed 's,uevent$,,') \
                                            /dev/null -name dev -o -name dev_id  | sed 's,[^/]*$,uevent,' |
                                            xargs sed -n -e s,DEVNAME=,/dev/,p -e s,INTERFACE=,,p`
#Auto Repeat every 1 secons
#echo "@V1#" > /dev/ttyACM0

#Disable Auto Repeat
echo "@V0#" > /dev/ttyACM0

#cat /dev/ttyACM0

#cleanup 
rm -rf /opt/m1s_ups/service.zip  
