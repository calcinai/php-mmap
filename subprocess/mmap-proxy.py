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

while True:
    command = sys.stdin.read(1)

    if(command == 's'):
        address = struct.unpack('<L', sys.stdin.read(4))[0];
        mem.seek(address)
    elif(command == 'r'):
        length = struct.unpack('<L', sys.stdin.read(4))[0];
        sys.stdout.write(mem.read(length))
    elif(command == 'w'):
        length = struct.unpack('<L', sys.stdin.read(4))[0];
        mem.write(sys.stdin.read(length))
    elif(command == 'e'):
        #mem.flush() - not working everywhere
        mem.close()
        exit(0)
