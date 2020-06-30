#!/bin/sh

cd /home/priceweb/board18.temp
/usr/bin/mysqldump -u priceweb -pHPeds2010 --host mysql.board18.org --single-transaction board18wiki > board18wiki.txt
nowdate=$(date +"%Y-%m-%d")
mv board18wiki.txt $nowdate.board18wiki.txt
echo
echo "board18wiki database backup completed."
echo

tar -zcf "board18wiki.image.tgz" ~/wiki2.board18.org/images/ 
mv board18wiki.image.tgz $nowdate.board18wiki.image.tgz
echo
echo "wiki image directory backup completed."
echo

#use aws-cli to upload to DreamObjects
aws --endpoint-url https://objects-us-east-1.dream.io s3 sync /home/priceweb/board18.temp s3://board18backups/
echo
echo "Backups are in AWS bucket."
echo

#delete backups from web server
rm -f /home/priceweb/board18.temp/* 
 
#######
# END #
#######


