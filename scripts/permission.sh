#!/bin/bash
#
# File Permissions on InsQube Deployment
#
# After pushing codebase to server, we have to run this script
# if we have
# After you have passed
# Usage: sudo bash permission.sh
#

# Check if Run as Root/Sudo
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root."
   echo "Usage: sudo bash permission.sh";
   exit 1
fi

# Web Root
_WEBROOT="/var/www/html/insqube-production/web"
_APPFILESROOT="/var/www/html/media-production"

cd $_WEBROOT
chown -R insqube:www-data $_WEBROOT
find $_WEBROOT -type d -exec chmod 750 {} \;
find $_WEBROOT -type f -exec chmod 644 {} \;

# MPDF Writable Folders
cd $_WEBROOT/vendor/mpdf/mpdf
chmod -R g+w ./tmp

# Files Permissions
cd $_APPFILESROOT
chown -R insqube:www-data $_APPFILESROOT
chmod -R g+w $_APPFILESROOT
# find $_APPFILESROOT -type d -exec chmod 775 {} \;
# find $_APPFILESROOT -type f -exec chmod 644 {} \;
