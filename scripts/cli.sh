#!/bin/bash
#
# CLI Cron JOBs
#
# @author 	IP Bastola < ip.bastola@gmail.com>
#
# Example Usage (cront tab record) - Every 07:30 AM
#
# 				30 7 * * * sh /home/insqube/repo/insqube-core/scripts/cli.sh
#

WEB_ROOT=/var/www/html/neco.insqube.local/web

#
# Go to web root folder
#
cd $WEB_ROOT

#
# Import Forex Data from NRB
#
echo $(date)" - Importing forex from NRB ..."
php index.php cli import_forex_rates




#
## Now Send Email Notification of this job
#
# Attachments:
# 	~/logs/import/17d-import-YYYY-MM-DD-HMS.log
# 	~/logs/cron/cron-17d-"`date +\%Y-\%m-\%d`".log
#
# _yesterday=$(date -d "yesterday" +%Y-%m-%d)
# _cron_log_file=~/logs/cron/cron-17d-"`date +\%Y-\%m-\%d`".log
# echo "Please find the attachments." | mutt -s "[NHRPMIS - Phase III - Daily Import Report] $_today"  -a $_logfile -a $_cron_log_file -- dipeshraja@gmail.com ip.bastola@gmail.com gautamxpratik@gmail.com pujan789556@gmail.com

