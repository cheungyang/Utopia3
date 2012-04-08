#!/bin/sh

clear

echo "============================"
echo "= Autocommit Script Begins ="
echo "============================"

if [ ! $# = 1 ]
then 
    echo "[ERROR]incorrect parameters, syntax: $0 [time_interval(s/m/h)]"
    exit -1;
fi;

while [ 1 ]
do 
  echo "[`date +"%F %X"`]committing now..."
  ../../change-svn-wc-format.py . 1.4
  svn cleanup
  svn update
  date=`date +'%F %X'`
  svn ci -m "autocommit by script at ${date}"
  echo "[`date +"%F %X"`]commit done, sleeping for $1 now..."
  sleep $1
done;
