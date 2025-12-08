#!/bin/bash

# Snake Case Conversion Script
# This script updates all PHP and Vue files to use snake_case column names

echo "Starting snake_case conversion..."

# Define the directories to process
PHP_DIRS=("app/Http/Controllers" "app/Services" "app/Http/Middleware")
VUE_DIRS=("resources/js/Pages" "resources/js/Components")

# Column name replacements (camelCase -> snake_case)
declare -A replacements=(
    # Primary Keys
    ["userID"]="user_id"
    ["attendanceID"]="attendance_id"
    ["shiftID"]="shift_id"
    ["requestID"]="request_id"
    
    # Regular Columns
    ["icNumber"]="ic_number"
    ["hiredOn"]="hired_on"
    ["userRole"]="user_role"
    ["annualLeaveBalance"]="annual_leave_balance"
    ["sickLeaveBalance"]="sick_leave_balance"
    ["emergencyLeaveBalance"]="emergency_leave_balance"
    ["shiftDate"]="shift_date"
    ["shiftType"]="shift_type"
    ["clockInTime"]="clock_in_time"
    ["clockOutTime"]="clock_out_time"
    ["startDate"]="start_date"
    ["endDate"]="end_date"
)

# Function to replace in files
replace_in_files() {
    local dir=$1
    local pattern=$2
    
    for old in "${!replacements[@]}"; do
        new="${replacements[$old]}"
        echo "Replacing '$old' with '$new' in $dir..."
        
        # Find and replace in all PHP/Vue files
        find "$dir" -type f -name "$pattern" -exec sed -i '' "s/\\b$old\\b/$new/g" {} +
        find "$dir" -type f -name "$pattern" -exec sed -i '' "s/['\"]$old['\"]/\"$new\"/g" {} +
        find "$dir" -type f -name "$pattern" -exec sed -i '' "s/->$old/->$new/g" {} +
        find "$dir" -type f -name "$pattern" -exec sed -i '' "s/\\['$old'\\]/['$new']/g" {} +
    done
}

# Process PHP files
for dir in "${PHP_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "Processing PHP files in $dir..."
        replace_in_files "$dir" "*.php"
    fi
done

# Process Vue files
for dir in "${VUE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "Processing Vue files in $dir..."
        replace_in_files "$dir" "*.vue"
    fi
done

# Special case: Replace table name
echo "Replacing table name shiftschedules -> shift_schedules..."
find app resources -type f \( -name "*.php" -o -name "*.vue" \) -exec sed -i '' "s/shiftschedules/shift_schedules/g" {} +

echo "✅ Snake case conversion complete!"
echo "⚠️  Please review changes and test your application!"

