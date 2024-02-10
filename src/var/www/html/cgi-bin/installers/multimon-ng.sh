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

# Function to check if a command is installed
check_command() {
  if ! command -v "$1" &> /dev/null; then
    if [ "$1" == "git" ]; then
      echo "Git is not installed. Installing..."
      sudo apt install -y git
    fi
  fi
}

# Function to uninstall multimon-ng and its dependents
uninstall_multimon_ng() {
  echo "Uninstalling multimon-ng and its dependents..."
  sudo rm -rf "$INSTALL_DIR"/multimon-ng
  echo "multimon-ng and its dependents have been uninstalled."
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
  uninstall_multimon_ng
  exit 0
fi

# Otherwise, install multimon-ng and its dependents

# Check if required commands are installed
check_command "git"

# Update package list
echo "Updating package list..."
sudo apt update

# Clone Multimon-ng repository
echo "Cloning Multimon-ng repository..."
cd "$INSTALL_DIR"
git clone https://github.com/talker365/multimon-ng.git

# Build and install Multimon-ng
echo "Building and installing Multimon-ng..."
cd multimon-ng
mkdir build
cd build
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install

echo "multimon-ng and its dependents have been installed successfully."
