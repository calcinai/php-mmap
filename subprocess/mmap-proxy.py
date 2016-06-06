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
        elif(command == 'r'):
            length = struct.unpack('<H', sys.stdin.read(2))[0];
            #Dirty hack to allow for differences in read methods
            #Read in blocks of 4 bytes
            #Need to find a resolution
            for x in range(0, length / 4):
                sys.stdout.write(str(struct.pack('L', struct.unpack_from('L', mem, mem.tell())[0])))
                mem.seek(4, os.SEEK_CUR)
            #sys.stdout.write(mem.read(length)) #issue with consistency
        elif(command == 't'):
            sys.stdout.write(str(struct.pack('<H', mem.tell())))
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
