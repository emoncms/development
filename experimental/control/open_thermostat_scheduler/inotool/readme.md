# Install

Install Arduino, ino & picocom

    sudo apt-get install arduino
    pip install ino
    sudo apt-get install picocom

Download jeelib library for RFM12, RFM69

    cd lib
    git clone https://github.com/jcw/jeelib.git

## Build

    ino build

## Upload

   ino upload

## Serial

   ino serial

## Set RF config 433MHz, build & upload

   sh setconfig.sh -f433 -i15 -g210 && ino build && ino upload

## Set RF config 868MHz, build & upload

   sh setconfig.sh -868 -i15 -g210 && ino build && ino upload


