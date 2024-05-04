#!/bin/bash
# 30042024
# by: N4LDR

# Define a function to print usage information
print_usage() {
    echo "Usage: $0 [-v]"
    echo "Options:"
    echo "  -v    Print the JSON data to the terminal"
    exit 1
}

# Check for command line options
while getopts ":v" opt; do
    case $opt in
        v) print_json=true ;;
        \?) echo "Invalid option: -$OPTARG" >&2; print_usage ;;
    esac
done

# Function to create udev rule file if not exists
create_udev_rule() {
    udev_rule_file="/etc/udev/rules.d/99-usb-script.rules"
    if [ ! -f "$udev_rule_file" ]; then
        echo 'SUBSYSTEMS=="usb", ATTRS{idVendor}=="0bda", ATTRS{idProduct}=="2838", MODE="0666", RUN+="/opt/bin/hubPPPS.sh"' | sudo tee "$udev_rule_file" >/dev/null
        sudo udevadm control --reload-rules
    fi
}

# Run the command to get the Hub IDs
echo "Scanning hub for utilized ports..."

# Log a message indicating the script has been called
echo "USB script called at $(date)" >> /tmp/usb_script.log

hub_ids=$(uhubctl -n 0bda | awk '/Current status for hub/ {print $5}' | sort)

# Check if the command executed successfully
if [ $? -eq 0 ]; then
    # Initialize an empty array to hold the JSON objects
    json_array=()

    # Loop through each sorted Hub ID
    for id in $hub_ids; do
        # Run uhubctl command for each Hub ID and filter ports containing both "0503" and "2838"
        uhubctl_output=$(uhubctl -l "$id" | awk -v id="$id" '
            BEGIN {
                PROCINFO["sorted_in"] = "@ind_str_asc";  # Sort array by index (port number) in ascending order
            }
            function map_port(hub_length, port_num) {
              if (hub_length == "3") {
                if (port_num == "2") {
                  return "3";
                } else if (port_num == "3") {
                  return "2";
                } else if (port_num == "4") {
                  return "1";
                } else {
                  return "Unknown Port";
                }
              } else if (hub_length == "5") {
                if (port_num == "2") {
                  return "6";
                } else if (port_num == "3") {
                  return "5";
                } else if (port_num == "4") {
                  return "4";
                } else {
                  return "Unknown Port";
                }
              } else if (hub_length == "7") {
                if (port_num == "1") {
                  return "10";
                } else if (port_num == "2") {
                  return "9";
                } else if (port_num == "3") {
                  return "8";
                } else if (port_num == "4") {
                  return "7";
                } else {
                  return "Unknown Port";
                }
              } else {
                return "Unknown Hub";
              }
            }
            /Port [0-9]+:.*0503.*2838/ {
                match($0, /Port ([0-9]+):/);
                hub_port = substr($0, RSTART + 5, RLENGTH - 6);  # Adjusted to remove the colon
                match($0, /\[([^]]+)\]/);
                device_info = substr($0, RSTART + 1, RLENGTH - 2);
                sub(/.*\] /, "", device_info);
                sub(/[^ ]+ /, "", device_info);  # Remove the leading vendor ID
                serial_match = match(device_info, / [0-9]+$/);
                if (serial_match) {
                    serial = substr(device_info, RSTART + 1);
                    gsub(/[[:space:]]+/, " ", serial);  # Remove extra spaces
                    device_info = substr(device_info, 1, RSTART - 1);  # Remove serial number from device info
                } else {
                    serial = "N/A";
                }
                hub_length = length(id);
                map_result = map_port(hub_length, hub_port);
                printf "{\"Hub_ID\": \"%s\", \"Port\": \"%s\", \"Physical Port\": \"%s\", \"Device\": \"%s\", \"Serial\": \"%s\"},", id, hub_port, map_result, device_info, serial
            }
        ')

        # Add the JSON objects to the json_array if uhubctl_output is not empty
        if [ -n "$uhubctl_output" ]; then
            json_array+=("$uhubctl_output")
        fi
    done

    # Remove the trailing comma if the array is not empty
    if [ ${#json_array[@]} -gt 0 ]; then
        # Remove the last character (comma) from the last JSON object
        last_index=$(( ${#json_array[@]} - 1 ))
        json_array[$last_index]=${json_array[$last_index]%?}
    fi

    # Convert the array of JSON objects to a JSON array string
    json_string="["
    for ((i = 0; i < ${#json_array[@]}; i++)); do
        json_string+="${json_array[$i]}"
    done
    json_string+="]"

    # If -v switch is provided, print the JSON data
    if [ "$print_json" = true ]; then
        echo "JSON Data:"
        echo "$json_string"
    fi

    # Write the JSON string to a file
    echo "$json_string" > /tmp/hub_info.json

    echo "Data exported to /tmp/hub_info.json"
else
    echo "Error: Failed to get Hub ID."
fi

# Create udev rule file if not exists
create_udev_rule
