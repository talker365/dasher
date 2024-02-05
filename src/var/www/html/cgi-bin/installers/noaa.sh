#!/bin/bash

start=$(date +'%s')

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
    if [ "$1" == "pip3"    ]; then sudo apt install -y python3-pip; fi
    if [ "$1" == "git"     ]; then sudo apt install -y git; fi
    if [ "$1" == "figlet". ]; then sudo apt install -y figlet; fi
    if [ "$1" == "rtl_433" ]; then echo -e 'Install rtl_433 first'; fi
    exit 1
  fi
}

# Function to uninstall pyPacket and its dependents
uninstall_pypacket() {
  systemctl stop noaa
  systemctl disable noaa
  sudo rm -rf /etc/systemd/system/noaa.service
  sudo rm -rf "$INSTALL_DIR"/noaa
   sudo sed -i '/# Setting this to allow multiple SDRs to work better/d' /etc/rc.local
   sudo sed -i '/\/sys\/module\/usbcore\/parameters\/usbfs_memory_mb/d' /etc/rc.local
  echo "NOAA and its dependents have been uninstalled."
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
check_command "rtl_433"
check_command "figlet"

# Update package list
sudo apt update

# Clone NOAA/DSAME repository
echo -e 'cloning repository https://github.com/talker365/dsame'
cd "$INSTALL_DIR"
git clone https://github.com/talker365/dsame
mv dsame noaa
cd noaa

# Creating the noaa.sh script...
echo -e '\nCreating the noaa.sh script file ...'
cat << EOF > "$INSTALL_DIR"/noaa/noaa.sh 
#!/bin/bash
    #
    # Page          051139
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
    
    DEVICE="$SERIAL_NUMBER"
    PPM=0
    FREQ=162.45M
    GAIN=49
    ./dsame.py --source ~/noaa/source.sh --json /var/log/dsame/messages.json
EOF

# Creating the source.sh script...
echo -e '\nCreating the source.sh script file ...'
cat << EOF > "$INSTALL_DIR"/noaa/source.sh 
#!/bin/sh
    #SDR Settings for NWS EAS Alerts
    echo INPUT: rtl_fm Device >&2
    DEVICE="$SERIAL_NUMBER"
    PPM=0
    FREQ=162.450M
    GAIN=42
    SQL=90
    
    
    until
      rtl_fm -d ${DEVICE} -M fm -s 22050 -E dc -F 5 -p ${PPM} -g ${GAIN} -l ${SQL} -f 162.450M |  multimon-ng -t raw -a  EAS /dev/stdin; do
      #rtl_fm -d ${DEVICE} -M fm -s 22050 -E dc -F 5 -p ${PPM} -g ${GAIN} -l ${SQL} -f 162.400M -f 162.425M -f 162.450M -f 162.525M -f 162.550M |  multimon-ng -t raw -a  EAS /dev/stdin; do
          echo Restarting... >&2
          sleep 2
    done
EOF

chmod +x noaa.sh 
chmod +x source.sh 

# Append the /etc/rc.local file
sudo sed -i -e '$i\# Setting this to allow multiple SDRs to work better' /etc/rc.local
sudo sed -i -e '$i\/sys\/module\/usbcore\/parameters\/usbfs_memory_mb' /etc/rc.local

# Creating the noaa System Service script...
echo -e '\nCreating the noaa Service  ...'
cat << EOF > /etc/systemd/system/noaa.service
[Unit]
    Description=NOAA, National Weather System: Emergency Alerting System decoder.
    
[Service]
    Type=forking
    ExecStart=/usr/bin/screen -d -m -S noaa /opt/noaa/noaa.sh
    ExecStop=/usr/bin/killall -p -w -s 2 noaa.sh
    WorkingDirectory=/opt/noaa
    Restart=on-failure
    RestartSec=5
    
[Install]
    WantedBy=multi-user.target
EOF

systemctl enable noaa
systemctl start noaa

