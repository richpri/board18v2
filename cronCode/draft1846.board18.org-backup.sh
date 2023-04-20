#!/bin/sh

cd /home/priceweb/board18.temp
/usr/bin/mysqldump -u priceweb -pHPeds2010 --host mysql.board18.org --single-transaction draft1846 > draft1846.txt
nowdate=$(date +"%Y-%m-%d")
mv draft1846.txt $nowdate.draft1846.txt
echo
echo "draft1846 database backup completed."
echo

#use aws-cli to upload to DreamObjects
aws --endpoint-url https://objects-us-east-1.dream.io s3 sync /home/priceweb/board18.temp s3://board18backups/
echo
echo "Backup in AWS bucket."
echo

#delete backup from web server
rm -f /home/priceweb/board18.temp/* 
 
#######
# END #
#######
