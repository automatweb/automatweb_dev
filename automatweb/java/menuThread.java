import java.awt.*; 
import java.awt.event.*; 
import java.applet.*; 
import java.net.*; 
import java.io.*; 

import java.awt.font.*;
import java.awt.geom.*;
import java.awt.event.*;
 
//protokoll kujul: 
//oid	alluvatearv		nimi	link	ikoonilink 
 
 
class pilt extends Canvas 
{ 
	Image icon; 
	Image icon1;//niisama refresh
	Image icon2;//mouse over refresh
 
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
		//this.setSize(astak*23,22); 
		this.setSize(astak*17,22); 
	} 
 
	public void paint(Graphics g) 
	{ 
		for(int i=0;i<step;i++) 
		{ 
			if(!joon[i]) 
			{ 
				//g.drawLine(7+23*i,0,7+23*i,22); 
				g.drawLine(7+17*i,0,7+17*i,22); 
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
		//this.setSize(23,22);
		this.setSize(17,22);
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
				g.drawLine(7,16,7,23); 
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
				g.drawLine(7,16,7,23); 
			} 
		} 
		else 
		{//joon 
			g.drawLine(7,0,7,10); 
 
			g.drawLine(7,11,17,11);// 
 
			if(!last) 
			{ 
				g.drawLine(7,12,7,23); 
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
		font=fo;
				 
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
		 
		Font style=new Font(font,Font.PLAIN,13);

		if((label.indexOf("<b>")!=-1)||(label.indexOf("<i>")!=-1)||(label.indexOf("<B>")!=-1)||
			(label.indexOf("<I>")!=-1)||(label.indexOf("<font ")!=-1)||(label.indexOf("<FONT ")!=-1))
		{
		//PURSIME HTMLI
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

			style=new Font(font,stiil,13);
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
		//right.name.setFont(new Font(font,Font.PLAIN,13));
		right.name.setFont(style);
		if(oid==-1)
		{		
			right.name.setFont(new Font(font,Font.BOLD,13));
		}
		right.image.setBackground(back); 
 
		this.add(right,-1); 
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
 
 
 
class hiireKuular2 implements MouseListener 
{ 
	Applet boss; 
	Panel aken; 
	branch folder; 
	String font,session;
 
	hiireKuular2(branch fold,Panel aaken,Applet bos) 
	{ 
		boss=bos; 
		aken=aaken; 
		folder=fold; 
		session=boss.getParameter("session");
		font=boss.getParameter("font");
	} 
 
	public void mousePressed(MouseEvent e)	{} 
	public void mouseReleased(MouseEvent e) 
	{ 
		int i,j,pikkus; 
 
		if(folder.left.state==0) 
		{//klikkan plussil 
			Component[] jada=aken.getComponents(); 
			//saan parasjagu nähtavad folderid 
			pikkus=jada.length; 

			folder.left.state=1; 
			folder.left.repaint();
 
 // System.out.println("alluvaid="+folder.alluvaid+"    koht="+folder.count+"  nimi="+folder.label+"   lehel komponente="+pikkus);

			if(folder.slaves==null) 
			{//pole veel alluvaid sissetõmmatud 

				branch[] liidetavad=new branch[folder.alluvaid]; 
				 
				byte[] array=new byte[1]; 
				String aadress="http://aw.struktuur.ee/automatweb/orb.aw?class=menuedit&action=get_branch&parent="+folder.oid+"&automatweb="+session; 
				
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
						branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.text,iconurl,iconurl,url,alluvaid,oid,folder.joon,font); 
						first.count=folder.count+i+1; 
						paneel.add(first); 
	//System.out.println(i+". "+first.label+"   koht="+first.count);
						aken.add(paneel,first.count); 
	 
						aken.doLayout();
						paneel.doLayout(); 
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
					layout=(GridLayout)aken.getLayout(); 
					j=layout.getRows();
					tab=aken.getComponentCount();
					if(j<tab)
					{
						aken.setLayout(new GridLayout(tab,1,0,0)); 
						aken.setSize(aken.getSize().width,aken.getSize().height+20*(tab-j)); 
						aken.doLayout();
						boss.getComponent(1).doLayout();
					}

					for(j=folder.count+1;j<pikkus;j++) 
					{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 	
						branch uus2=((branch)((Panel)jada[j]).getComponent(0));
						//System.out.print("uus2="+uus2.label+"  koht="+uus2.count+" i="+(i+1));			
						uus2.count+=(i+1);
						//System.out.println("   uus koht="+uus2.count);		
					} 

					boss.getComponent(1).doLayout();
					folder.addSlaves(liidetavad); 
				} 
				catch(IOException ee) 
				{ 
					System.out.println("IOKala: "+ee); 
				}	 
			} 
			else 
			{ 		
	
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
				for(i=0;i<folder.alluvaid;i++) 
				{//lisan uued nähtavad folderid 
					Panel paneel=new Panel();	 
					paneel.setLayout(new FlowLayout(FlowLayout.LEFT,0,-2)); 
					paneel.add(folder.slaves[i]); 
					folder.slaves[i].count=folder.count+1+i;	 					 
					aken.add(paneel,folder.slaves[i].count); 
					aken.doLayout();
					paneel.doLayout(); 
					folder.slaves[i].doLayout(); 
					folder.slaves[i].right.doLayout(); 
				} 

				for(i=folder.count+1;i<pikkus;i++) 
				{//annan teada, et nähtavad folderid nihkuvad allapoole folder.alluvaid foldri võrra 
					/*
					branch uus2=((branch)((Panel)jada[i]).getComponent(0));
					System.out.print("uus2="+uus2.label+"  koht="+uus2.count);			
					uus2.count+=folder.alluvaid;//casting ruulib :) 
					System.out.println("   uus koht="+uus2.count);		
					*/
					((branch)((Panel)jada[i]).getComponent(0)).count+=folder.alluvaid;
				}
			} 
 
		} 
		else 
		{//klikkan miinusel 
			folder.left.state=0; 
			folder.left.repaint(); 
 
			Component[] jada=aken.getComponents(); 
			//saan parasjagu nähtavad folderid 
 
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
				aken.doLayout();
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
				boss.getComponent(1).doLayout(); 
				aken.doLayout();
				aken.repaint();
			}
			
			jada=aken.getComponents(); 
			i=i-folder.count-1;

			for(j=folder.count+1;j<jada.length;j++) 
			{//annan teada, et nähtavad folderid nihkuvad ülespoole folder.alluvaid foldri võrra 				
				branch uus=((branch)((Panel)jada[j]).getComponent(0));
				//System.out.println("uus.count="+uus.count+"   nimi="+uus.label+" i="+i+"  j="+j);	
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
				boss.getAppletContext().showDocument(url,"list"); 
			}
		} 
		catch(java.net.MalformedURLException ee) 
		{ 
			System.out.println("Lehte ei leitud "+ee);
			System.out.println("URL: "+ox.url);
			//siin võiks vista PAGE NOT FOUND 
		} 
		previous=ox;  
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
		String session=boss.getParameter("session");
		String font=boss.getParameter("font");

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
					
					aadress="http://aw.struktuur.ee/automatweb/orb.aw?class=menuedit&action=get_branch&parent="+folder.oid+"&automatweb="+session; 
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
							branch first=new branch(nimi,(folder.step+1),last,aken,boss,folder.back,folder.mouse,folder.select,folder.text,iconurl,iconurl,url,alluvaid,oid,folder.joon,font); 
		 
							try
							{
								liidetavad[j]=first; 
							}
							catch(ArrayIndexOutOfBoundsException ee)
							{//alluvaid oli rohkem, kui väideti
								System.out.println("ERINEVUS "+folder.label+"  oli alluvaid rohkem, kui väideti");
								branch[] abi=new branch[liidetavad.length+1];
								int k;
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
							branch[] abi2=new branch[liidetavad.length+1];
							for(int kk=0;liidetavad[kk]!=null;kk++)
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
		
		try
		{
			destroy();
		}
		catch(java.lang.NoSuchMethodError e)
		{
			System.out.println("Lõim lõpetas töö");
		}
	}
}


 
public class menuThread extends Applet 
{ 
	Panel aken;
	ScrollPane scroll;
/*
	public void destroy()
	{
		this.removeAll();
		scroll.removeAll();
		aken.removeAll();
		aken.doLayout();
		System.gc();
	}
*/
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
		
		Color back=getColor(this.getParameter("background_color")); 
		Color mouse=getColor(this.getParameter("mouseover_color")); 
		Color select=getColor(this.getParameter("selected_color")); 
		Color text=getColor(this.getParameter("text_color"));
		Color top=getColor(this.getParameter("top_color"));
		
		if(back==null) back=new Color(238,238,238);
		if(mouse==null) mouse=new Color(138,171,190);
		if(select==null) select=new Color(189,210,220);
		if(text==null) text=new Color(0,0,0);
		if(top==null) top=new Color(219,232,238);

		String session=this.getParameter("session");
		String font=this.getParameter("font");

		Panel nupp=new Panel();
		nupp.setBackground(top);
		nupp.setLayout(new FlowLayout(FlowLayout.LEFT));

		aken=new Panel(); 
		aken.setBackground(back); 
//TOPELT SISALDAVUS, KUNA MUIDU TULEVAD OBJEKTIDE VAHELE KATKED 
//===================================== LOON SAIDILE NÄHTAVA PUU ==================================== 
 
		int i; 
		byte[] array=new byte[1]; 
		String aadress="http://aw.struktuur.ee/automatweb/orb.aw?class=menuedit&action=get_branch&automatweb="+session;
		//System.out.println("Kysin URL: "+aadress+"\n");
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
 
//System.out.println("Sain="+puu);

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
		branch aw=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,0,-1,null,font);
		aw.count=0;
		pea.add(aw);
		aken.add(pea);
		
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
			 
			if(tab==-1) 
			{ 
				last=true; 
			} 
			Panel paneel=new Panel(); 
			paneel.setLayout(new FlowLayout(FlowLayout.LEFT,0,-2)); 
			branch first=new branch(nimi,0,last,aken,this,back,mouse,select,text,iconurl,iconurl,url,alluvaid,oid,null,font); 
			first.count=i; 
			paneel.add(first); 
			aken.add(paneel); 
			i++;
			if(tab==-1) 
			{ 
				break; 
			} 
		} 


		if(i>hait)
		{//kohe on vaja scrollbari
			aken.setLayout(new GridLayout(i,1,0,0)); 
			aken.setSize(this.getSize().width,i*20);
		}

		scroll=new ScrollPane(); 
		scroll.setBackground(back); 
		scroll.add(aken); 
		((Adjustable)scroll.getVAdjustable()).setUnitIncrement(20);
		pilt refresh;

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

		this.add(nupp,"North");
		this.add(scroll,"Center"); 
/*
		try
		{//panen deemoniga suhtlema
			String host=this.getParameter("server");
			Socket s=new Socket(host,3333);

			Thread recall=new recall(aken,this,s);
			recall.start();
		}
		catch(Exception e)
		{//ei saanud deemoniga ühendust
			System.out.println("Ei suutnud deemoniga ühendust saada: "+e);
			Panel nupp=new Panel();
			Button refresh=new Button("Refresh");
			nupp.add(refresh);
			this.add(nupp,"North");
			refresh.addActionListener(new refreshKuular(this));
		}
*/
		
		//aken.doLayout();
		//aken.repaint();

		Thread tirija=new tirija(this,aken);
		tirija.start();
		System.out.println("Nähtavaid elemente puul: "+aken.getComponentCount());	
		System.out.println("Ridu puu aknal: "+((GridLayout)aken.getLayout()).getRows());
	} 
} 
