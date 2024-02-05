#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt/"
DEVICE_NUM="0"
SERIAL_NUM="00000433"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -h, --help            Display this help message"
  echo "  -u, --uninstall       Uninstall rtl_433 and its dependents"
  echo "  -d, --device          SDR Device # "
  echo "  -s, --serial.         SDR Serial # "
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
    -d|--device)
      shift
      DEVICE_NUM="$1"
      ;;
    -s|--serial)
      shift
      SERIAL_NUM="$1"
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
  systemctl stop rtl_433-wx.service
  systemctl disable rtl_433-wx.service
  sudo rm -rf "/etc/systemd/system/rtl_433-wx.service"
  sudo rm -rf "$INSTALL_DIR"/bin/rtl_433
  sudo rm -rf "$INSTALL_DIR"/share/rtl_433
  echo -e 'rtl_433 and its dependents have been uninstalled.'
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
sudo amp install -y autoconf cmake build-essential libtool libusb-1.0-0-dev librtlsdr-dev rtl-sdr pkg-config

# Clone rtl_433 repository
git clone https://github.com/merbanan/rtl_433.git
cd rtl_433

# Compile and install rtl_433 to the specified destination
mkdir build
cd build
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install

# Creating the rtl_433 Service...
echo -e '\nCreating a Servic efor the rtl_433...'
cat << EOF > /etc/systemd/system/rtl_433-wx.service
[Unit]
  Description=rtl_433 to /var/log/433/Accurite.json
  After=network.target

[Service]
  ExecStart=/usr/local/bin/rtl_433 -d :"$SERIAL_NUM" -C customaryjson:/var/log/433/Accurite.json
  Restart=always
  RestartSec=5

[Install]
  WantedBy=multi-user.target
EOF

# Changing the Serial # on the SDR to 00000433...
echo -e '\nChanging the Serial# on the SDR for the RTS_433 Service...'

echo "y" | rtl_eeprom -d"$DEVICE_NUM" -s"$SERIAL_NUM"
udevadm control --reload-rules && udevadm trigger

# Enabling and Starting the RTL_433 Service...
echo -e '\nEnabling and Starting the RTS_433 Service...'
systemctl enable rtl_433-wx.service
systemctl start rtl_433-wx.service

# Clean up
cd ../..
rm -rf rtl_433
