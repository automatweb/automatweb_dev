/*
protokoll:

(0 SAIDI_ID) - applet logib deemonisse
(1 SAIDI_ID haruID) - sait teatab et muutus haru ID-ga haruID
(haruID) - saadetakse saidi appletitele, et muutus haru ID-ga haruID
*/

import java.net.*;
import java.io.*;
import java.util.*;


class applet
{
	PrintStream to;
	String id;
	BufferedReader from;
	Socket s;

	applet(String idd,PrintStream too,BufferedReader fro,Socket ss)
	{
		to=too;
		id=idd;
		from=fro;
		s=ss;
	}
}



class ping extends Thread 
{//hoiab loodud socketeid üleval
	Vector appletid;
	
	ping(Vector app)
	{
		appletid=app;
	}

	public void run()
	{
		int i;

		while(true)
		{
			try
			{//iga 2 min. tagant
				yield();
				System.gc();
				sleep(120000);
			}
			catch(InterruptedException e)
			{
			}
			if(!appletid.isEmpty())
			{
				i=0;
				while(i<appletid.size())
				{	
					PrintStream see=((applet)appletid.elementAt(i)).to;
					BufferedReader see2=((applet)appletid.elementAt(i)).from;
					if(see==null)
					{//applet oli tagant ära kukkunud
						//System.out.println("EEMALDAN");			
						try
						{
							((applet)appletid.elementAt(i)).s.close();
						}
						catch(IOException e)
						{
						}
						appletid.removeElementAt(i);	
						i--;
					}
					else
					{
						see.println("PING");
						String sain="";
							//System.out.println("PING  i="+i);
						try
						{		
							sain=see2.readLine();
							//System.out.println("Sain="+sain);
							if(sain==null)
							{
								//System.out.println("EEMALDAN");			
								try
								{
									((applet)appletid.elementAt(i)).s.close();
								}
								catch(IOException e)
								{
								}
								appletid.removeElementAt(i);	
								i--;
							}
						}
						catch(IOException e)
						{
							//System.out.println("Aeg läbi");					
							try
							{
								((applet)appletid.elementAt(i)).s.close();
							}
							catch(IOException ee)
							{
							}
							appletid.removeElementAt(i);
							i--;
						}
						i++;
					}
				}
			}
		}
	}
}



public class treeDeemon 
{

	public static void main(String[] args) 
	{
		int port=3333;
		int tyhik,i,rida; 
		String in,saidiID;
		Vector appletid=new Vector();

		Thread ping =new ping(appletid);
		ping.start();

		try
		{
			ServerSocket ss=new ServerSocket(port);
			System.out.println("Ootan ühendusi pordil "+ss.getLocalPort()+"...");
			String cash="";
			while(true)
			{
				try
				{
					Socket s=ss.accept();
					System.out.println("\nÜhendus arvutist "+s.getInetAddress().getHostName()+", pordist "+s.getPort()+".");
					BufferedReader from=new BufferedReader(new InputStreamReader(s.getInputStream()));
				
					in=from.readLine();
					
					System.out.println("Saabus: "+in);
					if(in.substring(0,1).compareTo("1")==0)
					{//puus tehti muutus	
						//while(true)                                                                                              
						//{//võib saabuda mitu rida korraga                                                                        	
							tyhik=in.lastIndexOf(" ");                                                                           	
							saidiID=in.substring(2,tyhik);                                                                       	
							//rida=in.indexOf("\n");                                                                               	
						                                                                                                         
							if(!appletid.isEmpty())                                                                              	
							{                                                                                                    	
								i=0;                                                                                             	
								while(i<appletid.size())                                                                         	
								{                                                                                                	
									applet see=(applet)appletid.elementAt(i);                                                    	
									if(see.id.compareTo(saidiID)==0)                                                             	
									{//õige sait                                                                                 	
										if(see.to!=null)                                                                         	
										{                                                                                        	
											//if(rida!=-1)                                                                         	
											//{                                                                                    	
											//	see.to.println(in.substring(tyhik+1,rida));                                      	
											//}                                                                                    	
											//else                                                                                 	
											//{                                                                                    	
												see.to.println(in.substring(tyhik+1));                                           	
											//}                                                                                    	
										}                                                                                        	
										else                                                                                     	
										{//applet oli tagant ära kukkunud                                                        	
											appletid.removeElementAt(i);                                                         	
											i--;                                                                                 	
										}                                                                                        	
									}//if                                                                                        	
									i++;                                                                                         	
								}//while                                                                                         	
								/*
								if(rida!=-1)                                                                                     	
								{                                                                                                	
									in=in.substring(rida+1);                                                                     	
								}                                                                                                	
								else                                                                                             	
								{                                                                                                	
									break;                                                                                       	
								}  
								*/
							//}//while mitu rida                                                                                   	
						}//if
					}//muutus puus
					else
					{//applet logis sisse
						s.setSoTimeout(10000);
						PrintStream to=new PrintStream(s.getOutputStream());
						BufferedReader from2=new BufferedReader(new InputStreamReader(s.getInputStream()));			
						applet uus=new applet(in.substring(2),to,from2,s);
						appletid.add(uus);
					}
				}
				catch(IOException ee)
				{
					System.out.println("Appleti yhendusega oli probleeme: "+ee);
				}
			}//while
		}//try
		catch(IOException e)
		{
			System.out.println("Ühendusega on probleeme: "+e);
			System.exit(0);
		}
	}
}
