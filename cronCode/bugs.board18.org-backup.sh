#!/bin/sh

cd /home/priceweb/board18.temp
/usr/bin/mysqldump -u wikiboard18org -pswbLF^vg --host mysql.bugs.board18.org board18bugs > board18bugs.txt
nowdate=$(date +"%Y-%m-%d")
mv board18bugs.txt $nowdate.board18bugs.txt
echo
echo "Bugzilla backup completed."
echo

#use aws-cli to upload to DreamObjects
aws --endpoint-url https://objects-us-east-1.dream.io s3 sync /home/priceweb/board18.temp s3://board18backups/
echo
echo "Backup is in AWS bucket."
echo

#delete backup from web server
rm -f /home/priceweb/board18.temp/* 
 
#######
# END #
#######
