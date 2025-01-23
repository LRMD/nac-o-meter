#!/usr/bin/env python3

import re
import sys

def is_ru_by_callsign(callsign):
    # Russian callsigns: R*, UA-UI[0-9]* (except UA9* which is Asian Russia)
    # Belarusian callsigns: EU*, EV*, EW*
    ru_by_pattern = r'^(R[A-Z0-9]|U[A-I][0-9]|EU[0-9]|EV[0-9]|EW[0-9])'
    return bool(re.match(ru_by_pattern, callsign))

def process_sql_file(input_file, output_file):
    # Store IDs to remove
    callsigns_to_remove = set()
    callsign_ids_to_remove = set()
    
    with open(input_file, 'r') as f:
        lines = f.readlines()
    
    with open(output_file, 'w') as f:
        in_callsign_table = False
        skip_line = False
        
        for line in lines:
            # Track when we're in the callsign table section
            if 'INSERT INTO `callsign`' in line:
                in_callsign_table = True
            elif in_callsign_table and not line.startswith('INSERT'):
                in_callsign_table = False
            
            # Process callsign table entries
            if in_callsign_table:
                # Extract callsign data
                matches = re.findall(r'\((\d+),\'([A-Z0-9/]+)\'', line)
                if matches:
                    filtered_values = []
                    for id_str, callsign in matches:
                        if not is_ru_by_callsign(callsign):
                            filtered_values.append(f"({id_str},'{callsign}')")
                        else:
                            callsign_ids_to_remove.add(id_str)
                            callsigns_to_remove.add(callsign)
                    
                    if filtered_values:
                        f.write(f"INSERT INTO `callsign` VALUES {','.join(filtered_values)};\n")
                    skip_line = True
                    continue
            
            # Skip lines referencing removed callsigns in other tables
            if any(callsign in line for callsign in callsigns_to_remove) or \
               any(f"({id_str}," in line or f",{id_str}," in line or f",{id_str})" in line 
                   for id_str in callsign_ids_to_remove):
                continue
            
            if not skip_line:
                f.write(line)
            skip_line = False

if __name__ == "__main__":
    process_sql_file('sql/lyac.sql', 'sql/lyac_cleaned.sql') 