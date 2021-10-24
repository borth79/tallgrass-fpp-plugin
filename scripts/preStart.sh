#!/bin/sh

#echo "Running fpp-plugin-Template PreStart Script"
echo "Starting preStart.sh" > /home/fpp/media/plugins/tallgrass-fpp-plugin/xPreStart.txt
fpp -e "TuneToMatrix,0,1"

