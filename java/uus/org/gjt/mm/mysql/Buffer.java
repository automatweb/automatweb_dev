
/*
 * MM JDBC Drivers for MySQL
 *
 * $Id: Buffer.java,v 1.1 2002/09/26 15:54:50 kristo Exp $
 *
 * Copyright (C) 1998 Mark Matthews <mmatthew@worldserver.com>
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 * 
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place - Suite 330,
 * Boston, MA  02111-1307, USA.
 *
 * See the COPYING file located in the top-level-directory of
 * the archive of this library for complete text of license.
 */

package org.gjt.mm.mysql;

import java.io.*;
import java.net.*;
import java.util.*;
import java.sql.*;

/**
 * Buffer contains code to read and write packets from/to the MySQL server.
 *
 * @author Mark Matthews <mmatthew@worldserver.com>
 * @version $Id: Buffer.java,v 1.1 2002/09/26 15:54:50 kristo Exp $
 */

class Buffer 
{
    byte[] buf;
    int buf_length = 0;
    
    int pos = 0;
    int send_length = 0;

    static long NULL_LENGTH = -1;
    
    Buffer(byte[] buf)
    {
      this.buf = buf;
      buf_length = buf.length;
    }

    Buffer(int size)
    {
      buf = new byte[size];
      buf_length = buf.length;
      pos = MysqlIO.HEADER_LENGTH;
    }

    final void setBytes(byte[] buf)
    {
        send_length = buf_length;
        System.arraycopy(buf, 0, this.buf, 0, buf_length);
    }

    final byte readByte()
    {
      return buf[pos++];
    }

    final int readInt() 
    {
      return (int)(ub(buf[pos++])+(256*ub(buf[pos++])));
    }

    final int readLongInt() 
    {
      return (int)(ub(buf[pos++])+(256*ub(buf[pos++]))+(256*256*ub(buf[pos++])));
    }

    final long readLong() 
    {
      return (long)(ub(buf[pos++])+(256*ub(buf[pos++]))+(256*256*ub(buf[pos++]))+(256*256*256*ub(buf[pos++])));
    }

    final long readLongLong()
    {
	return ((ub(buf[pos++])) +
		(ub(buf[pos++]) << 8) +
		(ub(buf[pos++]) << 16) +
		(ub(buf[pos++]) << 24)) +
	       ((ub(buf[pos++]) ) +
	        (ub(buf[pos++]) << 8) +
	        (ub(buf[pos++]) << 16) +
	        (ub(buf[pos++]) << 24)
	         << 32 );
    }

    // Read n bytes depending
    
    final int readnBytes() 
    {
	switch(ub(buf[pos++])) {
	case 1 : return ub(buf[pos++]);
	case 2 : return this.readInt();
	case 3 : return this.readLongInt();
	case 4 : return (int)this.readLong();
	default : return 255;
	}
    }
    
    final long readLength() 
    {
	switch(ub(buf[pos])) {
	case 251 : {pos++; return (long) 0; }
	case 252 : {pos++; return (long) readInt();}
	case 253 : {pos++; return (long) readLongInt();}
	case 254 : {pos++; return (long) readLong();}
	default  : return (long) ub(buf[pos++]);
	}
    }

    // For MySQL servers > 3.22.5

    final long newReadLength()
    {
	switch(ub(buf[pos])) {
	case 251 : {pos++; return (long) 0; }
	case 252 : {pos++; return (long) readInt();} 
	case 253 : {pos++; return (long) readLongInt();}
	case 254 : {pos++; return (long) readLongLong();} // changed for 64 bit lengths
	default  : return (long) ub(buf[pos++]);
	}
    }

    final long readFieldLength()
    {
	switch(ub(buf[pos])) {
	case 251 : {pos++; return        NULL_LENGTH; }
	case 252 : {pos++; return (long) readInt();}
	case 253 : {pos++; return (long) readLongInt();}
	case 254 : {pos++; return (long) readLong();}
	default  : return (long) ub(buf[pos++]);
	}
    }

    // Read null-terminated string (native)
    
    final byte[] readByteArray()
    {
	return getNullTerminatedBytes();
    }
    
    // Read given-length string (native)
    
    final byte[] readLenByteArray()
    {
	long len = this.readFieldLength();
	
	if (len == NULL_LENGTH) {
	    return null;
	}
	if (len == 0) {
	    return new byte[0];
	}
	
	return getBytes((int)len);
    }
    
    //
    // Read a null-terminated string
    // 
    // To avoid alloc'ing a new byte array, we
    // do this by hand, rather than calling getNullTerminatedBytes()
    //
    
    final String readString() 
    {
       	int i = pos;
	int len = 0;
	
	while(buf[i] != 0 && i < buf_length) {
	    len++;
	    i++;
	}

	String S = new String(buf, pos, len);
	pos += (len + 1); // update cursor
	
	return S;
		
    }
    
    //
    // Read given-length string
    //
    // To avoid alloc'ing a byte array that will
    // quickly be thrown away, we do this by
    // hand instead of calling getBytes()
    //

    final String readLenString() 
    {
	long len = this.readFieldLength();
	
	if (len == NULL_LENGTH) {
	    return null;
	}
	if (len == 0) {
	    return "";
	}
		
	String S = new String(buf, pos, (int)len);

	pos += len; // update cursor

	return S;
    }
    
    // 
    // Read a given-length array of bytes
    //

    final byte[] getBytes(int len)
    {
	byte[] b = new byte[len];
	
	System.arraycopy(buf, pos, b, 0, len);
	
	pos += len; // update cursor
	
	return b;
    }

    //
    // Read a null-terminated array of bytes
    //

    final byte[] getNullTerminatedBytes()
    {
	int i = pos;
	int len = 0;
	
	while(buf[i] != 0 && i < buf_length) {
	    len++;
	    i++;
	}
	
	byte[] b = new byte[len];
	
	System.arraycopy(buf, pos, b, 0, len);
	
	pos += (len + 1); // update cursor
	
	return b;
    }
    
    final boolean isLastDataPacket() 
    {
	return ((buf_length <= 2) && (ub(buf[0]) == 254));
    }
    
    final void clear()
    {
	pos = MysqlIO.HEADER_LENGTH;
    }
    
    final void writeByte(byte b) 
    {
	buf[pos++]=b;
    }
    
    final void writeInt(int i) 
    {
	int r;
	r=i % 256;
	buf[pos++] = (byte)r;
	i = i / 256;
	buf[pos++] = (byte)i;
    }
    
    final void writeLongInt(int i) 
    {
	int r;
	r=i % 256;
	buf[pos++] = (byte)r;
	i = i / 256;
	r = i % 256;
	buf[pos++] = (byte)r;
	i = i / 256;
	buf[pos++] = (byte)i;
    }

    final void writeLong(long i) 
    {
	long r;
	r = i % 256;
	buf[pos++]=(byte)r;
	i = i / 256;
	r = i % 256;
	buf[pos++] = (byte)r;
	i = i / 256;
	r = i % 256;
	buf[pos++] = (byte)r;
	i = i / 256;
	buf[pos++] = (byte)i;
    }

    // Write null-terminated string

    final void writeString(String S) 
    {
	writeStringNoNull(S);
	buf[pos++] = 0;
    }

    // Write string, with no termination

    final void writeStringNoNull(String S) 
    {
	byte[] b = S.getBytes();
	
	int len = b.length;
	
	System.arraycopy(b, 0, buf, pos, len);
	pos += len;
    }

    // Write a String using the specified character
    // encoding
    
    final void writeStringNoNull(String S, String Encoding) 
	throws java.io.UnsupportedEncodingException
    {
	byte[] b = S.getBytes(Encoding);
	
	int len = b.length;
	
	System.arraycopy(b, 0, buf, pos, len);
	pos += len;
    }
    
    // Write a byte array
    final void writeBytesNoNull(byte[] Bytes)
    {
	int len = Bytes.length;
	
	System.arraycopy(Bytes, 0, buf, pos, len);
	
	pos += len;
    }
    
    final static int ub(byte b)
    {
	return b < 0 ? (int)(256 + b) : b;
    }
    
    final void dump()
    {
	int p = 0;
	
	int rows = buf_length / 8;

	for (int i = 0; i < rows; i++) {
	    int ptemp = p;
	    for (int j = 0; j < 8; j++) {
		String HexVal = Integer.toHexString((int)buf[ptemp]);
		if (HexVal.length() == 1) {
		    HexVal = "0" + HexVal;
		}
		System.out.print(HexVal + " ");
		ptemp++;
	    }

	    System.out.print("    ");

	    for (int j = 0; j < 8; j++) {
		if (buf[p] > 32 && buf[p] < 127) {
		    System.out.print((char)buf[p] + " ");
		}
		else {
		    System.out.print(". ");
		}
		p++;
	    }
		
	    System.out.println();
	}
	int n = 0;
	for (int i = p; i < buf_length; i++) {
	    String HexVal = Integer.toHexString((int)buf[i]);
	    if (HexVal.length() == 1) {
		HexVal = "0" + HexVal;
	    }
	    System.out.print(HexVal + " ");
	    n++;
	}
	for (int i = n; i < 8; i++) {
	    System.out.print("   ");
	}

	System.out.print("    ");

	for (int i = p; i < buf_length; i++) {
	    if (buf[i] > 32 && buf[i] < 127) {
		System.out.print((char)buf[i] + " ");
	    }
	    else {
		System.out.print(". ");
	    }
	}
	System.out.println();
    }    
}



