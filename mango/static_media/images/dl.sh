#!/bin/sh
images="$(awk -F'[()]'  '/images/{print $2}' ../css/style.css  | sed -e 's:"::g' | sort -u)"
base='http://www.gnome.org/wp-content/themes/gnome-grass'
#IFS="
#"
for image in $images; do
    echo wget -q ${base}/$image
    wget -q ${base}/$image
done
