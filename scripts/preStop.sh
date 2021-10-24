#!/bin/sh

echo "Starting preStop.sh" > /home/fpp/media/plugins/tallgrass-fpp-plugin/xPreStop.txt
fpp -E "TuneToMatrix"
#echo "Running fpp-plugin-Template PreStop Script"

