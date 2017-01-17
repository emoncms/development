# Learn: MQTT

Example publisher and subscriber MQTT python clients.

#### Setting up Mosquitto MQTT Broker

Install mosquitto: [http://mosquitto.org/download](http://mosquitto.org/download)

Create mosquitto password file (emonpi user):

    sudo mosquitto_passwd -c /etc/mosquitto/passwd emonpi

Configure mosquitto:

    sudo nano /etc/mosquitto/mosquitto.conf

Enter following contents to turn off anonymous users and specify a password file:

    pid_file /var/run/mosquitto.pid
    persistence false
    log_dest file /var/log/mosquitto/mosquitto.log
    include_dir /etc/mosquitto/conf.d
    allow_anonymous false
    password_file /etc/mosquitto/passwd

Run mosquitto with:

    sudo mosquitto -c /etc/mosquitto/mosquitto.conf

#### Run subscriber

Open to edit *mqtt_pub.py* enter username and password as set in configuration step above.

Then run the subscriber with:

    python mqtt_sub.py
    
#### Run publisher

Open to edit *mqtt_pub.py* enter username and password as set in configuration step above.

Then run the publisher with:

    python mqtt_pub.py


## 2) Install Mosquitto Auth Plugin

The mosquitto jpmens auth plugin enables authentication and access list control from an external database such as mysql or redis.

Download jpmens/mosquitto-auth-plug here:

- [https://github.com/jpmens/mosquitto-auth-plug](https://github.com/jpmens/mosquitto-auth-plug)

A useful guide on using the plugin: http://my-classes.com/2015/02/05/acl-mosquitto-mqtt-broker-auth-plugin/

#### Installation

Install dependencies:

    sudo apt-get install libc-ares-dev libcurl4-openssl-dev libmysqlclient-dev uuid-dev
    
Get Mosquitto and build it

    tar xvzf mosquitto-1.4.10.tar.gz
    cd mosquitto-1.4.10
    make mosquitto
    sudo make install
    
Get mosquitto-auth-plug source and create a suitable configuration file

    git clone https://github.com/jpmens/mosquitto-auth-plug.git
    cd mosquitto-auth-plug
    cp config.mk.in config.mk
    make
    
Fix for compile error:

- https://github.com/jpmens/mosquitto-auth-plug/issues/183

Recompile both mosquitto and auth plugin with option changed as detailed here:

- [https://github.com/jpmens/mosquitto-auth-plug/issues/33](https://github.com/jpmens/mosquitto-auth-plug/issues/33)

    nano mosquitto-1.4.10/config.mk
    Set: WITH_SRV:=no

Run: make clean, make, sudo make install in both.

#### Mosquitto configuration

mosquitto.conf config file:

    # Place your local configuration in /etc/mosquitto/conf.d/
    #
    # A full description of the configuration file is at
    # /usr/share/doc/mosquitto/examples/mosquitto.conf.example

    pid_file /var/run/mosquitto.pid
    persistence false
    log_dest file /var/log/mosquitto/mosquitto.log
    include_dir /etc/mosquitto/conf.d

    allow_anonymous false
    # password_file /etc/mosquitto/passwd

    auth_plugin /home/trystan/Desktop/learn_mqtt/mosquitto-auth-plug/auth-plug.so
    auth_opt_backends mysql
    auth_opt_host localhost
    auth_opt_port 3306
    auth_opt_dbname -----
    auth_opt_user -----
    auth_opt_pass -----
    auth_opt_userquery SELECT pw FROM users WHERE username = '%s'
    auth_opt_superquery SELECT COUNT(*) FROM users WHERE username = '%s' AND super = 1

#### Mysql database creation

Login to mysql:

    mysql -u root -p
    
Create database:
    
    CREATE DATABASE mosquitto;
    
Create users table: 

    CREATE TABLE users (`id` int(11) not null auto_increment primary key, `username` varchar(30), `pw` varchar(67), `super` int(11)) ENGINE=MYISAM;

Create access list table:
    
    CREATE TABLE acls (`id` int(11) not null auto_increment primary key, `username` varchar(30), `topic` text, `rw` int(11)) ENGINE=MYISAM;
    
Create PBKDF2 password for new user:

    cd mosquitto-auth-plug
    ./np
    
Insert into users table:

    INSERT INTO users (username,pw,super) VALUES ('example','PBKDF2$sha256$901$SgVAoHgm4w/gHGEu$3TAwRHSzrLUZwaPM+zXZJSIfHiianIPx',0);
    
Provide access to particular topic: rw:(read:0|write:1|readwrite:2)

    INSERT INTO acls (username,topic,rw) VALUES ('example','user/1/#',2);
    

