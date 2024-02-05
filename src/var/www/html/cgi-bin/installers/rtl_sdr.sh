#!/bin/bash

start=$(date +'%s')

# Default installation destination
INSTALL_DIR="/opt/"
MODULE_NAME="rtl-sdr"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall "$MODULE_NAME" and its dependents"
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

# Function to uninstall rtl-sdr and its dependents
uninstall_rtlsdr() {
  sudo apt remove -y rtl-sdr
  sudo apt autoremove -y
  sudo rm /etc/modprobe.d/blacklist-rtlsdr.conf
  sudo ldconfig
  sudo rm -rf "$INSTALL_DIR"/bin/rtl_*
  sudo rm -rf "$INSTALL_DIR"/include/rtl-sdr*
  sudo rm -rf "$INSTALL_DIR"/lib/librtlsdr*
  echo "RTL-SDR and its dependents have been uninstalled."
  echo -e "\n\nScript Completed! in $(($(date +'%s') - $start)) seconds."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_rtlsdr
  exit 0
fi

# Otherwise, install rtl-sdr and its dependents

# Update package list
sudo apt update

# Install dependencies
sudo apt install -y git cmake build-essential libusb-1.0-0-dev

# Clone RTL-SDR repository
#git clone https://github.com/osmocom/rtl-sdr.git
git clone https://github.com/talker365/rtl-sdr.git
cd rtl-sdr

# Compile and install rtl_433 to the specified destination
mkdir build
cd build
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install
sudo ldconfig

# Install RTL-SDR utilities
sudo apt install -y rtl-sdr

# Installing the rtl-sdr.rules...
echo -e '\nCreating the rtl-sdr rules file...'
wget https://raw.githubusercontent.com/osmocom/rtl-sdr/master/rtl-sdr.rules -O /etc/udev/rules.d/rtl-sdr.rules
sudo udevadm control --reload-rules

# Creating the SDR Blacklist file...
echo -e '\nCreating the USB module blacklist...'
cat << EOF > /etc/modprobe.d/blacklist-rtlsdr.conf
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

# Unloading certain usb modules...
echo -e '\nUnloading certain usb modules...'
modprobe -r dvb_core
modprobe -r dvb_usb_rtl2832u
modprobe -r dvb_usb_rtl28xxu
modprobe -r dvb_usb_v2
modprobe -r r820t
modprobe -r rtl2830
modprobe -r rtl2832
modprobe -r rtl2832_sdr
modprobe -r rtl2838
depmod -a

# Updating the Boot Img...
echo -e '\nUpdating the Boot Img (This may take a few seconds)...'
update-initramfs -u

# Clean up
cd ../..
rm -rf rtl-sdr

echo $MODULE_NAME "library and its dependents have been installed successfully."
echo -e "\n\nScript Completed! in $(($(date +'%s') - $start)) seconds."
