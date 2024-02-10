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

# Function to check if a command is installed
check_command() {
  if [ ! -x "$(command -v "$1")" ]; then
    echo "$1 does not exist or is not executable"
    case "$1" in
      wget) sudo apt install -y wget ;;
      git) sudo apt install -y git ;;
      unzip) sudo apt install -y unzip ;;
      bc) sudo apt install -y bc ;;
    esac
  fi
}

# Function to uninstall m1s_ups and its dependents
uninstall_m1s_ups() {
  sudo systemctl stop m1s_ups
  sudo systemctl disable m1s_ups
  sudo rm -rf "/etc/systemd/system/m1s_ups.service"
  sudo rm -rf "$INSTALL_DIR/m1s_ups"
  echo "m1s_ups and its dependents have been uninstalled."
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

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_m1s_ups
  exit 0
fi

# Otherwise, install m1s_ups and its dependents

# Check if required commands are installed
check_command "unzip"
check_command "git"
check_command "bc"

# Update package list
echo "Updating package list..."
sudo apt update

# Create installation directory
sudo mkdir -p "$INSTALL_DIR/m1s_ups"

# Clone m1s_ups repository
echo "Cloning m1s_ups repository..."
cd "$INSTALL_DIR/m1s_ups" || exit 1
wget https://wiki.odroid.com/_media/en/m1s_ups/service.zip
unzip -o ./service.zip
chmod -R 777 ./service

# Modify installation scripts
echo "Modifying installation scripts..."
sed -i 's|INSTALL_PATH="/root/m1s_ups"|INSTALL_PATH="/opt/m1s_ups"|g' install_service.sh
sed -i 's|WorkingDirectory=/root/m1s_ups|WorkingDirectory=/opt/m1s_ups|g' m1s_ups.service

# Install m1s_ups service
echo "Installing m1s_ups service..."
sudo ./install_service.sh

# Check m1s_ups installation
echo "Checking m1s_ups installation..."
lsusb | grep 1209:c550
echo "ch55xduino dev node =" $(find $(grep -l "PRODUCT=$(printf "%x/%x" "0x1209" "0xc550")" /sys/bus/usb/devices/[0-9]*:*/uevent | sed 's,uevent$,,') /dev/null -name dev -o -name dev_id  | sed 's,[^/]*$,uevent,' |
                                            xargs sed -n -e s,DEVNAME=,/dev/,p -e s,INTERFACE=,,p

# Disable Auto Repeat
echo "@V0#" > /dev/ttyACM0

# Clean up
echo "Cleaning up..."
rm -rf "$INSTALL_DIR/m1s_ups/service.zip"

echo "Installation completed successfully."
