import java.awt.*;  
import java.awt.event.*;  
import java.applet.*;  
import java.net.*;  
import java.io.*;  
  
//protokoll kujul:  
//oid	alluvatearv		nimi	link	ikoonilink  
  

class pilt extends Canvas  
{  
	Image icon;  
	Image icon1;//niisama refresh 
	Image icon2;//mouse over refresh 
  
	pilt(Image gif) 
	{  
		this.setSize(20,24);  
		icon=gif;  
	}  
  
	public void paint(Graphics g)  
	{  
		
		g.drawImage(icon, 0, 0, this);  
	}  
}  
  


class branchRight extends Panel  
{//nimi  
	Label name;  
	pilt image;  
	boolean closed;  
	String nimi;  
	  
	branchRight(String nimii,pilt icon,boolean kinni)  
	{  
		nimi=nimii;  
		image=icon;  
		closed=kinni;  
		name=new Label(nimi);
		name.setSize(name.getSize().width,35);
		this.setLayout(new FlowLayout(FlowLayout.LEFT,0,0));  
		this.add(image); 
		this.add(name);  
		Cursor kursor=new Cursor(Cursor.HAND_CURSOR);  
		name.setCursor(kursor);  
		image.setCursor(kursor);  
	}  
  
  
	public void changeState(pilt see)  
	{  
		this.remove(image);  
		image=see;  
		this.add(image,0);  
	}  
}  
  
  
  
class level extends Canvas  
{ // | | | 
	int step;  
	boolean[] joon;  
  
	level(int astak,boolean[] jon)  
	{  
		step=astak;  
		joon=jon; 
		this.setSize(astak*17,24);  
	}  
  
	public void paint(Graphics g)  
	{  
		for(int i=0;i<step;i++)  
		{  
			if(!joon[i])  
			{   
				g.drawLine(7+17*i,0,7+17*i,24);  
			}  
		}  
	}  
}  
  
  
  
class branchLeft extends Canvas  
{//+ -  
	int state;//0-closed,1-open,2-neutral  
	boolean last;//viimane element  
  
	branchLeft(int asend,boolean viimane)  
	{  
		state=asend;  
		last=viimane;  
		Cursor kursor=new Cursor(Cursor.HAND_CURSOR);  
		this.setCursor(kursor);  
		this.setSize(17,24); 
	}  
  
	public void paint(Graphics g)  
	{  
		if(state==0)  
		{//kinni  
			g.drawLine(7,0,7,6);  
  
			g.drawRect(3,7,8,8);  
			g.drawLine(5,11,9,11);//-  
			g.drawLine(7,9,7,13);//|  
  
			g.drawLine(12,11,17,11);//-  
			  
			if(!last)  
			{  
				g.drawLine(7,16,7,24);  
			}  
		}  
		else  
		if(state==1)  
		{  
			g.drawLine(7,0,7,6);  
  
			g.drawRect(3,7,8,8);  
			g.drawLine(5,11,9,11);//-  
  
			g.drawLine(12,11,17,11);//-  
			  
			if(!last)  
			{  
				g.drawLine(7,16,7,24);  
			}  
		}  
		else  
		{//joon  
			g.drawLine(7,0,7,10);  
  
			g.drawLine(7,11,17,11); 
  
			if(!last)  
			{  
				g.drawLine(7,12,7,24);  
			}
			this.setCursor(new Cursor(Cursor.DEFAULT_CURSOR)); 
		}  
	}  
}  
  
  
  
class branch extends Panel  
{//nimi  
	branchLeft left;  
	branchRight right;  
	boolean last;//kas viimane  
	int step;//mitmes tase juurest alates  
	int state;//kas folderi ees on +, - või -- (folderi all pole rohkem foldereid)  
	branch[] slaves;  
	int count;//mitmes element nähtavas puus  
	int alluvaid;//mitu folderit otse tema all on  
	Panel aken;  
	Applet boss;  
	level empty;  
	Color back,mouse,select,text,labelcolor,select2;  
	String label,iconurl,status,url,font;  
	Image closeicon,openicon;  
	int oid;//objekti ID  
	boolean[] joon;//kas tõmmata folderist allapoole veel joon   
	  
	branch(String nimi,int astak,boolean laast,Panel aaken,Applet bos,Color b,Color m,Color s,Color t, 
		String closeurl,String openurl,String urll,int arv,int ooid,boolean[] jon,String fo)  
	{  
		back=b;				mouse=m;  
		select=s;			text=t;  
		boss=bos;			aken=aaken;  
		step=astak;			label=nimi;  
		last=laast;			oid=ooid;  
		alluvaid=arv;		status=urll;  
		joon=jon;			url=urll;  
		font=fo;			labelcolor=text; 
		
		
		int size;
		
		try
		{
			size=new Integer(boss.getParameter("font_size")).intValue();	  
		}
		catch(Exception e)
		{
			size=11;
		}

		try  
		{  
			URL url=new URL(openurl);  
			openicon=boss.getImage(url);  
		}  
		catch(java.net.MalformedURLException e)  
		{  
			System.out.println("Ei saanud ikooni kätte "+e); 
			System.out.println("URL: "+url); 
			//openicon=boss.getImage(boss.getCodeBase(),"openicon.gif");	  
		}  
 		catch(Exception e)  
		{  
			System.out.println("!!!Ei saanud ikooni kätte "+e); 
			System.out.println("URL: "+url); 
			//openicon=boss.getImage(boss.getCodeBase(),"openicon.gif"); 
		}		 
		closeicon=openicon;  
	/*AW ei kasuta veel eraldi ikoone, võidan kiiruses	  
		try  
		{  
			URL url=new URL(closeurl);  
			closeicon=boss.getImage(url);  
		}  
		catch(java.net.MalformedURLException e)  
		{  
			closeicon=boss.getImage(boss.getCodeBase(),"closeicon.gif");	  
		}  
	*/  
  
		if(oid!=-1) 
		{ 
			if(joon==null)  
			{  
				joon=new boolean[1];  
				joon[0]=last;  
			}  
			else  
			{  
				int i;  
				boolean[] abi=joon;  
				joon=new boolean[abi.length+1];  
				for(i=0;i<abi.length;i++)  
				{  
					joon[i]=abi[i];  
				}  
				joon[i]=last;  
			}  
		}//oid -1 
  
		if(alluvaid==0)  
		{//j0onistame joone  
			state=2;  
		}  
  
		this.setLayout(new FlowLayout(FlowLayout.LEFT,0,0));  	

		if((step>0)&&(oid!=-1))  
		{  
			empty=new level(step,joon);  
			empty.setBackground(back); 
			empty.setForeground(Color.black);		
			this.add(empty);  
		}  
		 
		if(oid==-1) 
		{//ytlen, et 1. objekt alati nähtav 
			state=1; 
		} 
		 
		left=new branchLeft(state,last);  
		 
		if(oid!=-1) 
		{ 
			left.setBackground(back);  
			left.setForeground(Color.black);
			this.add(left);  
			if(state!=2)  
			{  
				left.addMouseListener(new hiireKuular2(this,aken,boss));  
			}  
		} 
		  
		Font style=new Font(font,Font.PLAIN,size); 
 
		if((label.indexOf("<b>")!=-1)||(label.indexOf("<i>")!=-1)||(label.indexOf("<B>")!=-1)|| 
			(label.indexOf("<I>")!=-1)||(label.indexOf("<font ")!=-1)||(label.indexOf("<FONT ")!=-1)) 
		{ 
		//PURSIME HTMLI  !!!!!!! tee täpitähed
			String abi; 
			int stiil=0; 
			int bold=label.indexOf("<b>"); 
			if(bold==-1) 
			{ 
				bold=label.indexOf("<B>"); 
			} 
			if(bold!=-1) 
			{ 
				stiil=1; 
				abi=label.substring(0,bold); 
				label=abi+label.substring(bold+3);//<b> nimest kõrvaldatud 
				bold=label.indexOf("</b>"); 
				if(bold==-1) 
				{ 
					bold=label.indexOf("</B>"); 
				} 
				abi=label.substring(0,bold); 
				try 
				{ 
					label=abi+label.substring(bold+4);//</b> nimest kõrvaldatud 
				} 
				catch(java.lang.StringIndexOutOfBoundsException e) 
				{ 
					label=abi; 
				} 
			} 
			 
			bold=label.indexOf("<i>"); 
			if(bold==-1) 
			{ 
				bold=label.indexOf("<I>"); 
			} 
			if(bold!=-1) 
			{ 
				stiil=stiil+2; 
				abi=label.substring(0,bold); 
				label=abi+label.substring(bold+3);//<i> nimest kõrvaldatud 
				bold=label.indexOf("</i>"); 
				if(bold==-1) 
				{ 
					bold=label.indexOf("</i>"); 
				} 
				abi=label.substring(0,bold); 
				try 
				{ 
					label=abi+label.substring(bold+4);//</i> nimest kõrvaldatud 
				} 
				catch(java.lang.StringIndexOutOfBoundsException e) 
				{ 
					label=abi; 
				} 
			} 
 
			style=new Font(font,stiil,size); 
			int tag; 
			bold=label.indexOf("<font "); 
			if(bold==-1) 
			{ 
				bold=label.indexOf("<FONT "); 
			} 
			if(bold!=-1) 
			{ 
				tag=label.indexOf(">"); 
				String color="#000000"; 
				if((label.indexOf("#")!=-1)&&(label.indexOf("#")<tag)) color=label.substring(label.indexOf("#"),label.indexOf("#")+7); else 
				if((label.indexOf("black")!=-1)&&(label.indexOf("black")<tag)) color="#000000"; else 
				if((label.indexOf("silver")!=-1)&&(label.indexOf("silver")<tag)) color="#C0C0C0"; else 
				if((label.indexOf("gray")!=-1)&&(label.indexOf("gray")<tag)) color="#808080"; else 
				if((label.indexOf("white")!=-1)&&(label.indexOf("white")<tag)) color="#FFFFFF"; else 
				if((label.indexOf("maroon")!=-1)&&(label.indexOf("maroon")<tag)) color="#800000"; else 
				if((label.indexOf("red")!=-1)&&(label.indexOf("red")<tag)) color="#FF0000"; else 
				if((label.indexOf("purple")!=-1)&&(label.indexOf("purple")<tag)) color="#800080"; else 
				if((label.indexOf("fuchsia")!=-1)&&(label.indexOf("fuchsia")<tag)) color="#FF00FF"; else 
				if((label.indexOf("green")!=-1)&&(label.indexOf("green")<tag)) color="#008000"; else 
				if((label.indexOf("lime")!=-1)&&(label.indexOf("lime")<tag)) color="#00FF00"; else 
				if((label.indexOf("olive")!=-1)&&(label.indexOf("olive")<tag)) color="#808000"; else 
				if((label.indexOf("yellow")!=-1)&&(label.indexOf("yellow")<tag)) color="#FFFF00"; else 
				if((label.indexOf("navy")!=-1)&&(label.indexOf("navy")<tag)) color="#000080"; else 
				if((label.indexOf("blue")!=-1)&&(label.indexOf("blue")<tag)) color="#0000FF"; else 
				if((label.indexOf("teal")!=-1)&&(label.indexOf("teal")<tag)) color="#008080"; else 
				if((label.indexOf("aqua")!=-1)&&(label.indexOf("aqua")<tag)) color="#00FFFF";  
				 
				text=getColor(color); 
 
				abi=label.substring(0,bold); 
				label=abi+label.substring(tag+1);//<font ...> nimest kõrvaldatud 
				 
				bold=label.indexOf("</font>"); 
				tag=label.indexOf(">"); 
				if(bold==-1) 
				{ 
					bold=label.indexOf("</FONT>"); 
				} 
				abi=label.substring(0,bold); 
				try 
				{ 
					label=abi+label.substring(bold+7);//</i> nimest kõrvaldatud 
				} 
				catch(java.lang.StringIndexOutOfBoundsException e) 
				{ 
					label=abi; 
				} 
			} 
 
		} 
		right=new branchRight(label,(new pilt(closeicon)),true);  
		right.name.setBackground(back);   
		right.name.setForeground(text); 
		right.name.setFont(style); 
		if(oid==-1) 
		{		 
			right.name.setFont(new Font(font,Font.BOLD,size+1)); 
		}  
		this.add(right);
	
		right.name.addMouseListener(new hiireKuular(this,boss,false));  
		right.image.addMouseListener(new hiireKuular(this,boss,true));  
	}  
   
	public static int getNumber(String arv) 
	{//teen stringist 16-nd koodi arvust 10 koodi oma 
		int esimene=-1; 
		int abi=-1; 
		String first=arv.substring(0,1); 
		 
		for(int i=0;i<2;i++) 
		{ 
			try 
			{ 
				abi=new Integer(first).intValue();	 
			} 
			catch(NumberFormatException e) 
			{ 
				if((first.compareTo("a")==0)||(first.compareTo("A")==0))	abi=10; 
				if((first.compareTo("b")==0)||(first.compareTo("B")==0))	abi=11; 
				if((first.compareTo("c")==0)||(first.compareTo("C")==0))	abi=12; 
				if((first.compareTo("d")==0)||(first.compareTo("D")==0))	abi=13; 
				if((first.compareTo("e")==0)||(first.compareTo("E")==0))	abi=14; 
				if((first.compareTo("f")==0)||(first.compareTo("F")==0))	abi=15; 
			} 
					 
			if(i==1) 
			{ 
				break; 
			} 
			esimene=abi; 
			first=arv.substring(1); 
			abi=-1; 
		}//for 
 
		if((abi==-1)||(esimene==-1)) 
		{ 
			return -1; 
		} 
		return esimene*16+abi; 
	} 
	 
 
 
	public static Color getColor(String arv) 
	{ 
		if(arv.indexOf("#")==-1) 
		{ 
			return null; 
		} 
 
		int red=getNumber(arv.substring(1,3)); 
		int green=getNumber(arv.substring(3,5)); 
		int blue=getNumber(arv.substring(5)); 
 
		if((red==-1)||(green==-1)||(blue==-1)) 
		{ 
			return null; 
		} 
		 
		return new Color(red,green,blue); 
	} 
 
 
	public void addSlaves(branch[] uued)  
	{//lisan uued alluvad  
		slaves=uued;  
		alluvaid=slaves.length;  
	}  
}  
  

//======================= KAUSTADE KINNI/LAHTI KLIKKAMINE ===============================
  
 
class hiireKuular2 implements MouseListener  
{  
	Applet boss;  
	Panel aken,fixer;  
	branch folder; 
	int jrk,i,j,pikkus; 
	String font,session,end;
	GridBagConstraints cc;
	GridBagLayout layout;
	Component[] jada;
	ScrollPane scroll;
  
	hiireKuular2(branch fold,Panel aaken,Applet bos)  
	{  
		cc=((menuTree)bos).cc;
		layout=((menuTree)bos).base;
		scroll=((menuTree)bos).scroll;
		fixer=((menuTree)bos).fixer;
		boss=bos;  
		aken=aaken;  
		folder=fold; 
 
		font=boss.getParameter("font"); 
		session=boss.getParameter("session"); 
		end=((menuTree)boss).end; 
	}  
  
	public void mousePressed(MouseEvent e)	{}  
	public void mouseReleased(MouseEvent e)  
	{   
		if(folder.left.state==0)  
		{//klikkan plussil  
			jada=aken.getComponents();  
			//saan parasjagu nähtavad folderid  
			pikkus=jada.length-1;//viimane element on fixer  
 
			folder.left.state=1;  
			folder.left.repaint(); 
  
 // System.out.println("alluvaid="+folder.alluvaid+"    koht="+folder.count+"  nimi="+folder.label+"   lehel komponente="+pikkus); 
 
			if(folder.slaves==null)  
			{//pole veel alluvaid sissetõmmatud
			System.out.println("PEAN ISE TÕMBAMA");
 
				branch[] liidetavad=new branch[folder.alluvaid];  
				  
				byte[] array=new byte[1];  
				String aadress=boss.getParameter("url")+"/automatweb/orb.aw?class=menuedit&action=get_branch&parent="+folder.oid+"&automatweb="+session+end;  
				 
				 
//System.out.println("Kysin URL: "+aadress);				 
				try  
				{  
					InputStream sisse=new URL(aadress).openConnection().getInputStream();  
					int in=sisse.read();  
					byte[] array2;  
					array[0]=(new Integer(in)).byteValue();  
  
					while(in>0)  
					{  
						array2=array;  
						array=new byte[array2.length+1];//IE ei toeta vectoreid  
						for(i=0;i<array2.length;i++)  
						{  
							array[i]=array2[i];  
						}  
						in=sisse.read();  
						array[i]=(new Integer(in)).byteValue();  
					}  
  
					sisse.close();   
 
					String puu=new String(array);  
					//System.out.println("Sain: "+puu); 
	  
	//============== Puu käes, It's a parsing time! ===========================  
			  
	//oid	alluvatearv		nimi	link	ikoonilink  
	  
					int oid,alluvaid,tab;  
					String nimi,iconurl,url;  
					boolean last=false;  
	  
					tab=puu.indexOf("	");  
					i=0;  

					while(tab!=-1)  
					{  
						  
						if(tab==-1)  
						{  
							break;  
						}  
						oid=new Integer(puu.substring(0,tab)).intValue();  
						puu=puu.substring(tab+1);  
						tab=puu.indexOf("	");  
						  
						alluvaid=new Integer(puu.substring(0,tab)).intValue();  
						puu=puu.substring(tab+1);  
						tab=puu.indexOf("	");  
	  
						nimi=puu.substring(0,tab);  
						puu=puu.substring(tab+1);  
						tab=puu.indexOf("	");  
	  
						url=puu.substring(0,tab);  
						puu=puu.substring(tab+1);  
						tab=puu.indexOf("\n");  
	  
						iconurl=puu.substring(0,tab);  
						puu=puu.substring(tab+1);  
						tab=puu.indexOf("	");  
						  
						if(tab==-1)  
						{  
							last=true;  
						}  
						
						branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.labelcolor,iconurl,iconurl,url,alluvaid,oid,folder.joon,font);  
						first.count=folder.count+i+1;  
						layout.setConstraints(first,cc);
						aken.add(first,first.count);					
						aken.doLayout(); 
						first.doLayout();  
						first.right.doLayout();  
						try 
						{ 
							liidetavad[i]=first;  
						} 
						catch(ArrayIndexOutOfBoundsException ee) 
						{//alluvaid oli rohkem, kui väideti 
							branch[] abi=new branch[liidetavad.length+1]; 
							for(j=0;j<liidetavad.length;j++) 
							{ 
								abi[j]=liidetavad[j]; 
							} 
							abi[j]=first; 
							liidetavad=abi; 
						} 
						if(tab==-1)  
						{  
							break;  
						}  
						i++;  
					}  
					
					//see jama sellex, et see krdi puu ei sõidax suvalt mööda paneeli ringi
					layout.removeLayoutComponent(fixer);
					aken.remove(fixer);
					aken.doLayout();
					scroll.doLayout();					
					cc.weighty = 1.0;
					layout.setConstraints(fixer, cc);
					aken.add(fixer,-1);
					aken.doLayout();
					scroll.doLayout();
					cc.weighty = 0.0;

					if((i+1)<folder.alluvaid) 
					{//alluvaid oli vähem, kui väideti 
						branch[] abi2=new branch[liidetavad.length+1]; 
						for(j=0;liidetavad[j]!=null;j++) 
						{ 
							abi2[j]=liidetavad[j]; 
						} 
						liidetavad=abi2; 
					} 
if((i+1)!=folder.alluvaid) 
{ 
	System.out.println("ERINEVUS "+folder.label+" pidi olema "+folder.alluvaid+" alluvat, oli "+i); 
	 
}					 

					for(j=folder.count+1;j<pikkus;j++)//viimane element on fixer  
					{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 	 
						branch uus2=(branch)jada[j];
						uus2.count+=(i+1);	 
					}  
 					
					folder.addSlaves(liidetavad); 				
				}  
				catch(IOException ee)  
				{  
					System.out.println("IOKala: "+ee);  
				}	  
			}  
			else  
			{ 		 
				for(i=0;i<folder.alluvaid;i++)  
				{//lisan uued nähtavad folderid   
					folder.slaves[i].count=folder.count+1+i;	 					  
					layout.setConstraints(folder.slaves[i], cc);
					aken.add(folder.slaves[i],folder.slaves[i].count);    

					aken.doLayout();   
					folder.slaves[i].doLayout();  
					folder.slaves[i].right.doLayout();  
				}  
 
				//see jama sellex, et see krdi puu ei sõidax suvalt mööda paneeli ringi
					layout.removeLayoutComponent(fixer);
					aken.remove(fixer);
					aken.doLayout();
					scroll.doLayout();					
					cc.weighty = 1.0;
					layout.setConstraints(fixer, cc);
					aken.add(fixer,-1);
					aken.doLayout();
					scroll.doLayout();
					cc.weighty = 0.0;

				for(i=folder.count+1;i<pikkus;i++)  
				{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra  
					((branch)jada[i]).count+=folder.alluvaid; 
				} 
			} 
		}  
		else  
		if(folder.left.state==1)  
		{//klikkan miinusel  
			folder.left.state=0;  
			folder.left.repaint();  
  
			jada=aken.getComponents();  
			//saan parasjagu nähtavad folderid  
  
			i=folder.count+1;  
 
			try  
			{  
				while(((branch)jada[i]).step>folder.step)  
				{  
					if(((branch)jada[i]).left.state==1)  
					{  
						((branch)jada[i]).left.state=0;  
					}  
					layout.removeLayoutComponent((branch)jada[i]);
					aken.remove(folder.count+1);  
					i++;  
				} 
				aken.doLayout(); 
			    scroll.doLayout();
			}  
			catch(Exception ee)  
			{  
			}  
 
			jada=aken.getComponents();  
			i=i-folder.count-1; 
 
			for(j=folder.count+1;j<jada.length-1;j++)  
			{//annan teada, et nähtavad folderid nihkuvad ülespoole folder.alluvaid foldri võrra 				 
				branch uus=(branch)jada[j];	 
				uus.count-=i; 
			} 
		} 
	}  
	public void mouseEntered(MouseEvent e){}  
	public void mouseExited(MouseEvent e){}  
	public void mouseClicked(MouseEvent e)	{}  
}  
 
  
  
class hiireKuular implements MouseListener  
{  
	Applet boss;  
	branch ox;  
	static branch previous;  
	Color back,text,select,mouse,select2;  
	boolean ikoonil;
	URL url;
  
	hiireKuular(branch oxx,Applet bos,boolean il)  
	{  
		ox=oxx;  
		boss=bos;  
		back=ox.back;  
		text=ox.text;  
		select=ox.select;  
		mouse=ox.mouse;  
		ikoonil=il; 
		select2=((menuTree)boss).select2;
	}  
  
	public void mousePressed(MouseEvent e)	{}  
	public void mouseReleased(MouseEvent e)  
	{  
		if(ox.right.closed)  
		{  
			//ox.right.image.icon=ox.openicon;  
			ox.right.name.setBackground(select);  
			ox.right.name.setForeground(select2);  
			//ox.right.image.repaint();  
			ox.right.closed=false;  
			if(previous!=null)  
			{//panen eelmise kinni  
				//previous.right.image.icon=previous.closeicon;  
				previous.right.name.setBackground(back);  
				previous.right.name.setForeground(text);  
				//previous.right.image.repaint();  
				previous.right.closed=true;  
			} 
		} 
 
		try  
		{  
			url=new URL(ox.url);  
			if(ikoonil) 
			{ 
				boss.getAppletContext().showDocument(url,ox.label); 
			} 
			else 
			{ 
				boss.getAppletContext().showDocument(url,"list");  
			} 
		}  
		catch(java.net.MalformedURLException ee)  
		{  
			System.out.println("Lehte ei leitud "+ee); 
			System.out.println("URL: "+ox.url); 
			//siin võiks visata PAGE NOT FOUND  
		}  
		previous=ox;   
	} 
	
	public void mouseEntered(MouseEvent e)  
	{  
		ox.right.name.setBackground(mouse);   
		ox.right.name.setForeground(select2);  
		boss.getAppletContext().showStatus("URL: "+ox.status);  
	}  

	public void mouseExited(MouseEvent e)  
	{  
		if(ox.right.closed)  
		{  
			ox.right.name.setBackground(back);   
			ox.right.name.setForeground(text);   
		}  
		else  
		{  
			ox.right.name.setBackground(select);  
		}  
		boss.getAppletContext().showStatus("");  
	}  
	public void mouseClicked(MouseEvent e)	{}  
}  
  
  
  
/*class recall extends Thread 
{ 
	Panel aken,topp; 
	Applet boss; 
	Socket s; 
	static PrintStream to; 
	static BufferedReader from; 
	int jrk; 
	String font,session,end; 
	InputStream sisse;
	pilt refresh;
	ScrollPane scroll;
	GridBagConstraints cc;
	GridBagLayout layout;
 
	recall(Applet bos) 
	{ 
		boss=bos; 
		s=((menuTree)bos).s;
		aken=((menuTree)bos).aken; 
		end=((menuTree)boss).end; 
		topp=((menuTree)boss).nupp;
		refresh=((menuTree)boss).refresh;
		scroll=((menuTree)boss).scroll;
		cc=((menuTree)bos).cc;
		layout=((menuTree)bos).base;
	} 
 
 
	public void crash() 
	{ 
		System.out.println("Crash"); 
		try 
		{                                                                                                                                                                                                               
			yield();
			interrupt();                                                                                                                                                                                                 	 
		}                                                                                                                                                                                                              	 
		catch(java.lang.NoSuchMethodError e)                                                                                                                                                                           	 
		{                                                                                                                                                                                                              	 
			System.out.println("Lõim recall lõpetas töö");                                                                                                                                                             	 
		    
			//if(topp.getComponentCount()<2)
			if(refresh==null)
			{//pole juba nuppu, millegi pärast teeb 2 korda			                                                                                                                                               	 
				try                                                                                                                                                                                                	 
				{                                                                                                                                                                                                  	 
					URL urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh.gif");                                                                                                         	 
					refresh=new pilt(boss.getImage(urll));                                                                                                                                                    	 
					refresh.setSize(25,25);                                                                                                                                                                        	 
					refresh.icon1=refresh.icon;                                                                                                                                                                    	 
					urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh_over.gif");                                                                                                        	 
					refresh.icon2=boss.getImage(urll);                                                                                                                                                             	 
					topp.add(refresh,0);                                                                                                                                                                            	 
					topp.doLayout();                                                                                                                                                                                	 
					refresh.addMouseListener(new refreshKuular(refresh,boss));                                                                                                                                     	 
				}                                                                                                                                                                                                  	 
				catch(java.net.MalformedURLException ee)                                                                                                                                                           	 
				{                                                                                                                                                                                                  	 
					System.out.println("Ei saanud ikooni kätte "+e);                                                                                                                                               	 
				}                                                                                                                                                                                                  	 
				catch(Exception ee)                                                                                                                                                                                	 
				{                                                                                                                                                                                                  	 
					System.out.println("!!!Ei saanud ikooni kätte "+e);                                                                                                                                            	 
				}                                                                                                                                                                                                  	 
			}//if           																																																			

			if(boss.getComponentCount()==1)                                                                                                                                                                             	 
			{                                                                                                                                                                                                          	                                                                                                                                                         	                                                                                                                                                                                                                
				boss.add(topp,"North");
				boss.doLayout();                                                                                                                                                                                       	 
				topp.doLayout();   
			}//if                                                                                                                                                                                                      	 
			
			try 
			{ 
				destroy(); 
			} 
			catch(java.lang.NoSuchMethodError ee) 
			{ 
			} 
		}//catch                                                                                                                                                                                                              	 
		System.gc(); 
	} 
 
 
	public void run() 
	{  	 
		try 
		{ 
			to=new PrintStream(s.getOutputStream()); 
			from=new BufferedReader(new InputStreamReader(s.getInputStream())); 
			to.println("0 "+(new Integer(boss.getParameter("sait")).intValue())); 
		} 
		catch(IOException e) 
		{
			crash();
		} 
			 
		byte[] array=new byte[1];  
		boolean last=false;  
		boolean over=true; 
		String aadress,nimi,iconurl,url,puu; 
		int oid,j,i,alluvaid,tab,in,k,kk,kkk,jj,pikkus,astak,liidetavaid; 
		branch[] jada3; 
		Component[] riba=aken.getComponents(); 
		Component[] jada5;                                                                                                                                                                                                                                     
		String session=boss.getParameter("session"); 
		String font=boss.getParameter("font"); 
		int top=new Integer(boss.getParameter("rootmenu")).intValue(); 
		boolean edasi,pea;
		k=0; 
 
		branch[] jada=new branch[riba.length-1];//fixer on viimane element 
		 
		for(i=0;i<jada.length;i++) 
		{//saan peaharud 
			jada[i]=(branch)riba[i]; 
		} 
		jada[0].oid=top; 
		jada[0].addSlaves(jada);		 
		String vastus=""; 
 
		while(true) 
		{ 
			try 
			{ 
				vastus=from.readLine(); 
	//System.out.println("Vastus: "+vastus); 
				oid=new Integer(vastus).intValue();//muudeti seda haru   
				edasi=true;                                                                                      	 
				pea=false;                                                                                       	                	 
				branch[] jada2=jada;                                                                                     	 
				jada3=jada;                                                                                              	 
				j=0;     
				branch folder=null;   
				if(oid==top) 
				{ 
					//oid=-1; 
					folder=jada[0]; 
				} 
 
				//while((true)&&(oid!=top))
				while(oid!=top)	
				{                                                                                                        	 		   
					for(i=1;i<jada2.length;i++) //i=0                                                                         	 
					{                                                                                                    	 
						//System.out.println("Uuritav"+jada2[i].oid+"  "+jada2[i].label+" "+oid+" i="+i); 
						if(jada2[i].oid!=oid)                                                                            	 
						{                                                                                                	 
							if(jada2[i].slaves!=null)                                                                    	 
							{                                                                                            	 
								for(k=0;k<jada2[i].slaves.length;k++)                                                    	 
								{                                                                                        	 
									//System.out.println("Uurin: "+jada2[i].slaves[k].oid+"  "+oid+" i="+i); 
									if(jada2[i].slaves[k].oid==oid)                                                      	 
									{//leidsin alluvatest                                                                	 
										edasi=false;                                                                     	 
										folder=jada2[i].slaves[k];                                                       	 
										break;                                                                           	 
									}                                                                                    	 
								}                                                                                        	 
								                                                                                         	 
								if(edasi)                                                                                	 
								{                                                                                        	 
									branch[] abi=new branch[jada3.length];                                               	 
									abi=jada3;                                                                           	 
									jada3=new branch[abi.length+jada2[i].slaves.length];                                 	 
									for(k=0;k<abi.length;k++)                                                            	 
									{                                                                                    	 
										jada3[k]=abi[k];                                                                 	 
									}                                                                                    	 
									for(k=0;k<jada2[i].slaves.length;k++)                                                	 
									{                                                                                    	 
										jada3[k+abi.length]=jada2[i].slaves[k];                                          	 
									}                                                                                    	 
								}                                                                                        	 
								else                                                                                     	 
								{                                                                                        	 
									break;                                                                               	 
								}                                                                                        	 
							}//if null                                                                                   	 
						}//if slaves.oid!=oid                                                                            	 
						else                                                                                             	 
						{//peaharu muutus                                                                                	 
							edasi=false;                                                                                 	 
							folder=jada2[i];                                                                             	 
							pea=true;                                                                                    	 
							break;                                                                                       	 
						}                                                                                                	 
					}//for                                                                                               	 
					if(!edasi)                                                                                           	 
					{                                                                                                    	 
						break;                                                                                           	 
					}                                                                                                    	 
					jada2=jada3;	                                                                                     	 
				}//while                                                                                                 	 
				                                                                                                          
//================== LEIDSIN MUUDETUD =============================== 
 
System.out.println("Leidsin muudetud: "+folder.label+" alluvaid="+folder.alluvaid); 
				 
				byte[] array2; 
				try 
				{		 
							if(folder.oid!=top) 
							{ 
								aadress=boss.getParameter("url")+"/automatweb/orb.aw?class=menuedit&action=get_branch&parent="+folder.oid+"&automatweb="+session+end; 						                                                                                                                                 	 
						    } 
							else 
							{ 
								aadress=boss.getParameter("url")+"/automatweb/orb.aw?class=menuedit&action=get_branch&automatweb="+session+end; 	 
							} 
							over=false;                                                                                                                  	 
							//branch[] liidetavad=new branch[folder.alluvaid];
							branch[] liidetavad=new branch[0];
							array=new byte[1];                                              	                                                         	 
							try
							{
								sisse=new URL(aadress).openConnection().getInputStream();       	                                                         	 
								in=sisse.read();                                                	                                                         	 
								array[0]=(new Integer(in)).byteValue();                         	                                                         	 
																																								 
								while(in>0)                                                     	                                                         	 
								{                                                               	                                                         	 
									array2=array;                                               	                                                         	 
									array=new byte[array2.length+1];//IE ei toeta vectoreid     	                                                         	 
									for(i=0;i<array2.length;i++)                                	                                                         	 
									{                                                           	                                                         	 
										array[i]=array2[i];                                     	                                                         	 
									}                                                           	                                                         	 
									in=sisse.read();                                            	                                                         	 
									array[i]=(new Integer(in)).byteValue();                     	                                                         	 
								}                                                               	                                                         	 
																																								 
								sisse.close();                                                  	                                                         	 
								puu=new String(array);     
							}
							catch(Exception ee)
							{
								puu="";
							}
	  System.out.println("Uued alluvad: "+puu);	                                                                                                                               	 
//============================= Puu käes, It's a parsing time! ====================================  
				  
		//oid	alluvatearv		nimi	link	ikoonilink  
		 						if(folder.oid==top) 
								{ 
									astak=0; 
								} 
								else 
								{ 
									astak=folder.step+1; 
								} 
 
								tab=puu.indexOf("	");                                                                                                                                                                                                                                          
								j=0;                                                                                                                                                                                                                                                            	 
								last=false;                                                                                                                                                                                                                                                     	 
								while(tab!=-1)                                                                                                                                                                                                                                                  	 
								{                                                                                                                                                                                                                                                               	 
									                                                                                                                                                                                                                                                            	 
									if(tab==-1)                                                                                                                                                                                                                                                 	 
									{                                                                                                                                                                                                                                                           	 
										break;                                                                                                                                                                                                                                                  	 
									}                                                                                                                                                                                                                                                           	 
									oid=new Integer(puu.substring(0,tab)).intValue();                                                                                                                                                                                                           	 
									puu=puu.substring(tab+1);                                                                                                                                                                                                                                   	 
									tab=puu.indexOf("	");                                                                                                                                                                                                                                     	 
									                                                                                                                                                                                                                                                            	 
									alluvaid=new Integer(puu.substring(0,tab)).intValue();                                                                                                                                                                                                      	 
									puu=puu.substring(tab+1);                                                                                                                                                                                                                                   	 
									tab=puu.indexOf("	");                                                                                                                                                                                                                                     	 
					 			                                                                                                                                                                                                                                                                 
									nimi=puu.substring(0,tab);                                                                                                                                                                                                                                  	 
									puu=puu.substring(tab+1);                                                                                                                                                                                                                                   	 
									tab=puu.indexOf("	");                                                                                                                                                                                                                                     	 
					 			                                                                                                                                                                                                                                                                 
									url=puu.substring(0,tab);                                                                                                                                                                                                                                   	 
									puu=puu.substring(tab+1);                                                                                                                                                                                                                                   	 
									tab=puu.indexOf("\n");                                                                                                                                                                                                                                      	 
					 			                                                                                                                                                                                                                                                                 
									iconurl=puu.substring(0,tab);                                                                                                                                                                                                                               	 
									puu=puu.substring(tab+1);                                                                                                                                                                                                                                   	 
									tab=puu.indexOf("	");                                                                                                                                                                                                                                     	 
									                                                                                                                                                                                                                                                            	 
									if(tab==-1)                                                                                                                                                                                                                                                 	 
									{                                                                                                                                                                                                                                                           	 
										last=true;                                                                                                                                                                                                                                              	 
									}                                                                                                                                                                                                                                                           	 
									branch first=new branch(nimi,astak,last,aken,boss,folder.back,folder.mouse,folder.select,folder.labelcolor,iconurl,iconurl,url,alluvaid,oid,folder.joon,font);                                                                                               	 
			 
									try                                                                                                                                                                                                                                                         	 
									{                                                                                                                                                                                                                                                           	 
										liidetavad[j]=first;                                                                                                                                                                                                                                    	 
									}                                                                                                                                                                                                                                                           	 
									catch(ArrayIndexOutOfBoundsException ee)                                                                                                                                                                                                                    	 
									{//alluv liideti juurde, ei tea veel milline 
										branch[] abi=new branch[liidetavad.length+1];                                                                                                                                                                                                           	 
								                                                                                                                                                                                                                                                                 
										for(k=0;k<liidetavad.length;k++)                                                                                                                                                                                                                        	 
										{                                                                                                                                                                                                                                                       	 
											abi[k]=liidetavad[k];                                                                                                                                                                                                                               	 
										}                                                                                                                                                                                                                                                       	 
										abi[k]=first;                                                                                                                                                                                                                                           	 
										liidetavad=abi;                                                                                                                                                                                                                                         	 
									}            									
									j++;
									
									if(tab==-1)                                                                                                                                                                                                                                                 	 
									{                                                                                                                                                                                                                                                           	 
										break;                                                                                                                                                                                                                                                  	 
									}                                                                                                                                                                                                                                                           	                                                                                                                                                                                                                                                         	 
								}//while                                                                                                                                                                                                                                                        	 
//===================== ALLUV LISATI ==================================						    
								if(j>folder.alluvaid)                                                                                                                                                                                                                                       	 
								{									
								    edasi=true; 
									liidetavaid=0; 
									branch[] abi=folder.slaves; 

									for(kk=0;kk<liidetavad.length;kk++) 
									{ 
										for(k=0;k<folder.alluvaid;k++) 
										{ 
											edasi=false; 
											if((folder.slaves[k].oid==liidetavad[kk].oid)||(liidetavad[kk].oid==0)) 
											{												 
												
												liidetavad[kk]=folder.slaves[k];//nii saavutan õige järjekorra (annan yle koos alluvatega)	!!!
												if(folder.left.state==1)                                                                                  
												{//alluvad nähtavad, tuleb alluvaid suurendada 1 võrra                                                            	 
												    liidetavad[kk].count+=liidetavaid;                                                               	 
												} 
												
												if (kk!=liidetavad.length-1)
												{
													liidetavad[kk].left.last=false;
													liidetavad[kk].left.repaint();
												}
												
												edasi=true; 
												break; 
											}
											
											if(!edasi)
											{
												
												if(folder.left.state==1)                                                                                  
												{//alluvad nähtavad, tuleb alluvaid suurendada 1 võrra                                                            	 
													liidetavaid++;
													
													
													branch uus2=liidetavad[kk-1];
													jj=uus2.count+1;

													while (true)
													{
														if (uus2.left.state==1)
														{//eelmine alluv on lahti	
															uus2=uus2.slaves[uus2.slaves.length-1];
															jj=uus2.count+1;
														}
														else
														{
															break;
														}
													}
													
													layout.setConstraints(liidetavad[kk], cc);
													aken.add(liidetavad[kk],jj);
													//aken.add(liidetavad[kk],liidetavad[kk-1].count+1);                                                   	 
													//liidetavad[kk].count=liidetavad[kk-1].count+1;
													liidetavad[kk].count=jj;
													aken.doLayout();                                                                                     	                                                                               	   
													liidetavad[kk].doLayout();                                                                         	 
													liidetavad[kk].right.doLayout();                                                                   	 
												}
												else                                                                                                     	 
												{//ei olnud lahti, teeme + ette                                                                                                        	 
													folder.left.state=0;                                                                                 	 
													folder.left.repaint();	
												}
												break;
											}
										} 
									}//for 

									aken.doLayout();                      		                                                                   	 
									scroll.doLayout(); 

									folder.addSlaves(liidetavad);
									if(folder.oid==top)                                                                                      	 
									{                                                                                                        	 
										folder.slaves[0].oid=top;                                                                            	 
										jada=folder.slaves;                                                                                  	 
									}      
									 
									if(folder.left.state==1) 
									{//nähtav, pean teada andma, et _kôik_ nihkusid allapoole 
										jada5=aken.getComponents();                                                                          	 
											                                                                                                         	 
										for(j=folder.slaves[folder.alluvaid-1].count+1;j<jada5.length-1;j++)//fixer lõpus                                                  
										{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 	             	 
											branch uus2=(branch)jada5[j];                                         		 
											uus2.count+=liidetavaid; 
										}    			                                                                                                	 
									}                		                                                                   	    		                                                                   	 
								} 
								else 
//===================== ALLUV EEMALDATI =============================== 
								if(j<folder.alluvaid)                                                                                                                                                                                                                                       	 
								{  
									liidetavaid=0;

									for(k=0;k<folder.alluvaid;k++) 
									{ 
										for(kk=0;kk<liidetavad.length;kk++) 										
										{ 
											edasi=false; 
											if((folder.slaves[k].oid==liidetavad[kk].oid)||(liidetavad[kk].oid==0)) 
											{												 
												
												liidetavad[kk]=folder.slaves[k];//nii saavutan õige järjekorra (annan yle koos alluvatega)	!!!
												if(folder.left.state==1)                                                                                  
												{//alluvad nähtavad, tuleb alluvaid vähendada 1 võrra                                                            	 
												    liidetavad[kk].count-=liidetavaid;                                                               	 
												} 
												
												edasi=true; 
												break; 
											}
											
											if(!edasi)
											{	
												if ((kk==folder.alluvaid-1)&&(kk!=0))
												{//kui kaotati viimane, siis eelmine viimasex
													liidetavad[kk-1].left.last=true;
													liidetavad[kk-1].left.repaint();
												}
												
												if(folder.left.state==1)                                                                                  
												{//alluvad nähtavad, tuleb alluvaid vähendada 1 võrra                                                            	 			
													branch uus2=folder.slaves[k];//see kustutati, kontrollin, kas oli ka lahtiseid alluvaid
													riba=aken.getComponents();  
													//saan parasjagu nähtavad folderid  
										  
													i=uus2.count+1;  
										 
													try  
													{  
														while(((branch)riba[i]).step>folder.step)  
														{  
															if(((branch)riba[i]).left.state==1)  
															{  
																((branch)riba[i]).left.state=0;  
															}  
															layout.removeLayoutComponent((branch)riba[i]);
															aken.remove(uus2.count+1);  
															i++;  
														}
														layout.removeLayoutComponent(uus2);
														aken.remove(uus2.count);
														i=i-uus2.count; 
														liidetavaid=liidetavaid+i;
														aken.doLayout(); 
														scroll.doLayout();
													}  
													catch(Exception ee)  
													{  
													}  
										 
													riba=aken.getComponents();  
													
										 
													for(j=uus2.count+1;j<riba.length-1;j++)  
													{//annan teada, et nähtavad folderid nihkuvad ülespoole folder.alluvaid foldri võrra 				 
														branch uus=(branch)riba[j];	 
														uus.count-=i; 
													}                                                          	 
												}
												else                                                                                                     	 
												{//ei olnud lahti
													if (liidetavad.length==0)
													{//pole yhtegi alluvat
														folder.left.state=2;
														folder.repaint();
													}	
												}
												break;
											}
										}// for 
									}//for 

									folder.addSlaves(liidetavad); 
									 
									if(folder.oid==top)           		                                                                                                            	 
									{                             		                                                                                                            	 
										folder.slaves[0].oid=top; 		                                                                                                            	 
										jada=folder.slaves;       		                                                                                                            	 
									}                             		                                                                                                            	 
									
								}//alluv eemaldati 
//==================== KATALOOGI NIME/JÄRJEKORDA MUUDETI ==========================								 
								else 
								{ //muudeti nime või järjekorda     SIIA JUURDE VAJA JUHTU, KUI MISKIT ON LAHTI TEHTUD !!!
									for(k=0;k<folder.alluvaid;k++) 
									{ 
										for(kk=0;kk<liidetavad.length;kk++) 										
										{ 
											if((folder.slaves[k].oid==liidetavad[kk].oid)||(liidetavad[kk].oid==0)) 
											{												 	
												liidetavad[kk]=folder.slaves[k];//nii saavutan õige järjekorra (annan yle koos alluvatega)							 
												break; 
											}
										}//for
									}//for
									
									folder.addSlaves(liidetavad);				
								}//rename 
						}//try 
						catch(Exception ee) 
						{ 
							System.out.println("ERROR: "+ee); 
						} 
			}//try 
			catch(SocketException e) 
			{ 
				System.out.println("SocketException: "+e); 
				break; 
			} 
			catch(IOException e) 
			{ 
				System.out.println("IOException: "+e); 
				break; 
			} 
			catch(java.lang.NumberFormatException e) 
			{ 
				if(vastus==null) 
				{ 
					break; 
				} 
				to.println("PONG"); 
			} 
			catch(Exception e) 
			{ 
				System.out.println("Värskendamisel viga: "+e); 
				if((s==null)||(vastus==null)) 
				{ 
					break; 
				} 
			} 		 
		}//while 		
		crash(); 
	} 
} */ 
  
 
 
class refreshKuular implements MouseListener  
{  
	pilt foto; 
	Applet boss; 
 
	refreshKuular(pilt fot,Applet bos)  
	{  
		foto=fot; 
		boss=bos; 
	}  
  
	public void mousePressed(MouseEvent e)	{}  
	public void mouseReleased(MouseEvent e) {}  
	public void mouseEntered(MouseEvent e)  
	{  
		foto.icon=foto.icon2; 
		foto.repaint(); 
	}  
	public void mouseExited(MouseEvent e)  
	{  
		foto.icon=foto.icon1; 
		foto.repaint(); 
	}  
	public void mouseClicked(MouseEvent e) 
	{ 
		boss.destroy();

		/*((menuTree)boss).end="";	
		((menuTree)boss).base=new GridBagLayout();
		((menuTree)boss).cc=new GridBagConstraints();
		((menuTree)boss).aken=new Panel();
		((menuTree)boss).scroll=new ScrollPane(ScrollPane.SCROLLBARS_AS_NEEDED);
		((menuTree)boss).fixer=new Panel();
		((menuTree)boss).nupp=new Panel();*/
		boss.init();
	 
		Component[] jada=((menuTree)boss).aken.getComponents(); 
		
		boss.doLayout();
		((menuTree)boss).scroll.doLayout();

		for(int i=0;i<jada.length-1;i++) 
		{ 
			((menuTree)boss).aken.doLayout();
			((branch)jada[i]).doLayout(); 
			((branch)jada[i]).right.doLayout(); 			 
		}
		
		((menuTree)boss).nupp.doLayout();
	}  
}  
 
 
 
class tirija extends Thread 
{ 
	Applet boss; 
	branch[] jada; 
	Panel aken; 
	String end; 
 
	tirija(Applet bos) 
	{ 
		boss=bos; 
		end=((menuTree)boss).end;
		aken=((menuTree)boss).aken;
	} 
 
 
	public void run() 
	{ 
		branch[] jada2,abi,abi2; 
		branch[] jada3=new branch[0];
		byte[] array,array2;
 
		int oid,alluvaid,tab,in,i,j,k,kk;  
		String nimi,iconurl,url,aadress,puu;  
		boolean last=false;  
		InputStream sisse; 
		boolean over=true;
		branch folder;
		
	 
		Component[] riba=aken.getComponents(); 
 
		branch[] jada=new branch[riba.length-2]; 
		for(i=1;i<jada.length+1;i++) //viimane element on fixer,seda ei loe
		{//saan alguses lehel oleva stuffi 
			jada[i-1]=(branch)riba[i]; 
		} 
		String session=boss.getParameter("session"); 
		String font=boss.getParameter("font"); 
 
		while (true) 
		{ 
			for(i=0;i<jada.length;i++) 
			{ 
				folder=jada[i]; 
				if((folder.slaves==null)&&(folder.alluvaid>0))  
				{//pole veel alluvaid sisse tõmmatud ja on mida tõmmata 
 					over=false; 
					branch[] liidetavad=new branch[folder.alluvaid];  
				 
					array=new byte[1];   
					aadress=boss.getParameter("url")+"/automatweb/orb.aw?class=menuedit&action=get_branch&parent="+folder.oid+"&automatweb="+session+end;  
					 					 
					try                                                                                            	 
					{                                                                                              	 
						sisse=new URL(aadress).openConnection().getInputStream();                      	 
						in=sisse.read();                                                                       	                                                                             	 
						array[0]=(new Integer(in)).byteValue();                                                    	 
																												    
						while(in>0)                                                                                	 
						{                                                                                          	 
							array2=array;                                                                          	 
							array=new byte[array2.length+1];//IE ei toeta vectoreid                                	 
							for(j=0;j<array2.length;j++)                                                           	 
							{                                                                                      	 
								array[j]=array2[j];                                                                	 
							}                                                                                      	 
							in=sisse.read();                                                                       	 
							array[j]=(new Integer(in)).byteValue();                                                	 
						}                                                                                          	 
																												    
						sisse.close();  
						yield(); 
						 
						puu=new String(array);                                                                  	 
 
					 
					//============== Puu käes, It's a parsing time! ===========================  
				  
		//oid	alluvatearv		nimi	link	ikoonilink  
		  
		  
						tab=puu.indexOf("	");  
						j=0;  
						last=false; 
						while(tab!=-1)  
						{  
							  
							if(tab==-1)  
							{  
								break;  
							}  
							oid=new Integer(puu.substring(0,tab)).intValue();  
							puu=puu.substring(tab+1);  
							tab=puu.indexOf("	");  
							  
							alluvaid=new Integer(puu.substring(0,tab)).intValue();  
							puu=puu.substring(tab+1);  
							tab=puu.indexOf("	");  
		  
							nimi=puu.substring(0,tab);  
							puu=puu.substring(tab+1);  
							tab=puu.indexOf("	");  
		  
							url=puu.substring(0,tab);  
							puu=puu.substring(tab+1);  
							tab=puu.indexOf("\n");  
		  
							iconurl=puu.substring(0,tab);  
							puu=puu.substring(tab+1);  
							tab=puu.indexOf("	");  
							  
							if(tab==-1)  
							{  
								last=true;  
							}  
							branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.labelcolor,iconurl,iconurl,url,alluvaid,oid,folder.joon,font);                                                                                               	 
 
							try 
							{ 
								liidetavad[j]=first;  
							} 
							catch(ArrayIndexOutOfBoundsException ee) 
							{//alluvaid oli rohkem, kui väideti 
								System.out.println("ERINEVUS "+folder.label+"  oli alluvaid rohkem, kui väideti"); 
								System.out.println("URL: "+aadress); 
								abi=new branch[liidetavad.length+1]; 
								
								for(k=0;k<liidetavad.length;k++) 
								{ 
									abi[k]=liidetavad[k]; 
								} 
								abi[k]=first; 
								liidetavad=abi; 
							} 
							if(tab==-1)  
							{  
								break;  
							}  
							j++;  
						}  
 
						if((j+1)<folder.alluvaid) 
						{//alluvaid oli vähem, kui väideti 
							System.out.println("ERINEVUS "+folder.label+"  oli alluvaid vähem, kui väideti"); 
							System.out.println("URL: "+aadress); 
							abi2=new branch[liidetavad.length+1]; 
							for(kk=0;liidetavad[kk]!=null;kk++) 
							{ 
								abi2[kk]=liidetavad[kk]; 
							} 
							liidetavad=abi2; 
						} 
 
						folder.addSlaves(liidetavad);  
 
						jada2=jada3; 
						jada3=new branch[jada2.length+liidetavad.length];//saan kõik selle kihi alluvad 
 
						for(j=0;j<jada2.length;j++) 
						{ 
							jada3[j]=jada2[j]; 
						} 
 
						for(j=0;j<liidetavad.length;j++) 
						{ 
							jada3[j+jada2.length]=liidetavad[j]; 
						} 
					}                                                                                              	 
					catch(IOException ee)                                                                          	 
					{                                                                                              	 
						System.out.println("IOKala: "+ee);                                                         	 
					}	                                                                                           	 	 
				}//if 
			}//for 
			 
			if(over) 
			{ 
				break; 
			} 
			jada=jada3; 
			i=0; 
			over=true; 
		}//while 
		 
		//System.out.println("Lõim tirija lõpetas töö");
		//sisse.flush();
		try 
		{ 
			interrupt(); 
		} 
		catch(java.lang.SecurityException ee) 
		{ 
			try 
			{ 
				destroy(); 
			} 
			catch(java.lang.NoSuchMethodError e) 
			{ 
				System.gc(); 
			} 
		} 
	} 
} 
 

 
 class perioodiKuular implements ItemListener 
 { 
	Applet boss; 
	int[] period; 
	Choice perioodid; 
 
	perioodiKuular(Applet bos,int[] per) 
	{ 
		boss=bos; 
		period=per; 
		perioodid=((menuTree)boss).perioodid; 
	} 
 
	public void itemStateChanged(ItemEvent event) 
	{ 
		int valitu=perioodid.getSelectedIndex(); 
		 
		if(((menuTree)boss).aktiivne!=valitu) 
		{ 
			((menuTree)boss).end="&automatweb="+period[valitu]; 
			((menuTree)boss).aktiivne=perioodid.getSelectedIndex(); 
			boss.destroy();

			/*((menuTree)boss).end="";	
			((menuTree)boss).base=new GridBagLayout();
			((menuTree)boss).cc=new GridBagConstraints();
			((menuTree)boss).aken=new Panel();
			((menuTree)boss).scroll=new ScrollPane(ScrollPane.SCROLLBARS_AS_NEEDED);
			((menuTree)boss).fixer=new Panel();
			((menuTree)boss).nupp=new Panel();*/
			boss.init();
		 
			Component[] jada=((menuTree)boss).aken.getComponents(); 
			
			boss.doLayout();
			((menuTree)boss).scroll.doLayout();

			for(int i=0;i<jada.length-1;i++) 
			{ 
				((menuTree)boss).aken.doLayout();
				((branch)jada[i]).doLayout(); 
				((branch)jada[i]).right.doLayout(); 			 
			}
			
			((menuTree)boss).nupp.doLayout();
		} 
	}
}
 
 
 
public class menuTree extends Applet  
{  
	  
	int aktiivne=-1;
	Thread tirija,recall; 
	static Socket s; 
	String end;
	GridBagLayout base;
	GridBagConstraints cc;
	Panel aken;
	ScrollPane scroll;
	Panel fixer;
	Panel nupp;
	pilt refresh;
	Choice perioodid;
	Color select2;
 
	public void destroy() 
	{ 
		if (recall!=null)
		{
			try 
			{ 
				recall.yield();
				recall.interrupt(); 
			} 
			catch(java.lang.SecurityException ee) 
			{ 
				try 
				{ 
					recall.destroy(); 
				} 
				catch(java.lang.NoSuchMethodError e) 
				{ 
				} 
			}
		}//tirija!=null
 
		if(s!=null) 
		{ 
			try 
			{ 
				System.out.println("Sulgesin yhenduse"); 
				s.close(); 
			} 
			catch(IOException e) 
			{ 
			} 
		} 
		 
		if (tirija!=null)
		{
			try 
			{ 
				tirija.yield();
				tirija.interrupt(); 
			} 
			catch(java.lang.SecurityException ee) 
			{ 
				try 
				{ 
					tirija.destroy(); 
				} 
				catch(java.lang.NoSuchMethodError e) 
				{ 
				} 
			}
		}//tirija!=null 
		removeAll();
		System.gc(); 
	} 
	 
	public static int getNumber(String arv) 
	{//teen stringist 16-nd koodi arvust 10 koodi oma 
		int esimene=-1; 
		int abi=-1; 
		String first=arv.substring(0,1); 
		 
		for(int i=0;i<2;i++) 
		{ 
			try 
			{ 
				abi=new Integer(first).intValue();	 
			} 
			catch(NumberFormatException e) 
			{ 
				if((first.compareTo("a")==0)||(first.compareTo("A")==0))	abi=10; 
				if((first.compareTo("b")==0)||(first.compareTo("B")==0))	abi=11; 
				if((first.compareTo("c")==0)||(first.compareTo("C")==0))	abi=12; 
				if((first.compareTo("d")==0)||(first.compareTo("D")==0))	abi=13; 
				if((first.compareTo("e")==0)||(first.compareTo("E")==0))	abi=14; 
				if((first.compareTo("f")==0)||(first.compareTo("F")==0))	abi=15; 
			} 
					 
			if(i==1) 
			{ 
				break; 
			} 
			esimene=abi; 
			first=arv.substring(1); 
			abi=-1; 
		}//for 
 
		if((abi==-1)||(esimene==-1)) 
		{ 
			return -1; 
		} 
		return esimene*16+abi; 
	} 
	 
 
 
	public static Color getColor(String arv) 
	{ 
		if(arv.indexOf("#")==-1) 
		{ 
			return null; 
		} 
 
		int red=getNumber(arv.substring(1,3)); 
		int green=getNumber(arv.substring(3,5)); 
		int blue=getNumber(arv.substring(5)); 
 
		if((red==-1)||(green==-1)||(blue==-1)) 
		{ 
			return null; 
		} 
		 
		return new Color(red,green,blue); 
	} 
 
  
 
	public void init()  
	{	
		end="";
		base=new GridBagLayout();
		cc=new GridBagConstraints();
		aken=new Panel();
		scroll=new ScrollPane(ScrollPane.SCROLLBARS_AS_NEEDED);
		fixer=new Panel();
		nupp=new Panel();

		Color back=getColor(this.getParameter("background_color"));  
		Color mouse=getColor(this.getParameter("mouseover_color"));  
		Color select=getColor(this.getParameter("selected_color"));  
		Color text=getColor(this.getParameter("text_color")); 
		select2=getColor(this.getParameter("active_text_color")); 
		Color top=getColor(this.getParameter("top_color")); 
	 
		if(back==null) back=new Color(238,238,238); 
		if(mouse==null) mouse=new Color(138,171,190); 
		if(select==null) select=new Color(189,210,220); 
		if(text==null) text=new Color(0,0,0); 
		if(top==null) top=new Color(219,232,238); 
 
		String session=this.getParameter("session"); 
		String font=this.getParameter("font"); 
 
		nupp.setBackground(top); 
		nupp.setLayout(new FlowLayout(FlowLayout.LEFT,2,2)); 
  
		aken.setBackground(back); 
  
//===================================== LOON SAIDILE NÄHTAVA PUU ====================================  
  
		int i,in;  
		byte[] array=new byte[1];  
		byte[] array2;  
		InputStream sisse; 
		String aadress=this.getParameter("url")+"/automatweb/orb.aw?class=menuedit&action=get_branch&automatweb="+session+end; 
		//aadress="http://aw.struktuur.ee/risto/puu/toide.txt";
		System.out.println("Kysin URL: "+aadress+"\n"); 
			try  
			{  
				sisse=new URL(aadress).openConnection().getInputStream();  
				in=sisse.read();  
							 
				array[0]=(new Integer(in)).byteValue();  
  
				while(in>0)  
				{  
					array2=array;  
					array=new byte[array2.length+1];//IE ei toeta vectoreid  
					for(i=0;i<array2.length;i++)  
					{  
						array[i]=array2[i];  
					}  
					in=sisse.read();  
					array[i]=(new Integer(in)).byteValue();  
				}  
  
				sisse.close();  
			}  
			catch(IOException e)  
			{  
				System.out.println("IOKala: "+e);  
			}	 
			catch(Exception e)  
			{  
				System.out.println("ERROR PUU SAAMISEL: "+e);  
			}	 
			String puu=new String(array);  
  
//System.out.println("Sain="+puu); 
 
//============== Puu käes, It's a parsing time! ===========================  
		  
//oid	alluvatearv		nimi	link	ikoonilink  
  
		int oid,alluvaid,tab;  
		String nimi,iconurl,url;  
		boolean last=false;  
		
	//0 0 AutomatWeb http://aw.struktuur.ee/?class=menuedit&action=right_frame&fastcall=1&parent=4 http://aw.struktuur.ee/images/aw_ikoon.gif 
			 
			puu=puu.substring(4); 
			tab=puu.indexOf("	"); 
			 
			nimi=puu.substring(0,tab); 
			puu=puu.substring(tab+1); 
			tab=puu.indexOf("	"); 
			 
			url=puu.substring(0,tab);  
			puu=puu.substring(tab+1);  
			tab=puu.indexOf("\n");  
	  
			iconurl=puu.substring(0,tab);  
			puu=puu.substring(tab+1);  
	
//System.out.println("nimi="+nimi);				  
//System.out.println("sihturl="+url);	
//System.out.println("ikooniurl="+iconurl); 
			
			cc.weighty = 0.0;		   //reset to the default
			cc.weightx = 1.0;
			cc.gridwidth = GridBagConstraints.REMAINDER; //end row REMAINDER
			cc.gridheight = 1;

			cc.anchor=GridBagConstraints.NORTHWEST;
			aken.setLayout(base); 

	//LOON ESIMESE PUU OBJEKTI 
  
			branch aw=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,0,-1,null,font); 
			aw.count=0; 
			
			base.setConstraints(aw, cc);

			aken.add(aw); 
			 
			tab=puu.indexOf("	");  
			i=1;  
	  
			while(tab!=-1)  
			{  
				  
				if(tab==-1)  
				{  
					break;  
				}  
				oid=new Integer(puu.substring(0,tab)).intValue();  
				puu=puu.substring(tab+1);  
				tab=puu.indexOf("	");  
				  
				alluvaid=new Integer(puu.substring(0,tab)).intValue();  
				puu=puu.substring(tab+1);  
				tab=puu.indexOf("	");  
	  
				nimi=puu.substring(0,tab);  
				puu=puu.substring(tab+1);  
				tab=puu.indexOf("	");  
	  
				url=puu.substring(0,tab);  
				puu=puu.substring(tab+1);  
				tab=puu.indexOf("\n");  
	  
				iconurl=puu.substring(0,tab);  
				puu=puu.substring(tab+1);  
				tab=puu.indexOf("	");  
//System.out.println("oid="+oid+"  alluvaid="+alluvaid+"   nimi="+nimi);				  
//System.out.println("sihturl="+url);	
//System.out.println("ikooniurl="+iconurl);
				if(tab==-1)  
				{  
					last=true;  
				}  
				branch first=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,alluvaid,oid,null,font); 
				first.count=i;  			
				base.setConstraints(first, cc);

				aken.add(first);  
				i++;  
 
				if(tab==-1)  
				{  
					break;  
				} 		 
			}  
 
			cc.weighty = 1.0;
			base.setConstraints(fixer, cc);
			aken.add(fixer);
			
			s=null; 
			scroll.setBackground(back);  
			scroll.add(aken);  
			((Adjustable)scroll.getVAdjustable()).setUnitIncrement(24); 
			 
			 
 
			//if(this.getParameter("deemon").compareTo("ON")!=0) 
			//{//ei taheta deemonit  
				try  
				{  
					
					URL urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh.gif");  
					refresh=new pilt(this.getImage(urll)); 
					refresh.setSize(25,25); 
					refresh.icon1=refresh.icon; 
					urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh_over.gif");  
					refresh.icon2=this.getImage(urll); 
					nupp.add(refresh); 
					refresh.addMouseListener(new refreshKuular(refresh,this)); 
				}  
				catch(java.net.MalformedURLException e)  
				{  
					System.out.println("Ei saanud ikooni kätte "+e); 
				}  
				catch(Exception e)  
				{  
					System.out.println("!!!Ei saanud ikooni kätte "+e); 
				}	 
			//}//refresh nupp 
			//else 
			/*if(this.getParameter("deemon").compareTo("ON")==0) 
			{//tahetakse deemonit 
				try 
				{//panen deemoniga suhtlema 
					String host=this.getParameter("server"); 
					int port=new Integer(this.getParameter("port")).intValue();  
					s=new Socket(host,port); 
				} 
				catch(Exception e) 
				{//ei saanud deemoniga ühendust 
					System.out.println("Ei suutnud deemoniga ühendust saada: "+e); 

					try  
					{  
						if(refresh==null)
						{
							URL urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh.gif");  
							refresh=new pilt(this.getImage(urll)); 
							refresh.setSize(25,25); 
							refresh.icon1=refresh.icon; 
							urll=new URL("http://aw.struktuur.ee/automatweb/images/blue/awicons/refresh_over.gif");  
							refresh.icon2=this.getImage(urll); 
							nupp.add(refresh); 
							refresh.addMouseListener(new refreshKuular(refresh,this)); 
						}
					}  
					catch(java.net.MalformedURLException ee)  
					{  
						System.out.println("Ei saanud ikooni kätte "+e); 
					}  
					catch(Exception ee)  
					{  
						System.out.println("!!!Ei saanud ikooni kätte "+e); 
					} 
				} 
			}//deemon*/ 
			 
			if(this.getParameter("perioodiline").compareTo("ON")==0) 
			{//on perioodiline sait 
				perioodid=new Choice(); 
				array=new byte[1];  
				aadress=this.getParameter("url")+"/orb.aw?class=menuedit&action=get_periods"; 
				 
				try  
				{  
					sisse=new URL(aadress).openConnection().getInputStream();  
					in=sisse.read();  
					array[0]=(new Integer(in)).byteValue();  
	  
					while(in>0)  
					{  
						array2=array;  
						array=new byte[array2.length+1];//IE ei toeta vectoreid  
						for(i=0;i<array2.length;i++)  
						{  
							array[i]=array2[i];  
						}  
						in=sisse.read();  
						array[i]=(new Integer(in)).byteValue();  
					}  
	  
					sisse.close(); 
				} 
				catch(Exception e) 
				{ 
					System.out.println("Ei saanud perioodide lehte kätte aadressilt: \n"+aadress); 
					System.out.println("ERROR: "+e); 
				} 
				puu=new String(array);//perioodide list käes, parsime 
 
				tab=puu.indexOf("	"); 
				int[] period=new int[0]; 
				int[] periodabi; 
 
				int active;  
				i=0; 
				while(tab!=-1)  
				{  
				  
					if(tab==-1)  
					{  
						break;  
					}  
 
					oid=new Integer(puu.substring(0,tab)).intValue();  
					puu=puu.substring(tab+1);  
					tab=puu.indexOf("	");  
 
					nimi=puu.substring(0,tab);  
					puu=puu.substring(tab+1);  
					tab=puu.indexOf("\n");  
					 
					active=new Integer(puu.substring(0,tab)).intValue(); 
					puu=puu.substring(tab+1);  
					tab=puu.indexOf("	");  
 
					perioodid.add(nimi); 
					if(active==1) 
					{//see periood on aktiivne 
						perioodid.select(nimi); 
					} 
					 
 
					periodabi=period; 
					period=new int[periodabi.length+1]; 
 
					for(int j=0;j<periodabi.length;j++) 
					{ 
						period[j]=periodabi[j]; 
					} 
					period[i]=oid; 
					 
					i++; 
				} 
				if(aktiivne!=-1) 
				{//juhuks, kui perioodi vahetusel kutsun init() välja 
					perioodid.select(aktiivne); 
				} 
				nupp.add(perioodid); 
				perioodid.addItemListener(new perioodiKuular(this,period)); 
			}//perioodid 		 
		
			if(nupp.getComponentCount()>0) 
			{ 
				this.setLayout(new BorderLayout()); 
				this.add(nupp,"North"); 
			} 
			this.add(scroll,"Center");  

			aken.doLayout(); 
			aken.repaint(); 

			cc.weighty = 0.0;		   //reset to the default
			cc.weightx = 1.0;
			cc.gridwidth = GridBagConstraints.REMAINDER; //end row

		if (tirija!=null)
		{
			try 
			{ 
				tirija.yield();
				tirija.interrupt(); 
			} 
			catch(java.lang.SecurityException ee) 
			{ 
				try 
				{ 
					tirija.destroy(); 
				} 
				catch(java.lang.NoSuchMethodError e) 
				{ 
					System.gc(); 
				} 
			}
		}//tirija!=null
		tirija=new tirija(this); 
		tirija.start();

		/*if(s!=null) 
		{ 
			recall=new recall(this); 
			recall.start(); 
		}*/
	}  
}  
