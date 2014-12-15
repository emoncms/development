freq="433"
node="15"
group="210"

TEMP=`getopt --long -o "f:i:g:" "$@"`
eval set -- "$TEMP"
while true ; do
    case "$1" in
        -f )
            freq=$2
            shift 2
        ;;
        -i )
            node=$2
            shift 2
        ;;
        -g )
            group=$2
            shift 2
        ;;
        *)
            break
        ;;
    esac 
done;

echo "\n======== Hardcoded RFMPI configurator ============"
echo "Overwritting ino file with following radio config:\n"
echo "RF_freq: RF12_"$freq"MHZ, nodeID: $node, networkGroup: $group\n"

var="#define RF_freq RF12_"$freq"MHZ"
sed "1s/.*/$var/" src/sketch.ino > tmp && mv tmp src/sketch.ino

var="const int nodeID = "$node";"
sed "2s/.*/$var/" src/sketch.ino > tmp && mv tmp src/sketch.ino

var="const int networkGroup = "$group";"
sed "3s/.*/$var/" src/sketch.ino > tmp && mv tmp src/sketch.ino
