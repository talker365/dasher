#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"
SERIAL_NUMBER="00000433"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall NOAA and its dependents"
  echo "  -h, --help            Display this help message"
  echo "  -s, --serial          Serial number for installation"
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
  local command="$1"
  if ! command -v "$command" &> /dev/null; then
    case "$command" in
      pip3) sudo apt install -y python3-pip ;;
      git) sudo apt install -y git ;;
      figlet) sudo apt install -y figlet ;;
      rtl_433) sudo apt install -y rtl_433 ;;
      "$INSTALL_DIR/bin/rtl_test") ./rtl-sdr.sh || { echo "Failed to run rtl-sdr.sh"; exit 1; } ;;
      multimon-ng) sudo apt install -y multimon-ng;;
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

# Function to uninstall NOAA and its dependents
uninstall_noaa() {
  echo "Stopping and disabling NOAA service..."
  sudo systemctl stop noaa
  sudo systemctl disable noaa

  echo "Removing NOAA installation..."
  verify del "/etc/systemd/system/noaa.service"
  verify del "$INSTALL_DIR/noaa"

  echo "Removing rc.local modifications..."
  sudo sed -i '/# Setting this to allow multiple SDRs to work better/d' /etc/rc.local
  sudo sed -i '/\/sys\/module\/usbcore\/parameters\/usbfs_memory_mb/d' /etc/rc.local

  echo "NOAA and its dependents have been uninstalled."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_noaa
  echo -e "\nDasher Status: Success"
  exit 0
fi

# Check if mandatory options are provided
missing_options=()
if [[ -z "$SERIAL_NUMBER" ]]; then
  missing_options+=("-s |--serial")
fi

if [[ ${#missing_options[@]} -gt 0 ]]; then
  echo "Error: Mandatory options ${missing_options[@]} are required."
  echo -e "\nDasher Status: Failure"
  exit 1
fi

########################################[ SCRIPT START ]#####################################

# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "rtl_433"
check_command "$INSTALL_DIR/bin/rtl_test"
check_command "figlet"
check_command "multimon-ng"

# Update package list
echo "Updating package list..."
sudo apt update

# Clone NOAA repository
echo "Cloning NOAA repository..."
cd "$INSTALL_DIR" || { echo "Failed to change directory to $INSTALL_DIR"; exit 1; }
git clone https://github.com/talker365/dsame noaa || { echo "Failed to clone NOAA repository"; exit 1; }
verify add "$INSTALL_DIR/noaa"

# Create scripts and configuration files
echo "Creating scripts and configuration files..."
cat << EOF > "$INSTALL_DIR/noaa/noaa.sh"
#!/bin/bash
#
# Page        051139
# Shenandoah  051171
# Rockingham  051165
# Warren  051187
# --same 051139 051171 051165 051187
now=$(date)
clear
echo -e "\e[32m"
figlet "NWS - EAS"
echo -e "\e[31m"
echo "Launched: $now"
echo -e "\e[0m"

DEVICE=$SERIAL_NUMBER
PPM=0
FREQ=162.4M
GAIN=49
./dsame.py --source "$INSTALL_DIR"/noaa/source.sh --json /var/log/dsame/messages.json
EOF
verify add "$INSTALL_DIR/noaa/noaa.sh"

cat << 'EOF' > "$INSTALL_DIR/noaa/source.sh"
#!/bin/sh
#SDR Settings for NWS EAS Alerts
echo INPUT: rtl_fm Device >&2
DEVICE=00000439
PPM=0
FREQ=162.400M
GAIN=42
SQL=90

until
  rtl_fm -d ${DEVICE} -M fm -s 22050 -E dc -F 5 -p ${PPM} -g ${GAIN} -l ${SQL} -f 162.400M -f 162.450M |  multimon-ng -t raw -a  EAS /dev/stdin; do
# rtl_fm -d ${DEVICE} -f ${FREQ} -M fm -s 22050 -E dc -p ${PPM} -g ${GAIN}  -|  multimon-ng -t raw -a  EAS /dev/stdin; do
      echo Restarting... >&2
      sleep 2
done

EOF
verify add "$INSTALL_DIR/noaa/source.sh"

# Replace the default Serial # with user selected one
sed -i 's/DEVICE=00000439/DEVICE='"$SERIAL_NUMBER"'/' "$INSTALL_DIR/noaa/source.sh"

# Set executable permission for scripts
chmod +x "$INSTALL_DIR/noaa/noaa.sh" "$INSTALL_DIR/noaa/source.sh" "$INSTALL_DIR/noaa/dsame.py"

# Append to rc.local
echo "Modifying rc.local..."
sudo sed -i -e '$i\# Setting this to allow multiple SDRs to work better' /etc/rc.local
sudo sed -i -e '$i\/sys/module/usbcore/parameters/usbfs_memory_mb' /etc/rc.local

# Create systemd service
echo "Creating NOAA systemd service..."
cat << EOF | sudo tee /etc/systemd/system/noaa.service > /dev/null
[Unit]
Description=NOAA - National Oceanic and Atmospheric Administration

[Service]
Type=simple
ExecStart=/bin/bash $INSTALL_DIR/noaa/noaa.sh
WorkingDirectory=$INSTALL_DIR/noaa
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
verify add /etc/systemd/system/noaa.service

# Enable and start the service
echo "Enabling and starting NOAA service..."
sudo systemctl enable noaa
sudo systemctl start noaa

echo "Installation completed successfully."
echo -e "\nDasher Status: Success"
