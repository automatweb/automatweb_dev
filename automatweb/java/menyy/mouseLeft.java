import java.awt.*; 
import java.awt.event.*; 
import java.applet.*; 
import java.net.*; 
import java.io.*;
import java.util.*;
 

class menu extends Menu 
{ 
	String label; 
	int id,parent; 
 
	menu(String nimi,int idd,int par,Applet boss) 
	{  
		label=nimi;
		id=idd;
		parent=par;
	
		setLabel(label);

		int size=new Integer(boss.getParameter("menu_textsize")).intValue(); 
		setFont(new Font(boss.getParameter("menu_font"),Font.PLAIN,size)); 
	}
}



class menuItem extends MenuItem 
{ 
	URL viide; 
	String label,frame; 
	Applet boss; 
	int id,parent;
 
	menuItem(String nimi,String viit,Applet bos,int idd,int par,String raam) 
	{ 
		parent=par;
		id=idd;
		boss=bos; 
		label=nimi;
		frame=raam;
		setLabel(label); 
 
		int size=new Integer(boss.getParameter("menu_textsize")).intValue(); 
		setFont(new Font(boss.getParameter("menu_font"),Font.PLAIN,size)); 
 
		try 
		{ 
			viide=new URL(viit); 
 
			addActionListener(new ActionListener(){                                                                                                                                                                                                             
				public void actionPerformed(ActionEvent event){                                                                                                                                                                                                        
					boss.getAppletContext().showDocument(viide,frame);                                                                                                                                                                                                               
				}                                                                                                                                                                                                                                                      
			});	 
		} 
		catch(Exception e) 
		{ 
			System.out.println("Viga urliga "+e); 
			System.out.println("URL: "+viit); 
		} 
	} 
} 
 

 
class hiireKuular implements MouseListener 
{ 
	mouseLeft boss; 
 
	hiireKuular(mouseLeft bos) 
	{ 
		boss=bos; 
	} 
 
	public void mousePressed(MouseEvent e)	{} 
	public void mouseReleased(MouseEvent e) {} 
	public void mouseEntered(MouseEvent e) 
	{ 
		if (boss.ikoon)
		{
			boss.icon=boss.icon2; 
			boss.repaint(); 
		}

		if (boss.popup.getItemCount()==0)
		{
			boss.popup.plugIn();
		}

		if(boss.getParameter("onClick").compareTo("1")!=0)
		{
			jump(e);
		}
	} 
	public void mouseExited(MouseEvent e) 
	{ 
		if (boss.ikoon)
		{
			boss.icon=boss.icon1; 
			boss.repaint();
		}
	} 
	public void mouseClicked(MouseEvent e) 
	{ 
		jump(e);
	} 

	public void jump(MouseEvent e) 
	{
		if(!e.isMetaDown())//ainult vasaku klahviga avaneb 
		{ 
			int x=new Integer(boss.getParameter("x")).intValue();
			int y=new Integer(boss.getParameter("y")).intValue();
			boss.popup.show(e.getComponent(), x, y); 
		} 
	}
} 
 


class menyy extends PopupMenu
{
	Applet boss;

	menyy(Applet bos)
	{
		boss=bos;
	}

	public void plugIn()
	{
		String eraldaja="\n";
		String menyy=boss.getParameter("fetchcontent");
		int i=1;
		try 
		{ 
		if ((menyy.compareTo("1")==0)||(menyy==null))
		{
			String aadress=""; 
			byte[] array=new byte[1]; 
			byte[] array2; 
					  
			//pärimise aadressi ehitamine
			aadress="http://"+boss.getParameter("url")+"/orb.aw?class=menuedit&action=get_popup_data";

			String parameeter;
			try
			{
				while(true)
				{
					parameeter=boss.getParameter("urlparam"+i);
					if (parameeter==null)
					{
						break;
					}
					aadress=aadress+parameeter;
					i++;
				}
			}
			catch(Exception e)
			{
			}
			System.out.println("URL="+aadress);
			
			//aadress="http://"+getParameter("url")+"/orb.aw?class=menuedit&action=get_popup_data&id="+getParameter("oid"); 
			
			i=0;
			InputStream sisse=new URL(aadress).openConnection().getInputStream(); 
			int in=sisse.read(); 
							 
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
 
				menyy=new String(array);
		}
		else
		{
			menyy=boss.getParameter("content");
			eraldaja="#";
			//System.out.println("menyy="+menyy);
		}
//System.out.println("menyy="+menyy); 
/*=================== PARSIN ============================ 
//(id|parent|nimi|url|frame)

1|0|Set notification|http://server/?set_notification&id=667|frame
 separator
 2|0|Info||
        3|2|General|http://server/?info&id=667|frame
        4|2|Audit||
                5|4|alam1|http://server/?info&id=667|frame
                separator
                6|4|alam2|http://server/?info&id=667|frame
        7|2|General2|http://server/?info&id=667|frame
        8|2|Audit2|http://server/?audit&id=667|frame
 9|0|alam1|http://server/?info&id=667|frame

*/
 
			String nimi,viit,alam,frame; 
			int tab,id,parent; 
			menu[] jada=new menu[0];
			menu[] jada2;
			menu seff=new menu("temp",-1,-1,boss);//kirjutatakse kohe yle

			//tab=menyy.indexOf("\n");
			tab=menyy.indexOf(eraldaja);
			while(tab!=-1) 
			{ 
				if(tab<menyy.indexOf("|"))
				{//kui reavahetus enne eraldajaid
					this.addSeparator(); 
					menyy=menyy.substring(tab+1);
				}
				else
				{
					tab=menyy.indexOf("|");

					id=new Integer(menyy.substring(0,tab)).intValue(); 
					menyy=menyy.substring(tab+1); 
					tab=menyy.indexOf("|"); 
					
					parent=new Integer(menyy.substring(0,tab)).intValue(); 
					menyy=menyy.substring(tab+1); 
					tab=menyy.indexOf("|");

					nimi=menyy.substring(0,tab); 
					menyy=menyy.substring(tab+1); 
					tab=menyy.indexOf("|"); 
	 
					viit=menyy.substring(0,tab); 
					menyy=menyy.substring(tab+1); 
					//tab=menyy.indexOf("\n"); 
					tab=menyy.indexOf(eraldaja);

					frame=menyy.substring(0,tab);	
					menyy=menyy.substring(tab+1); 
//System.out.println("id="+id+"    parent="+parent+"   nimi="+nimi+"     viit="+viit+"  frame="+frame);
					if(parent==0)
					{
						if(viit.compareTo("")!=0)
						{
							this.add(new menuItem(nimi,viit,boss,id,parent,frame));
						}
						else
						{
							menu uus=new menu(nimi,id,parent,boss);
							this.add(uus);
							jada2=jada;
							jada=new menu[jada2.length+1];

							for(i=0;i<jada2.length;i++)
							{
								jada[i]=jada2[i];	
							}
							jada[i]=uus;
							seff=uus;
						}
					}//parent==0
					else
					{
						if((viit.compareTo("")!=0)&&(seff.id==parent))
						{
							seff.add(new menuItem(nimi,viit,boss,id,parent,frame));
						}
						else
						if(seff.id==parent)
						{
							menu uus=new menu(nimi,id,parent,boss);
							seff.add(uus);
							jada2=jada;
							jada=new menu[jada2.length+1];

							for(i=0;i<jada2.length;i++)
							{
								jada[i]=jada2[i];	
							}
							jada[i]=uus;
							seff=uus;	
						}
						else
						{//mingi ylem menyy jälle
							for(i=0;i<jada.length;i++)
							{
								if(jada[i].id==parent)
								{//sain kätte ylemmenyy
									seff=jada[i];
									break;
								}
							}

							if((viit.compareTo("")!=0)&&(seff.id==parent))
							{
								seff.add(new menuItem(nimi,viit,boss,id,parent,frame));
							}
							else
							if(seff.id==parent)
							{
								menu uus=new menu(nimi,id,parent,boss);
								seff.add(uus);
								jada2=jada;
								jada=new menu[jada2.length+1];

								for(i=0;i<jada2.length;i++)
								{
									jada[i]=jada2[i];	
								}
								jada[i]=uus;
								seff=uus;	
							}
						}//mingi ylemmenyy jälle
					}//else parent==0
				}//else separator
				
				//tab=menyy.indexOf("\n");
				tab=menyy.indexOf(eraldaja);

				if(tab==-1) 
				{ 
					break; 
				} 
			}

			menyy=null;
			 
		}                                                                                              			                                                                                         			 
		catch(Exception e)                                                                            			 
		{                                                                                              			 
			//System.out.println("Ei saanud menüüd kätte "+e); 
			//System.out.println("URL: "+aadress); 
		}  
	}
} 
 


class master extends Thread
{
	Applet boss;
	
	master(Applet bos)
	{
		boss=bos;
	}

	
	public void run()
	{
		Enumeration jada=boss.getAppletContext().getApplets();

		while (jada.hasMoreElements())
		{
			try
			{
				if (((mouseLeft)jada.nextElement()).popup.getItemCount()==0)
				{
					((mouseLeft)jada.nextElement()).popup.plugIn();
				}		
			}
			catch(Exception e)
			{
			}
		}

	}
}



public class mouseLeft extends Applet 
{ 
	Image icon; 
	Image icon1;//niisama refresh 
	Image icon2;//mouse over refresh 
	menyy popup;
	boolean ikoon=true;
 
	public void destroy()
	{
		popup.removeAll();
		removeAll();
	}

	
	public void init() 
	{ 
		URL urll; 
		popup=new menyy(this); 

		Color back=getColor(getParameter("back_color")); 
		if(back==null) 
		{ 
			back=Color.white; 
		} 
		setBackground(back); 
		setCursor(new Cursor(Cursor.HAND_CURSOR)); 
 
		try 
		{                                                                                              
			urll=new URL(getParameter("icon"));     			 
			icon=this.getImage(urll);   
			//icon=getImage(getCodeBase(),"print.gif"); 
			icon1=icon;                                                                			 
			urll=new URL(getParameter("mouse_over_icon"));   
			icon2=this.getImage(urll);     
			//icon2=getImage(getCodeBase(),"printRol.gif"); 
		}                                                                                              			                                                                                         			 
		catch(Exception e)                                                                            			 
		{                                                                                              			 
			System.out.println("Ei saanud ikooni kätte "+e);
			ikoon=false;
		}  
		
		this.add(popup); 
		this.addMouseListener(new hiireKuular(this));
		
		if (getParameter("now").compareTo("1")==0)
		{
			popup.plugIn();
		}
		if (getParameter("boss").compareTo("1")==0)
		{
			Thread master=new master(this);
			master.start();
			
		}
	} 
 

 
	public void paint(Graphics g) 
	{ 
		int tekststart=0;
		if(ikoon)
		{
			g.drawImage(icon, 0, 0, this);
			tekststart=25;
		}
		 
		String text=getParameter("text"); 
		if(text.compareTo("")!=0) 
		{ 
			int size=new Integer(getParameter("textsize")).intValue(); 

			if(getParameter("Style").compareTo("B")==0)
			{
				g.setFont(new Font(getParameter("font"),Font.BOLD,size)); 
			}
			else
			if(getParameter("Style").compareTo("I")==0)
			{
				g.setFont(new Font(getParameter("font"),Font.ITALIC,size)); 
			}
			else
				g.setFont(new Font(getParameter("font"),Font.PLAIN,size)); 

			Color fore=getColor(getParameter("fore_color")); 
			if(fore==null) 
			{ 
				fore=Color.black; 
			} 
			g.setColor(fore); 
			//g.drawString(text,25,20); 
			g.drawString(text,tekststart,getSize().height-1);

			if(getParameter("underline").compareTo("U")==0)
			{
				//g.drawLine(25,23,getSize().width-5,23);
				g.drawLine(tekststart,getSize().height-1,getSize().width-5,getSize().height-1);
			}
		} 
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
		 
		int red,green,blue; 
		try 
		{ 
			red=getNumber(arv.substring(1,3)); 
			green=getNumber(arv.substring(3,5)); 
			blue=getNumber(arv.substring(5)); 
 
			if((red==-1)||(green==-1)||(blue==-1)) 
			{ 
				return null; 
			} 
		} 
		catch(java.lang.StringIndexOutOfBoundsException ee) 
		{ 
			return null; 
		} 
		 
		return new Color(red,green,blue); 
	} 
 
} 
