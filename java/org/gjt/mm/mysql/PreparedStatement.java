/*
 * MM JDBC Drivers for MySQL
 *
 * $Id: PreparedStatement.java,v 1.1 2002/06/10 15:59:40 kristo Exp $
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

/**
 * A SQL Statement is pre-compiled and stored in a PreparedStatement object.
 * This object can then be used to efficiently execute this statement multiple
 * times.
 *
 * <p><B>Note:</B> The setXXX methods for setting IN parameter values must
 * specify types that are compatible with the defined SQL type of the input
 * parameter.  For instance, if the IN parameter has SQL type Integer, then
 * setInt should be used.
 *
 * <p>If arbitrary parameter type conversions are required, then the setObject 
 * method should be used with a target SQL type.
 *
 * @see java.sql.ResultSet
 * @see java.sql.PreparedStatement
 * @author Mark Matthews <mmatthew@worldserver.com>
 * @version $Id: PreparedStatement.java,v 1.1 2002/06/10 15:59:40 kristo Exp $
 */

package org.gjt.mm.mysql;

import java.io.*;
import java.math.*;
import java.sql.*;
import java.text.*;
import java.util.*;

public class PreparedStatement extends org.gjt.mm.mysql.Statement 
    implements java.sql.PreparedStatement  
{

    private String        _Sql              = null;
    private String[]      _TemplateStrings  = null;
    private String[]      _ParameterStrings = null;
    private InputStream[] _ParameterStreams = null;
    private boolean[]     _IsStream         = null;
    private Connection    _Conn             = null;

    private boolean       _do_concat        = false;
    private boolean       _has_limit_clause = false;
    
    /**
     * Constructor for the PreparedStatement class.
     * Split the SQL statement into segments - separated by the arguments.
     * When we rebuild the thing with the arguments, we can substitute the
     * args and join the whole thing together.
     *
     * @param conn the instanatiating connection
     * @param sql the SQL statement with ? for IN markers
     * @exception java.sql.SQLException if something bad occurs
     */

    public PreparedStatement(Connection Conn, String Sql, String Catalog) throws java.sql.SQLException
    {
	super(Conn, Catalog);

	if (Sql.indexOf("||") != -1) {
	    _do_concat = true;
	}
	
	_has_limit_clause = (Sql.toUpperCase().indexOf("LIMIT") != -1);

	Vector V = new Vector();
	boolean inQuotes = false;
	int lastParmEnd = 0, i;

	_Sql = Sql;
	_Conn = Conn;

	for (i = 0; i < _Sql.length(); ++i) {
	    int c = _Sql.charAt(i);
		    
	    if (c == '\'')
		inQuotes = !inQuotes;
	    if (c == '?' && !inQuotes)
		{
		    V.addElement(_Sql.substring (lastParmEnd, i));
		    lastParmEnd = i + 1;
		}
	}
	V.addElement(_Sql.substring (lastParmEnd, _Sql.length()));

	_TemplateStrings = new String[V.size()];
	_ParameterStrings = new String[V.size() - 1];
	_ParameterStreams = new InputStream[V.size() - 1];
	_IsStream         = new boolean[V.size() - 1];
	clearParameters();

	for (i = 0 ; i < _TemplateStrings.length; ++i) {
	    _TemplateStrings[i] = (String)V.elementAt(i);
	}

	for (int j = 0; j < _ParameterStrings.length; j++) {
	    _IsStream[j] = false;
	}
    }

    /**
     * A Prepared SQL query is executed and its ResultSet is returned
     *
     * @return a ResultSet that contains the data produced by the
     *      query - never null
     * @exception java.sql.SQLException if a database access error occurs
     */

    public java.sql.ResultSet executeQuery() throws java.sql.SQLException
    {
	boolean do_escape_processing = _escapeProcessing;
	_escapeProcessing = false; // Do escape processing part-by-part
   
	Buffer Packet = new Buffer(MysqlIO.getMaxBuf());
	Packet.writeByte((byte)MysqlDefs.QUERY);
	      
	String Encoding = null;

	if (_Conn.useUnicode()) {
	    Encoding = _Conn.getEncoding();
	}

	try {
	    for (int i = 0 ; i < _ParameterStrings.length ; ++i) {

		if (_ParameterStrings[i] == null && 
		    (_IsStream[i] && _ParameterStreams[i] == null)) {
		    throw new java.sql.SQLException("No value specified for parameter " + (i + 1), "07001");
		}

		if (Encoding != null) {
		    Packet.writeStringNoNull(_TemplateStrings[i], Encoding);
		}
		else {
		    Packet.writeStringNoNull(_TemplateStrings[i]);
		}
	    
		if (_IsStream[i]) {
		    Packet.writeBytesNoNull(streamToBytes(_ParameterStreams[i]));
		}
		else {
		    if (do_escape_processing) {
			_ParameterStrings[i] = _Escaper.escapeSQL(_ParameterStrings[i]);
		    }
			
		    if (Encoding != null) {
			Packet.writeStringNoNull(_ParameterStrings[i], Encoding);
		    }
		    else {
			Packet.writeStringNoNull(_ParameterStrings[i]);
		    }
		}
	    }

	    if (Encoding != null) {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length], Encoding);
	    }
	    else {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length]);
	    }
	}
	catch (java.io.UnsupportedEncodingException UE) {
	    throw new SQLException("Unsupported character encoding '" + Encoding + "'");
	}
		
	if (_Results != null) {
	    _Results.close();
	}

	// We need to execute this all together
	// So synchronize on the Connection's mutex (because 
	// even queries going through there synchronize
	// on the same mutex.

	synchronized (_Conn.getMutex()) {
	    String OldCatalog = null;

	    if (!_Conn.getCatalog().equals(_Catalog)) {
		OldCatalog = _Conn.getCatalog();
		_Conn.setCatalog(_Catalog);
	    }

	    if (_Conn.useMaxRows()) {

		// If there isn't a limit clause in the SQL
		// then limit the number of rows to return in 
		// an efficient manner. Only do this if
		// setMaxRows() hasn't been used on any Statements
		// generated from the current Connection (saves
		// a query, and network traffic).

		if (_has_limit_clause) { 
		    _Results = _Conn.execSQL(null, _max_rows, Packet);
		}
		else {
		    if (_max_rows <= 0) {
			_Conn.execSQL("SET OPTION SQL_SELECT_LIMIT=" 
				      + MysqlDefs.MAX_ROWS, -1);
		    }
		    else {
			_Conn.execSQL("SET OPTION SQL_SELECT_LIMIT=" + _max_rows,-1);
		    }
			        		            
		    _Results = _Conn.execSQL(null, -1, Packet);

		    if (OldCatalog != null) {
			_Conn.setCatalog(OldCatalog);
		    }
		}
	    }
	    else {
		_Results = _Conn.execSQL(null, -1, Packet);	    
	    }

	    if (OldCatalog != null) {
		_Conn.setCatalog(OldCatalog);
	    }
	}

	_last_insert_id = _Results.getUpdateID();
	_NextResults = _Results;
	_Results.setConnection(_Conn);
	
	_escapeProcessing = do_escape_processing;
	
	return _Results;
    }

    /**
     * Execute a SQL INSERT, UPDATE or DELETE statement.  In addition,
     * SQL statements that return nothing such as SQL DDL statements can
     * be executed.
     *
     * @return either the row count for INSERT, UPDATE or DELETE; or
     *      0 for SQL statements that return nothing.
     * @exception java.sql.SQLException if a database access error occurs
     */

    public int executeUpdate() throws java.sql.SQLException
    {
	boolean do_escape_processing = _escapeProcessing;
	_escapeProcessing = false;
		            
	Buffer Packet = new Buffer(MysqlIO.getMaxBuf());
	Packet.writeByte((byte)MysqlDefs.QUERY);
	
	String Encoding = null;

	if (_Conn.useUnicode()) {
	    Encoding = _Conn.getEncoding();
	}

	try {
	    for (int i = 0 ; i < _ParameterStrings.length ; ++i) {
		if (_ParameterStrings[i] == null && 
		    (_IsStream[i] && _ParameterStreams[i] == null)) {
		    throw new java.sql.SQLException("No value specified for parameter " + (i + 1), "07001");
		}

		if (Encoding != null) {
		    Packet.writeStringNoNull(_TemplateStrings[i], Encoding);
		}
		else {
		    Packet.writeStringNoNull(_TemplateStrings[i]);
		}
		    
		if (_IsStream[i]) {
		    Packet.writeBytesNoNull(streamToBytes(_ParameterStreams[i]));
		}
		else {
		    if (do_escape_processing) {
			_ParameterStrings[i] = _Escaper.escapeSQL(_ParameterStrings[i]);
		    }
			
		    if (Encoding != null) {
			Packet.writeStringNoNull(_ParameterStrings[i], Encoding);
		    }
		    else {
			Packet.writeStringNoNull(_ParameterStrings[i]);
		    }
		}
	    }
		
	    if (Encoding != null) {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length], Encoding);
	    }
	    else {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length]);
	    }
	}
	catch (java.io.UnsupportedEncodingException UE) {
	    throw new SQLException("Unsupported character encoding '" + Encoding + "'");
	}
		
	// The checking and changing of catalogs
        // must happen in sequence, so synchronize
        // on the same mutex that _Conn is using

	ResultSet RS = null;

	synchronized (_Conn.getMutex()) {
	    String OldCatalog = null;

	    if (!_Conn.getCatalog().equals(_Catalog)) {
		OldCatalog = _Conn.getCatalog();
		_Conn.setCatalog(_Catalog);
	    }

	    RS = _Conn.execSQL(null, -1, Packet);

	    if (OldCatalog != null) {
		_Conn.setCatalog(OldCatalog);
	    }
	}
	
	if (RS.reallyResult()) {
	    throw new java.sql.SQLException("Results returned for UPDATE ONLY.", "01S03");
	}
	else {
	    _update_count = RS.getUpdateCount();
		    
	    int truncated_update_count = 0;

	    if (_update_count > Integer.MAX_VALUE) {
		truncated_update_count = Integer.MAX_VALUE;
	    }
	    else {
		truncated_update_count = (int)_update_count;
	    }

	    _last_insert_id = RS.getUpdateID();
		    
	    _escapeProcessing = do_escape_processing;
			
	    return truncated_update_count;
	}
    }       

    /**
     * Set a parameter to SQL NULL
     *
     * <p><B>Note:</B> You must specify the parameters SQL type (although
     * PostgreSQL ignores it)
     *
     * @param parameterIndex the first parameter is 1, etc...
     * @param sqlType the SQL type code defined in java.sql.Types
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setNull(int parameterIndex, int sqlType) throws java.sql.SQLException
    {
	set(parameterIndex, "null");
    }

    /**
     * Set a parameter to a Java boolean value.  The driver converts this
     * to a SQL BIT value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setBoolean(int parameterIndex, boolean x) throws java.sql.SQLException
    {
	set(parameterIndex, x ? "'t'" : "'f'");
    }

    /**
     * Set a parameter to a Java byte value.  The driver converts this to
     * a SQL TINYINT value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setByte(int parameterIndex, byte x) throws java.sql.SQLException
    {
	set(parameterIndex, (new Integer(x)).toString());
    }

    /**
     * Set a parameter to a Java short value.  The driver converts this
     * to a SQL SMALLINT value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setShort(int parameterIndex, short x) throws java.sql.SQLException
    {
	set(parameterIndex, (new Integer(x)).toString());
    }

    /**
     * Set a parameter to a Java int value.  The driver converts this to
     * a SQL INTEGER value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setInt(int parameterIndex, int x) throws java.sql.SQLException
    {
	set(parameterIndex, (new Integer(x)).toString());
    }

    /**
     * Set a parameter to a Java long value.  The driver converts this to
     * a SQL BIGINT value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setLong(int parameterIndex, long x) throws java.sql.SQLException
    {
	set(parameterIndex, (new Long(x)).toString());
    }

    /**
     * Set a parameter to a Java float value.  The driver converts this
     * to a SQL FLOAT value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setFloat(int parameterIndex, float x) throws java.sql.SQLException
    {
	set(parameterIndex, (new Float(x)).toString());
    }

    /**
     * Set a parameter to a Java double value.  The driver converts this
     * to a SQL DOUBLE value when it sends it to the database
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setDouble(int parameterIndex, double x) throws java.sql.SQLException
    {
	set(parameterIndex, _DoubleFormatter.format(x));
	// - Fix for large doubles by Steve Ferguson
    }

    /**
     * Set a parameter to a java.lang.BigDecimal value.  The driver
     * converts this to a SQL NUMERIC value when it sends it to the
     * database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */
    public void setBigDecimal(int parameterIndex, BigDecimal X) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.DECIMAL);
	}
	else {
	    set(parameterIndex, X.toString());
	}
    }

    /**
     * Set a parameter to a Java String value.  The driver converts this
     * to a SQL VARCHAR or LONGVARCHAR value (depending on the arguments
     * size relative to the driver's limits on VARCHARs) when it sends it
     * to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setString(int parameterIndex, String X) throws java.sql.SQLException
    {
	// if the passed string is null, then set this column to null
		
	if(X == null) {
	    set(parameterIndex, "null");
	}
	else {
	    StringBuffer B = new StringBuffer();
	    int i;
			        
	    B.append('\'');

	    for (i = 0 ; i < X.length() ; ++i) {
		char c = X.charAt(i);
				
		if (c == '\\' || c == '\'' || c == '"') {
		    B.append((char)'\\');
		}
		B.append(c);
	    }
			
	    B.append('\'');
	    set(parameterIndex, B.toString());
	}
    }

    /**
     * Set a parameter to a Java array of bytes.  The driver converts this
     * to a SQL VARBINARY or LONGVARBINARY (depending on the argument's
     * size relative to the driver's limits on VARBINARYs) when it sends
     * it to the database.
     *
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setBytes(int parameterIndex, byte x[]) throws java.sql.SQLException
    {
	if (x == null) {
	    setNull(parameterIndex, java.sql.Types.BINARY);
	}
	else {
	    ByteArrayInputStream BIn = new ByteArrayInputStream(x);
	    setBinaryStream(parameterIndex, BIn, x.length);
	}
    }

    /**
     * Set a parameter to a java.sql.Date value.  The driver converts this
     * to a SQL DATE value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setDate(int parameterIndex, java.sql.Date X) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.DATE);
	}
	else {
	    SimpleDateFormat DF = new SimpleDateFormat("''yyyy-MM-dd''");
	    
	    set(parameterIndex, DF.format(X));
	}
    }

    /**
     * Set a parameter to a java.sql.Time value.  The driver converts
     * this to a SQL TIME value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...));
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setTime(int parameterIndex, Time X) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.TIME);
	}
	else {
	    set(parameterIndex, "'" + X.toString() + "'");
	}
    }

    /**
     * Set a parameter to a java.sql.Timestamp value.  The driver converts
     * this to a SQL TIMESTAMP value when it sends it to the database.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setTimestamp(int parameterIndex, Timestamp X) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.TIMESTAMP);
	}
	else {
	    EscapeProcessor EP = new EscapeProcessor();
	    String TimestampString = EP.escapeSQL("{ts '" + X.toString() + "'}");
	    set(parameterIndex, TimestampString);
	}
    }

    /**
     * When a very large ASCII value is input to a LONGVARCHAR parameter,
     * it may be more practical to send it via a java.io.InputStream.
     * JDBC will read the data from the stream as needed, until it reaches
     * end-of-file.  The JDBC driver will do any necessary conversion from
     * ASCII to the database char format.
     *
     * <P><B>Note:</B> This stream object can either be a standard Java
     * stream object or your own subclass that implements the standard
     * interface.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @param length the number of bytes in the stream
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setAsciiStream(int parameterIndex, InputStream X, int length) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.VARCHAR);
	}
	else {
	    setBinaryStream(parameterIndex, X, length);
	}
    }

    /**
     * When a very large Unicode value is input to a LONGVARCHAR parameter,
     * it may be more practical to send it via a java.io.InputStream.
     * JDBC will read the data from the stream as needed, until it reaches
     * end-of-file.  The JDBC driver will do any necessary conversion from
     * UNICODE to the database char format.
     *
     * <P><B>Note:</B> This stream object can either be a standard Java
     * stream object or your own subclass that implements the standard
     * interface.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setUnicodeStream(int parameterIndex, InputStream X, int length) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.VARCHAR);
	}
	else {
	    setBinaryStream(parameterIndex, X, length);
	}
    }

    /**
     * When a very large binary value is input to a LONGVARBINARY parameter,
     * it may be more practical to send it via a java.io.InputStream.
     * JDBC will read the data from the stream as needed, until it reaches
     * end-of-file.  
     *
     * <P><B>Note:</B> This stream object can either be a standard Java
     * stream object or your own subclass that implements the standard
     * interface.
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the parameter value
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setBinaryStream(int parameterIndex, InputStream X, int length) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.BINARY);
	}
	else {
	    if (parameterIndex < 1 || 
		parameterIndex > _TemplateStrings.length) {
		throw new java.sql.SQLException("Parameter index out of range (" + parameterIndex + " > " + _TemplateStrings.length + ")", "S1009");
	    }
	    _ParameterStreams[parameterIndex - 1] = X;
	    _IsStream[parameterIndex - 1] = true;
	}
    }

    /**
     * In general, parameter values remain in force for repeated used of a
     * Statement.  Setting a parameter value automatically clears its
     * previous value.  However, in coms cases, it is useful to immediately
     * release the resources used by the current parameter values; this
     * can be done by calling clearParameters
     *
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void clearParameters() throws java.sql.SQLException
    {
	for (int i = 0 ; i < _ParameterStrings.length ; i++) {
	    _ParameterStrings[i] = null;
	    _ParameterStreams[i] = null;
	    _IsStream[i] = false;
	}

    }

    /**
     * Set the value of a parameter using an object; use the java.lang
     * equivalent objects for integral values.
     *
     * <P>The given Java object will be converted to the targetSqlType before
     * being sent to the database.
     *
     * <P>note that this method may be used to pass database-specific
     * abstract data types.  This is done by using a Driver-specific
     * Java type and using a targetSqlType of java.sql.Types.OTHER
     *
     * @param parameterIndex the first parameter is 1...
     * @param x the object containing the input parameter value
     * @param targetSqlType The SQL type to be send to the database
     * @param scale For java.sql.Types.DECIMAL or java.sql.Types.NUMERIC
     *      types this is the number of digits after the decimal.  For 
     *      all other types this value will be ignored.
     * @exception java.sql.SQLException if a database access error occurs
     */

    public void setObject(int parameterIndex, Object X, int targetSqlType, int scale) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.OTHER);
	}
	else try {
	    switch (targetSqlType)
		{
		case Types.TINYINT:
		case Types.SMALLINT:
		case Types.INTEGER:
		case Types.BIGINT:
		case Types.REAL:
		case Types.FLOAT:
		case Types.DOUBLE:
		case Types.DECIMAL:
		case Types.NUMERIC:
                    Number X_as_number;
		    if (X instanceof Boolean)
			X_as_number=((Boolean)X).booleanValue() ? new Integer(1) : new Integer(0);
		    else if (X instanceof String)
                        switch (targetSqlType) {
			case Types.TINYINT:
			case Types.SMALLINT:
			case Types.INTEGER:
			    X_as_number=Integer.valueOf((String)X);
			    break;
			case Types.BIGINT:
			    X_as_number=Long.valueOf((String)X);
			    break;
			case Types.REAL:
			    X_as_number=Float.valueOf((String)X);
			    break;
			case Types.FLOAT:
			case Types.DOUBLE:
			    X_as_number=Double.valueOf((String)X);
			    break;
			case Types.DECIMAL:
			case Types.NUMERIC:
			default:
			    X_as_number=new java.math.BigDecimal((String)X);
			}
		    else
			X_as_number=(Number)X;
                    switch (targetSqlType) {
		    case Types.TINYINT:
		    case Types.SMALLINT:
		    case Types.INTEGER:
			setInt(parameterIndex, X_as_number.intValue());
			break;
		    case Types.BIGINT:
			setLong(parameterIndex, X_as_number.longValue());
			break;
		    case Types.REAL:
			setFloat(parameterIndex, X_as_number.floatValue());
			break;
		    case Types.FLOAT:
		    case Types.DOUBLE:
			setDouble(parameterIndex, X_as_number.doubleValue());
			break;
		    case Types.DECIMAL:
		    case Types.NUMERIC:
		    default:
			if (X_as_number instanceof java.math.BigDecimal)
			    setBigDecimal(parameterIndex, (java.math.BigDecimal)X_as_number);
			else if (X_as_number instanceof java.math.BigInteger)
			    setBigDecimal(parameterIndex, new java.math.BigDecimal((java.math.BigInteger)X_as_number,scale));
			else
			    setBigDecimal(parameterIndex, new java.math.BigDecimal(X_as_number.doubleValue()));
			break;
		    }
		    break;
		case Types.CHAR:
		case Types.VARCHAR:
		case Types.LONGVARCHAR:
		    setString(parameterIndex, X.toString());
		    break;
		case Types.BINARY:
		case Types.VARBINARY:
		case Types.LONGVARBINARY:
		    if (X instanceof String)
			setBytes(parameterIndex, ((String)X).getBytes());
		    else
			setBytes(parameterIndex, (byte[])X);
		    break;
		case Types.DATE:
		case Types.TIMESTAMP:
                    java.util.Date X_as_date;
		    if (X instanceof String) {
                        ParsePosition pp=new ParsePosition(0);
			java.text.DateFormat sdf=new java.text.SimpleDateFormat(getDateTimePattern((String)X,false));
                        X_as_date=sdf.parse((String)X,pp);
		    } else
			X_as_date=(java.util.Date)X;
		    switch(targetSqlType) {
		    case Types.DATE:
			if (X_as_date instanceof java.sql.Date)
			    setDate(parameterIndex,(java.sql.Date)X_as_date);
			else
			    setDate(parameterIndex,new java.sql.Date(X_as_date.getTime()));
			break;
		    case Types.TIMESTAMP:
			if (X_as_date instanceof java.sql.Timestamp)
			    setTimestamp(parameterIndex,(java.sql.Timestamp)X_as_date);
			else
			    setTimestamp(parameterIndex,new java.sql.Timestamp(X_as_date.getTime()));
			break;
		    }
		    break;
		case Types.TIME:
		    if (X instanceof String) {
			java.text.DateFormat sdf=new java.text.SimpleDateFormat(getDateTimePattern((String)X,true));
			setTime(parameterIndex,new java.sql.Time(sdf.parse((String)X).getTime()));
		    } else
			setTime(parameterIndex,(java.sql.Time)X);
		    break;
		case Types.OTHER:
		    try {
			ByteArrayOutputStream BytesOut = new ByteArrayOutputStream();
			ObjectOutputStream ObjectOut = new ObjectOutputStream(BytesOut);
			ObjectOut.writeObject(X);
			ObjectOut.flush();
			ObjectOut.close();
			BytesOut.flush();
			BytesOut.close();
      
			byte[] buf = BytesOut.toByteArray();
			ByteArrayInputStream BytesIn = new ByteArrayInputStream(buf);
			setBinaryStream(parameterIndex, BytesIn, -1);
		    }
		    catch (Exception E) {
			throw new java.sql.SQLException("Invalid argument value: " + E.getClass().getName(), "S1009");
		    }
		    break;
		default:
		    throw new java.sql.SQLException("Unknown Types value", "S1000");
		}
        } catch (Exception ex) {
	    if (ex instanceof java.sql.SQLException) throw (java.sql.SQLException)ex;
	    else throw new java.sql.SQLException("Cannot convert "+X.getClass().toString()+" to SQL type requested", "S1000");
	}
    }

    public void setObject(int parameterIndex, Object X, int targetSqlType) throws java.sql.SQLException
    {
	setObject(parameterIndex, X, targetSqlType, 0);
    }

    public void setObject(int parameterIndex, Object X) throws java.sql.SQLException
    {
	if (X == null) {
	    setNull(parameterIndex, java.sql.Types.OTHER);
	}
	else {
	    if (X instanceof String)
		setString(parameterIndex, (String)X);
	    else if (X instanceof BigDecimal)
		setBigDecimal(parameterIndex, (BigDecimal)X);
	    else if (X instanceof Integer)
		setInt(parameterIndex, ((Integer)X).intValue());
	    else if (X instanceof Long)
		setLong(parameterIndex, ((Long)X).longValue());
	    else if (X instanceof Float)
		setFloat(parameterIndex, ((Float)X).floatValue());
	    else if (X instanceof Double)
		setDouble(parameterIndex, ((Double)X).doubleValue());
	    else if (X instanceof byte[])
		setBytes(parameterIndex, (byte[])X);
	    else if (X instanceof java.sql.Date)
		setDate(parameterIndex, (java.sql.Date)X);
	    else if (X instanceof Time)
		setTime(parameterIndex, (Time)X);
	    else if (X instanceof Timestamp)
		setTimestamp(parameterIndex, (Timestamp)X);
	    else if (X instanceof Boolean)
		setBoolean(parameterIndex, ((Boolean)X).booleanValue());
	    else {
		try {
		    ByteArrayOutputStream BytesOut = new ByteArrayOutputStream();
		    ObjectOutputStream ObjectOut = new ObjectOutputStream(BytesOut);
		    ObjectOut.writeObject(X);
		    ObjectOut.flush();
		    ObjectOut.close();
		    BytesOut.flush();
		    BytesOut.close();
		    
		    byte[] buf = BytesOut.toByteArray();
		    ByteArrayInputStream BytesIn = new ByteArrayInputStream(buf);
		    setBinaryStream(parameterIndex, BytesIn, -1);
		}
		catch (Exception E) {
		    throw new java.sql.SQLException("Invalid argument value: " + E.getClass().getName(), "S1009");
		}
	    }
	}
    }
    
    /**
     * Some prepared statements return multiple results; the execute method
     * handles these complex statements as well as the simpler form of 
     * statements handled by executeQuery and executeUpdate
     *
     * @return true if the next result is a ResultSet; false if it is an
     *      update count or there are no more results
     * @exception java.sql.SQLException if a database access error occurs
     */

    public boolean execute() throws java.sql.SQLException
    {
	boolean do_escape_processing = _escapeProcessing;
	_escapeProcessing = false;

	
	Buffer Packet = new Buffer(MysqlIO.getMaxBuf());
	Packet.writeByte((byte)MysqlDefs.QUERY);
		            
	String Encoding = null;

	if (_Conn.useUnicode()) {
	    Encoding = _Conn.getEncoding();
	}

	try {
	    for (int i = 0 ; i < _ParameterStrings.length ; ++i) {
		if (_ParameterStrings[i] == null && 
		    (_IsStream[i] && _ParameterStreams[i] == null)) {
		    throw new java.sql.SQLException("No value specified for parameter " + (i + 1));
		}

		if (Encoding != null) {
		    Packet.writeStringNoNull(_TemplateStrings[i], Encoding);
		}
		else {
		    Packet.writeStringNoNull(_TemplateStrings[i]);
		}
		    
		if (_IsStream[i]) {
		    Packet.writeBytesNoNull(streamToBytes(_ParameterStreams[i]));
		}
		else {
		    if (do_escape_processing) {
			_ParameterStrings[i] = _Escaper.escapeSQL(_ParameterStrings[i]);
		    }
			
		    if (Encoding != null) {
			Packet.writeStringNoNull(_ParameterStrings[i], Encoding);
		    }
		    else {
			Packet.writeStringNoNull(_ParameterStrings[i]);
		    }
		}
	    }
		
	    if (Encoding != null) {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length], Encoding);
	    }
	    else {
		Packet.writeStringNoNull(_TemplateStrings[_ParameterStrings.length]);
	    }
	}
	catch (java.io.UnsupportedEncodingException UE) {
	    throw new SQLException("Unsupported character encoding '" + Encoding + "'");
	}

	ResultSet RS = null;

	
	synchronized (_Conn.getMutex()) {
	    String OldCatalog = null;

	    if (!_Conn.getCatalog().equals(_Catalog)) {
		OldCatalog = _Conn.getCatalog();
		_Conn.setCatalog(_Catalog);
	    }

	    // If there isn't a limit clause in the SQL
	    // then limit the number of rows to return in 
	    // an efficient manner. Only do this if
	    // setMaxRows() hasn't been used on any Statements
	    // generated from the current Connection (saves
	    // a query, and network traffic).
	
	    if (_Conn.useMaxRows()) {
		if (_has_limit_clause) { 
		    RS = _Conn.execSQL(null, _max_rows, Packet);
		}
		else {
		    if (_max_rows <= 0) {
			_Conn.execSQL("SET OPTION SQL_SELECT_LIMIT=" 
				      + MysqlDefs.MAX_ROWS, -1);
		    }
		    else {
			_Conn.execSQL("SET OPTION SQL_SELECT_LIMIT=" + _max_rows,-1);
		    }                
		    RS = _Conn.execSQL(null, -1, Packet);
		}
	    }
	    else {
		RS = _Conn.execSQL(null, -1, Packet);	    
	    }

	    if (OldCatalog != null) {
		_Conn.setCatalog(OldCatalog);
	    }
	}
		    
	_last_insert_id = RS.getUpdateID();

	if (RS != null) {
	    _Results = RS;
	}

	_escapeProcessing = do_escape_processing;
	
	RS.setConnection(_Conn);

	return (RS != null && RS.reallyResult());
    }

    public String toString()
    {
	String Encoding = null;

	if (_Conn.useUnicode()) {
	    Encoding = _Conn.getEncoding();
	}

	StringBuffer SB = new StringBuffer();
	SB.append(super.toString());
	SB.append(": ");
	
	try {
	    for (int i = 0 ; i < _ParameterStrings.length ; ++i) {
	    
		if (Encoding != null) {
		    SB.append(new String(_TemplateStrings[i].getBytes(), Encoding));
		}
		else {
		    SB.append(_TemplateStrings[i]);
		}
	    	if (_ParameterStrings[i] == null && 
		    (_IsStream[i] && _ParameterStreams[i] == null)) {
		    SB.append("** NOT SPECIFIED **");
		}
		else if (_IsStream[i]) {
		    SB.append("** STREAM DATA **");
		}
		else {
		    if (_escapeProcessing) {
			try {
			    _ParameterStrings[i] = _Escaper.escapeSQL(_ParameterStrings[i]);
			}
			catch (SQLException SQE) {}
		    }
		
		    if (Encoding != null) {
			SB.append(new String(_ParameterStrings[i].getBytes(), Encoding));
		    }
		    else {
			SB.append(_ParameterStrings[i]);
		    }
		}
	    }
	
	    if (Encoding != null) {
		SB.append(new String(_TemplateStrings[_ParameterStrings.length].getBytes(), Encoding));
	    }
	    else {
		SB.append(_TemplateStrings[_ParameterStrings.length]);
	    }
	}
	catch (java.io.UnsupportedEncodingException UE) {
	    SB.append("\n\n** WARNING **\n\n Unsupported character encoding '");
	    SB.append(Encoding);
	    SB.append("'");
	}

	return SB.toString();
    }
	
	      
    /**
     * There are a lot of setXXX classes which all basically do
     * the same thing.  We need a method which actually does the
     * set for us.
     *
     * @param paramIndex the index into the inString
     * @param s a string to be stored
     * @exception java.sql.SQLException if something goes wrong
     */

    private final void set(int paramIndex, String S) throws java.sql.SQLException
    {
	if (paramIndex < 1 || paramIndex > _TemplateStrings.length) {
	    throw new java.sql.SQLException("Parameter index out of range (" + paramIndex + " > " + _TemplateStrings.length + ").", "S1009");
	}
	_ParameterStrings[paramIndex - 1] = S;
    }

    private final int readblock(InputStream i,byte[] b) throws java.sql.SQLException
    {
	try  {
	    return i.read(b);
	}
	catch (Throwable E) {
            throw new java.sql.SQLException("Error reading from InputStream " +
					    E.getClass().getName(), "S1000");
	}
    }

    private final void escapeblock(byte[] buf,ByteArrayOutputStream BytesOut,int
				   size)
    {
	int c =0;
    
	for (int i=0;i<size;i++) {
	    byte b = buf[i];
	    if (b == '\0') {
		BytesOut.write('\\');
		BytesOut.write('0');
	    }
	    else {
		if (b == '\\' || b == '\'' || b == '"') {
		    BytesOut.write('\\');
		}
		BytesOut.write(b);
	    }
	}
    }

    /**
     * For the setXXXStream() methods. Basically converts an
     * InputStream into a String. Not very efficient, but it
     * works.
     *
     */
     
    private final byte[] streamToBytes(InputStream In) throws java.sql.SQLException
    {
	byte[] bi=new byte[128*1024];
	ByteArrayOutputStream BytesOut = new ByteArrayOutputStream();
	int bc = readblock(In,bi);
 
	BytesOut.write('\'');
   
	while (bc > 0) {
	    escapeblock(bi,BytesOut,bc);
	    bc = readblock(In,bi);
	}
   
	BytesOut.write('\'');
   
	return BytesOut.toByteArray();
    }

    private final char getSuccessor(char c,int n) 
    {
	return  (c=='y' && n==2) ? 'X' : //ym
	    ((c=='y' && n<4) ? 'y' :
	     ((c=='y') ? 'M' :
	      ((c=='M' && n==2) ? 'Y' : //Md
	       ((c=='M' && n<3) ? 'M' :
		((c=='M') ? 'd' :
		 ((c=='d' && n<2) ? 'd' :
		  ((c=='d') ? 'h' :
		   ((c=='h' && n<2) ? 'h' :
		    ((c=='h') ? 'm' :
		     ((c=='m' && n<2) ? 'm' :
		      ((c=='m') ? 's' :
		       ((c=='s' && n<2) ? 's' : 'W' ))))))))))));
    }

    private final String getDateTimePattern(String dt,boolean toTime) throws Exception 
    {
	int n,z,count,maxvecs;
	char c,separator;
	StringReader reader=new StringReader(dt+" ");
	Vector vec=new Vector();
	Vector vec_removelist=new Vector();
	Object[] nv=new Object[3];
	Object[] v;
	nv[0]=new Character('y');
	nv[1]=new StringBuffer();
	nv[2]=new Integer(0);
	vec.addElement(nv);
	if (toTime) {
	    nv=new Object[3];
	    nv[0]=new Character('h');
	    nv[1]=new StringBuffer();
	    nv[2]=new Integer(0);
	    vec.addElement(nv);
	}
	while ((z=reader.read())!=-1) {
	    separator=(char)z;
	    maxvecs=vec.size();
	    for(count=0;count<maxvecs;count++) {
		v=(Object [])vec.elementAt(count);
		n=((Integer)v[2]).intValue();
		c = getSuccessor(((Character)v[0]).charValue(),n);
		if (!Character.isLetterOrDigit(separator)) {
		    if ((c==((Character)v[0]).charValue())&&(c!='S'))
			vec_removelist.addElement(v);
		    else {
			((StringBuffer)v[1]).append(separator);
			if (c=='X' || c=='Y')
			    v[2]=new Integer(4);
		    }
		} else {
		    if (c=='X') {
			c='y';
			nv=new Object[3];
			nv[1]=(new StringBuffer(((StringBuffer)v[1]).toString())).append('M');
			nv[0]=new Character('M');
			nv[2]=new Integer(1);
			vec.addElement(nv);
		    } else if (c=='Y') {
			c='M';
			nv=new Object[3];
			nv[1]=(new StringBuffer(((StringBuffer)v[1]).toString())).append('d');
			nv[0]=new Character('d');
			nv[2]=new Integer(1);
			vec.addElement(nv);
		    }
		    ((StringBuffer)v[1]).append(c);
			
		    if (c==((Character)v[0]).charValue())
			v[2]=new Integer(n+1);
		    else {
			v[0]=new Character(c);
			v[2]=new Integer(1);
		    }
		}
	    }
	    for(Enumeration en=vec_removelist.elements();
		en.hasMoreElements();) {
		v=(Object [])en.nextElement();
		vec.removeElement(v);
	    }
	    vec_removelist.removeAllElements();
	}
	for(Enumeration en=vec.elements();en.hasMoreElements();) {
	    v=(Object [])en.nextElement();
	    c=((Character)v[0]).charValue();
	    n=((Integer)v[2]).intValue();
	    boolean bk=getSuccessor(c,n)!=c;
	    boolean atEnd=((c=='s'||c=='m'||(c=='h' && toTime))&&bk);
	    boolean finishesAtDate=(bk&&(c=='d')&& !toTime);
	    boolean containsEnd=(((StringBuffer)v[1]).toString().indexOf('W')!=-1);
	    if ((!atEnd && !finishesAtDate) || (containsEnd)) {
		vec_removelist.addElement(v);
	    }
	}
	for(Enumeration en=vec_removelist.elements();en.hasMoreElements();)
	    vec.removeElement(en.nextElement());
	vec_removelist.removeAllElements();
	v=(Object [])vec.firstElement(); //might throw exception
	StringBuffer format=((StringBuffer)v[1]);
	format.setLength(format.length()-1);
	return format.toString();
    }
  
    /**
     * Formatter for double - Steve Ferguson
     */
  
    private static NumberFormat _DoubleFormatter; 
 
    // Class Initializer
	
    static {
	_DoubleFormatter = 
	    NumberFormat.getNumberInstance(java.util.Locale.US);
	_DoubleFormatter.setGroupingUsed(false);
	// attempt to prevent truncation
	_DoubleFormatter.setMaximumFractionDigits(8); 
    }
};
