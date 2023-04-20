#!/bin/sh

cd /home/priceweb/board18.temp
/usr/bin/mysqldump -u priceweb -pHPeds2010 --host mysql.board18.org --single-transaction board18prod1 > board18prod1.txt
nowdate=$(date +"%Y-%m-%d")
mv board18prod1.txt $nowdate.board18prod1
echo
echo "prod1 database backup completed."
echo

tar -zcf "board18prod1.image.tgz" ~/prod1.board18.org/images/ 
mv board18prod1.image.tgz $nowdate.board18prod1.image.tgz
echo
echo "prod1 image directory backup completed."
echo

#use aws-cli to upload to DreamObjects
aws --endpoint-url https://objects-us-east-1.dream.io s3 sync /home/priceweb/board18.temp s3://board18backups/
echo
echo "Backups in AWS bucket."
echo
 
#delete backups from web server
rm -f /home/priceweb/board18.temp/*

#######
# END #
#######
