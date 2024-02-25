#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall pyPacket and its dependents"
  echo "  -h, --help            Display this help message"
  echo "  -s, --serial          Serial number for installation"
  echo "  -c, --callsign        Callsign for pyPacket"
  echo "  -p, --passcode        Passcode for pyPacket"
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
    -c|--callsign)
      shift
      CALLSIGN="$1"
      ;;
    -p|--passcode)
      shift
      PASSCODE="$1"
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
    echo "$1 is not installed or not executable"
    exit 1
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  sudo rm -rf "$INSTALL_DIR"/bin/pypacket
  sudo rm -rf "$INSTALL_DIR"/share/pypacket
  sudo rm -rf "$INSTALL_DIR"/pypacket
  sudo rm -rf /etc/systemd/system/pyPacket.service
  sudo rm -rf "$INSTALL_DIR"/etc/profile.d/02-pypacket.sh
  echo "pyPacket and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_pypacket
  exit 0
fi

# Otherwise, install pyPacket and its dependents

# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "rtl_sdr"
check_command "figlet"

# Update package list
sudo apt update

# Install dependencies
sudo apt install -y build-essential cmake libusb-1.0-0-dev

# Clone Multimon-ng repository
cd "$INSTALL_DIR" || exit 1
git clone https://github.com/EliasOenal/multimon-ng.git
cd multimon-ng || exit 1
mkdir build
cd build || exit 1
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install

# Clone pyPacket repository
cd "$INSTALL_DIR" || exit 1
echo "Cloning pyPacket repository..."
git clone https://github.com/talker365/pypacket.git
cd pypacket || exit 1

# Edit requirements.txt file and change pytest version
sed -i 's/^pytest==.*/pytest==4.6.9/' requirements.txt
pip3 install -r requirements.txt

# Update SDR's Serial Number in the config/configuration.json file
sed -i 's/"serial": "12345678"/"serial": "'"$SERIAL_NUMBER"'"/' "$INSTALL_DIR"/pypacket/config/configuration.json

# Update pypacket/implementations/rtl_listener.py
sed -i '12s/.*/config.sample_rate(), "-l", "0", "-d", config.serial(), "-g", config.gain(), "-"],/' implementations/rtl_listener.py

# Create the pyPacket profile file
echo -e '\nCreating the file /etc/profile.d/02-pypacket.sh...'
cat << EOF | sudo tee "$INSTALL_DIR"/etc/profile.d/02-pypacket.sh > /dev/null
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
EOF

# Create the start script
echo -e '\nCreating the file /opt/pypacket/start.sh...'
cat << EOF | sudo tee "$INSTALL_DIR"/pypacket/start.sh > /dev/null
#!/bin/bash
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
cd "$INSTALL_DIR"/pypacket/ || exit 1
python3 main.py
EOF

sudo chmod +x "$INSTALL_DIR"/pypacket/start.sh
sudo chmod +x "$INSTALL_DIR"/pypacket/main.py

# Create the systemd service for pyPacket
echo -e '\nCreating the Systemd service for pyPacket...'
cat << EOF | sudo tee /etc/systemd/system/pyPacket.service > /dev/null
[Unit]
Description=PyPacket - ARPS / IGATE Decoder

[Service]
Type=forking
ExecStart=/usr/bin/screen -d -m -S PyPacket "$INSTALL_DIR"/pypacket/./start.sh
ExecStop=/usr/bin/killall -p -w -s 2 start.sh
WorkingDirectory="$INSTALL_DIR"/pypacket
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# Enable and start the pyPacket service
echo "Enabling and starting the pyPacket service..."
sudo systemctl enable pyPacket
sudo systemctl start pyPacket

echo "APRS / pyPacket and its dependents have been installed successfully."
