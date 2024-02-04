#!/bin/bash

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall   Uninstall rtl_433 and its dependents"
  echo "  -h, --help        Display this help message"
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

# Function to uninstall rtl_433 and its dependents
uninstall_rtl433() {
  sudo apt remove -y rtl_433
  sudo apt autoremove -y
  echo "rtl_433 and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_rtl433
  exit 0
fi

# Otherwise, install rtl_433 and its dependents

# Update package list
sudo apt update

# Install dependencies
sudo apt install -y cmake build-essential libusb-1.0-0-dev

# Clone rtl_433 repository
git clone https://github.com/merbanan/rtl_433.git
cd rtl_433

# Compile and install rtl_433
mkdir build
cd build
cmake ../
make
sudo make install

# Clean up
cd ../..
rm -rf rtl_433

echo "rtl_433 and its dependents have been installed successfully."
