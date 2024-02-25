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

# Function to verify callsign format
verify_callsign() {
    local callsign=$(echo "$CALLSIGN" | tr '[:lower:]' '[:upper:]')  # Convert to uppercase
    local pattern="^[A-Z]{1,2}[0-9][A-Z]{1,3}$"
    if [[ $callsign =~ $pattern && ${#callsign} -le 6 ]]; then
        echo "$CALLSIGN is a valid Amateur Radio Callsign."
    else
        echo "$CALLSIGN is not a valid Amateur Radio Callsign."
        exit 1
    fi
}

# Function to check if a command is installed
check_command() {
  if ! command -v "$1" &> /dev/null; then
    echo "$1 is not installed or not executable"
    exit 1
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  sudo rm -rf "$INSTALL_DIR"/pypacket
  sudo rm -rf /etc/systemd/system/pyPacket.service
  sudo rm -rf /etc/profile.d/02-pypacket.sh
  echo "pyPacket and its dependents have been uninstalled."
}


# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_pypacket
  exit 0
fi


# Check if mandatory options are provided
missing_options=()
if [[ -z "$SERIAL_NUMBER" ]]; then
  missing_options+=("-s |--serial")
fi

if [[ -z "$CALLSIGN" ]]; then
  missing_options+=("-c |--callsign")
fi

if [[ -z "$PASSCODE" ]]; then
  missing_options+=("-p |--passcode")
fi

if [[ ${#missing_options[@]} -gt 0 ]]; then
  echo "Error: Mandatory options ${missing_options[@]} are required."
  exit 1
fi


# Otherwise, install pyPacket and its dependents
verify_callsign "$CALLSIGN"


# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "figlet"
check_command "multimon-ng"

# Update package list
sudo apt update

# Clone pyPacket repository
cd "$INSTALL_DIR" || exit 1
echo "Cloning pyPacket repository..."
git clone https://github.com/talker365/pypacket.git
cd pypacket || exit 1

# Edit requirements.txt file and change pytest version
sed -i 's/^pytest==.*/pytest==4.6.9/' requirements.txt
pip3 install -r requirements.txt

# Update configuration with serial number
sed -i 's/"serial": "12345678"/"serial": "'"$SERIAL_NUMBER"'"/' config/configuration.json

# Update rtl_listener.py with serial number
sed -i '12s/.*/config.sample_rate(), "-l", "0", "-d", config.serial(), "-g", config.gain(), "-"],/' implementations/rtl_listener.py

# Create the pyPacket profile file
echo -e '\nCreating the file /etc/profile.d/02-pypacket.sh...'
cat << EOF | sudo tee /etc/profile.d/02-pypacket.sh > /dev/null
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
EOF

# Create the start script
echo -e '\nCreating the file /opt/pypacket/start.sh...'
cat << EOF | sudo tee /opt/pypacket/start.sh > /dev/null
#!/bin/bash
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
cd /opt/pypacket/ || exit 1
python3 main.py
EOF

sudo chmod +x /opt/pypacket/start.sh
sudo chmod +x /opt/pypacket/main.py

# Create the systemd service for pyPacket
echo -e '\nCreating the Systemd service for pyPacket...'
cat << EOF | sudo tee /etc/systemd/system/pyPacket.service > /dev/null
[Unit]
Description=PyPacket - ARPS / IGATE Decoder

[Service]
Type=forking
ExecStart=/usr/bin/screen -d -m -S PyPacket /opt/pypacket/start.sh
ExecStop=/usr/bin/killall -p -w -s 2 start.sh
WorkingDirectory=/opt/pypacket
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
