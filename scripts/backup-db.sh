#!/bin/sh
#
#----------------------------------------------------
# Mysql Dump and Rsync to Remote Backup Server
#----------------------------------------------------
#
# This script generate a local bakcup copy using
# mysqldump and rsync to remote server.
# Further, this script deletes old dump (older than
# 15 days)
#
# Usage:
# 	Regular:
# 		bash ~/scripts/backdup-db.sh
#
# 	Crontab Entry (Every 3 AM)
# 		3 * * * sh ~/scripts/backdup-db.sh > ~/logs/backup-db/"`date +\%Y-\%m-\%d`".log
#
#----------------------------------------------------
#

# (1) set up all the mysqldump variables
echo "InsQube DB Backup @ `date +"%Y-%m-%d %H:%M:%S"`"
echo "Setting up variables..."

BACKUP_DIR=/home/insqube/backup/mysql
FILE="$BACKUP_DIR/insqube-core.`date +"%Y%m%d"`.sql.gz"
DBSERVER=127.0.0.1

DATABASE="db_name"
USER="db_user"
PASS="db_pass"

NOTIFICATION_EMAIL="insqube@example.com"


# (2) in case you run this more than once a day, remove the previous version of the file
echo "Removing previous version of the file (if any)..."
unalias rm     2> /dev/null
rm ${FILE}  2> /dev/null

# (3) do the mysql database backup (dump) and compress using gzip

# use this command for a database server on a separate host:
#mysqldump --opt --protocol=TCP --user=${USER} --password=${PASS} --host=${DBSERVER} ${DATABASE} > ${FILE}

# use this command for a database server on localhost. add other options if need be.
echo -n "Generating database dump..."
mysqldump --opt --single-transaction --routines --triggers --user=${USER} --password=${PASS} --databases ${DATABASE} | gzip > ${FILE}
RESULT=$?
if [ $RESULT -eq 0 ]; then
  echo "OK"
else
  echo "FAILED"
fi

# (4) Delete files older than 15 days
echo "Removing dump files older than 15 days..."
find $BACKUP_DIR/* -mtime +15 -exec rm {} \;


# (5) rsync to remote backup (NHRP-XUP)
# DATE=`date +%Y-%m-%d:%H:%M:%S`
# echo "Rsync process started @ $DATE"
# echo ""
# rsync -av --progress ${FILE} nhrpadmin@nhrpxup:/zfstank/dbdump/mysql/

