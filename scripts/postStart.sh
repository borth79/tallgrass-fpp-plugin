#!/bin/sh

echo "Running TallGrass Lights Plugin PostStart Script"

/usr/bin/php /home/fpp/media/plugins/tallgrass-fpp-plugin/show_updater.php &
#postStart
