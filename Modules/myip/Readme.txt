Module to find IP address of local emonBase node posting to remote emoncms, useful for heating control or obtaining remote SSH into local emonBase (as long as port is open) without setting up Dynamic DNS. 

1. Install my IP Module on remote server (it's already installed on emoncms.org)

2. Create con job on local emonBase to update remote IP:

$ contab -e

* * */1 * * wget "http://REMOTE_EMONCMS_SERVER/myip/set.json?apikey=YOUR_REMOTE_EMONCMS_API_KEY"
