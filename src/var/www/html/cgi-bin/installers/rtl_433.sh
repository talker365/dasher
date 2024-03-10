#!/bin/bash

# Default installation destination
INSTALL_DIR="/opt"

# Function to check if a command is installed. Install it if not found.
check_command() {
  if ! command -v "$1" &> /dev/null; then
    case "$1" in
      pip3) sudo apt install -y python3-pip ;;
      git) sudo apt install -y git ;;
      figlet) sudo apt install -y figlet ;;
      "$INSTALL_DIR/bin/rtl_test") ./rtl-sdr.sh || { echo "Failed to run rtl-sdr.sh"; exit 1; } ;; 
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

########################################[ SCRIPT START ]#####################################

# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "figlet"
check_command "$INSTALL_DIR/bin/rtl_test"

echo "Installing RTL_433 - this process may take several minutes..."

# Update package list and install dependencies
echo "Updating package list and installing dependencies..."
sudo apt update
sudo apt install -y autoconf cmake build-essential libtool libusb-1.0-0-dev librtlsdr-dev rtl-sdr pkg-config git

# Clone rtl_433 repository
cd "$INSTALL_DIR" || exit
echo "Cloning rtl_433 repository..."
git clone https://github.com/merbanan/rtl_433.git
cd rtl_433 
verify add "$INSTALL_DIR/rtl_433"

# Compile and install rtl_433 to the specified destination
echo "Compiling and installing rtl_433..."
mkdir build
cd build 
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
sudo make install

if [ -e "$INSTALL_DIR/bin/rtl_433" ]; then
  # Clean up
  cd ~/
  echo "Cleaning up the build directory..."
  rm -rf "$INSTALL_DIR/rtl_433"
fi

cp "$INSTALL_DIR/bin/rtl* /bin/"
echo -e "\nInstallation completed successfully."
