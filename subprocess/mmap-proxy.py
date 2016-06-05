import os
import sys
import mmap
import struct

filename = sys.argv[1]
block_size = int(sys.argv[2], 0)
offset = int(sys.argv[3], 0)

fd = os.open(filename, os.O_RDWR | os.O_SYNC)
mem = mmap.mmap(fd, block_size, offset=offset)
os.close(fd)

try:
    while True:
        command = sys.stdin.read(1)

        if(command == 's'):
            address = struct.unpack('<H', sys.stdin.read(2))[0];
            mem.seek(address)
            #sys.stdout.write(struct.pack('<H', mem.tell())) #To tell position, read seems to be very slow from php so to revisit.
        elif(command == 'r'):
            length = struct.unpack('<H', sys.stdin.read(2))[0];
            sys.stdout.write(mem.read(length))
        elif(command == 'w'):
            length = struct.unpack('<H', sys.stdin.read(2))[0];
            mem.write(sys.stdin.read(length))
        elif(command == 'e'):
            #mem.flush() #- not working everywhere
            mem.close()
            exit(0)

except KeyboardInterrupt:
    mem.close()
    exit();
