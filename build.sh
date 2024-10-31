#!/bin/bash
sync=false
develop=false
while getopts ":s" opt; do
  case ${opt} in
    s )
      sync=true
      ;;
    d )
      develop=true
      ;;
    \? ) echo "Usage: build [-s]"
      ;;
  esac
done

[[ ! -d build ]] && mkdir build
# rm -rf build/*
[[ ! -d build/mcs ]] && mkdir build/mcs
if [ ! $sync ]; then
	rm -rf build/mcs/*
	cd admin && yarn && yarn build
	cd ../widget && yarn && yarn build
	cd ..
fi
if [ $develop ]; then
	rsync -av --exclude '/build/' --exclude 'node_modules/' --exclude '.git/' ./ build/mcs
	cd build/mcs && composer i
else
	rsync -av --exclude '/build/' --exclude '/tests/' --exclude 'node_modules/' --exclude '.git/' --exclude 'GeoLite2-City-Locations-en.csv'  ./ build/mcs
	cd build/mcs && composer i --no-dev
fi
zip -qr ../mcs.zip ./
cd ../..
#rm -rf build/mcs
