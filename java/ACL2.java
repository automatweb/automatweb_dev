import java.net.*;
import java.io.*;
import java.sql.*;
import java.util.*;
import java.lang.*;


class Point{
int x,y;

	Point(int xx, int yy){
		x=xx;
		y=yy;
	}
}


class sqlPoint{
Statement x,y;

	sqlPoint(Statement xx,Statement yy){
	x=xx;
	y=yy;
	}
}


class conf extends Thread{	//saab teada uut ja huvitavat
int[] v�tanm�lu,piir;
BufferedReader in;

	conf(int[] m�lu,BufferedReader inn,int[] piiir){
	in=inn;
	v�tanm�lu=m�lu;	
	piir=piiir;
	}	

 public void run(){ 

while(true){
try{
             String vastus=in.readLine();
			 
	if(vastus.length()>5){
	if(vastus.substring(0,4).equals("m�lu")){ 	 
		System.out.print(">");
		piir[0]=Integer.parseInt(vastus.substring(5));
		}
		else System.out.print("Ei tunne sellist k�sku, tryki help.\n>");
	}
	else
	if(vastus.equals("?")){
		System.out.println("M�lu laeks on "+piir[0]+" KB.");
		//double memm=v�tanm�lu[0]/1024.0;
		System.out.print("Hetkel on m�lu kasutusel "+v�tanm�lu[0]+" Baiti\n>");
	}
	else{
		System.out.println("M�lu piirix tryki: m�lu X, kus X on m�lumaht KB-des");
		System.out.print("Parasjagu k�igus olevate seadete jaox tryki: ?\n>");
	}
		}

	catch (IOException e) {
      System.err.println(e);
}
  catch (NumberFormatException e) {
      System.out.print("Ei tunne sellist k�sku, tryki help.\n>");
    }
 }
}
}

class kysimine extends Thread{	//klient kysib
Socket s;
Connection C2;
static int Saidi_ID,Objekti_IDD,Tegevus,gids,�,abi,tyhik,seoseobj,index,abi33,seoseobjekt;
static String Kasutaja_ID,Objekti_ID,obs,saada;
static PrintStream toclient;
static BufferedReader fromclient;
static String[] protokoll= new String[4];
int[] v�tanm�lu,piir;
ACL2 �igused;
static Statement stmt,stmt2,stmt3;
ResultSet tulemus,tulemus2,rs,result1,result;
static Point[] grups = new Point[100];
m�lu[][] memory;
byte[] komaga;
static byte[] bitsid = new byte[29];
static Point k�rgeim= new Point(-1,-1);
static Point triple,ggg;
Point[][] parentid;
static Vector objekti_id;
static Vector grupid;
static sqlPoint[] yhendused;
static ServerSocket ss;
static boolean panen_m�llu,kasutaja_m�lus;



public void saatmine(PrintStream toclient,int objekt,int grupp,int sait,byte[] bitsid,Vector objekti_id){

if((panen_m�llu)&&(grupp!=-2)){
	obs=Integer.toString(objekt);
	index= objekti_id.indexOf(obs);
	abi33=0;

	while(abi33<29){
		if(grupp<0) bitsid[abi33]=0;//kui ei ole seost defitud
		  else bitsid[abi33]=memory[sait][index].get2(grupp,abi33);
		abi33++;
	}

	if(bitsid[6]==-1) bitsid[6]=0;
}

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

toclient.print(saada);
Calendar c=Calendar.getInstance();   //saan teada saatmise aja
		int sekund=c.get(c.SECOND);
		int ms=c.get(c.MILLISECOND);
		System.out.println("Saadan  Sekund="+sekund+"   mill="+ms);
System.out.print("Tagastan seose acl, mis on Objekti "+objekt+" ja grupi "+grupp+" vahel.\n>");

}


public void kontakt(int sait,Statement stmt,PrintStream toclient){//user saab yhenduse mySQL baasiga 
	try{
		String sql="select SQLserver,Login,Parool,Baas from saidid Where saidid.Saidi_id="+sait;
		rs = stmt.executeQuery(sql);	
				
		String serv = rs.getString("SQLserver");
		String user = rs.getString("Login");
		String psw = rs.getString("Parool");
		String baas = rs.getString("Baas");	
		
		if(serv==null){
			System.out.print("Tundmatu Saidi_ID "+protokoll[1]+"\n>");
			toclient.println("Tundmatu Saidi_ID "+protokoll[1]);s.close();
		}
		else{
			//System.out.println(serv+"    "+user+"    "+psw+"     "+baas);	
			C2 = DriverManager.getConnection(
			"jdbc:mysql://"+serv+"/"+baas+"?user="+user+"&&password="+psw);		
			System.out.print(user+" sai SQL serveriga yhenduse...\n>");
		}
	}

	catch (IOException e) {
      System.err.println(e);
	}
	catch (SQLException E) {
               System.out.println("Ei saanud saidile ID-ga "+sait+" ligi!!!");
               System.out.println("SQLState:     " + E.getSQLState());
               System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
	}
}



public void p��s(int obj, int gid,ResultSet rs, Statement st, byte [] bitsid){//p�rib baasist ACLi
	
try{
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
	"from acl where oid = "+obj+" and gid = "+gid;
	rs = st.executeQuery(acl);

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

catch (SQLException E) {
      System.out.println("Saidil ID-ga "+protokoll[1]+" on ACL tabelis kala grupi "+gids+" ja objekti "+Objekti_ID+" vahel!!!");
      System.out.println("SQLState:     " + E.getSQLState());
      System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
			 }

catch(NullPointerException grr){//sql p�ring sai "Empty Set"
bitsid[6]=-1;
}
}


	kysimine(ServerSocket sss,int[] m�lu,int[] piiir,ACL2 �,m�lu[][] memm,Point[][] parents,
				Vector objekti_i,Vector gruups,sqlPoint[] sqls,Statement st,byte[] koma){

	ss=sss;				piir=piiir;					parentid=parents;				
	v�tanm�lu=m�lu;		grupid=gruups;				stmt=st;
	�igused=�;			memory=memm;				objekti_id=objekti_i;
	yhendused=sqls;		komaga=koma;				
	}	

 public void run(){
	 String sql;
	 panen_m�llu=false;
while(true){
try{ 	 
		if(s==null){ 
			s=ss.accept();
			fromclient=new BufferedReader(new InputStreamReader(s.getInputStream()));
			toclient=new PrintStream(s.getOutputStream());
			//System.out.print("�hendus arvutist "+s.getInetAddress().getHostName()+
			//  ", pordist "+s.getPort()+".\n>");
		}

		sql=fromclient.readLine();
		if(sql.compareTo("")==0) s.close();
		/*	
			Calendar ccc2=Calendar.getInstance();//saan yhendust v�tmise aja
			int sekund=ccc2.get(ccc2.SECOND);
			int ms=ccc2.get(ccc2.MILLISECOND);
			System.out.println("Sain protokolli  Sekund="+sekund+"   mill="+ms);
		*/
		for(int i=0;i<3;i++){
		tyhik=sql.indexOf(" ");//tyhiku koht	
		protokoll[i]=sql.substring(0,tyhik);
		sql=sql.substring(tyhik+1);
		}

		//System.out.println("Lisati_Gruppi="+protokoll[0]);
		//System.out.println("Saidi_ID="+protokoll[1]);
		//System.out.println("Kasutaja_ID="+protokoll[2]);
		//System.out.print("Objekti_ID="+sql+"\n>");	

		Tegevus=Integer.parseInt(protokoll[0]);
		Saidi_ID=Integer.parseInt(protokoll[1]);
		Kasutaja_ID=protokoll[2];//string
		Objekti_ID=sql;
		Objekti_IDD=Integer.parseInt(sql);

		seoseobj=Objekti_IDD;
		abi=0;
		triple= new Point(0,-1); 

		if(v�tanm�lu[0]/1024<piir[0]) panen_m�llu=true;

		index=objekti_id.indexOf(Objekti_ID);//kas sellise oid-ga objektil on kuskil-kunagi klikatud
		
		if((index==-1)&&(panen_m�llu)){
			objekti_id.add(Objekti_ID);//lisan objekti vektorisse
			index=objekti_id.indexOf(Objekti_ID);//objekti koht vektoris
		}

		if(yhendused[Saidi_ID]==null){	//j�relikult vajadus sql poole p��rduda, esimest korda sellelt saidilt				
			kontakt(Saidi_ID,stmt,toclient);   	
			stmt2 = C2.createStatement();
			stmt3 = C2.createStatement();
			sqlPoint sq=new sqlPoint(stmt2,stmt3);
			yhendused[Saidi_ID]=sq;
			
		}//if

		if((Kasutaja_ID.compareTo(",")==0)||(Objekti_IDD==2147483647)){ saatmine(toclient,0,-2,Saidi_ID,komaga,objekti_id);}//pole sisse loginud	

			else{//oli tegu objektil klikkamisega
																	
				if(�igused.get(String.valueOf(Kasutaja_ID),0,Saidi_ID).x!=-1){//kasutajat m�lus

					while(triple.x!=-1){//kuni kasutajal gruppe on (saan k�ik kasutaja grupid)
						triple=�igused.get(String.valueOf(Kasutaja_ID),abi,Saidi_ID);
						if(triple.x!=-1){
							grups[abi]=new Point(triple.x,triple.y);
							//System.out.println("Grupix="+grups[abi].x+"   priotiteet="+grups[abi].y);
							abi++;
						 } //if
					 }//while
				}
				else{
					kasutaja_m�lus=false;
					sql="select gid from groupmembers where groupmembers.uid='"+Kasutaja_ID+"'";				
					result1 = yhendused[Saidi_ID].x.executeQuery(sql);

					//panen userid koos oma gruppidega ja nende prioriteetidega hashtabelisse �igused
					abi=0;
					int vanagid=-1;

					while (result1.next()) {
						gids=result1.getInt("gid");
						
						if(vanagid!=gids){//v�ltimax korduseid
							sql="select priority from groups where gid="+gids;
							tulemus = yhendused[Saidi_ID].y.executeQuery(sql);
							�=tulemus.getInt("priority");
							if(panen_m�llu) �igused.put(Kasutaja_ID,gids,�,Saidi_ID,abi,v�tanm�lu);			
							grups[abi]=new Point(gids,�);

							//System.out.println("Grupix"+grups[abi].x+"   priotiteet="+grups[abi].y);
							abi++;
						}//if
						vanagid=gids;
					}//while
				}//kasutajat ei olnud veel m�lus


				index=objekti_id.indexOf(Objekti_ID);
				ggg = new Point(-1,-1);
				k�rgeim=ggg;

				if((index>-1)&&(memory[Saidi_ID][index]!=null)){//objekt on m�lus

					index=objekti_id.indexOf(Objekti_ID);
					seoseobjekt=Objekti_IDD;

					while(true){//kuni on parenteid objektil	 
		  
						for(int yy=0;yy<abi;yy++){//k�in l�bi k�ik useri grupid, kas user kuulub m�nda neist
	
							if((kasutaja_m�lus==false)&&(panen_m�llu)){//kui userit ei olnud m�lus
								byte[] saan3=new byte[29]; 	
								p��s(seoseobjekt,grups[yy].x,rs,yhendused[Saidi_ID].x,saan3);		
								memory[Saidi_ID][index].put2(grups[yy].x,saan3,v�tanm�lu);
							}//if	
		
					//System.out.println("Grupp="+grups[yy].x+"   prioriteet="+grups[yy].y+"   k�rgeim.x="+k�rgeim.x+"   k�rgeim.y"+k�rgeim.y);

							if(memory[Saidi_ID][index].get2(grups[yy].x,6)==0){//selle grupi kohta pole veel p�ringut tehtud
								byte[] saan4=new byte[29];
								p��s(seoseobjekt,grups[yy].x,rs,yhendused[Saidi_ID].x,saan4);
								memory[Saidi_ID][index].put2(grups[yy].x,saan4,v�tanm�lu);
							}

							if(memory[Saidi_ID][index].get2(grups[yy].x,6)==1)//kui can_view jah	
									//kui selle objekti ja grupi vahel on acl
								if(k�rgeim.y<grups[yy].y){//kui oli seos ja prioriteet on k�rgem
									k�rgeim.x=grups[yy].x;
									k�rgeim.y=grups[yy].y;
									seoseobj=seoseobjekt;
								}
						 }//for

						if(parentid[Saidi_ID][index].y==0) break;//rohkem parenteid pole	
						//System.out.println("Tegin="+parentid[Saidi_ID][index].y);
						seoseobjekt=parentid[Saidi_ID][index].y;
						obs=Integer.toString(seoseobjekt);
						index=objekti_id.indexOf(obs);
					 }//while(true)
				}//objekt on m�lus

				else{//objekti ei ole veel m�lus
				if(panen_m�llu){
					m�lu oo=new m�lu();
					memory[Saidi_ID][index]=oo;//l�in m�lu v�lja
				}
				//mis gruppidega see objekt seotud

					sql="select gid from acl where oid="+Objekti_ID;
					tulemus = yhendused[Saidi_ID].y.executeQuery(sql);
					int objekt=Objekti_IDD;
					seoseobj=Objekti_IDD;
					if(panen_m�llu) index=objekti_id.indexOf(Objekti_ID);

				//millised neist useriga seotud, j�tan meelde k�rgeima prioriteediga
					while (tulemus.next()) {
						gids=tulemus.getInt("gid");//sain otse objektiga seotud grupi
						//System.out.println("Otse objektiga seotud grupp "+gids);
				
						if(panen_m�llu){
							byte[] saan=new byte[29]; 	//loen sisse ka nenede vahelised acl-d		
							p��s(Objekti_IDD,gids,rs,yhendused[Saidi_ID].x,saan);
							memory[Saidi_ID][index].put2(gids,saan,v�tanm�lu);
						}
				
						for(int yy=0;yy<abi;yy++){//k�in l�bi k�ik useri grupid, kas user kuulub m�nda neist

							if((gids==grups[yy].x)&&(k�rgeim.y<grups[yy].y)){
								k�rgeim.x=gids;
								k�rgeim.y=grups[yy].y;
							}
						}//for
					}//tulemus.next()

					//System.out.println("grpx sain"+k�rgeim.x+"   prioriteet="+k�rgeim.y+"    objektix="+seoseobj);

					//k�ime ka parentid l�bi, saan k�ige k�rgema prioriteediga parenti, mis useriga seotud

					while (objekt!=0){
						sql="select parent from objects where oid ="+objekt;
						tulemus = yhendused[Saidi_ID].y.executeQuery(sql);
						int poid=tulemus.getInt("parent");//objekti parent ehk parenti oid
	
						if(panen_m�llu){
							String obs=Integer.toString(poid);
							objekti_id.add(obs);
							index=objekti_id.indexOf(obs);
							m�lu ooo=new m�lu();
							memory[Saidi_ID][index]=ooo;//l�in m�lu v�lja

							Point par= new Point(objekt,poid); 
							obs=Integer.toString(objekt);
							index=objekti_id.indexOf(obs);
							parentid[Saidi_ID][index]=par;//lisan parenti m�llu
							v�tanm�lu[0]=v�tanm�lu[0]+8;
						}
						objekt=poid;
					//System.out.println("parentid="+parentid[Saidi_ID][index].y);
			
						if(objekt!=0){

							sql="select gid from acl where oid="+objekt;//mis grupiga parent seotud
							tulemus = yhendused[Saidi_ID].y.executeQuery(sql);

							while (tulemus.next()) {
								gids=tulemus.getInt("gid");	
								//System.out.println("Parentiga "+objekt+" seotud grupp: "+gids);

								if(panen_m�llu){
									byte[] saan2=new byte[29];
									obs=Integer.toString(objekt);
									index=objekti_id.indexOf(obs);
									p��s(objekt,gids,rs,yhendused[Saidi_ID].x,saan2);
									memory[Saidi_ID][index].put2(gids,saan2,v�tanm�lu);
								}

								for(int yy=0;yy<abi;yy++)
									if((gids==grups[yy].x)&&(k�rgeim.y<grups[yy].y)){
										k�rgeim.x=gids;
										k�rgeim.y=grups[yy].y;//gids=grups[yy].x
										seoseobj=objekt;
										//System.out.println("grpx sain "+k�rgeim.x+"   objx sain "+seoseobj);
									}//if
							}//tulemus.next()
						}//if objekt!=0
					}//while(objekt!=0)

				}//objekti ei olnud m�lus
	
				if(panen_m�llu) saatmine(toclient,seoseobj,k�rgeim.x,Saidi_ID,bitsid,objekti_id);	//peab m�lus olema
			
					else{//m�lu liiga t�is
						p��s(seoseobj,k�rgeim.x,rs,yhendused[Saidi_ID].x,bitsid);
						saatmine(toclient,seoseobj,k�rgeim.x,Saidi_ID,bitsid,objekti_id);
					}
			}//oli tegu objektil klikkamisega

		//else
			if(Tegevus==0){//lisan userile uue gruppi m�llu

			sql="select priority from groups where gid="+Objekti_ID;//siin Objekti_Id==lisatud grupiga protokollis
			result1 = yhendused[Saidi_ID].x.executeQuery(sql);
			�=result1.getInt("priority");
			�igused.put(String.valueOf(Kasutaja_ID),Objekti_IDD,�,Saidi_ID,0,v�tanm�lu);//siin Objekti_Id==lisatud grupiga protokollis
			}
		
		else
			if(Tegevus==1){ �igused.kustutaGrupist(Kasutaja_ID,Saidi_ID,Objekti_IDD,v�tanm�lu);}//user kustutati grupist
		else
			if(Tegevus==2) �igused.kustutaUser(Kasutaja_ID,Saidi_ID,v�tanm�lu);//sellelt saidilt kustutati user 

		panen_m�llu=false;
		kasutaja_m�lus=true;
}//try

catch(java.net.SocketException e){}

catch(java.lang.NullPointerException e){}

catch (IOException e) {
      System.err.println(e);
}
catch (SQLException E) {
               System.out.println("Ei saanud saidile ID-ga "+protokoll[1]+" ligi!!!");
               System.out.println("SQLState:     " + E.getSQLState());
               System.out.print("VendorError:  " + E.getErrorCode()+"\n>");
			 }
}//l�ime tsykkel
}
}


class m�lu{
	byte[][] bitid = new byte[200][29];

m�lu(){}

public Object put2(int grupp, byte[] bits, int[] v�tanm�lu) {
		if(bits[6]==0) bits[6]=-1;//et hiljem saax aru, et p�ring on tehtud (gruppide lisamisel)
		bitid[grupp]=bits;
		v�tanm�lu[0]=v�tanm�lu[0]+29;
		return null;
	}


public byte get2(int grupp,int abi) {
		return bitid[grupp][abi];
  }
}	//m�lu


public class ACL2 extends AbstractMap{

static Vector kylastajad = new Vector();
static Vector grupid=new Vector();
static int[][][] grupiddd = new int[1000][100][25];//[user][sait][grupp]
static int[][][] prioriteedid = new int[1000][100][25];
static Connection C;
static ServerSocket ss;
static BufferedReader in;
static m�lu[][] memory = new m�lu[100][10000];
static int[] v�tanm�lu= new int[1] ;
static int[] piir= new int[1] ;
static Point[][] parentid = new Point[100][10000];//[sait][objekt]
static Vector objekti_id = new Vector();
static sqlPoint[] yhendused= new sqlPoint[1000];
static Statement stmt;
static Thread kysimine;
Socket s;



public Object put(String kylastaja, int grupp, int prior, int sait, int abi,int[] v�tanm�lu) { 
	
      int index = kylastajad.indexOf(String.valueOf(kylastaja));
	  
	  if(index==-1){ 
		  kylastajad.add(String.valueOf(kylastaja));
		  index = kylastajad.indexOf(String.valueOf(kylastaja));
		  v�tanm�lu[0]=kylastaja.length()*2;
	  }//kui veel ei olnud
	  int i=abi;

	  while(grupiddd[index][sait][i]!=0){
			i++; 
	  }
	  
	  prioriteedid[index][sait][i]=prior;
	  grupiddd[index][sait][i]=grupp;
	  v�tanm�lu[0]=v�tanm�lu[0]+8;
	  return null;
  }


  public Point get(Object kylastaja,int abi,int sait) {
	int index = kylastajad.indexOf(kylastaja);
    if(index == -1) return new Point(-1,-1);	//kui ei olnud tagastab (kuulub gruppi -1,prioriteediga -1)				 	
	
	int x=grupiddd[index][sait][abi];
	if(x==0) return new Point(-1,-1); //kasutaja olemas, kuid mingi teise saidi oma
	int y=prioriteedid[index][sait][abi];
	return new Point(x,y);
  }


  public Object kustutaGrupist(Object kylastaja,int sait,int grupp,int[] v�tanm�lu) {
    int index = kylastajad.indexOf(kylastaja);
    if(index == -1) return null;	//kui sellist userit ei olegi

	 int i=0;
	 while(grupiddd[index][sait][i]!=grupp) i++;
	  
	 grupiddd[index][sait][i]=0;
	 prioriteedid[index][sait][i]=0;
	 v�tanm�lu[0]=v�tanm�lu[0]-8;
	 return null;
  }


 public Object kustutaUser(Object kylastaja,int sait,int[] v�tanm�lu) {

    int index = kylastajad.indexOf(kylastaja);
    if(index == -1) return null;	//kui k�iki kustutatud tagastab -1

		for(int i=24;i>-1;i--){//ei kuulu enam yhtegi gruppi
			grupiddd[index][sait][i]=0;
			prioriteedid[index][sait][i]=0;
			v�tanm�lu[0]=v�tanm�lu[0]-8;
		}
	return null;
 }


public Set entrySet() {//peab olema - �ra kysi mix
    Set set = new HashSet();
    return set;
  }


  public static void main(String[] args) {
	  ACL2 �igused= new ACL2();
	  int port=10000;
	 
try {//loadin SQLi jaox draiveri
                     Class.forName("org.gjt.mm.mysql.Driver").newInstance();				 
					 }//lisasin draiveri
                 catch (Exception E) {
                     System.err.println("Ei suuda draiverit loadida");
                     E.printStackTrace();
                 }

try {
                 
				C = DriverManager.getConnection(//��sen SQL serverisse hell,kus abx on wahvel
				"jdbc:mysql://hell:3306/wahvel?user=wahvel&&password=everything");
				System.out.print("SQL serveriga yhendus loodud...\n>");
				in=new BufferedReader(new InputStreamReader(System.in));
				ss=new ServerSocket(port);
				stmt = C.createStatement();
}

catch (SQLException E) {
               System.out.println("SQLException: " + E.getMessage());
               System.out.println("SQLState:     " + E.getSQLState());
               System.out.println("VendorError:  " + E.getErrorCode());
			 }

catch (IOException ee) {
      System.err.println(ee);
}

piir[0]=20000;//default m�lu piirix 20MB
byte[] komaga=new byte[29];
komaga[6]=1;

Thread conf = new conf(v�tanm�lu,in,piir);
	conf.start();


int i=0;
boolean aps=false;
while(i<25){//loon esialgu 25 l�ime
	i++;

	kysimine = new kysimine(ss,v�tanm�lu,piir,�igused,memory,parentid,objekti_id,grupid,yhendused,
		stmt,komaga);
	kysimine.start();

  }
  System.out.print("L�imed valmis...\n>");
}
}