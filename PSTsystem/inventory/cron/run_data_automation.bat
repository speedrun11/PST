@echo off
REM Windows Batch File for Data Automation
REM This file can be scheduled using Windows Task Scheduler

echo Starting PST Data Automation...
echo Date: %date%
echo Time: %time%

REM Change to the correct directory
cd /d "C:\xampp\htdocs\PST\PSTsystem\inventory\cron"

REM Run the PHP script
"C:\xampp\php\php.exe" auto_data_update.php

REM Check if the script ran successfully
if %errorlevel% equ 0 (
    echo Data automation completed successfully
) else (
    echo Data automation failed with error code %errorlevel%
)

echo Script execution finished
pause
