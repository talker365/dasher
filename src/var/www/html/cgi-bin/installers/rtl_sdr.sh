#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt/"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall RTL-SDR and its dependents"
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

# Function to uninstall RTL-SDR and its dependents
uninstall_rtlsdr() {
  echo "Uninstalling RTL-SDR and its dependents..."
  sudo apt remove -y rtl-sdr
  sudo apt autoremove -y
  sudo rm -f /etc/modprobe.d/blacklist-rtlsdr.conf
  sudo rm -f /etc/udev/rules.d/rtl-sdr.rules
  sudo ldconfig
  sudo rm -rf "$INSTALL_DIR/bin/rtl_*" "$INSTALL_DIR/include/rtl-sdr*" "$INSTALL_DIR/lib/librtlsdr*"
  echo "RTL-SDR and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_rtlsdr
  exit 0
fi

# Function to check if a command is installed
check_command() {
  if ! command -v "$1" &> /dev/null; then
    echo "$1 is not installed. Installing..."
    sudo apt install -y "$1"
    if [ $? -ne 0 ]; then
      echo "Failed to install $1. Exiting."
      exit 1
    fi
  fi
}

# Check if required commands are installed
check_command "git"
check_command "figlet"
check_command "cmake"
check_command "build-essential"
check_command "libusb-1.0-0-dev"

# Otherwise, install RTL-SDR and its dependents

# Update package list
sudo apt update

# Clone RTL-SDR repository
echo "Cloning RTL-SDR repository..."
cd "$INSTALL_DIR" || exit 1
git clone https://github.com/talker365/rtl-sdr.git
cd rtl-sdr || exit 1

# Compile and install RTL-SDR to the specified destination
echo "Compiling and installing RTL-SDR..."
mkdir build
cd build || exit 1
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install
sudo ldconfig

# Install rtl-sdr.rules
echo "Installing rtl-sdr rules file..."
sudo wget -O /etc/udev/rules.d/rtl-sdr.rules https://raw.githubusercontent.com/osmocom/rtl-sdr/master/rtl-sdr.rules
sudo udevadm control --reload-rules

# Create blacklist-rtlsdr.conf
echo "Creating USB module blacklist..."
cat << EOF | sudo tee /etc/modprobe.d/blacklist-rtlsdr.conf > /dev/null
blacklist dvb_core
blacklist dvb_usb_rtl2832u
blacklist dvb_usb_rtl28xxu
blacklist dvb_usb_v2
blacklist r820t
blacklist rtl2830
blacklist rtl2832
blacklist rtl2832_sdr
blacklist rtl2838
install dvb_core /bin/false
install dvb_usb_rtl2832u /bin/false
install dvb_usb_rtl28xxu /bin/false
install dvb_usb_v2 /bin/false
install r820t /bin/false
install rtl2830 /bin/false
install rtl2832 /bin/false
install rtl2832_sdr /bin/false
install rtl2838 /bin/false
EOF

# Unload certain USB modules
echo "Unloading certain USB modules..."
sudo modprobe -r dvb_core dvb_usb_rtl2832u dvb_usb_rtl28xxu dvb_usb_v2 r820t rtl2830 rtl2832 rtl2832_sdr rtl2838
sudo depmod -a

# Update the boot image
echo "Updating the boot image..."
sudo update-initramfs -u

# Clean up
echo "Cleaning up..."
cd ../..
sudo rm -rf rtl-sdr

echo "RTL-SDR library and its dependents have been installed successfully."
