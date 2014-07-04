"""

  This code is released under the GNU Affero General Public License.
  
  OpenEnergyMonitor project:
  http://openenergymonitor.org

"""

import struct
import logging

# Initialize logger
log = logging.getLogger("EmonHub")

# Initialize nodes data
nodelist = {}
defaultdatacode = 'i'


def check_datacode(datacode):

    # Data types & sizes (number of bytes)
    datacodes = {'b': '1', 'h': '2', 'i': '4', 'l': '4', 'q': '8', 'f': '4', 'd': '8',
                 'B': '1', 'H': '2', 'I': '4', 'L': '4', 'Q': '8', 'c': '1', '?': '1'}

    # if datacode is valid return the data size in bytes
    if datacode in datacodes:
        return int(datacodes[datacode])
    # if not valid return False
    else:
        return False


def decode(datacode, frame):
    # Ensure little-endian & standard sizes used
    e = '<'

    # set the base data type to bytes
    b = 'B'

    # get data size from data code
    s = int(check_datacode(datacode))

    try:
        result = struct.unpack(e + datacode[0], struct.pack(e + b*s, *frame))
        return result[0]
    except:
        log.info("Unable to decode as datacode incorrect for value")
        return False
        
def decode_frame(received):

    node = str(received[0])
    data = received[1:]

    if node in nodelist and 'codes' in nodelist[node]:
        datacodes = nodelist[node]['codes']
        datasizes = []
        for code in datacodes:
            datasizes.append(check_datacode(code))

        if len(data) != sum(datasizes):
            print "RX data length: "+str(len(data))+" is not valid for data codes "+str(datacodes)
            return False
        else:
            count = len(datacodes)
            datacode = False
    else:
        if node in nodelist and nodelist[node]['code']:
            datacode = nodelist[node]['code']
        else:
            datacode = defaultdatacode
        if not datacode:
            return received
        elif len(data) % check_datacode(datacode) != 0:
            print "RX data length: "+str(len(data))+" is not valid for data code "+str(datacode)
            return False
        else:
            count = len(data) / check_datacode(datacode)

    # Decode the string of data one value at a time into "decoded"
    decoded = []
    bytepos = int(0)
    #v = 0
    for i in range(0, count, 1):
        dc = datacode
        if not datacode:
            dc = datacodes[i]
        size = int(check_datacode(dc))
        value = decode(dc, [int(v) for v in data[bytepos:bytepos+size]])
        bytepos += size
        decoded.append(value)

    # Insert node ID before data
    decoded.insert(0, int(node))
    return decoded
