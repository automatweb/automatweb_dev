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
 
	pilt(Image gif)
	{ 
		this.setSize(20,22); 
		icon=gif; 
	} 
 
	public void paint(Graphics g) 
	{ 
		g.drawImage(icon, 0, 0, this); 
	} 
} 
 
 
 
class branchRight extends Container 
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
 
		this.setLayout(new FlowLayout(FlowLayout.LEFT,0,0)); 
		this.add(image,0); 
		this.add(name,-1); 
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
{ 
	int step; 
	boolean[] joon; 
 
	level(int astak,boolean[] jon) 
	{ 
		step=astak; 
		joon=jon; 
		this.setSize(astak*23,22); 
	} 
 
	public void paint(Graphics g) 
	{ 
		for(int i=0;i<step;i++) 
		{ 
			if(!joon[i]) 
			{ 
				g.drawLine(7+23*i,0,7+23*i,22); 
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
		this.setSize(23,22); 
	} 
 
	public void paint(Graphics g) 
	{ 
		if(state==0) 
		{//kinni 
			g.drawLine(7,0,7,6); 
 
			g.drawRect(3,7,8,8); 
			g.drawLine(5,11,9,11);//- 
			g.drawLine(7,9,7,13);//| 
 
			g.drawLine(12,11,18,11);//- 
			 
			if(!last) 
			{ 
				g.drawLine(7,16,7,22); 
			} 
		} 
		else 
		if(state==1) 
		{ 
			g.drawLine(7,0,7,6); 
 
			g.drawRect(3,7,8,8); 
			g.drawLine(5,11,9,11);//- 
 
			g.drawLine(12,11,18,11);//- 
			 
			if(!last) 
			{ 
				g.drawLine(7,16,7,22); 
			} 
		} 
		else 
		{//joon 
			g.drawLine(7,0,7,10); 
 
			g.drawLine(7,11,16,11);//8 
 
			if(!last) 
			{ 
				g.drawLine(7,12,7,22); 
			} 
		} 
	} 
} 
 
 
 
class branch extends Container 
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
	Color back,mouse,select,text; 
	String label,iconurl,status,url; 
	Image closeicon,openicon; 
	int oid;//objekti ID 
	boolean[] joon;//kas tõmmata folderist allapoole veel joon  
	 
	branch(String nimi,int astak,boolean laast,Panel aaken,Applet bos,Color b,Color m,Color s,Color t,String closeurl,String openurl,String urll,int arv,int ooid,boolean[] jon) 
	{ 
		back=b;				mouse=m; 
		select=s;			text=t; 
		boss=bos;			aken=aaken; 
		step=astak;			label=nimi; 
		last=laast;			oid=ooid; 
		alluvaid=arv;		status=urll; 
		joon=jon;			url=urll; 
				 
		try 
		{ 
			URL url=new URL(openurl); 
			openicon=boss.getImage(url); 
		} 
		catch(java.net.MalformedURLException e) 
		{ 
			System.out.println("Ei saanud ikooni kätte "+e);
			openicon=boss.getImage(boss.getCodeBase(),"openicon.gif");	 
		} 
 		catch(Exception e) 
		{ 
			System.out.println("!!!Ei saanud ikooni kätte "+e);
			openicon=boss.getImage(boss.getCodeBase(),"openicon.gif");
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
		 
		right=new branchRight(label,(new pilt(closeicon)),true); 
		right.name.setBackground(back); 
		right.name.setForeground(text);
		if(oid==-1)
		{		
			right.name.setFont(new Font("TimesRoman",Font.BOLD,13));
		}
		right.image.setBackground(back); 
 
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
			empty.setForeground(text); 
			this.add(empty,0); 
		} 
		
		if(oid!=-1)
		{
			left=new branchLeft(state,last); 
			left.setBackground(back); 
			left.setForeground(text); 
			this.add(left); 
			if(state!=2) 
			{ 
				left.addMouseListener(new hiireKuular2(this,aken,boss)); 
			} 
		}
		 
 
 
		this.add(right,-1); 
		right.name.addMouseListener(new hiireKuular(this,boss,false)); 
		right.image.addMouseListener(new hiireKuular(this,boss,true)); 
	} 
 
 
	public void addSlaves(branch[] uued) 
	{//lisan uued alluvad 
		slaves=uued; 
		alluvaid=slaves.length; 
	} 
} 
 
 
 
class hiireKuular2 implements MouseListener 
{ 
	Applet boss; 
	Panel aken; 
	branch folder; 
 
	hiireKuular2(branch fold,Panel aaken,Applet bos) 
	{ 
		boss=bos; 
		aken=aaken; 
		folder=fold; 
	} 
 
	public void mousePressed(MouseEvent e)	{} 
	public void mouseReleased(MouseEvent e) 
	{ 
		int i,j,pikkus; 
 
		if(folder.left.state==0) 
		{//klikkan plussil 
			folder.left.state=1; 
			folder.left.repaint(); 
 
			Component[] jada=aken.getComponents(); 
			//saan parasjagu nähtavad folderid 
			pikkus=jada.length; 
 
			for(i=folder.count+1;i<pikkus;i++) 
			{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 
				((branch)((Panel)jada[i]).getComponent(0)).count+=folder.alluvaid;//casting ruulib :) 
			} 
			 
			GridLayout layout=(GridLayout)aken.getLayout(); 
			i=layout.getRows();
			
			if(i<(pikkus+folder.alluvaid)) 
			{ 
				j=pikkus+folder.alluvaid-i+1;
				aken.setLayout(new GridLayout(i+j,1,0,0)); 
				aken.setSize(aken.getSize().width,aken.getSize().height+20*j); 
				aken.doLayout();
				boss.getComponent(1).doLayout();
			} 

			if(folder.slaves==null) 
			{//pole veel alluvaid sissetõmmatud 
 
				branch[] liidetavad=new branch[folder.alluvaid]; 
				 
				byte[] array=new byte[1]; 
				String aadress="http://aw.struktuur.ee/?class=menuedit&action=get_branch&parent="+folder.oid; 
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
				} 
				catch(IOException ee) 
				{ 
					System.out.println("IOKala: "+ee); 
				}	 
				String puu=new String(array); 
 
 
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
					Panel paneel=new Panel(); 
					paneel.setLayout(new FlowLayout(FlowLayout.LEFT,0,-1)); 
					branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.text,iconurl,iconurl,url,alluvaid,oid,folder.joon); 
					first.count=folder.count+i+1; 
					paneel.add(first); 
					aken.add(paneel,first.count); 
 
					paneel.doLayout(); 
					first.doLayout(); 
					first.right.doLayout(); 
 
					liidetavad[i]=first; 
					if(tab==-1) 
					{ 
						break; 
					} 
					i++; 
				} 
				boss.getComponent(1).doLayout();
				folder.addSlaves(liidetavad); 
			} 
			else 
			{ 
 
				for(i=0;i<folder.alluvaid;i++) 
				{//lisan uued nähtavad folderid 
					Panel paneel=new Panel();	 
					paneel.setLayout(new FlowLayout(FlowLayout.LEFT,0,-2)); 
					paneel.add(folder.slaves[i]); 
					folder.slaves[i].count=folder.count+1+i;	 					 
					aken.add(paneel,folder.slaves[i].count); 
					paneel.doLayout(); 
					folder.slaves[i].doLayout(); 
					folder.slaves[i].right.doLayout(); 
				} 
			} 
 
		} 
		else 
		{//klikkan miinusel 
			folder.left.state=0; 
			folder.left.repaint(); 
 
			Component[] jada=aken.getComponents(); 
			//saan parasjagu nähtavad folderid 
 
			i=0; 
			i=folder.count+1; 

			try 
			{ 
				while(((branch)((Panel)jada[i]).getComponent(0)).step>folder.step) 
				{ 
					if(((branch)((Panel)jada[i]).getComponent(0)).left.state==1) 
					{ 
						((branch)((Panel)jada[i]).getComponent(0)).left.state=0; 
					} 
					aken.remove(folder.count+1); 
					i++; 
				} 
			} 
			catch(Exception ee) 
			{ 
			} 

			GridLayout layout=(GridLayout)aken.getLayout(); 
			j=boss.getSize().height-30; 
			j=j/20;//leidsin mitu menüü elementi mahub ilma kerimisribadeta 
			pikkus=layout.getRows();

			if(pikkus>j) 
			{ 
				j=Math.min(folder.alluvaid,(pikkus-j));//(alluvaid,palju yle nähtava)		
				aken.setLayout(new GridLayout((pikkus-j),1,0,0)); 
				aken.setSize(aken.getSize().width,aken.getSize().height-20*j); 
				aken.doLayout();
				boss.getComponent(1).doLayout(); 
			}
			
			jada=aken.getComponents(); 
			j=i-folder.count-1; 
			for(i=folder.count+1;i<jada.length;i++) 
			{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 
 
				((branch)((Panel)jada[i]).getComponent(0)).count-=j;//casting ruulib 
			} 
		} 
		aken.doLayout();
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
	Color back,text,select,mouse; 
	boolean ikoonil;
 
	hiireKuular(branch oxx,Applet bos,boolean il) 
	{ 
		ox=oxx; 
		boss=bos; 
		back=ox.back; 
		text=ox.text; 
		select=ox.select; 
		mouse=ox.mouse; 
		ikoonil=il;
	} 
 
	public void mousePressed(MouseEvent e)	{} 
	public void mouseReleased(MouseEvent e) 
	{ 
		if(ox.right.closed) 
		{ 
			//ox.right.image.icon=ox.openicon; 
			ox.right.name.setBackground(select); 
			ox.right.name.setForeground(Color.white); 
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
			try 
			{ 
				URL url=new URL(ox.url); 
				if(ikoonil)
				{
					boss.getAppletContext().showDocument(url,ox.label);
				}
				else
				{
					boss.getAppletContext().showDocument(url,"MAIN"); 
				}
			} 
			catch(java.net.MalformedURLException ee) 
			{ 
				System.out.println("Lehte ei leitud "+ee); 
				//siin võiks vista PAGE NOT FOUND 
			} 
			previous=ox; 
		} 
	} 
	public void mouseEntered(MouseEvent e) 
	{ 
		ox.right.name.setBackground(mouse);  
		ox.right.name.setForeground(Color.white); 
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
 
 
 
class color 
{ 
	String x; 
	Color y; 
 
	color(String xx,Color yy) 
	{ 
		x=xx; 
		y=yy;	 
	} 
} 
 

class refreshKuular implements ActionListener
{
	Applet boss;

	refreshKuular(Applet bos)
	{
		boss=bos;
	}

	public void actionPerformed(ActionEvent e)
	{
		boss.removeAll();
		boss.init();
		boss.doLayout();
		boss.getComponent(0).doLayout();		
		boss.getComponent(1).doLayout();
		Panel aken=(Panel)((ScrollPane)boss.getComponent(1)).getComponent(0);
		Component[] jada=aken.getComponents();
		for(int i=0;i<jada.length;i++)
		{
			jada[i].doLayout();
			((branch)((Panel)jada[i]).getComponent(0)).doLayout();
			((branch)((Panel)jada[i]).getComponent(0)).right.doLayout();
			aken.doLayout();
		}
	}
}



class tirija extends Thread
{
	Applet boss;
	branch[] jada;
	Panel aken;

	tirija(Applet bos,Panel ake)
	{
		boss=bos;
		aken=ake;
	}


	public void run()
	{
		branch[] jada2;
		branch[] jada3=new branch[0];

		int oid,alluvaid,tab,in,i,j; 
		String nimi,iconurl,url,aadress; 
		boolean last=false; 
		InputStream sisse;
		boolean over=true;
	
		Component[] riba=aken.getComponents();

		branch[] jada=new branch[riba.length-1];
		for(i=1;i<jada.length+1;i++)
		{//saan alguses lehel oleva stuffi
			jada[i-1]=(branch)((Panel)riba[i]).getComponent(0);
		}

		while (true)
		{
			for(i=0;i<jada.length;i++)
			{
				branch folder=jada[i];
				
				if((folder.slaves==null)&&(folder.alluvaid>0)) 
				{//pole veel alluvaid sisse tõmmatud ja on mida tõmmata
 					over=false;
					branch[] liidetavad=new branch[folder.alluvaid]; 
				
				
					byte[] array=new byte[1]; 
					aadress="http://aw.struktuur.ee/?class=menuedit&action=get_branch&parent="+folder.oid;  
					try                                                                                            	
					{                                                                                              	
						sisse=new URL(aadress).openConnection().getInputStream();                      	
						in=sisse.read();                                                                       	
						byte[] array2;                                                                             	
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
					}                                                                                              	
					catch(IOException ee)                                                                          	
					{                                                                                              	
						System.out.println("IOKala: "+ee);                                                         	
					}	                                                                                           	
					String puu=new String(array);                                                                  	

					
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
						branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.text,iconurl,iconurl,url,alluvaid,oid,folder.joon); 
	 
						liidetavad[j]=first; 
						if(tab==-1) 
						{ 
							break; 
						} 
						j++; 
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
		
		try
		{
			destroy();
		}
		catch(java.lang.NoSuchMethodError e)
		{
		}
	}
}


 
public class menuThread extends Applet 
{ 
 
	public static Color getColor(String varv,color[] colors) 
	{ 
		if(varv==null) 
		{ 
			return null; 
		} 
 
		for(int i=0;i<colors.length;i++) 
		{ 
			if(colors[i].x.compareTo(varv)==0) 
			{ 
				return colors[i].y; 
			} 
		} 
			return null; 
	} 

 
	public void init() 
	{

		color[] colors=new color[13]; 
		colors[0]=new color("black",Color.black); 
		colors[1]=new color("blue",Color.blue); 
		colors[2]=new color("cyan",Color.cyan); 
		colors[3]=new color("darkGray",Color.darkGray); 
		colors[4]=new color("gray",Color.gray); 
		colors[5]=new color("green",Color.green); 
		colors[6]=new color("lightGray",Color.lightGray); 
		colors[7]=new color("magenta",Color.magenta); 
		colors[8]=new color("orange",Color.orange); 
		colors[9]=new color("pink",Color.pink); 
		colors[10]=new color("red",Color.red); 
		colors[11]=new color("white",Color.white); 
		colors[12]=new color("yellow",Color.yellow); 
 
		Color back=getColor(this.getParameter("background_color"),colors); 
		Color mouse=getColor(this.getParameter("mouseover_color"),colors); 
		Color select=getColor(this.getParameter("selected_color"),colors); 
		Color text=getColor(this.getParameter("text_color"),colors); 

		Panel nupp=new Panel();
		Button refresh=new Button("Refresh");
		nupp.add(refresh);

		Panel aken=new Panel(); 
		aken.setBackground(back); 
//TOPELT SISALDAVUS, KUNA MUIDU TULEVAD OBJEKTIDE VAHELE KATKED 
//===================================== LOON SAIDILE NÄHTAVA PUU ==================================== 
 
		int i; 
		byte[] array=new byte[1]; 
		String aadress="http://aw.struktuur.ee/?class=menuedit&action=get_branch"; 
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
			} 
			catch(IOException e) 
			{ 
				System.out.println("IOKala: "+e); 
			}	 
			String puu=new String(array); 
 
//============== Puu käes, It's a parsing time! =========================== 
		 
//oid	alluvatearv		nimi	link	ikoonilink 
 
		int oid,alluvaid,tab; 
		String nimi,iconurl,url; 
		boolean last=false; 
 
		int hait=this.getSize().height-30;
		hait=hait/20;//leidsin mitu menüü elementi mahub ilma kerimisribadeta 

		aken.setLayout(new GridLayout(hait,1,0,0)); 
		this.setBackground(back); 
		this.setLayout(new BorderLayout()); 
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
		
		
//LOON ESIMESE PUU OBJEKTI

		Panel pea=new Panel();
		pea.setLayout(new FlowLayout(FlowLayout.LEFT,0,-2));
		branch aw=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,0,-1,null);
		aw.count=0;
		pea.add(aw);
		aken.add(pea);
		
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
			Panel paneel=new Panel(); 
			paneel.setLayout(new FlowLayout(FlowLayout.LEFT,0,-2)); 
			branch first=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,alluvaid,oid,null); 
			first.count=(i+1); 
			paneel.add(first); 
			aken.add(paneel); 
			 
			if(tab==-1) 
			{ 
				break; 
			} 
			i++; 
		} 

		if(i>hait)
		{//kohe on vaja scrollbari
			aken.setLayout(new GridLayout(i,1,0,0)); 
			aken.setSize(this.getSize().width,i*20);
		}

		ScrollPane scroll=new ScrollPane(); 
		scroll.setBackground(back); 
		scroll.add(aken); 
		this.add(nupp,"North");
		this.add(scroll,"Center"); 

		refresh.addActionListener(new refreshKuular(this));

		//Thread tirija=new tirija(this,aken);
		//tirija.start();
	} 
} 
