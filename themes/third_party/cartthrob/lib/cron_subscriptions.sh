#!/bin/sh

if [ $1 ]
then
	EXTLOAD_PATH=$1
else
	EXTLOAD_PATH="extload.php"
fi

SUBSCRIPTIONS=$(php "$EXTLOAD_PATH" cron process_subscriptions 1)

for i in $SUBSCRIPTIONS
do
	php "$EXTLOAD_PATH" cron process_subscription "$i"
done