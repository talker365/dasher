#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"
USER_NAME="hbc"
PASSWORD="mesh"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -h, --help            Display this help message"
  echo "  -u, --uninstall       Uninstall mqtt and its dependents"
  echo "  -n, --name            mqtt user name"
  echo "  -p, --password        mqtt password"
  exit 1
}

## Function to check if a command is installed
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

# Function to uninstall mqtt and its dependents
uninstall_mqtt() {
  echo "Removing mosquitto log files..."
  rm -rf /var/log/mosquitto
  rm -rf /etc/mosquitto/conf.d/02_tcp_port.conf
  rm -rf /etc/mosquitto/conf.d/03_ws_port.conf
  
  echo "Removing Mosquitto and Mosquitto clients..."
  sudo apt remove --purge -y mosquitto mosquitto-clients
  echo "Mosquitto and Mosquitto clients removed."
  echo "Uninstall completed."
}

# Check for command line options
while [[ "$#" -gt 0 ]]; do
  case $1 in
    -u|--uninstall)
      uninstall_mqtt
      exit 0
      ;;
    -h|--help)
      usage
      ;;
    -n|--name)
      shift
      USER_NAME="$1"
      ;;
    -p|--password)
      shift
      PASSWORD="$1"
      ;;
    *)
      echo "Invalid option: $1"
      usage
      ;;
  esac
  shift
done

# Install dependencies
check_command "software-properties-common"

# Check if the repository is already added
if grep -hrl "^deb.*mosquitto-dev/mosquitto-ppa" /etc/apt/sources.list /etc/apt/sources.list.d/ >/dev/null 2>&1; then
    echo "Repository already exists."
else
    echo "Adding repository..."
    sudo add-apt-repository ppa:mosquitto-dev/mosquitto-ppa
    # Update package list after adding the repository
    sudo apt update
    echo "Repository added successfully."
fi

# Update package list
sudo apt update

apt install mosquitto
apt install mosquitto-clients

# Creating the /etc/mosquitto/conf.d/02_tcp_port.conf file ...
echo -e '\nCreating the mosquitto/conf.d/02_tcp_port.conf file  ...'
cat << 'EOF' > /etc/mosquitto/conf.d/02_tcp_port.conf
listener 1883
EOF

# Creating the mosquitto/conf.d/03_tws_port.conf file ...
echo -e '\nCreating the mosquitto/conf.d/03_ws_port.conf file  ...'
cat << 'EOF' > /etc/mosquitto/conf.d/03_ws_port.conf
listener 8083
protocol websockets
EOF

# Automatically enter password for mosquitto_passwd
echo "$PASSWORD" | mosquitto_passwd -c /etc/mosquitto/passwd "$USER_NAME"

# Confirm that the password has been set correctly
grep "$USER_NAME" /etc/mosquitto/passwd >/dev/null && echo "Password set successfully." || echo "Failed to set password."

# Set proper permissions and ownership on passwd file
chmod 700 /etc/mosquitto/passwd
chown mosquitto:mosquitto /etc/mosquitto/passwd

echo "Installation completed successfully."
