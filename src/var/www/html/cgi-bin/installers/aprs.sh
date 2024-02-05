#!/bin/bash

start=$(date +'%s')

# Default installation destination
INSTALL_DIR="/opt"
SERIAL_NUMBER=""
CALLSIGN=""
PASSCODE=""

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

# Function to check if a command is installed
check_command() {
  if ! command -v "$1" &> /dev/null; then
    if [ "$1" == "pip3"    ]; then sudo apt install -y python3-pip; fi
    if [ "$1" == "git"     ]; then sudo apt install -y git; fi
    if [ "$1" == "rtl_sdr" ]; then sudo apt install -y rtl_sdr; fi
    if [ "$1" == "figlet". ]; then sudo apt install -y figlet; fi
    exit 1
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  sudo rm -rf "$INSTALL_DIR"/bin/pypacket
  sudo rm -rf "$INSTALL_DIR"/share/pypacket
  sudo rm -rf "$INSTALL_DIR"/pypacket
  sudo rm -rf /etc/systemd/system/pyPacket.service
  sudo rm -rf "$INSTALL_DIR"/etc/profile.d/02-pypacket.sh
  echo "pyPacket and its dependents have been uninstalled."
  echo -e "\n\nScript Completed! in $(($(date +'%s') - $start)) seconds."

}

# If uninstall flag is set, execute uninstall function
if [ "$uninstall" = true ]; then
  uninstall_pypacket
  exit 0
fi

# Otherwise, install pyPacket and its dependents

# Check if required commands are installed
check_command "pip3"
check_command "git"
check_command "rtl_sdr"
check_command "figlet"

# Update package list
sudo apt update

# Clone Multimon-ng repository
cd "$INSTALL_DIR"
git clone https://github.com/talker365/multimon-ng.git
cd multimon-ng
mkdir build
cd build
cmake ../ -DCMAKE_INSTALL_PREFIX="$INSTALL_DIR"
make
make install

# Clone pyPacket repository
cd "$INSTALL_DIR"
echo -e 'Cloning pyPacket repository...'
git clone https://github.com/talker365/pypacket.git
cd pypacket

# Edit requirements.txt file and change pytest variable
sed -i 's/^pytest==.*/pytest==4.6.9/' requirements.txt
pip3 install -r requirements.txt

# Insert Serial Listener @ line #24 in file pypacket/base/configuration.py
#sed -i '24i \
#    def serial(self): \
#        """Gets the configured listener serial number setting.""" \
#        return self.data['\''listener'\'']['\''serial'\'']' pypacket/base/configuration.py

# Insert the SDR's Serial Number in the config/configuration.json file.
sed -i 's/"serial": "12345678"/"serial": "'"$SERIAL_NUMBER"'"/' /opt/pypacket/config/configuration.json

#Update pypacket/implementations/rtl_listener.py, changing line 12
sed -i '12s/.*/config.sample_rate(), "-l", "0", "-d", config.serial(), "-g", config.gain(), "-"],/' pypacket/implementations/rtl_listener.py

# Creating the pyPacket profile file...
echo -e '\nCreating the file /etc/profile.d/02-pypacket.sh...'
cat << EOF > /etc/profile.d/02-pypacket.sh
export PYPACKET_USERNAME="$CALLSIGN"
export PYPACKET_PASSWORD="$PASSCODE"
EOF

#Creating file /opt/pypacket/start.sh
echo -e '\nCreating the file /opt/pypacket/start.sh...'
cat << EOF > /opt/pypacket/start.sh
#!/bin/bash
export PYPACKET_USERNAME=wd4va-15
export PYPACKET_PASSWORD=20976
cd /opt//pypacket/
/usr/bin/python3 /opt/pypacket/main.py
EOF

chmod +x /opt/pypacket/start.sh
chmod +x /opt/pypacket/main.py

#Creating a System Service for pyPacket
echo -e '\nCreating the System Service for pyPacket..'
cat << EOF > /etc/systemd/system/pyPacket.service
# Pypacket service for systemd

[Unit]
Description=PyPacket - ARPS / IGATE Decoder

[Service]
Type=forking
ExecStart=/usr/bin/screen -d -m -S PyPacket /opt/pypacket/./start.sh
ExecStop=/usr/bin/killall -p -w -s 2 start.sh
WorkingDirectory=/opt/pypacket
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

# Enabling and Starting the pyPacket Service...
echo -e 'Enabling and starting the pyPacket Service...'
systemctl enable pyPacket
systemctl start pyPacket

echo $MODULE_NAME "library and its dependents have been installed successfully."
echo -e "\n\nScript Completed! in $(($(date +'%s') - $start)) seconds."