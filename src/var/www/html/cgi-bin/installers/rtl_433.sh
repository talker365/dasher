#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"
DEVICE_NUM="0"
SERIAL_NUM="00000433"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -h, --help            Display this help message"
  echo "  -u, --uninstall       Uninstall rtl_433 and its dependents"
  exit 1
}

# Function to uninstall rtl_433 and its dependents
uninstall_rtl433() {
  echo "Stopping and disabling rtl_433-wx service..."
  sudo systemctl stop rtl_433-wx.service
  sudo systemctl disable rtl_433-wx.service
  sudo rm -rf "/etc/systemd/system/rtl_433-wx.service"
  sudo rm -rf "$INSTALL_DIR/bin/rtl_433"
  sudo rm -rf "$INSTALL_DIR/share/rtl_433"
  echo "rtl_433 and its dependents have been uninstalled."
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
  uninstall_rtl433
  exit 0
fi

# Otherwise, install rtl_433 and its dependents

# Update package list and install dependencies
echo "Updating package list and installing dependencies..."
sudo apt update
sudo apt install -y autoconf cmake build-essential libtool libusb-1.0-0-dev librtlsdr-dev rtl-sdr pkg-config git

# Clone rtl_433 repository
echo "Cloning rtl_433 repository..."
git clone https://github.com/merbanan/rtl_433.git
cd rtl_433 || exit 1

# Compile and install rtl_433 to the specified destination
echo "Compiling and installing rtl_433..."
mkdir build
cd build || exit 1
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install

## Creating the rtl_433 service
#echo "Creating the rtl_433 service..."
#cat << EOF | sudo tee "/etc/systemd/system/rtl_433-wx.service" > /dev/null
#[Unit]
#Description=rtl_433 to /var/log/433/Accurite.json
#After=network.target
#
#[Service]
#ExecStart=/usr/local/bin/rtl_433 -d :"$SERIAL_NUM" -C customaryjson:/var/log/433/Accurite.json
#Restart=always
#RestartSec=5
#
#[Install]
#WantedBy=multi-user.target
#EOF

# Changing the Serial number on the SDR to match SERIAL_NUM
#echo "Changing the Serial number on the SDR to $SERIAL_NUM..."
#echo "y" | rtl_eeprom -d"$DEVICE_NUM" -s"$SERIAL_NUM"
#udevadm control --reload-rules && udevadm trigger

# Enabling and starting the rtl_433 service
echo "Enabling and starting the rtl_433 service..."
sudo systemctl enable rtl_433-wx.service
sudo systemctl start rtl_433-wx.service

# Clean up
cd ../..
echo "Cleaning up..."
rm -rf rtl_433

echo "Installation completed successfully."
