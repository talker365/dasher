#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"
SERIAL_NUMBER="00000000"
# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -s, --serial          Serial number for installation"
  echo "  -u, --uninstall       Uninstall TPMS and its dependents"
  echo "  -h, --help            Display this help message"
  exit 1
}

# Check for command line options
while [[ "$#" -gt 0 ]]; do
  case $1 in
    -u|--uninstall)
      uninstall=true
      ;;
    -s|--serial)
      shift
      SERIAL_NUMBER="$1"
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

# Function to check if a command is installed. Install it if not found.
check_command() {
  if ! command -v "$1" &> /dev/null; then
    case "$1" in
      pip3) sudo apt install -y python3-pip ;;
      git) sudo apt install -y git ;;
      figlet) sudo apt install -y figlet ;;
	  rtl_433) sudo ./rtl_433.sh ;;
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

# Function to uninstall TPMS and its dependents
uninstall_WEATHER() {
  echo "Stopping and disabling WX service..."
  systemctl stop rtl_433-wx.service
  systemctl disable rtl_433-wx.service
  verify del "/etc/systemd/system/rtl_433-wx.service"
  echo "Removing rtl_433-wx log files..."
  verify del "/var/log/433"
  echo "Uninstall completed."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_WEATHER
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
check_command "git"
check_command "rtl_433"
check_command "multimon-ng"

echo "Installing 433_wx - this process may take several minutes..."

# Update package list
sudo apt update

# Creating the rtl_433 service
echo "Creating the rtl_433 service..."
cat << EOF | sudo tee "/etc/systemd/system/rtl_433-wx.service" > /dev/null
[Unit]
Description=rtl_433 to /var/log/433/Accurite.json
After=network.target

[Service]
ExecStart=$INSTALL_DIR/bin/rtl_433 -d:$SERIAL_NUMBER -C customary -F "json:/var/log/433/Accurite.json"
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

verify add "/etc/systemd/system/rtl_433-wx.service"
mkdir -p /var/log/433
touch /var/log/433/Accurite.json
verify add "/var/log/433"
verify add "/var/log/433/Accurite.json"

echo "Enabling and starting the rtl_433 service..."
sudo systemctl enable rtl_433-wx.service
sudo systemctl start rtl_433-wx.service

echo -e "\nDasher Status: Success"
