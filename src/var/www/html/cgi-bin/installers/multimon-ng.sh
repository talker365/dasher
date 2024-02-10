#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall multimon-ng and its dependents"
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
    -s|--serial)
      shift
      SERIAL_NUMBER="$1"
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
  if ! command -v "$1" &> /dev/null; then
    if [ "$1" == "git"     ]; then sudo apt install -y git; fi
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_multimon-ng() {
  sudo rm -rf "$INSTALL_DIR"/multimon-ng
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_multimon-ng
  exit 0
fi

# Otherwise, install pyPacket and its dependents

# Check if required commands are installed
check_command "git"

# Update package list
sudo apt update

# Clone Multimon-ng repository
cd "$INSTALL_DIR"
git clone https://github.com/talker365/multimon-ng.git
cd multimon-ng
mkdir build
cd build
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
make install

echo -e ' multimon-ng and its dependents have been installed successfully.'


