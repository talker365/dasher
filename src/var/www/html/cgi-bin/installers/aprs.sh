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

# Function to check if a command is installed. Install it if not found.
check_command() {
  if ! command -v "$1" &> /dev/null; then
    case "$1" in
      pip3) sudo apt install -y python3-pip ;;
      git) sudo apt install -y git ;;
      figlet) sudo apt install -y figlet ;;
      multimon-ng) sudo apt install -y multimon-ng ;; 
    esac
  fi
}

# Function to verify a directory on adding or removing.
verify() {
    local command="$1"
    local path_file="$2"

    case "$command" in
        "add")
            # Check if path exists
            if [ -e "$path_file" ]; then
                echo "$path_file was successfully added."
            else
                echo "$path_file was not created."
                echo -e "Dasher Status: Failure\n"
                exit 1
            fi
            ;;
        "del")
            # Check if path exists
            if [ -e "$path_file" ]; then
                # Remove the path
                rm -rf "$path_file"

                # Check if removal was successful
                if [ $? -eq 0 ]; then
                    echo "$path_file removed successfully."
                else
                    echo "Failed to remove path $path_file"
                    echo -e "Dasher Status: Failure\n"
                    exit 1
                fi
            else
                echo "$path_file did not exist."
            fi
            ;;
        *)
            echo "Invalid command. Please use 'add', 'del'."
            exit 1
            ;;
    esac
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  verify del "$INSTALL_DIR/pypacket"
#  verify del "$INSTALL_DIR/bin/pypacket"
#  verify del "$INSTALL_DIR/share/pypacket"
  sudo systemctl stop pyPacket.service
  sudo systemctl disable pyPacket.service
  verify del "/etc/systemd/system/pyPacket.service"
  verify del "/etc/profile.d/02-pypacket.sh"

  echo "pyPacket and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_pypacket
  echo -e "\nDasher Status: Success"
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
  echo -e "\nDasher Status: Failure"
  exit 1
fi

########################################[ SCRIPT START ]#####################################

echo "Installing APRS - this process may take several seconds..."
# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "figlet"
check_command "multimon-ng"

# Update package list
echo "Updating APT Packages"
sudo apt update

# Clone pyPacket repository
cd "$INSTALL_DIR" || exit
echo "Cloning pyPacket repository..."
git clone https://github.com/talker365/pypacket.git
cd pypacket || exit
verify add "$INSTALL_DIR/pypacket"

# Edit requirements.txt file and change pytest version
echo "Editing the requirements.txt file to change pytest version."
pip3 install -r requirements.txt
sed -i 's/^pytest==.*/pytest==4.6.9/' requirements.txt

# Update configuration with serial number
echo "Updating the configuration.json file with the provided $SERIAL_NUMBER serial number."
sed -i 's/"serial": "12345678"/"serial": "'"$SERIAL_NUMBER"'"/' config/configuration.json

# Update rtl_listener.py with serial number
echo "Updating rtl_listener.py file."
sed -i '12s/.*/config.sample_rate(), "-l", "0", "-d", config.serial(), "-g", config.gain(), "-"],/' pypacket/implementations/rtl_listener.py

# Create the pyPacket profile file
echo -e '\nCreating the file /etc/profile.d/02-pypacket.sh...'
cat << EOF | sudo tee /etc/profile.d/02-pypacket.sh > /dev/null
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
EOF
verify add "/etc/profile.d/02-pypacket.sh"

# Create the start script
echo -e '\nCreating the file /opt/pypacket/start.sh...'
cat << EOF | sudo tee /opt/pypacket/start.sh > /dev/null
#!/bin/bash
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
cd /opt/pypacket/ || exit
python3 main.py
EOF
verify add "/opt/pypacket/start.sh"

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
verify add "/etc/systemd/system/pyPacket.service"

# Enable and start the pyPacket service
echo "Enabling and starting the pyPacket service..."
sudo systemctl enable pyPacket
sudo systemctl start pyPacket

echo -e "\nDasher Status: Success"
