#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"

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
uninstall_TPMS() {
  echo "Stopping and disabling TPMS service..."
  systemctl stop tpms
  systemctl disable tpms
  verify del "/etc/systemd/system/tpms.service"
  echo "Removing TPMS log files..."
  verify del "/var/log/tpms"
  echo "Removing TPMS installation directory..."
  verify del "$INSTALL_DIR/tpms"
  echo "Removing TPMS crontab entry..."
  (crontab -l | grep -v "$INSTALL_DIR/tpms/trimlog_tpms.sh" ) | crontab -
  echo "Uninstall completed."
}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_TPMS
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

echo "Installing TPMS - this process may take several minutes..."

# Update package list
sudo apt update

# Create the TPMS directory if it doesn't exist
mkdir -p "$INSTALL_DIR"/tpms
verify add "$INSTALL_DIR/tpms"
mkdir -p /var/log/tpms
verify add /var/log/tpms

# Navigate to the TPMS directory
cd "$INSTALL_DIR"/tpms || exit 1

# Creating the Startup script...
echo -e '\nCreating the Startup Script  ...'
cat << EOF > start_tpms.sh
#!/bin/bash
cd /opt/tpms/
rtl_433 -f 314.9M -d:$SERIAL_NUMBER -F json:/var/log/tpms/tpms.json
EOF
verify add "$INSTALL_DIR/tpms/start_tpms.sh"

# Creating the Log Trimming script...
cat << 'EOF' > trimlog_tpms.sh
#!/bin/bash

# Change directory to /opt/tpms/
cd /opt/tpms/

# Set the limit for the number of lines
limit=100

# Check if the log file exists
if [ -f /var/log/tpms/tpms.json ]; then
    # Get the number of lines in the log file
    size=$(wc -l < /var/log/tpms/tpms.json)
    
    # Check if the number of lines exceeds the limit
    if [ "$size" -gt "$limit" ]; then
        echo "The size of the file is $size lines."
        echo "The file is larger than the limit of $limit lines, trimming log file."
        # Trim the log file to the last 100 lines and overwrite the original file
        tail -n "$limit" /var/log/tpms/tpms.json > /var/log/tpms/tpms2.json && mv /var/log/tpms/tpms2.json /var/log/tpms/tpms.json
    else
        echo "The file size is within the limit of $limit lines."
    fi
else
    echo "The log file /var/log/tpms/tpms.json does not exist."
fi
EOF

# Set executable permission for the script
chmod +x trimlog_tpms.sh
verify add "$INSTALL_DIR/tpms/trimlog_tpms.sh"

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

echo -e "\nDasher Status: Success"
