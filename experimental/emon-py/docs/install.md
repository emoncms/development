## Emon-py Raspberrypi install

You will need:
- SD Card with 4GB or larger capacity.
- RaspberryPI
- rfm12pi adapter board
- ethernet, usb power.

Download the official raspberrpi raspbian image and write to the SD card.

    [http://www.raspberrypi.org/downloads](http://www.raspberrypi.org/downloads)
    
To upload the image using dd on linux 

Check the mount location of the SD card using:

    df -h
    
Unmount any mounted SD card partitions
    
    umount /dev/sdb1
    umount /dev/sdb2
    
Write the raspbian image to the SD card (Make sure of=/dev/sdb is the correct location)
    
    sudo dd bs=4M if=2014-01-07-wheezy-raspbian.img of=/dev/sdb
    
**Open the SD Card in GParted and format the unallocated 899 MiB disk space as a FAT16 Drive** 

Once uploaded to the SD card, insert the SD card into the raspberrypi and power the pi up.

Find the IP address of your raspberrypi on your network then connect and login to your pi with SSH, for windows users there's a nice tool called [putty](http://www.putty.org/) which you can use to do this. To connect via ssh on linux, type the following in terminal:

    ssh pi@YOUR_PI_IP_ADDRESS

It will then prompt you for a username and password which are: **username:**pi, **password:**raspberry.

Now that your loged in to your pi, the first step is to edit the _inittab_ and _boot cmdline config_ file to allow the python gateway which we will install next to use the serial port, type:

    sudo nano /etc/inittab

At the bottom of the file comment out the line, by adding a ‘#’ at beginning:

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100

[Ctrl+X] then [y] then [Enter] to save and exit

Edit boot cmdline.txt

    sudo nano /boot/cmdline.txt

replace the line:

    dwc_otg.lpm_enable=0 console=ttyAMA0,115200 kgdboc=ttyAMA0,115200 console=tty1 
    root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait

with:

    dwc_otg.lpm_enable=0 console=tty1 root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait
    
Create a directory that will be a mount point for the rw data partition

    mkdir /home/pi/data

    
## Read only mode

Configure Raspbian to run in read-only mode for increased stability (optional but recommended)

The following is copied from: 
http://emonhub.org/documentation/install/raspberrypi/sd-card/

Then run these commands to make changes to filesystem

    sudo cp /etc/default/rcS /etc/default/rcS.orig
    sudo sh -c "echo 'RAMTMP=yes' >> /etc/default/rcS"
    sudo mv /etc/fstab /etc/fstab.orig
    sudo sh -c "echo 'tmpfs           /tmp            tmpfs   nodev,nosuid,size=30M,mode=1777       0    0' >> /etc/fstab"
    sudo sh -c "echo 'tmpfs           /var/log        tmpfs   nodev,nosuid,size=30M,mode=1777       0    0' >> /etc/fstab"
    sudo sh -c "echo 'proc            /proc           proc    defaults                              0    0' >> /etc/fstab"
    sudo sh -c "echo '/dev/mmcblk0p1  /boot           vfat    defaults                              0    2' >> /etc/fstab"
    sudo sh -c "echo '/dev/mmcblk0p2  /               ext4    defaults,ro,noatime,errors=remount-ro 0    1' >> /etc/fstab"
    sudo sh -c "echo '/dev/mmcblk0p3  /home/pi/data   vfat    defaults,user,rw,umask=000,noatime    0    2' >> /etc/fstab"
    sudo sh -c "echo ' ' >> /etc/fstab"
    sudo mv /etc/mtab /etc/mtab.orig
    sudo ln -s /proc/self/mounts /etc/mtab
    
The Pi will now run in Read-Only mode from the next restart.

Before restarting create two shortcut commands to switch between read-only and write access modes.

Firstly “ rpi-rw “ will be the command to unlock the filesystem for editing, run

    sudo nano /usr/bin/rpi-rw

and add the following to the blank file that opens

    #!/bin/sh
    sudo mount -o remount,rw /dev/mmcblk0p2  /
    echo "Filesystem is unlocked - Write access"
    echo "type ' rpi-ro ' to lock"

save and exit using ctrl-x -> y -> enter and then to make this executable run

    sudo chmod +x  /usr/bin/rpi-rw

Next “ rpi-ro “ will be the command to lock the filesytem down again, run

    sudo nano /usr/bin/rpi-ro

and add the following to the blank file that opens

    #!/bin/sh
    sudo mount -o remount,ro /dev/mmcblk0p2  /
    echo "Filesystem is locked - Read Only access"
    echo "type ' rpi-rw ' to unlock"

save and exit using ctrl-x -> y -> enter and then to make this executable run

    sudo chmod +x  /usr/bin/rpi-ro

Lastly reboot for changes to take effect

    sudo shutdown -r now
    
    

Next we will install git and python gateway dependencies

    ipe-rw

    sudo apt-get update
    sudo apt-get install screen sysstat git-core python-serial python-configobj redis-server python-pip
    
    sudo pip install redis
    sudo pip install web.py
    
Configure redis to run without logging or data persistance.

    sudo nano /etc/redis/redis.conf

comment out redis log file

    # logfile /var/log/redis/redis-server.log

comment out all redis saving

    # save 900 1
    # save 300 10
    # save 60 10000
    
    sudo /etc/init.d/redis-server start

Install emon-py:
    
    git clone https://github.com/emoncms/development.git
    cd development/experimental/emon-py
    cp default.emon-py.conf emon-py.conf
    
Run emon-py using 3 screen's:
    
    screen
    python listener.py
    ctrl a+d
    
    screen
    python server.py
    ctrl a+d
    
    screen
    python writer.py
    ctrl a+d
    
Test write rate:
    
    sudo iostat 60
    
With 20x 10s feeds and 25x 60s feeds and a commit rate of 60s the kb_wrtn/s rate should be around 0.4:

    avg-cpu:  %user   %nice %system %iowait  %steal   %idle
               2.26    0.00    0.57    0.00    0.00   97.16

    Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
    mmcblk0           0.79         0.20         0.42         12         25

    avg-cpu:  %user   %nice %system %iowait  %steal   %idle
               2.40    0.00    0.54    0.00    0.00   97.07

    Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
    mmcblk0           0.77         0.00         0.44          0         26

    avg-cpu:  %user   %nice %system %iowait  %steal   %idle
               2.17    0.00    0.52    0.02    0.00   97.30

    Device:            tps    kB_read/s    kB_wrtn/s    kB_read    kB_wrtn
    mmcblk0           0.82         2.35         0.42        140         25
