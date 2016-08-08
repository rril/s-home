#!/bin/bash
GPIO=`ls /var/www/gpio/`
while true; do 
	sleep 1
	for TMP4324 in $GPIO
	do
		echo ${GPIO} > /sys/class/gpio/export
		echo "out" > /sys/class/gpio/gpio${GPIO}/direction
		head -1 /var/www/gpio/${GPIO} > /sys/class/gpio/gpio${GPIO}/value
	done
done
