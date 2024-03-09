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
  if ! command -v "$1" &> /dev/null; then
    case "$1" in
      pip3) sudo apt install -y python3-pip ;;
      git) sudo apt install -y git ;;
      figlet) sudo apt install -y figlet ;;
      rtl_433) sudo apt install -y rtl_433 ;;
      multimon-ng) sudo apt install -y multimon-ng;;
    esac
  fi
}

# Function to remove a directory and verify its removal
remove_and_verify() {
    local path="$1"

    # Check if path exists
    if [ -e "$path" ]; then
        # Remove the path
        rm -rf "$path"

        # Check if removal was successful
        if [ $? -eq 0 ]; then
            echo "'$path' removed successfully."
        else
            echo "Failed to remove path '$path'."
            echo -e "\nDasher Status: Failure"
            exit 1
        fi
    else
        echo "'$path' does not exist."
        echo -e "\nDasher Status: Failure"
    fi
}

# Function to uninstall NOAA and its dependents
uninstall_noaa() {
  echo "Stopping and disabling NOAA service..."
  sudo systemctl stop noaa
  sudo systemctl disable noaa

  echo "Removing NOAA installation..."
  remove_and_verify /etc/systemd/system/noaa.service
  remove_and_verify "$INSTALL_DIR"/noaa

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


# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "rtl_433"
check_command "figlet"
check_command "multimon-ng"

# Update package list
echo "Updating package list..."
sudo apt update

# Clone NOAA repository
echo "Cloning NOAA repository..."
cd "$INSTALL_DIR"
git clone https://github.com/talker365/dsame noaa

# Create scripts and configuration files
echo "Creating scripts and configuration files..."
cat << 'EOF' > "$INSTALL_DIR/noaa/noaa.sh"
#!/bin/bash

# NOAA script content...
EOF

cat << 'EOF' > "$INSTALL_DIR/noaa/source.sh"
#!/bin/sh

# Source script content...
EOF

chmod +x "$INSTALL_DIR/noaa/noaa.sh" "$INSTALL_DIR/noaa/source.sh"

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

# Enable and start the service
echo "Enabling and starting NOAA service..."
sudo systemctl enable noaa
sudo systemctl start noaa

echo "Installation completed successfully."
  echo -e "\nDasher Status: Success"

