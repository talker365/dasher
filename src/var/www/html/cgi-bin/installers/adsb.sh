#!/bin/bash

# Default installation destination

SERVICE=fr24feed
CHANNEL=stable
SYSTEM=raspberrypi
REPO="repo-feed.flightradar24.com"

FR24_SHARED_KEY="e2179d00eab81a20"
FR24_UAT_SHARED_KEY="3fe8cd46c4f4b6a8"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
#  echo "  -s, --serial          Serial number for installation"
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
 #   -s|--serial)
 #     shift
 #     SERIAL_NUMBER="$1"
 #     ;;
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
      "$INSTALL_DIR/bin/rtl_test") ./rtl-sdr.sh || { echo "Failed to run rtl-sdr.sh"; exit 1; } ;;
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
uninstall_ADSB() {
  echo "Stopping ADSB fr24feed service..."
  systemctl stop fr24feed

  echo "Removing ADSB fr24feed installation..."
  apt purge -y fr24feed
  rm -rf /etc/apt/sources.list.d/fr24feed.list

  echo "Uninstall completed successfully."
   exit 0
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_ADSB
  echo -e "\nDasher Status: Success"
  exit 0
fi

# Check if mandatory options are provided
missing_options=()
#if [[ -z "$SERIAL_NUMBER" ]]; then
#  missing_options+=("-s |--serial")
#fi

if [[ ${#missing_options[@]} -gt 0 ]]; then
  echo "Error: Mandatory options ${missing_options[@]} are required."
  echo -e "\nDasher Status: Failure"
  exit 1
fi

# Function to get system IP address
get_system_ip() {
    # Use hostname command to get the system's IP address
    hostname -I | cut -d ' ' -f1
}

########################################[ SCRIPT START ]#####################################

# Check if required commands are installed
check_command "git"
check_command "rtl_433"
check_command "multimon-ng"

echo "Installing ADSB - this process may take several minutes..."

# Update package list
apt-get update -y
apt-get install dirmngr -y

if [ ! -e "/etc/apt/keyrings" ]; then
    mkdir /etc/apt/keyrings
    chmod 0755 /etc/apt/keyrings
fi

# Import GPG key for the APT repository
# C969F07840C430F5
wget -O- https://repo-feed.flightradar24.com/flightradar24.pub | gpg --dearmor > /etc/apt/keyrings/flightradar24.gpg

# Add APT repository to the config file, removing older entries if exist
mv /etc/apt/sources.list /etc/apt/sources.list.bak
grep -v flightradar24 /etc/apt/sources.list.bak > /etc/apt/sources.list  || true
echo "deb [signed-by=/etc/apt/keyrings/flightradar24.gpg] https://${REPO} flightradar24 ${SYSTEM}-${CHANNEL}" > /etc/apt/sources.list.d/fr24feed.list

apt-get update -y
apt-get install -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y fr24feed

# Stop older instances if exist
systemctl stop fr24feed || true
systemctl stop fr24uat-feed || true

# Get system IP address
SYSTEM_IP=$(get_system_ip)

sudo tee /etc/fr24feed.ini > /dev/null <<EOF
receiver="beast-tcp"
fr24key="$FR24_SHARED_KEY"
host="$SYSTEM_IP:30003"
bs="no"
raw="no"
mlat="no"
mlat-without-gps="no"
EOF

sudo tee /etc/fr24uat-feed.ini > /dev/null <<EOF
receiver="uat-fr24"
fr24key="$FR24_UAT_SHARED_KEY"
uat-port="10978"
EOF




# Start fr24feed service
echo "Starting fr24feed service..."
systemctl start fr24feed

echo "Installation completed successfully."
echo -e "\nDasher Status: Success"
