/*’iguste kontroll ja cashimine

Saidid(Objektid(m‰lu))

Protokolli kirjeldus:

(-1 Saidi_ID "," suva_arv)		Mitte sisseloginud kylastaja, saab ainult vaatamise ıiguse
(-1 Saidi_ID user oid)			Saidile sisse loginud user, klikkab objektil Objekti_ID
(0 Saidi_ID user gid)			Lisataxe userile grupp
(1 Saidi_ID user gid)			User kustutati grupist
(2 Saidi_ID user suva_arv)		Sellelt saidilt kustutati user
(3 Saidi_ID user gid)			Grupil muudeti ACLi
(4 Saidi_ID "mitte koma" gid)	Grupil muudeti prioriteeti
(5 Saidi_ID user gid)			Grupp kustutati saidilt
(6 Saidi_ID user oid)			Objekt kustutati/muudeti tema ACLi
*/

import java.net.*;
import java.io.*;
import java.sql.*;
import java.util.*;
import java.util.Date;
import java.lang.*;


class Point
{
	int x,y;

	Point(int xx, int yy)
	{
		x=xx;
		y=yy;
	}
}



class sqlPoint
{
	Statement x,y;

	sqlPoint(Statement xx,Statement yy)
	{
		x=xx;
		y=yy;
	}
}



class conf extends Thread
{	
	//saab teada uut ja huvitavat
	int[] mituACLi,piir,aegund;
	byte[] lıimi,lıimedeArv;
	BufferedReader in;
	boolean olin=false;
	int vahe,maht;
	byte vana;

	conf(int[] mitu,BufferedReader inn,int[] piiir,byte[] lıimii,int[] aegu,byte[] Arv)
	{
		in=inn;				
		lıimedeArv=Arv;
		mituACLi=mitu;		
		aegund=aegu;	
		piir=piiir;			
		lıimi=lıimii;
	}	

	public void run()
	{ 
		while(true)
		{
			yield();
			try
			{             
				String vastus=in.readLine();
			 
				if(vastus.length()>5)
				{
					if(vastus.substring(0,4).equals("malu"))
					{ //mitu objekti vıib m‰lus hoida	 
						System.out.print(">");
						piir[0]=Integer.parseInt(vastus.substring(5));
						olin=true;
					}

					if(vastus.substring(0,4).equals("loim"))
					{ //mitu lıimekorraga tˆˆtab	 
						System.out.print(">");
						vana=lıimi[0];
						lıimi[0]=Byte.parseByte(vastus.substring(5));
						olin=true;
						vahe=lıimi[0]-vana;
						if(vahe>0)
						{
							System.out.print("Teen "+vahe+" lıime juurde\n>");
						}
						if(vahe<0)
						{
							vahe=-1*vahe;
							maht=vana-1;
							if(maht==0) 
								System.out.print("V‰hemalt yx lıim peab tˆˆsse j‰‰ma!!!\n>");
							else 
								System.out.print("H‰vitan "+vahe+" lıime\n>");
								
							while((vahe!=0)&&(maht!=0))
							{
								lıimedeArv[maht]=-1;
								vahe--;
								maht--;
							}
						}
					}

					if(vastus.substring(0,4).equals("aegu"))
					{ 
						//millise ajaga tunnistatakse objekt aegunuks	 
						System.out.print(">");
						aegund[0]=Integer.parseInt(vastus.substring(5));
						olin=true;
					}
					if(olin==false) 
					{
						System.out.print("Ei tunne sellist k‰sku, tryki help.\n>");
					}
					olin=false;
				}
				else
				if(vastus.equals("?"))
				{
					System.out.print("M‰lus hoitakse kuni "+piir[0]+" objekti.\n>");
					System.out.print("Hetkel on m‰lus "+mituACLi[0]+" Objekti\n>");
					System.out.print("K‰ima on tımmatud "+lıimi[0]+" lıime.\n>");
					System.out.print("Objekt tunnistatakse aegunuks "+aegund[0]+" tunni mˆˆdudes.\n>");
				}
				else
				{
					System.out.print("M‰lu piirix tryki: 'malu X', kus X on m‰lus hoitavate objektide arv.\n>");
					System.out.print("Lıimede arvuks tryki: 'loim X', kus X on tˆˆtavate lıimede arv.\n>");
					System.out.print("Objekti aegumise aja m‰‰ramiseks tryki: 'aegu X', kus X on objekti aegumise aeg tundides.\n>");
					System.out.print("Parasjagu k‰igus olevate seadete jaox tryki: ?\n>");
				}
			}

			catch (IOException e) 
			{
		      System.err.println(e);
			}
			catch (NumberFormatException e) 
			{
		      System.out.print("Ei tunne sellist k‰sku, tryki help.\n>");
		    }
		 }
	}
}



class M‰lu
{
	//siin hoian grupile vastavat ACLi koos miskise otsustajaga, kas tasub seda ACLi m‰lus hoida
	byte[] Ùigus=new byte[29];
	int grupp;
	int gid;


	M‰lu()
	{
	}

	public Object put(byte[] bitid,int[] mitu_ACLi) 
	{
		Ùigus=bitid;
		mitu_ACLi[0]++;
		return null;
	}


	public byte[] get() 
	{
		return Ùigus;
	}

	//pean need tegema, muidu ei tunne komponenti ‰ra
	public void putGid(int grupp)
	{
		gid=grupp;
	}

	public int getGid()
	{
		return gid;
	}
}



class Objektid
{
	Vector ACLid=new Vector();
	int parentid;
	long aeg,vana_aeg;
	M‰lu mem;

	Objektid()
	{
	}

	public void put(int gid,byte[] bitid,int[] mitu_ACLi) 
	{
		mem=new M‰lu();
		mem.putGid(gid);
		mem.put(bitid,mitu_ACLi);
		ACLid.addElement(mem);
	}

	public void replacinACL(int gid,byte[] bitid,int[] mitu_ACLi) 
	{
		for(int i=0;i<ACLid.size();i++)
		{
			mem = (M‰lu) ACLid.elementAt(i);
			if(mem.getGid()==gid)
			{
				ACLid.remove(i);
				break;
			}
		}
		mitu_ACLi[0]--;
		mem=new M‰lu();
		mem.putGid(gid);
		mem.put(bitid,mitu_ACLi);
		ACLid.addElement(mem);
	}


	public byte[] get(int gid,byte[] missing,byte[] missing2)
	{
		int index=0;
		try
		{
			Date d = new Date();
			vana_aeg=d.getTime();//j‰tan meelde millal ACLi viimati kysiti

			if(gid==0) 
				return missing2;

			while(true)
			{
				mem=(M‰lu) ACLid.elementAt(index);//kui vektor lıpeb viskab erindi
				if(mem.getGid()==gid) 
					break;
				index++;
			}				
		}
		catch(java.lang.ArrayIndexOutOfBoundsException e)
		{
			//if(mem.getGid()==0) return missing;//kui on ainult gid=0 (juhul kui olen siia parenti pannud)
			return missing2;//objekt olemas, kuid gruppi pole
		}

		return mem.get(); 
	}


	public boolean Aegunud()
	{
		//saab teada palju on kysimise hetkex viimasest klikist aega mˆˆdunud
		Date d = new Date();
		aeg=d.getTime();
		if(aeg-vana_aeg>86400000)
		{ 
			//pole 24h jooxul klikatud 24h=86400000ms, tee confitavax
			return true;
		}
		else 
		{
			return false;
		}
	}


	public int getParents()
	{
		return parentid;
	}

	public void putParents(int parent)
	{
		parentid=parent;
	}
}



class  Sait
{
	Vector oidid=new Vector();
	Vector userid = new Vector();
	Vector names = new Vector();
	sqlPoint sql;
	User uus;
	Objektid uus2;

	Sait(){}

	public Object put(int oid, int gid, byte[] bitid, int[] mitu_ACLi, int parentid) 
	{
		if(oidid.size()<=oid) 
		{
			oidid.setSize(oid+1);
		}

		if(oidid.elementAt(oid)==null)
		{
			uus2=new Objektid();
			uus2.putParents(parentid);
			oidid.set(oid,uus2);
		}
		else
		{
			uus2=(Objektid) oidid.elementAt(oid);
		}

		if(bitid[6]==0) 
		{
			return null;//kui ıigusi pole, siis pole mıtet ka m‰lu vıtta
		}
		uus2.put(gid,bitid,mitu_ACLi);
		return null;
	}


	public void replaceACL(int oid, int gid, byte[] bitid, int[] mitu_ACLi)
	{
		uus2=(Objektid) oidid.elementAt(oid);
		uus2.replacinACL(gid,bitid,mitu_ACLi);
	}


	public byte[] get(int gid,int oid,byte[] missing,byte[] missing2)
	{
		try
		{
			if(oidid.elementAt(oid)==null) 
			{
				return missing;//viskab erindi, kui oid<vektori pikkusest
			}
			uus2=(Objektid) oidid.elementAt(oid);
			return uus2.get(gid,missing,missing2);
		}
		catch(java.lang.ArrayIndexOutOfBoundsException e)
		{
			return missing;
		}
	}


	public int getParent(int oid)
	{
		try
		{
			uus2=(Objektid) oidid.elementAt(oid);
			return uus2.getParents();
		}
		catch(Exception e)
		{
			//sellist objekti ei olnud vektoris
			return -1;
		}
	}


	public void putParent(int oid,int parentid)
	{
		if(oidid.size()<=oid) 
			oidid.setSize(oid+1);

		if(oidid.elementAt(oid)==null)
		{
			uus2=new Objektid();
			uus2.putParents(parentid);
			oidid.set(oid,uus2);
		}
		else
		{
			uus2=(Objektid) oidid.elementAt(oid);
			uus2.putParents(parentid);
		}
	}


	public Object putUser(String user, int gid, int prioriteet) 
	{
		//lisab sellele saidile useri koos grupi ja selle prioriteediga
		int index=names.indexOf(user);
		if(index==-1)
		{
			names.add(user);
			uus=new User();
			userid.addElement(uus);
		}
		else
		{
			uus=(User) userid.elementAt(index);
		}
		uus.put(gid,prioriteet);
		return null;
	}


	public Point getUser(String user,int place)
	{
		//saan useri grupi koos prioriteediga
		int index=names.indexOf(user);
		
		if(index==-1) 
		{
			return new Point(-1,-1);
		}
		uus=(User) userid.elementAt(index);
		return uus.get(place);
	}

	public Object removeGid(String user, int gid)
	{
		//eemaldab userilt grupi
		int index=names.indexOf(user);		
		if(index==-1) 
		{
			return null;
		}
		uus=(User) userid.elementAt(index);
		uus.removeGrup(gid);
		return null;
	}


	public void removeUser(String user)
	{
		//kustutab useri saidilt
		int index=names.indexOf(user);
		names.remove(index);
		userid.remove(index);
	}

	public void changePriority(int gid,int prior)
	{
		//muudab grupi prioriteeti
		for(int i=0;i<names.size();i++)
		{
			uus=(User) userid.elementAt(i);
			uus.removeGrup(gid);
			uus.put(gid,prior);
		}
	}


	public void removeOid(int oid)
	{
		//kustutan objekti saidilt
		oidid.set(oid,null);
	}


	public void putSQL(Statement x,Statement y)
	{
		//selle saidi sql yhendused
		sql=new sqlPoint(x,y);		
	}

	public sqlPoint getSQL()
	{
		return(sql);
	}
}



class User
{
	//peab meeles useri koos oma gruppide ja nende prioriteetidega
	Vector grupid = new Vector();

	User(){}

	public Object put(int gid, int prioriteet)
	{
		Point gruup=new Point(gid,prioriteet);
		grupid.add(gruup);
		return null;
	}


	public Point get(int place)
	{
		try
		{
			Point send=(Point) grupid.elementAt(place); 
			return send;
		}
		catch(ArrayIndexOutOfBoundsException e)
		{
			//rohkem ei olnud useril gruppe
			return new Point(-1,-1);
		}
	}

	public Object removeGrup(int gid)
	{
		for(int i=0;i<grupid.size();i++)
		{
			Point send=(Point) grupid.elementAt(i);
			if(send.x==gid)
			{
				grupid.remove(i);
				return null;
			}
		}
		return null;
	}
}




class kysimine extends Thread
{	//klient kysib
	Socket s;
	Connection C2;
	static int Saidi_ID,Objekti_IDD,Tegevus,gids,prior,abi,tyhik,seoseobj,index,abi33,seoseobjekt,objekt,poid,mitmes;
	static String Kasutaja_ID,Objekti_ID,obs,saada,acl,acl2,sql,cache;
	static PrintStream toclient;
	static BufferedReader fromclient;
	static String[] protokoll= new String[4];
	int[] mituACLi,piir,aegund;
	static Statement stmt,stmt2,stmt3;
	ResultSet tulemus,tulemus2,rs,result1,result;
	static Point[] grups = new Point[75];
	byte[] komaga,missing,missing2,lıimedeArv;
	static byte[] bitsid = new byte[29];
	static Point kırgeim;
	static Point triple,ggg;
	Point[][] parentid;
	static Vector objekti_id;
	static Vector grupid;
	static ServerSocket ss;
	static boolean panen_m‰llu,kasutaja_m‰lus;
	Vector saidid;
	static Vector tempGrups=new Vector();
	Sait seeSait;
	byte[] saan=new byte[29];
	int i,vanagid;
	Timestamp time;
	long huuu;
	int sees,ring,sekund,ms;
	Calendar c;


	public void saatmine(PrintStream toclient,byte[] bitsid)
	{
		bitsid[6]=1;//ajutine, sest minul on default 0, AW-l =1
		saada="can_edit "+bitsid[0]+","+
			"can_add "+bitsid[1]+","+
			"can_admin "+bitsid[2]+","+
			"can_delete "+bitsid[3]+","+	
			"can_clone "+bitsid[4]+","+
			"can_stat "+bitsid[5]+","+
			"can_view "+bitsid[6]+","+
			"can_fill "+bitsid[7]+","+
			"can_export "+bitsid[8]+","+	
			"can_import "+bitsid[9]+","+
			"can_action "+bitsid[10]+","+	
			"can_import_styles "+bitsid[11]+","+
			"can_import_data "+bitsid[12]+","+
			"can_add_output "+bitsid[13]+","+	
			"can_delegate "+bitsid[14]+","+
			"can_export_styles "+bitsid[15]+","+
			"can_export_data "+bitsid[16]+","+
			"can_view_filled "+bitsid[17]+","+
			"can_send "+bitsid[18]+","+	
			"can_active "+bitsid[19]+","+
			"can_periodic "+bitsid[20]+","+
			"can_order "+bitsid[21]+","+
			"can_copy "+bitsid[22]+","+
			"can_view_users "+bitsid[23]+","+	
			"can_change_users "+bitsid[24]+","+
			"can_delete_users "+bitsid[25]+","+
			"can_add_users "+bitsid[26]+","+
			"can_change_variables "+bitsid[27]+","+
			"can_change_variable_acl "+bitsid[28]+"\n";


		//huuu=System.currentTimeMillis();
		//System.out.println("Aeg="+huuu);
		//time=new Timestamp(System.currentTimeMillis());
		//System.out.println("Nanod="+time.getNanos()); 
		/*
		c=Calendar.getInstance();   //saan teada saatmise aja
		sekund=c.get(c.SECOND);
		ms=c.get(c.MILLISECOND);
		System.out.println("Saadan  Sekund="+sekund+"   mill="+ms);
		*/
		toclient.print(saada);
		//System.out.println("Saadan="+saada);
	}


	public void kontakt(int sait,Statement stmt,PrintStream toclient)
	{
		//user saab yhenduse mySQL baasiga 
		try
		{
			String sql="select SQLserver,Login,Parool,Baas from saidid Where saidid.Saidi_id="+sait;
			rs = stmt.executeQuery(sql);	
				
			String serv = rs.getString("SQLserver");
			String user = rs.getString("Login");
			String psw = rs.getString("Parool");
			String baas = rs.getString("Baas");	
		
			if(serv==null)
			{
				System.out.print("Tundmatu Saidi_ID "+protokoll[1]+"\n>");
				s.close();
			}
			else
			{
				//System.out.println(serv+"    "+user+"    "+psw+"     "+baas);	
				C2 = DriverManager.getConnection(
					"jdbc:mysql://"+serv+"/"+baas+"?user="+user+"&&password="+psw);		
				System.out.print(user+" sai SQL serveriga yhenduse...\n>");
			}
		}

		catch (IOException e) 
		{
	      System.err.println(e);
		}
		catch (SQLException E) 
		{
			System.out.println("Ei saanud saidile ID-ga "+sait+" ligi!!!");
            System.out.println("SQLState:     " + E.getSQLState());
            System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
		}
	}

	public void p‰‰s(int obj, int gid,ResultSet rs, Statement st, byte [] bitsid)
	{
		//p‰rib baasist ACLi
		//System.out.println("Kysin objekti "+obj+" ja grupi "+gid+" vahelist seost");
		try
		{
			acl2=acl+obj+" and gid = "+gid;
			rs = st.executeQuery(acl2);

			bitsid[0]=rs.getByte("can_edit");
			bitsid[1]=rs.getByte("can_add");
			bitsid[2]=rs.getByte("can_admin");
			bitsid[3]=rs.getByte("can_delete");
			bitsid[4]=rs.getByte("can_clone");
			bitsid[5]=rs.getByte("can_stat");
			bitsid[6]=rs.getByte("can_view");
			bitsid[7]=rs.getByte("can_fill");
			bitsid[8]=rs.getByte("can_export");
			bitsid[9]=rs.getByte("can_import");
			bitsid[10]=rs.getByte("can_action");
			bitsid[11]=rs.getByte("can_import_styles");
			bitsid[12]=rs.getByte("can_import_data");
			bitsid[13]=rs.getByte("can_add_output");	  
			bitsid[14]=rs.getByte("can_delegate");
			bitsid[15]=rs.getByte("can_export_styles");
			bitsid[16]=rs.getByte("can_export_data");
			bitsid[17]=rs.getByte("can_view_filled");
			bitsid[18]=rs.getByte("can_send");
			bitsid[19]=rs.getByte("can_active");
			bitsid[20]=rs.getByte("can_periodic");
			bitsid[21]=rs.getByte("can_order");
			bitsid[22]=rs.getByte("can_copy");
			bitsid[23]=rs.getByte("can_view_users");
			bitsid[24]=rs.getByte("can_change_users");
			bitsid[25]=rs.getByte("can_delete_users");
			bitsid[26]=rs.getByte("can_add_users");
			bitsid[27]=rs.getByte("can_change_variables");
			bitsid[28]=rs.getByte("can_change_variable_acl");
		}

		catch (SQLException E) 
		{
			System.out.println("Saidil ID-ga "+protokoll[1]+" on ACL tabelis kala grupi "+gids+" ja objekti "+Objekti_ID+" vahel!!!");
			System.out.println("SQLState:     " + E.getSQLState());
			System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
		}

		catch(NullPointerException grr)
		{
			//sql p‰ring sai "Empty Set"
			bitsid[6]=0;//-1
		}
	}


	kysimine(ServerSocket sss,int[] m‰lus,int[] piiir,Statement st,byte[] koma,Vector saits,
			byte[] miss,byte[] miss2,int mitm,byte[] Arv,String acll,int[] vana)
	{
		ss=sss;					piir=piiir;				komaga=koma;				
		mituACLi=m‰lus;			stmt=st;				saidid=saits;
		missing=miss;			missing2=miss2;			mitmes=mitm;
		lıimedeArv=Arv;			acl=acll;				aegund=vana;
	}	

	public void run()
	{		
		Objekti_ID="";
		boolean sulgesin=true;
		cache="";
		while(true)
		{
			try
			{ 		
				abi=0;
				triple= new Point(0,-1);
				kırgeim = new Point(-1,-1);

				if((sulgesin)||(s==null))
				{
					yield();//vabastan ressursid
					System.gc();//yritan m‰lu saastast puhastada
					System.out.println("Ootan uut yhendust\n>");

					s=ss.accept();
					fromclient=new BufferedReader(new InputStreamReader(s.getInputStream()));
					toclient=new PrintStream(s.getOutputStream());
					sulgesin=false;
					//System.out.print("‹hendus arvutist "+s.getInetAddress().getHostName()+
					// ", pordist "+s.getPort()+".\n>");
				}
	
				//huuu=System.currentTimeMillis();
				//System.out.println("uuesti Aeg="+huuu);
				//time=new Timestamp(System.currentTimeMillis());
				//System.out.println("uuesti Nanod="+time.getNanos());
	
				Objekti_ID=fromclient.readLine();
				System.out.println("Protokoll="+Objekti_ID);
				
				//huuu=System.currentTimeMillis();
				//System.out.println("Aeg="+huuu);
				//time=new Timestamp(System.currentTimeMillis());
				//System.out.println("Nanod="+time.getNanos()); 
				/*
				c=Calendar.getInstance();   //saan teada saatmise aja
				sekund=c.get(c.SECOND);
				ms=c.get(c.MILLISECOND);
				System.out.println("Sain  Sekund="+sekund+"   mill="+ms);
				*/
				if(Objekti_ID.compareTo(cache)==0)
				{
					//eelmine kord kysiti sama asja
					System.out.println("Cachist");
				 	saatmine(toclient,bitsid);
					//sees++;
				}
				else
				{
					//ring++;
					cache=Objekti_ID;
					//Calendar ccc2=Calendar.getInstance();//saan yhendust vıtmise aja
					//int sekund=ccc2.get(ccc2.SECOND);
					//int ms=ccc2.get(ccc2.MILLISECOND);
					//System.out.println("Sain protokolli  Sekund="+sekund+"   mill="+ms);
		
					for(i=0;i<3;i++)
					{
						tyhik=Objekti_ID.indexOf(" ");//tyhiku koht	
						protokoll[i]=Objekti_ID.substring(0,tyhik);
						Objekti_ID=Objekti_ID.substring(tyhik+1);
					}

					
					Tegevus=Integer.parseInt(protokoll[0]);
					Saidi_ID=Integer.parseInt(protokoll[1]);
					Kasutaja_ID=protokoll[2];//string
					Objekti_IDD=Integer.parseInt(Objekti_ID);

					//System.out.println("Tegevus="+Tegevus);
					//System.out.println("Saidi_ID="+Saidi_ID);
					//System.out.println("Kasutaja_ID="+Kasutaja_ID);
					//System.out.print("Objekti_IDD="+Objekti_IDD+"\n>");	

					if((Kasutaja_ID.compareTo(",")==0)||(Objekti_IDD==2147483647))
					{
						System.out.print("Tagastan seose acl, mis on Objekti 0 ja grupi -2 vahel.\n>");
						saatmine(toclient,komaga);
						bitsid=komaga;
					}	//pole sisse loginud	
					else
					{
						//oli tegu objektil klikkamisega
						try
						{
							if(saidid.elementAt(Saidi_ID)==null)
							{
								System.out.println("Lisan uue saidi: "+Saidi_ID);
								kontakt(Saidi_ID,stmt,toclient);   	
								stmt2 = C2.createStatement();
								stmt3 = C2.createStatement();
								seeSait=new Sait();
								seeSait.putSQL(stmt2,stmt3);
								saidid.set(Saidi_ID,seeSait);
							}				
						}
						catch(java.lang.ArrayIndexOutOfBoundsException e)
						{
							System.out.println("Lisan uue saidi: "+Saidi_ID);
							saidid.setSize(Saidi_ID+1);
							kontakt(Saidi_ID,stmt,toclient);   	
							stmt2 = C2.createStatement();
							stmt3 = C2.createStatement();
							seeSait=new Sait();
							seeSait.putSQL(stmt2,stmt3);
							saidid.set(Saidi_ID,seeSait);
						}

						seeSait=(Sait) saidid.elementAt(Saidi_ID);
						if(Tegevus==-1)
						{
							if(seeSait.getUser(Kasutaja_ID,0).x!=-1)
							{
								//kasutaja m‰lus
								tempGrups.clear();
								while(triple.x!=-1)
								{
									//kuni kasutajal gruppe on (saan kıik kasutaja grupid)
									triple=seeSait.getUser(Kasutaja_ID,abi);
									if(triple.x!=-1)
									{
										tempGrups.add(new Point(triple.x,triple.y));
										//System.out.println("m‰lust Grupix="+triple.x+"   priotiteet="+triple.y);
										abi++;
									} //if
								}//while
							}
							else
							{
								kasutaja_m‰lus=false;
								sql="select gid from groupmembers where groupmembers.uid='"+Kasutaja_ID+"'";				
								result1 =seeSait.getSQL().x.executeQuery(sql);					
								vanagid=-1;
								tempGrups.clear();

								while (result1.next()) 
								{
									gids=result1.getInt("gid");
						
									if(vanagid!=gids)
									{
										//v‰ltimax korduseid
										sql="select priority from groups where gid="+gids;
										tulemus = seeSait.getSQL().y.executeQuery(sql);
										prior=tulemus.getInt("priority");
										//if(panen_m‰llu){
										seeSait.putUser(Kasutaja_ID,gids,prior);			
										//}
										tempGrups.add(new Point(gids,prior));
										abi++;//loen gruppe
										//System.out.println("Grupix"+gids+"   priotiteet="+prior);
									}//if
									vanagid=gids;
								}//while
							}//kasutajat ei olnud veel m‰lus
	
							if(seeSait.getParent(Objekti_IDD)!=-1)
							{
								//objekt on m‰lus
								//System.out.println("Kysin m‰lust");
								seoseobjekt=Objekti_IDD;

								while(true)
								{
									//kuni on parenteid objektil	 
									for(i=0;i<abi;i++)
									{
										//k‰in l‰bi kıik useri grupid, kas user kuulub mında neist
										triple=(Point)tempGrups.elementAt(i);

										if(kasutaja_m‰lus==false)
										{
											//&&(panen_m‰llu)){//kui userit ei olnud m‰lus
											byte[] saan3=new byte[29];
								
											p‰‰s(seoseobjekt,triple.x,rs,seeSait.getSQL().x,saan3);
											seeSait.put(Objekti_IDD,triple.x,saan3,mituACLi,0);//siin ei pane parentit nullix
										}//if	
		
										//System.out.println("Grupp="+triple.x+"   prioriteet="+triple.y+"   kırgeim.x="+kırgeim.x+"   kırgeim.y"+kırgeim.y);
										/*		vist ei ole vaja
												if(seeSait.get(triple.x,seoseobjekt,missing,missing2)[6]==-2){//selle grupi kohta pole veel p‰ringut tehtud
													byte[] saan4=new byte[29];
													p‰‰s(seoseobj,triple.x,rs,seeSait.getSQL().x,saan4);
													seeSait.put(seoseobjekt,triple.x,saan4,mituACLi,0);//siin ei pane parentit nullix
												}
										*/

										if(seeSait.get(triple.x,seoseobjekt,missing,missing2)[6]==1)//kui can_view jah	
										{
											//kui selle objekti ja grupi vahel on acl
											if(kırgeim.y<triple.y)
											{
												//kui oli seos ja prioriteet on kırgem
												kırgeim.x=triple.x;
												kırgeim.y=triple.y;
												seoseobj=seoseobjekt;
											}
										}
									}//for
									//System.out.println("Tˆˆtan="+seeSait.getParent(seoseobjekt)+" parentiga");
						
									seoseobjekt=seeSait.getParent(seoseobjekt);		
									if(seoseobjekt==0) 
									{
										break;//rohkem parenteid pole
									}
								}//while(true)

								 bitsid=seeSait.get(kırgeim.x,seoseobj,missing,missing2);
								 System.out.print("Tagastan m‰lust seose acl, mis on Objekti "+seoseobj+" ja grupi "+kırgeim.x+" vahel.\n>");
								 saatmine(toclient,bitsid);	//peab m‰lus olema
							}//objekt on m‰lus
							else
							{
								//objekti ei ole veel m‰lus
								//ring++;
								//mis gruppidega see objekt seotud

								sql="select gid from acl where oid="+Objekti_ID;
								tulemus = seeSait.getSQL().y.executeQuery(sql);
								seoseobj=Objekti_IDD;

								//millised neist useriga seotud, j‰tan meelde kırgeima prioriteediga
								while (tulemus.next()) 
								{
									gids=tulemus.getInt("gid");//sain otse objektiga seotud grupi
									//System.out.println("Otse objektiga seotud grupp "+gids);
				
									//if(panen_m‰llu){
									byte[] saan1=new byte[29]; 	//loen sisse ka nenede vahelised acl-d, panen m‰llu	
									p‰‰s(Objekti_IDD,gids,rs,seeSait.getSQL().x,saan1);
									seeSait.put(Objekti_IDD,gids,saan1,mituACLi,0);
									//}
				
									for(i=0;i<abi;i++)
									{
										//k‰in l‰bi kıik useri grupid, kas user kuulub mında neist

										triple=(Point)tempGrups.elementAt(i);
										if((gids==triple.x)&&(kırgeim.y<triple.y))
										{
											kırgeim.x=gids;
											kırgeim.y=triple.y;
										}
									}//for
								}//tulemus.next()

								//System.out.println("grpx sain="+kırgeim.x+"   prioriteet="+kırgeim.y+"    objektix="+seoseobj);
								//k‰ime ka parentid l‰bi, saan kıige kırgema prioriteediga parenti, mis useriga seotud

								while (Objekti_IDD!=0)
								{
									//sql="select parent from objects where oid ="+objekt;
									sql="select parent from objects where oid ="+Objekti_IDD;
									tulemus = seeSait.getSQL().y.executeQuery(sql);
									poid=tulemus.getInt("parent");//objekti parent ehk parenti oid
									//System.out.println("Objektiga "+Objekti_IDD+" on seotud parent "+poid);
	
									//if(panen_m‰llu){					
									//seeSait.putParent(objekt,poid);//lisan parenti m‰llu
									seeSait.putParent(Objekti_IDD,poid);//lisan parenti m‰llu
									//}
									Objekti_IDD=poid;
			
									if(Objekti_IDD!=0)
									{

										sql="select gid from acl where oid="+Objekti_IDD;//mis grupiga parent seotud
										tulemus = seeSait.getSQL().y.executeQuery(sql);

										while (tulemus.next()) 
										{
											gids=tulemus.getInt("gid");	
											//System.out.println("Parentiga "+Objekti_IDD+" seotud grupp: "+gids);

											byte[] saan2=new byte[29];
											p‰‰s(Objekti_IDD,gids,rs,seeSait.getSQL().x,saan2);
											seeSait.put(Objekti_IDD,gids,saan2,mituACLi,0);//esialgu parent 0, j‰rgmisel tiirul saab ıige

											for(i=0;i<abi;i++)
											{
												triple=(Point)tempGrups.elementAt(i);

												if((gids==triple.x)&&(kırgeim.y<triple.y))
												{
													kırgeim.x=gids;
													kırgeim.y=triple.y;
													seoseobj=Objekti_IDD;
													//System.out.println("grpx sain "+kırgeim.x+"   objx sain "+seoseobj);
												}//if
											}//for
										}//tulemus.next()
									}//if objekt!=0
								}//while(objekt!=0)
								 bitsid=seeSait.get(kırgeim.x,seoseobj,missing,missing2);
								 System.out.print("Tagastan seose acl, mis on Objekti "+seoseobj+" ja grupi "+kırgeim.x+" vahel.\n>");
								 saatmine(toclient,bitsid);	
							}//objekti ei olnud m‰lus
							//if(panen_m‰llu)
			 
							//else{//m‰lu liiga t‰is
							//	p‰‰s(seoseobj,kırgeim.x,rs,yhendused[Saidi_ID].x,bitsid);
							//	saatmine(toclient,seoseobj,kırgeim.x,Saidi_ID,bitsid,objekti_id);
							//}
						}//oli tegu objektil klikkamisega
					}//tegevus==-1	
		
					if(Tegevus!=-1)
					{
						if(Tegevus==0)
						{//lisan userile uue gruppi m‰llu
							sql="select priority from groups where gid="+Objekti_ID;//siin Objekti_Id==lisatud grupiga protokollis
							result1 = seeSait.getSQL().x.executeQuery(sql);
							prior=result1.getInt("priority");
							seeSait.putUser(Kasutaja_ID,Objekti_IDD,prior);//siin Objekti_Id==lisatud grupiga protokollis
						}
						else 
						if(Tegevus==1)
						{ 
							seeSait.removeGid(Kasutaja_ID,Objekti_IDD);
						}//user kustutati grupist
						else
						if(Tegevus==2)
						{ 
							seeSait.removeUser(Kasutaja_ID);
						}//sellelt saidilt kustutati user 
						else	
						if(Tegevus==3)
						{//grupil muudeti ıiguseid, kirjutan vanad seosed yle
							sql="select oid from acl where gid="+Objekti_ID;//mis objektidega see seotud
							result1 = seeSait.getSQL().x.executeQuery(sql);
							while (result1.next())
							{
								prior=result1.getInt("oid");
								if(seeSait.get(0,prior,missing,missing2)[6]!=-1)
								{//objekt on m‰lus
									byte[] saan5=new byte[29];
									p‰‰s(prior,Objekti_IDD,rs,seeSait.getSQL().y,saan5);
									seeSait.replaceACL(prior,Objekti_IDD,saan5,mituACLi);					
								}
							}
						}
						else
						if(Tegevus==4)
						{
							//Grupil muudeti prioriteeti
							sql="select priority from groups where gid="+Objekti_ID;
							result1 = seeSait.getSQL().x.executeQuery(sql);
							prior=result1.getInt("priority");
							seeSait.changePriority(Objekti_IDD,prior);			
						}
						else
						if(Tegevus==5)
						{
							//kustutati grupp saidilt
							sql="select uid from groupmembers where gid="+Objekti_ID;
							result1 = seeSait.getSQL().x.executeQuery(sql);
							while (result1.next())
							{
								Kasutaja_ID=result1.getString("uid");
								seeSait.removeGid(Kasutaja_ID,Objekti_IDD);
							}
						}
						else
						if(Tegevus==6)
						{
							//kustutati objekt/muudeti ACLi
							seeSait.removeOid(Objekti_IDD);
						}
					}//Tegevus!=-1
					kasutaja_m‰lus=true;
				}//else cache		
			}//try
			catch(java.net.SocketException e){}

			catch(java.lang.NumberFormatException e)
			{
				saatmine(toclient,missing2);	//keelan ıigused
				bitsid=missing2;//cachi jaox
				if(sql.compareTo("")!=0)
				{
					//System.out.println("Vigane protokoll!!!\n>");
					if(Saidi_ID!=0) //muidu ka saidi number vigane
						System.out.println("Saabus saidilt "+Saidi_ID+"\n>");
				}	
			}

			catch(java.lang.StringIndexOutOfBoundsException e)
			{
				if((sql!=null)&&(sql.compareTo("")!=0))//muidu teatax ka connectioni sulgemisel, et on vigane
					saatmine(toclient,missing2);	//keelan ıigused
					bitsid=missing2;
				System.out.println("Vigane protokoll!!!\n>");
			}

			catch(java.lang.NullPointerException e)
			{
				try
				{
					s.close();
					System.out.println("sulgesin yhenduse");
					sulgesin=true;
					//System.out.println("Ringiga="+ring);
					if(lıimedeArv[mitmes]==-1) 
						destroy(); 
				}
				catch(Exception ee){}
			}

			catch (IOException e) 
			{
		      System.err.println(e);
			}
			catch (SQLException E) 
			{
               System.out.println("Ei saanud saidile ID-ga "+protokoll[1]+" ligi!!!");
               //System.out.println("SQLState:     " + E.getSQLState());
               //System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
			}
			catch(java.lang.NoSuchMethodError e)
			{
				//kui destroyd, siis viskab siia
				System.gc();
			}
			catch(Exception e)
			{
				saatmine(toclient,missing2);	//keelan ıigused
				bitsid=missing;
			}
		}//lıime tsykkel
	}
}



class ACL
{
	static Vector saidid=new Vector();
	static Connection C;
	static BufferedReader in;
	static Statement stmt;
	static ServerSocket ss;
	Socket s;


	public static void main(String[] args) 
	{
		int port=10000;		
		try 
		{
			//loadin SQLi jaox draiveri
            Class.forName("org.gjt.mm.mysql.Driver").newInstance();
			System.out.println("SQL draiver loaditud...");
		}//lisasin draiveri
		catch (Exception E) 
		{
	       System.out.println("Ei suuda draiverit loadida");
		   E.printStackTrace();
		}
		try 
		{
			C = DriverManager.getConnection(//‰‰sen SQL serverisse hell,kus abx on wahvel
			"jdbc:mysql://hell:3306/wahvel?user=wahvel&&password=everything");
			in=new BufferedReader(new InputStreamReader(System.in));
			ss=new ServerSocket(port);
			stmt = C.createStatement();
			System.out.print("SQL serveriga yhendus loodud...\n>");
		}

		catch (SQLException E) 
		{
		   System.out.println("SQLException: " + E.getMessage());
		   System.out.println("SQLState:     " + E.getSQLState());
		   System.out.println("VendorError:  " + E.getErrorCode());
		 }

		catch (IOException ee) 
		{
			System.out.println(ee);
		}


		byte[] komaga=new byte[29];
		komaga[6]=1;
		byte[] missing=new byte[29];//indikaatorid
		byte[] missing2=new byte[29];

		for(int i=0;i<29;i++)
		{ 
			missing[i]=-1;//see jada tagastataxe, kui miskit ei leitud
			missing2[i]=0;  //-2
		}

		int[] mituACLi= new int[1] ;
		int[] piir= new int[1];
		int[] aegund=new int[1];
		byte[] lıimi=new byte[1];
		byte[] lıimedeArv=new byte[75];

		lıimi[0]=1;//esialgu teen 1 lıime
		aegund[0]=24;//24 tunni mˆˆdudes aegub
		piir[0]=20000;//20000 objekti vıib m‰lus hoida

		Thread conf = new conf(mituACLi,in,piir,lıimi,aegund,lıimedeArv);
		conf.start();

		String acl="select "+
			"((acl >> 0) & 3) AS can_edit,"+ 
			"((acl >> 2) & 3) AS can_add, "+
			"((acl >> 4) & 3) AS can_admin, "+
			"((acl >> 6) & 3) AS can_delete, "+
			"((acl >> 8) & 3) AS can_clone, "+
			"((acl >> 10) & 3) AS can_stat, "+
			"((acl >> 12) & 3) AS can_view, "+
			"((acl >> 14) & 3) AS can_fill, "+
			"((acl >> 16) & 3) AS can_export, "+
			"((acl >> 18) & 3) AS can_import, "+
			"((acl >> 20) & 3) AS can_action, "+
			"((acl >> 22) & 3) AS can_import_styles, "+ 
			"((acl >> 24) & 3) AS can_import_data, "+ 
			"((acl >> 26) & 3) AS can_add_output, "+
			"((acl >> 28) & 3) AS can_delegate, "+
			"((acl >> 30) & 3) AS can_export_styles, "+ 
			"((acl >> 32) & 3) AS can_export_data, "+
			"((acl >> 34) & 3) AS can_view_filled, "+
			"((acl >> 36) & 3) AS can_send, "+
			"((acl >> 38) & 3) AS can_active, "+
			"((acl >> 40) & 3) AS can_periodic, "+
			"((acl >> 42) & 3) AS can_order, "+
			"((acl >> 44) & 3) AS can_copy, "+
			"((acl >> 46) & 3) AS can_view_users, "+ 
			"((acl >> 48) & 3) AS can_change_users, "+ 
			"((acl >> 50) & 3) AS can_delete_users, "+
			"((acl >> 52) & 3) AS can_add_users, "+
			"((acl >> 54) & 3) AS can_change_variables, "+
			"((acl >> 56) & 3) AS can_change_variable_acl "+
			"from acl where oid = ";
	
		byte i=0;

		while(i<lıimi[0])
		{
			//loon esialgu i lıime
			Thread kysimine = new kysimine(ss,mituACLi,piir,stmt,komaga,saidid,missing,missing2,i,lıimedeArv,acl,aegund);
			kysimine.start();
			lıimedeArv[i]=i;
			i++;
		}
		System.out.print("Lıimed valmis...\n>");
	}
}