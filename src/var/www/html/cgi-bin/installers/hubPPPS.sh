#!/bin/bash
# 30042024
# by: N4LDR

# Run the command to get the Hub IDs
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
                printf "{\"Hub_ID\": \"%s\", \"Port\": \"%s\", \"Physical Port\": \"%s\", \"Device\": \"%s\", \"Serial\": \"%s\"}\n", id, hub_port, map_result, device_info, serial
            }
        ')

        # Add the JSON objects to the json_array
        json_array+=("$uhubctl_output")
    done

    # Convert the array of JSON objects to a JSON array string
    json_string=$(IFS=,; echo "[${json_array[*]}]")

    # Write the JSON string to a file
    echo "$json_string" > hub_info.json

    echo "Data exported to hub_info.json"
else
    echo "Error: Failed to get Hub ID."
fi
