#!/bin/bash
#
# InsQube Cron JOBs
#
# @author 	IP Bastola < ip.bastola@gmail.com>
#
# Example Usage (cront tab record) - Every 06:00 AM
#
# 				* 6 * * * sh /home/insqube/repo/insqube-core/scripts/cron.sh &> ~/logs/cron/cron-"`date +\%Y-\%m-\%d`".log
#

WEB_ROOT=/var/www/html/insqube-production/web

#
# Go to web root folder
#
cd $WEB_ROOT

#
# Import Forex Data from NRB
#
echo $(date)" - Importing forex from NRB ..."
php index.php cli forex


#
## Now Send Email Notification of this job
#
# Attachments:
# 	~/logs/cron/cron-"`date +\%Y-\%m-\%d`".log
#
# _today=$(date +%Y-%m-%d)
# _cron_log_file=~/logs/cron/cron-"$_today".log
# echo "Please find the attachments." | mutt -s "[InsQube - Cron Reports] $_today" -a $_cron_log_file -- ip.bastola@gmail.com

