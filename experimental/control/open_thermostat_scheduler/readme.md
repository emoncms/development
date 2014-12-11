# Heating controller software development

This is work in progress - it has not yet reached a working first release state

## Development Notes:


**rfmpi2mqtt.py** – the bridge between serial IO of rfmpi and MQTT.

Node data received is decoded according to config file and posted to rx mqtt topic:

	18,0,0,0,0:
	*rxtopic/nodename/varname*
	rx/room/temperature		0
	rx/room/battery			0

Data can be sent out on rfm network by publishing messages to the tx mqtt topic:

	*txtopic/nodename/varname*
	tx/heating/state			1

is encoded using config file.

**heating.html** - The scheduler interface provides a UI that generates a schedule object detailing the heating schedule for every day of the week. The heating schedule can be overridden with a manual setpoint and heating state in manual mode. The variables required for this application are:
    
	heating_state			    on/off
	heating_manual_setpoint		<temperature>		
	heating_mode			    manual/schedule
	heating_schedule			<schedule object>

These variables need to be persisted in the server database – (ideally persisted to disk, at the moment the app is using redis which can be configured to persist but its not ideal as you don’t want to persist the regularly updated node data – to reduce disk writes)

**runschedule.py** - The scheduler interface needs to be used in conjunction with an always running script that runs the schedule when the web page interface is not loaded by the user. The scheduler UI needs to pass the above configuration variables to the runschedule.py script.

## Command routing

The scheduler interface could also send commands directly to rfmpi2mqtt.py script controlling heating system but then any changes of state also need to be transferred to the runschedule.py script before runschedule.py sends any commands to rfmpi2mqtt.py.
This provides two possibilities either commands to rfmpi2mqtt are only sent my runshedule.py and so to get fast response runschedule.py needs to receive ui state change via mqtt rather than polling redis.

In the event that runscheduler fails, it may be an advantage if the UI can send commands directly via sever:api to rfmpi2mqtt. That failure of runschedule.py only reduces functionality  rather than completely disabling heating control. 

In both cases whether control is via runschedule.py or if commands can go both directly and via runschedule.py. If you want responsive control, state updates to the heating variables need to be passed to runscheduler via a push mechanism (such as mqtt pub/sub)

## Recording state and changes of state

- program state (schedule object, manual heating settings)
- node rx state (node data, room temperature etc)
- node tx state (command state)

you need control over exactly what is persisted to disk

changes of state are pushed to mqtt topics
state is persisted in redis
