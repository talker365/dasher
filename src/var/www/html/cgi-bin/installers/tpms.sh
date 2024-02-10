#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"
DEVICE_NUMBER="0"

# Function to display usage information
usage() {
  echo "Usage: $0 [OPTIONS]"
  echo "  -u, --uninstall       Uninstall TPMS and its dependents"
  echo "  -h, --help            Display this help message"
  echo "  -d, --device          device number for installation"
  exit 1
}

# Function to check if a command is installed
check_command() {
  if ! command -v "$1" &> /dev/null; then
    if [ "$1" == "git" ]; then
      sudo apt install -y git || { echo "Failed to install git"; exit 1; }
    elif [ "$1" == "rtl_433" ]; then
      ./rtl_433.sh || { echo "Failed to run rtl_433.sh"; exit 1; }
    fi
  fi
}

# Function to uninstall TPMS and its dependents
uninstall_TPMS() {
  echo "Stopping and disabling TPMS service..."
  systemctl stop tpms
  systemctl disable tpms

  echo "Removing TPMS log files..."
  rm -rf /var/log/tpms

  echo "Removing TPMS installation directory..."
  rm -rf "$INSTALL_DIR"/tpms

  echo "Removing TPMS crontab entry..."
  (crontab -l | grep -v "$INSTALL_DIR/tpms/trimlog_tpms.sh" ) | crontab -

  echo "Uninstall completed."
}

# Check for command line options
while [[ "$#" -gt 0 ]]; do
  case $1 in
    -u|--uninstall)
      uninstall_TPMS
      exit 0
      ;;
    -h|--help)
      usage
      ;;
    -d|--device)
      shift
      DEVICE_NUMBER="$1"
      ;;
    *)
      echo "Invalid option: $1"
      usage
      ;;
  esac
  shift
done

# Install dependencies
check_command "git"
check_command "rtl_433"
check_command "multimon-ng"

# Update package list
sudo apt update

# Create the TPMS directory if it doesn't exist
mkdir -p "$INSTALL_DIR"/tpms
if [ ! -d "$INSTALL_DIR"/tpms ]; then
  echo "Failed to create TPMS directory: $INSTALL_DIR/tpms"
  exit 1
fi

# Navigate to the TPMS directory
cd "$INSTALL_DIR"/tpms || exit 1

# Creating the Startup script...
echo -e '\nCreating the Startup Script  ...'
cat << 'EOF' > start_tpms.sh
#!/bin/bash
cd /opt/tpms/
rtl_433 -f314.9M -d:00000002 -F json:/var/log/tpms/tpms.json
EOF

# Creating the Log Trimming script...
echo -e '\nCreating the Log Trimming Script  ...'
cat << 'EOF' > trimlog_tpms.sh
#!/bin/bash
cd /opt/tpms/

limit=100
size=$(wc -l < /var/log/tpms/tpms.json) # changed to -l for # of Lines.

if [ "$size" -gt "$limit" ]; then
    echo "The Size of the file is $size"
    echo "File is larger than the $limit, trimming log file."
    tail -n "$limit" /var/log/tpms/tpms.json > /var/log/tpms/tpms2.json
    mv /var/log/tpms/tpms2.json /var/log/tpms/tpms.json
else
    echo "File is smaller than the $limit size."
fi
EOF

# Make the scripts executable
chmod +x start_tpms.sh trimlog_tpms.sh

# Add trimming script to crontab
echo "Adding log trimming script to crontab..."
line="00 00 * * * $INSTALL_DIR/tpms/trimlog_tpms.sh"
(crontab -l 2>/dev/null; echo "$line") | crontab -

# Enable and start the service
echo "Enabling and starting TPMS service..."
systemctl enable tpms
systemctl start tpms

echo "Installation completed successfully."
