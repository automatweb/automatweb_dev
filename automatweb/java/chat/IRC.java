import jp.kyasu.graphics.*;
import jp.kyasu.awt.TextArea;

import java.lang.*;
import java.io.*;
import java.awt.Button;
import java.awt.*;
import java.awt.TextField;
import java.awt.Frame;
import java.awt.Font;
import java.awt.List;
import java.awt.event.*;
import java.applet.Applet;
import java.net.*;
import java.util.*;
import java.awt.datatransfer.*;

//set CLASSPATH=kfc.jar; - windows
//export CLASSPATH; CLASSPATH=kfc.jar: - unix

//deemon /home/risto/IRCDeemon2/IRCDeemon2.java
//www/aw.com/public/risto/temp/   -  faili asukoht
//======================= FONDI STUFF ===========================



class ruut {    //moodustab kasti risti jaoks
        int X, Y;
        Color varv;

        ruut(int x, int y,Color v) {
                X = x;
                Y = y;
                varv = v;
        }

        public void joonistada(Graphics g) {
                g.setColor(varv);
				g.fillRect(X,Y,18,18);
        }
}



class ymbris {    //must ruut ymber värvi valiku
        int X, Y;

        ymbris(int x, int y) {
                X = x;
                Y = y;
        }

        public void joonistada(Graphics g) {
                g.setColor(Color.black);
                g.drawRect(X+2, Y+2, 19, 19);
}
}



class ymbris2 {    //must ruut ymber värvi valiku teine, kuna IE on imbetsill
        int X, Y;

        ymbris2(int x, int y) {
                X = x;
                Y = y;
        }

        public void joonistada(Graphics g) {
                g.setColor(Color.black);
                g.drawRect(X+2, Y+2, 19, 19);
}
}



class chooceColor extends Canvas {//Canvas
        ruut[] kastid=new ruut[49];
		ymbris ymberText;
		ymbris2 ymberTaust;
  
  chooceColor(){
        this.setBackground(Color.white);
		this.setSize(145,145);
          } 

       public void paint(Graphics g) {

	for (int i  = 0; i < 49; i++){
                        kastid[i].joonistada(g);
						Object o = kastid[i];      
                        if (o instanceof ruut)
                                ((ruut) o).joonistada(g);            
    }

                       Object o = ymberText;      
                       if (o instanceof ymbris)
                               ((ymbris) o).joonistada(g);            
   
                       Object oo = ymberTaust;      
                       if (oo instanceof ymbris2)
                               ((ymbris2) oo).joonistada(g);            
	   }
}



class OKKuular implements ActionListener{	//vajutati "OK"
	Dialog raam;
	TextArea test;
	channel[] channels;
	priva[] privad;
	priva status;

    OKKuular(Dialog raaam,TextArea testt,channel[] chas,priva[] privs,priva stat){
        raam=raaam;	
		test=testt;
		status=stat;
		privad=privs;
		channels=chas;
	}

   public void actionPerformed(ActionEvent event){
	Color back=test.getBackground();
	Color front=test.getForeground();

	status.jutt.setBackground(back);
	status.textColor=front;
	status.font=test.getFont();
	status.in.setBackground(back);
	status.in.setForeground(front);
	status.in.setFont(test.getFont());

	for(int i=0;i<channels.length;i++)
	{
		if(channels[i]!=null)
		{
			channels[i].jutt.setBackground(back);
			channels[i].textColor=front;
			channels[i].font=test.getFont();
			channels[i].in.setBackground(back);
			channels[i].in.setForeground(front);
			channels[i].in.setFont(test.getFont());
			channels[i].list.setBackground(back);
			channels[i].list.setForeground(front);
			channels[i].list.setFont(test.getFont());
		}
	}
	for(int i=0;i<privad.length;i++)
	{
		if(privad[i]!=null)
		{
			privad[i].jutt.setBackground(back);
			privad[i].textColor=front;
			privad[i].font=test.getFont();
			privad[i].in.setBackground(back);
			privad[i].in.setForeground(front);
			privad[i].in.setFont(test.getFont());
		}
	}
	raam.dispose();
	System.gc();
      }
}



class IEkala implements MouseListener{
Checkbox text,taust;
ymbris[] textAbi;
ymbris2[] taustAbi;
chooceColor c;

	IEkala(Checkbox textt,Checkbox back,chooceColor cc,ymbris[] abi1,ymbris2[] abi2){
		text=textt;
		taust=back;		textAbi=abi1;
		taustAbi=abi2;	c=cc;
		}

	public void mousePressed(MouseEvent e)	{}
	public void mouseReleased(MouseEvent e) {
	if(!text.getState()){
		taustAbi[0]=c.ymberTaust;
		c.ymberTaust=null;
		c.ymberText=textAbi[0];
		}
		else{
			textAbi[0]=c.ymberText;
			c.ymberText=null;
			c.ymberTaust=taustAbi[0];
		}
	c.repaint();
	}
	public void mouseEntered(MouseEvent e)	{}
	public void mouseExited(MouseEvent e)	{}
	public void mouseClicked(MouseEvent e)	{}
}



class valik implements ItemListener{
List fonts,suurus,stiil;
Checkbox bold;
TextArea test;
Font plainFont;
String nimi,style;

	valik(List font,List suur,Checkbox boldd,TextArea testt){
		fonts=font;
		suurus=suur;
		bold=boldd;
		test=testt;
		}

	public void itemStateChanged(ItemEvent event){
		nimi=fonts.getSelectedItem();
		
		int size=new Integer(suurus.getSelectedItem()).intValue();

		if(bold.getState()){		
		plainFont = new Font(nimi, Font.BOLD, size);	
		test.setFont(plainFont);
		}
		else{
		plainFont = new Font(nimi, Font.PLAIN, size);
		test.setFont(plainFont);
		}
	}
}




class selectColor implements MouseListener {
TextArea test;
Color[] pallett;
ymbris2[] taustAbi;
ymbris[] textAbi;
chooceColor c;
MouseListener action;
Checkbox text,taust;

		selectColor(TextArea testt,Color[] pallet,Checkbox tekst,Checkbox taustt,ymbris[] abi1,
				ymbris2[] abi2,chooceColor cc,MouseListener actioon){
			test=testt;				text=tekst;
			pallett=pallet;			taust=taustt;
			textAbi=abi1;			taustAbi=abi2;
			c=cc;					action=actioon;
		}

		public void mousePressed(MouseEvent e)	{}
        public void mouseReleased(MouseEvent e) {
		int x = e.getX()-3;               //saab hiire kordinaadid
		int y = e.getY()-3;
		int xx=x/20;
		int yy=y/20*7;
		int yyy=y/20;	
		int kohal=xx+yy;

		if(text.getState()){	
			test.setForeground(pallett[kohal]);
			c.ymberText=null;
			c.ymberText=new ymbris(xx*20,yyy*20);
			textAbi[0]=c.ymberText;
		}
		else{
			test.setBackground(pallett[kohal]);
			c.ymberTaust=null;
			c.ymberTaust=new ymbris2(xx*20,yyy*20);
			taustAbi[0]=c.ymberTaust;
		}
		c.repaint();
		text.removeMouseListener(action);
		taust.removeMouseListener(action);
		action=new IEkala(text,taust,c,textAbi,taustAbi);
		text.addMouseListener(action);
		taust.addMouseListener(action);
		}
        public void mouseEntered(MouseEvent e)	{}
        public void mouseExited(MouseEvent e)	{}
        public void mouseClicked(MouseEvent e)	{}
}



class fondiKuular implements ActionListener{
	channel[] channels;
	priva[] privad;
	priva status;
	Frame peaaken;
	Color back,buttonback;

	fondiKuular(channel[] chas,priva[] privs,priva stat,Frame pea,Color bac,Color buttonbac)
	{
		channels=chas;
		privad=privs;
		status=stat;
		peaaken=pea;
		back=bac;
		buttonback=buttonbac;
	}

		public void actionPerformed(ActionEvent event){

		Dialog raam=new Dialog(peaaken,"Vali kirja stiil ja suurus.",true);
		raam.addWindowListener(new AknaKuular(raam));
		raam.setSize(450,450);
		raam.setLocation(peaaken.getLocation().x+50,peaaken.getLocation().y+50);
	
		ymbris2[] taustAbi=new ymbris2[1];
		ymbris[] textAbi=new ymbris[1];

		List fonts= new List(5);
		fonts.add("TimesRoman");
		fonts.add("Times New Roman");
		fonts.add("Monospaced");
		fonts.add("Serif");

		Label tyhi=new Label("       ");
		Label tyhi2=new Label("       ");
		Label tyhi4=new Label("       ");
		Label tyhi5=new Label("       ");
		Label tyhi6=new Label("       ");
		Label fondid=new Label("Vali font.",1);
		Label suurus2=new Label("Vali kirja suurus",1);
		Label valik=new Label("Värvi seaded",1);
		suurus2.setBackground(back);
		tyhi.setBackground(back);
		tyhi2.setBackground(back);
		tyhi4.setBackground(back);
		tyhi5.setBackground(back);
		tyhi6.setBackground(back);
		valik.setBackground(back);
		
		Panel  all=new Panel();
		Panel vasak =new Panel();
		Panel keskel =new Panel();
		Panel parem =new Panel();
		Panel alumine =new Panel();
		
		Button cancel=new Button("Katkesta");
		Button ok=new Button("    OK    ");
		cancel.setBackground(buttonback);
		ok.setBackground(buttonback);

		alumine.add(ok);
		alumine.add(cancel);

		keskel.setBackground(back);
		vasak.setBackground(back);
		parem.setBackground(back);
		alumine.setBackground(back);	
	
		List suurus=new List(5);
		suurus.add("9");
		suurus.add("10");
		suurus.add("11");
		suurus.add("12");
		suurus.add("13");
		suurus.add("14");
		suurus.add("15");
		suurus.add("16");
		suurus.add("17");
		suurus.add("18");
		suurus.add("19");
		suurus.add("20");
		suurus.add("21");
		suurus.add("22");
		
Color[] pallett=new Color[49];
chooceColor c=new chooceColor();


int j=0;
for(int i=0;i<=255;i=i+42){
Color värv1=new Color(i,i,i);//7 halli
pallett[j]=värv1;
j++;
}
pallett[j-1]=new Color(255,255,255);

int üks=7;
int kaks=14;
int kolm=21;
int neli=28;
int viis=35;
int kuus=42;


		for(int i=63;i<=255;i=i+63){		
			pallett[üks++]=new Color(0,i,i);//sinakas roheline
			pallett[kaks++]=new Color(0,0,i);//sinine
			pallett[kolm++]=new Color(i,0,i);//lilla
			pallett[neli++]=new Color(i,0,0);//punane
			pallett[viis++]=new Color(i,i,0);//kollane
			pallett[kuus++]=new Color(0,i,0);//roheline
		}

		for(int i=63;i<210;i=i+63){		
			pallett[üks++]=new Color(i,255,255);//sinakas roheline
			pallett[kaks++]=new Color(i,i,255);//sinine
			pallett[kolm++]=new Color(255,i,255);//lilla
			pallett[neli++]=new Color(255,i,i);//punane
			pallett[viis++]=new Color(255,255,i);//kollane
			pallett[kuus++]=new Color(i,255,i);//roheline
		}

	int www=0;
		for(int i=0;i<7;i++)	//joonistan ruudud
			for(int jj=0;jj<7;jj++){	
				c.kastid[www]=new ruut(jj*20+3,i*20+3,pallett[www]);//+3, muidu servi ei näe
				www++;
		}
		
		Font font;
		boolean emm=false;
		int size=12;
		String fondinimi="TimesRoman";

		font=status.font;
		fondinimi=font.getName();
		emm=font.isBold();
		size=font.getSize();

		
		if(emm)
		font = new Font(fondinimi, Font.BOLD, size);
		else
			font = new Font(fondinimi, Font.PLAIN, size);

		TextArea test =new TextArea("See on test!",3,22,3);//keelan kerimisribad
		test.setFont(font);
		//test.setEditable(false);
		Checkbox bold=new Checkbox("Bold",emm);

		
		for(www=0;www<4;www++){
			if(fonts.getItem(www).compareTo(fondinimi)==0){
				fonts.select(www);//valitud on kehtiv font
				break;
			}

		}

		int arv=0;
		for (www=0;www<14;www++){
			arv=new Integer(suurus.getItem(www)).intValue();
			if(arv==size){
				suurus.select(www);
				break;
			}

		}

		Color front=status.textColor;
		Color back=status.getBackground();
		
		test.setBackground(back);
		test.setForeground(front);

		if(front==null){
			test.setBackground(pallett[6]);
			test.setForeground(pallett[0]);
			front=pallett[0];
			back=pallett[6];
		}
		
		int ees=0;
		int taga=0;
		int red,green,blue,red2,green2,blue2,i;

		for(i=0;i<49;i++){
			red2=pallett[i].getRed();
			green2=pallett[i].getGreen();
			blue2=pallett[i].getBlue();
			red=back.getRed();
			green=back.getGreen();
			blue=back.getBlue();

			if((red==red2)&&(green==green2)&&(blue==blue2)) taga=i;

			red=front.getRed();
			green=front.getGreen();
			blue=front.getBlue();

			if((red==red2)&&(green==green2)&&(blue==blue2)) ees=i;
		}
		
		//leian värvi järjekorra numbrile vastava värvi asukoha
		int y=ees/7;
		double x1=(ees*1.0)/7;
		long x2=Math.round((x1-y)*7);
		int x=Math.round(x2);
		int yy=taga/7;
		double xx1=(taga*1.0)/7;
		long xx2=Math.round((xx1-yy)*7);
		int xx=Math.round(xx2);

		c.ymberTaust=new ymbris2(xx*20,yy*20);//valgel kast ymber
		taustAbi[0]=c.ymberTaust;
		textAbi[0]=new ymbris(x*20,y*20);
		c.repaint();

		CheckboxGroup g = new CheckboxGroup(); 
		Checkbox text=new Checkbox("Text",g,false);
		Checkbox taust=new Checkbox("Taust",g,true);	
		all.setLayout(new GridLayout (0,2));
		GridBagLayout baseVasak=new GridBagLayout();
		GridBagLayout baseKeskel=new GridBagLayout();
		GridBagLayout baseParem=new GridBagLayout();
		GridBagConstraints cc=new GridBagConstraints();//pane itemid ylestikku

		cc.weighty = 0.0;		   //reset to the default
		cc.weightx = 0.0;	
 	    cc.gridwidth = GridBagConstraints.REMAINDER; //end row REMAINDER
 	    cc.gridheight = 1;
		baseVasak.setConstraints(fondid, cc);
        baseVasak.setConstraints(fonts, cc);
		baseVasak.setConstraints(tyhi, cc);
		baseVasak.setConstraints(bold, cc);
		baseVasak.setConstraints(tyhi2, cc);
		baseVasak.setConstraints(suurus2, cc);
		baseVasak.setConstraints(suurus, cc);
            
		vasak.setLayout(baseVasak);//
		vasak.add(fondid);//label
		vasak.add(fonts);//fondid
		vasak.add(tyhi);
		vasak.add(bold);//checkbox
		vasak.add(tyhi2);
		vasak.add(suurus2);//label
		vasak.add(suurus);//texti suurus
		
		keskel.setLayout(baseKeskel);
		baseKeskel.setConstraints(valik, cc);//kiri yleval
		cc.gridwidth = 3;
		baseKeskel.setConstraints(tyhi6, cc);
		baseKeskel.setConstraints(text, cc);
		baseKeskel.setConstraints(taust, cc);
		cc.gridwidth = 0;
		baseKeskel.setConstraints(tyhi4, cc);
		baseKeskel.setConstraints(c, cc);
		baseKeskel.setConstraints(tyhi5, cc);
		baseKeskel.setConstraints(test, cc);//testi ala
		
		keskel.add(valik);
		keskel.add(tyhi6);
		keskel.add(text);
		keskel.add(taust);
		keskel.add(tyhi4);
		keskel.add(c);
		keskel.add(tyhi5);
		keskel.add(test);
		
		all.add(vasak);
		all.add(keskel);
		
		raam.add(all);
		raam.add(alumine,"South");
		raam.add(parem,"East");
		
fonts.addItemListener(new valik(fonts,suurus,bold,test));
bold.addItemListener(new valik(fonts,suurus,bold,test));
suurus.addItemListener(new valik(fonts,suurus,bold,test));
MouseListener action=new IEkala(text,taust,c,textAbi,taustAbi);
text.addMouseListener(action);
taust.addMouseListener(action);
c.addMouseListener(new selectColor(test,pallett,text,taust,textAbi,taustAbi,c,action));
ok.addActionListener(new OKKuular(raam,test,channels,privad,status));
cancel.addActionListener(new KatkestaKuular(raam));
raam.show();
}
}



//================================================================


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



class keyMem implements KeyListener
{
	channel see;
	priva see2;
	int kumb;
	
	TextField in;
	String[] mem,memUp;
	int i,j,last;

	keyMem(priva se2,channel se,int kum)
	{
		kumb=kum;
		see=se;
		see2=se2;
	}


	public void keyPressed(KeyEvent e)
	{
		if(kumb==0)
		{
			in=see.in;
			i=see.i;
			j=see.j;
			last=see.last;
			mem=see.mem;
			memUp=see.memUp;
		}
		else
		{
			in=see2.in;
			i=see2.i;
			j=see2.j;
			last=see2.last;
			mem=see2.mem;
			memUp=see2.memUp;
		}
		
		if(e.getKeyCode()==38)
		{//yles nool
			if(mem[i]!=null)
			{
				j++;
				in.setText(mem[i]);
				//System.out.print("  up Kuvan: "+mem[i]+"   i="+i);
				if(j>memUp.length-1)
				{
					j=0;
				}
				memUp[j]=mem[i];
				//j++;
			}
			else
			{
				for(int ii=i-1;mem[0]!=null;ii--)
				{//1. koht peab ikka täidetud olema, muidu endless tsykkel
					if(mem[ii]!=null)
					{
						i=ii;
						in.setText(mem[i]);
						//System.out.print("  up  Kuvan: "+mem[i]+"   i="+i);
						break;
					}
					if(ii<0)
					{
						ii=memUp.length-1;
					}
				}
			}
			i--;
			if(i<0)
			{
				i=memUp.length-1;
			}
			//System.out.println("    up   i="+i+"   j="+j);
		}
		else
		if(e.getKeyCode()==40)
		{//alla nool
			j--;
			
			if(j!=-1)
			{
				in.setText(memUp[j]);
				//System.out.print("  down  Kuvan: "+memUp[j]+"   j="+j);
				i--;
				
				if(i<0)
				{
					i=memUp.length-1;
				}
				if(mem[i]==null)
				{
					for(int ii=i-1;mem[0]!=null;ii--)
					{
						if(mem[ii]!=null)
						{
							i=ii;
							break;
						}
					}
				}
			}
			else
			{
				in.setText("");
				j=0;
			}
			//System.out.println("  down   i="+i+"   j="+j);
		}
		else
		if(e.getKeyCode()==10)
		{//vajutati enter
			
			if(last>mem.length-1)
			{
				last=0;
			}
			
			mem[last]=in.getText();
			memUp=new String[mem.length];
			i=last;
			last++;	
			j=0;
		}

		if(kumb==0)
		{
			see.i=i;
			see.j=j;
			see.last=last;
			see.mem=mem;
			see.memUp=memUp;
		}
		else
		{
			see2.i=i;
			see2.j=j;
			see2.last=last;
			see2.mem=mem;
			see2.memUp=memUp;
		}
	}

	public void keyReleased(KeyEvent e)
	{
	}

	public void keyTyped(KeyEvent e)
	{//hoiad all
	}
}



class PopupListener extends MouseAdapter
{
	PopupMenu popups;

	PopupListener(PopupMenu po)
	{
		popups=po;
	}

    public void mousePressed(MouseEvent e)
	{
      maybeShowPopup(e);
    }
    public void mouseReleased(MouseEvent e)
	{
      maybeShowPopup(e);
    }
    private void maybeShowPopup(MouseEvent e)
	{	
		if(e.isPopupTrigger())
		{
			popups.show(
			e.getComponent(), e.getX(), e.getY());
		}
	}
}



class menuList extends List{
	PopupMenu popups=new PopupMenu();
	MenuItem chat = new MenuItem("Priva");
	MenuItem whois = new MenuItem("Kes on...");
	MenuItem op = new MenuItem("Anna op.");
	MenuItem deop = new MenuItem("Võta op.");
	MenuItem ignore = new MenuItem("Ignoreeri");
	MenuItem unignore = new MenuItem("Aktsepteeri");
	MenuItem kick = new MenuItem("Viska välja");
	MenuItem ban = new MenuItem("Keela tuba");
	
	menuList()
	{
		popups.add(chat);
		popups.add(whois);
		popups.add(op);	
		popups.add(unignore);
		popups.addSeparator();
		popups.add(ignore);
		popups.add(deop);
		popups.add(kick);
		popups.add(ban);	
		this.add(popups);
		PopupListener pl = new PopupListener(popups);
		addMouseListener(pl);	
  }
}



class menuButton extends Button{
	//PopupMenu popups;
	//CheckboxMenuItem beep,flash,timer,desktop,log;
	//MenuItem close;
	PopupMenu popups=new PopupMenu();
	CheckboxMenuItem beep=new CheckboxMenuItem("Piiks");
	CheckboxMenuItem flash=new CheckboxMenuItem("Vilgub");
	CheckboxMenuItem timer=new CheckboxMenuItem("Timer");
	CheckboxMenuItem desktop=new CheckboxMenuItem("Desktopil");
	CheckboxMenuItem log=new CheckboxMenuItem("Log");
	MenuItem close=new MenuItem("Sulge");


	menuButton()
	{	
		popups.add(beep);
		popups.add(flash);
		popups.add(timer);
		popups.add(desktop);
		popups.add(log);
		popups.addSeparator();
		popups.add(close);
		this.add(popups);
		PopupListener pl = new PopupListener(popups);
		addMouseListener(pl);	
  }
}



class AknaKuular2 implements WindowListener, ComponentListener{
           Frame raam;
		   PrintStream toserver;
		   Socket s;
		   boolean[] luba;
		   channel[] channels;
		   priva[] privad;
		   action see;
		   Color back;
		   static Dialog aken;
		   static String aadress;
		   static TextField adress;

        AknaKuular2(Frame f,PrintStream to,Socket ss,boolean[] uba,channel[] chas,priva[] privs,action se,Color bac)
		{
			raam = f;				channels=chas;
			toserver=to;			privad=privs;
			s=ss;					luba=uba;
			see=se;					back=bac;
		}


		public static String date(){
			String mitmes1="", tund1="", minut1="", sekund1="", kuu1=""; 
			Calendar c=Calendar.getInstance();

			int kuu=c.get(c.MONTH)+1;
			if(kuu<10) kuu1="0";
			int aasta=c.get(c.YEAR);
			int mitmes=c.get(c.DAY_OF_MONTH);
			if(mitmes<10) mitmes1="0";
			int tund= c.get(c.HOUR_OF_DAY);
			if(tund<10) tund1="0";
			int minut=c.get(c.MINUTE);
			if(minut<10) minut1="0";
			int sekund=c.get(c.SECOND);
			if(sekund<10) sekund1="0";

			return mitmes1+mitmes+"."+kuu1+kuu+"."+aasta+"  "+tund1+tund+":"+minut1+minut+")";
		}


        public void windowOpened(WindowEvent e)		{}
        public void windowClosing(WindowEvent e)	{

			boolean mail=false;//pole veel teada, kuhu logid meilida

			aken=new Dialog(raam,"Log",true);
			aken.setLocation(raam.getLocation().x+raam.getSize().width/2-125,raam.getLocation().y+raam.getSize().height/2-60);                                          		   				
			aken.setSize(250,130);
			Label kiri=new Label("Sisesta meili aadress, kuhu log(id) saata.");
			kiri.setBackground(back);
			Panel panel1=new Panel();
			panel1.setBackground(back);
			panel1.add(kiri);
		
			adress=new TextField(25);
			Panel panel2=new Panel();
			panel2.setBackground(back);
			panel2.add(adress);

			Button ok=new Button("  OK  ");                                      				
			Button cancel=new Button("Katkesta");                                				
			Panel panel3=new Panel();
			panel3.setBackground(back);
			panel3.add(ok);
			panel3.add(cancel);

			aken.add(panel1,"North");
			aken.add(panel2,"Center");
			aken.add(panel3,"South");
						
			cancel.addActionListener(new ActionListener(){
				public void actionPerformed(ActionEvent event){
					aken.dispose();
				}
			});

			ok.addActionListener(new ActionListener(){
				public void actionPerformed(ActionEvent event){
					aadress=adress.getText();
					aken.dispose();
				}
			});

			/*IE tõmbab sellele võimalusele vee peale
			Frame[] jada=raam.getFrames();
			for(int i=0;i<jada.length;i++)
			{//sulgen kõik loodud aknad
				jada[i].dispose();
			}
			teeme selle siis ringiga*/

			for(int i=0;i<channels.length;i++)
			{
				if(channels[i]!=null)
				{
					if(channels[i].nupp.log.getState())
					{//nõutakse logimist
						if(!mail)
						{
							mail=true;
							aken.show();
						}			
						toserver.println(channels[i].kestvus+date());//subject
						toserver.println(aadress);
						toserver.println(channels[i].jutt.getText());
						toserver.println(".");
					}
					channels[i].desk.dispose();
				}
			}

			for(int i=0;i<privad.length;i++)
			{
				if(privad[i]!=null)
				{
					if(privad[i].nupp.log.getState())
					{//nõutakse logimist
						if(!mail)
						{
							mail=true;
							aken.show();
						}
						toserver.println(privad[i].kestvus+date());//subject
						toserver.println(privad[i].jutt.getText());
						toserver.println(aadress);
					}
					privad[i].desk.dispose();
				}
			}

			raam.dispose();

			if(toserver!=null)
			{
				if(see.canSee.getState())
				{
					toserver.println("0000 "+see.nick[0]);
				}
				toserver.println("Quit");
			}
			try
			{
				s.close();
			}
			catch(IOException ee)
			{
			}

			try
			{
				see.interrupt();
			}
			catch(java.lang.SecurityException ee)
			{
				try
				{
					see.destroy();
				}
				catch(java.lang.NoSuchMethodError eee)
				{
					System.gc();
				}
			}

			System.gc();
		}
        public void windowClosed(WindowEvent e)		{}
        public void windowIconified(WindowEvent e)	{}
        public void windowDeiconified(WindowEvent e)	{}
        public void windowActivated(WindowEvent e)
		{
			luba[0]=true;
			luba[1]=true;
		}
        public void windowDeactivated(WindowEvent e)
		{
			luba[0]=false;
		}


		public void componentHidden(ComponentEvent e)
		{
		}
         
	public void componentMoved(ComponentEvent e) 
    {
	}  
	
	public void componentResized(ComponentEvent e) 
    {//muidu ei kohanda akna sisu akna mõõduga
		if(raam.getComponentCount()>1)
		{
			raam.getComponent(1).setSize(raam.getSize().width-7,raam.getSize().height-108);                                  	
			raam.show();
		}
	}
	
	public void componentShown(ComponentEvent e) 
    {
	}   
}



class AknaKuular implements WindowListener{
           Dialog raam;
		  
        AknaKuular(Dialog f)
		{
			raam = f;
		}

        public void windowOpened(WindowEvent e)		{}
        public void windowClosing(WindowEvent e)	{
			raam.dispose();
			System.gc();
		}
        public void windowClosed(WindowEvent e)		{}
        public void windowIconified(WindowEvent e)	{}
        public void windowDeiconified(WindowEvent e)	{}
        public void windowActivated(WindowEvent e)	{}
        public void windowDeactivated(WindowEvent e)	{}
}


class destroy implements WindowListener{
           Applet boss;
		  
        destroy(Applet bos)
		{
			boss=bos;;
		}

        public void windowOpened(WindowEvent e)		{}
        public void windowClosing(WindowEvent e)	{
			boss.destroy();
		}
        public void windowClosed(WindowEvent e)		{}
        public void windowIconified(WindowEvent e)	{}
        public void windowDeiconified(WindowEvent e)	{}
        public void windowActivated(WindowEvent e)	{}
        public void windowDeactivated(WindowEvent e)	{}
}



class kanaliKuular implements WindowListener
{
	Frame peaaken;
	priva status,chat;
	channel kanal;
	PrintStream toserver;
	Menu windows;
	static Dialog aken;
	static String aadress;
	static TextField adress;
	boolean mail;

		  

		public static String date(){
			String mitmes1="", tund1="", minut1="", sekund1="", kuu1=""; 
			Calendar c=Calendar.getInstance();

			int kuu=c.get(c.MONTH)+1;
			if(kuu<10) kuu1="0";
			int aasta=c.get(c.YEAR);
			int mitmes=c.get(c.DAY_OF_MONTH);
			if(mitmes<10) mitmes1="0";
			int tund= c.get(c.HOUR_OF_DAY);
			if(tund<10) tund1="0";
			int minut=c.get(c.MINUTE);
			if(minut<10) minut1="0";
			int sekund=c.get(c.SECOND);
			if(sekund<10) sekund1="0";

			return mitmes1+mitmes+"."+kuu1+kuu+"."+aasta+"  "+tund1+tund+":"+minut1+minut+")";
		}


        kanaliKuular(Frame pea,priva ct,channel kan,priva stat,PrintStream to,Menu win,Color back)
		{
			peaaken=pea;		kanal=kan;
			chat=ct;			status=stat;
			toserver=to;		windows=win;

			mail=false;

			aken=new Dialog(peaaken,"Log",true);
			aken.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-125,peaaken.getLocation().y+peaaken.getSize().height/2-60);                                          		   				
			aken.setSize(250,130);
			Label kiri=new Label("Sisesta meili aadress, kuhu log(id) saata.");
			kiri.setBackground(back);
			Panel panel1=new Panel();
			panel1.setBackground(back);
			panel1.add(kiri);
		
			adress=new TextField(25);
			Panel panel2=new Panel();
			panel2.setBackground(back);
			panel2.add(adress);

			Button ok=new Button("  OK  ");                                      				
			Button cancel=new Button("Katkesta");                                				
			Panel panel3=new Panel();
			panel3.setBackground(back);
			panel3.add(ok);
			panel3.add(cancel);

			aken.add(panel1,"North");
			aken.add(panel2,"Center");
			aken.add(panel3,"South");
						
			cancel.addActionListener(new ActionListener(){
				public void actionPerformed(ActionEvent event){
					aken.dispose();
				}
			});

			ok.addActionListener(new ActionListener(){
				public void actionPerformed(ActionEvent event){
					aadress=adress.getText();
					aken.dispose();
				}
			});
		}

        public void windowOpened(WindowEvent e)		{}
        public void windowClosing(WindowEvent e)
		{
			

			if(kanal!=null)
			{
				if(kanal.nupp.log.getState())
				{//nõutakse logimist
					if(!mail)
					{
						mail=true;
						aken.show();
					}			
					toserver.println(kanal.kestvus+date());//subject
					toserver.println(aadress);
					toserver.println(kanal.jutt.getText());
					toserver.println(".");
				}
				//kanal.desk.dispose();
				kanal.desk.setVisible(false);
				kanal.desk.removeAll();
				((Panel)peaaken.getComponent(0)).remove(kanal.nupp);
				toserver.println("PART "+kanal.name);
				//kanal.name="";
				status.setVisible(true);
				status.nupp.setForeground(Color.blue);
				windows.remove(kanal.win);
			}
			else
			{
				if(chat.nupp.log.getState())
				{//nõutakse logimist
					if(!mail)
					{
						mail=true;
						aken.show();
					}			
					toserver.println(chat.kestvus+date());//subject
					toserver.println(aadress);
					toserver.println(chat.jutt.getText());
					toserver.println(".");
				}
				//chat.desk.dispose();
				chat.desk.setVisible(false);
				chat.desk.removeAll();
				((Panel)peaaken.getComponent(0)).remove(chat.nupp);
				windows.remove(chat.win);
				chat.name="";
				status.setVisible(true);
				status.nupp.setForeground(Color.blue);
			}
			((Panel)peaaken.getComponent(0)).removeNotify();
			((Panel)peaaken.getComponent(0)).addNotify();
			peaaken.show();
			System.gc();
		}
        public void windowClosed(WindowEvent e)		{}
        public void windowIconified(WindowEvent e)	{}
        public void windowDeiconified(WindowEvent e)	{}
        public void windowActivated(WindowEvent e){
			if(kanal!=null)
			{
				kanal.aktiivne=true;
			}
			else
			{
				chat.aktiivne=true;
			}
		}
        public void windowDeactivated(WindowEvent e)
		{
			if(kanal!=null)
			{
				kanal.aktiivne=false;
			}
			else
			{
				chat.aktiivne=false;
			}
		}
}



class Väljumine implements ActionListener{
PrintStream toserver;
Socket s;
priva status;
priva[] privad;
channel[] channels;
MenuItem exit;
action see;


	public static String date()
	{
		Calendar c=Calendar.getInstance();
		String panen2="";
		String answer="";
		int tund= c.get(c.HOUR_OF_DAY);
		if(tund<10) panen2="0";
		int minut=c.get(c.MINUTE);
		if(minut<10) answer="0";
		return "["+""+panen2+""+tund+":"+""+answer+""+minut+"]";
	}


   Väljumine(PrintStream to,Socket ss,priva stat,channel[] chas,priva[] privs,MenuItem ext,action se){
		toserver=to;		channels=chas;
		s=ss;				privad=privs;
		status=stat;		exit=ext;
		see=se;
	}

	public void actionPerformed(ActionEvent event){
		toserver.println("Quit");
		
		String aeg=date();
		status.append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,status.jutt,status.buffer);                           
		status.connect=false;                                                                                                   			  
		int i;
		
		for(i=0;i<privad.length;i++)                                                                                            			
		{                                                                                                                       			
			if(privad[i]!=null)                                                                                                 			
			{                                                                                                                   			
				if(privad[i].name.compareTo("")!=0)                                                                             			
				{                                                                                                               			
					privad[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,privad[i].jutt,privad[i].buffer);      			                                                                  			
				}                                                                                                               			
			}                                                                                                                   			
		}                                                                                                                       			
		                                                                                                                        
		for(i=0;i<channels.length;i++)                                                                                          			
		{                                                                                                                       			
			if(channels[i]!=null)                                                                                               			
			{                                                                                                                   			
				if(channels[i].name.compareTo("")!=0)                                                                           			
				{                                                                                                               			
					channels[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,channels[i].jutt,channels[i].buffer);						                                                                  			
				}                                                                                                               			
			}                                                                                                                   			
		}                                                                                                                       			
					exit.setEnabled(false);

		try{
			s.close();
			System.gc();
		}
		catch(Exception e){}

		try
		{
			see.interrupt();
		}
		catch(java.lang.SecurityException ee)
		{
			try
			{
				see.destroy();
			}
			catch(java.lang.NoSuchMethodError e)
			{
				System.gc();
			}
		}
	}
}



class closeKuular implements ActionListener
{
	PrintStream toserver;
	Frame peaaken;
	priva[] privad;
	channel[] channels;
	priva status;
	Button nupp;
	Menu windows;
	static Dialog aken;
	static TextField adress;
	static String aadress;
	boolean mail;


		public static String date(){
			String mitmes1="", tund1="", minut1="", sekund1="", kuu1=""; 
			Calendar c=Calendar.getInstance();

			int kuu=c.get(c.MONTH)+1;
			if(kuu<10) kuu1="0";
			int aasta=c.get(c.YEAR);
			int mitmes=c.get(c.DAY_OF_MONTH);
			if(mitmes<10) mitmes1="0";
			int tund= c.get(c.HOUR_OF_DAY);
			if(tund<10) tund1="0";
			int minut=c.get(c.MINUTE);
			if(minut<10) minut1="0";
			int sekund=c.get(c.SECOND);
			if(sekund<10) sekund1="0";

			return mitmes1+mitmes+"."+kuu1+kuu+"."+aasta+"  "+tund1+tund+":"+minut1+minut+")";
		}


	closeKuular(Frame pea,priva stat,channel[] chas,priva[] privs,PrintStream to,Button npp,Menu wins,Color back){
		channels=chas;		privad=privs;
		status=stat;		toserver=to;
		peaaken=pea;		nupp=npp;
		windows=wins;

		mail=false;//pole veel teada, kuhu logid meilida

		aken=new Dialog(peaaken,"Log",true);
		aken.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-125,peaaken.getLocation().y+peaaken.getSize().height/2-60);                                          		   				
		aken.setSize(250,130);                                                 
		Label kiri=new Label("Sisesta meili aadress, kuhu log(id) saata.");    	
		kiri.setBackground(back);                                              	
		Panel panel1=new Panel();                                              	
		panel1.setBackground(back);                                            	
		panel1.add(kiri);                                                      	
		                                                                       
		adress=new TextField(25);                                              	
		Panel panel2=new Panel();                                              	
		panel2.setBackground(back);                                            	
		panel2.add(adress);                                                    	
		                                                                       
		Button ok=new Button("  OK  ");                                      					
		Button cancel=new Button("Katkesta");                                					
		Panel panel3=new Panel();                                              	
		panel3.setBackground(back);                                            	
		panel3.add(ok);                                                        	
		panel3.add(cancel);                                                    	
		                                                                       
		aken.add(panel1,"North");                                              	
		aken.add(panel2,"Center");                                             	
		aken.add(panel3,"South");                                              	
					                                                           	
		cancel.addActionListener(new ActionListener(){                         	
			public void actionPerformed(ActionEvent event){                    	
				aken.dispose();                                                	
			}                                                                  	
		});                                                                    	
		                                                                       
		ok.addActionListener(new ActionListener(){                             	
			public void actionPerformed(ActionEvent event){                    	
				aadress=adress.getText();                                      	
				aken.dispose();                                                	
			}                                                                  	
		});                                                                    	
	}

	public void actionPerformed(ActionEvent event){
		
		Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();
		boolean edasi=true;
		String name2;
		String name=nupp.getLabel();
		int trellid,i,j;

		for(i=0;i<jada.length;i++)                     	    	
		{                                                  	    	                                           	    	
			name2=((menuButton)jada[i]).getLabel();
			if(name2.compareTo(name)==0)
			{
				/*if(name2.compareTo("Status")==0)
				{
					status.jutt.append("\nMind ei saa sulgeda.\n");
					edasi=false;
					break;
				}
				else
				*/
				{
					trellid=name2.indexOf("#");
					if(trellid!=-1)
					{
						for(j=0;j<channels.length;j++)                              
						{                                                                                                 
		    				if(channels[j]!=null)                                                                         				
		    				{                                                                                             				
		    					if(channels[j].name.compareTo(name2)==0)                                                   				
		    					{   
									if(channels[j].nupp.log.getState())
									{//nõutakse logimist
										if(!mail)
										{
											mail=true;
											aken.show();
										}			
										toserver.println(channels[j].kestvus+date());//subject
										toserver.println(aadress);
										toserver.println(channels[j].jutt.getText());
										toserver.println(".");
									}

									toserver.println("PART "+name2);
									windows.remove(channels[j].win);
									channels[j].desk.dispose();
									if(!channels[j].nupp.getForeground().equals(Color.blue))
									{//suleti mitte aktiivne aken
										edasi=false;
									}
									((Panel)peaaken.getComponent(0)).remove(channels[j].nupp);
									peaaken.remove(channels[j]);
		    						//channels[j].name="";
									break;                                                                                				
		    					}                                                                                         				
		    				}                                                               				
						}//for           
					}
					else
					{
						for(j=0;j<privad.length;j++)                              
						{                                                                                                 
		    				if(privad[j]!=null)                                                                         				
		    				{                                                                                             				
		    					if(privad[j].name.compareTo(name2)==0)                                                   				
		    					{                                                                                         				
									if(privad[j].nupp.log.getState())
									{//nõutakse logimist
										if(!mail)
										{
											mail=true;
											aken.show();
										}			
										toserver.println(privad[j].kestvus+date());//subject
										toserver.println(aadress);
										toserver.println(privad[j].jutt.getText());
										toserver.println(".");
									}
									windows.remove(privad[j].win);
									privad[j].desk.dispose();
									if(!privad[j].nupp.getForeground().equals(Color.blue))
									{//suleti mitte aktiivne aken
										edasi=false;
									}
									((Panel)peaaken.getComponent(0)).remove(privad[j].nupp);
									peaaken.remove(privad[j]);
		    						privad[j].name="";
		    						break;                                                                                				
		    					}                                                                                         				
		    				}                                                                				
						}//for      
					}//else
				}//else
				break;
			}//if                                            	    	
		}//for 
		if(edasi)
		{//suleti aktiivne aken
			status.setVisible(true);
			status.nupp.setForeground(Color.blue);
			((Panel)peaaken.getComponent(0)).removeNotify();
			((Panel)peaaken.getComponent(0)).addNotify();
			peaaken.show();
		}
	}
}



class desktopKuular implements ItemListener{
	channel kanal;
	priva chat,status;
	channel[] channels;
	priva[] privad;
	int kumb;
	Frame peaaken;

	desktopKuular(int kum,channel chan,priva pri,channel[] chas,priva[] privs,priva stat,Frame pea){
		kanal=chan;			channels=chas;			
		kumb=kum;			privad=privs;
		chat=pri;			status=stat;
		peaaken=pea;
	}

	public void itemStateChanged(ItemEvent event){
		if(kumb==0)
		{//menyys
			if(kanal!=null)
			{
				kanal.nupp.desktop.setState(kanal.desktop.getState());
			}
			else
			{
				chat.nupp.desktop.setState(chat.desktop.getState());
			}
		}
		else
		{//nupust
			if(kanal!=null)
			{
				kanal.desktop.setState(kanal.nupp.desktop.getState());
			}
			else
			{
				chat.desktop.setState(chat.nupp.desktop.getState());
			}
		}

		Component[] jada=((Panel)peaaken.getComponent(0)).getComponents(); 
		
		String name2;
		int trellid,i,j;

		for(i=0;i<jada.length;i++)                     	    	
		{                                                  	    	
			if(jada[i].getForeground().equals(Color.blue)) 	    	
			{                                              	    	
				name2=((menuButton)jada[i]).getLabel();
				
				if(name2.compareTo("Status")==0)                                                
				{                                                                               	
					status.setVisible(false);                                                   	
				}                                                                               	
				else                                                                            	
				{                                                                               	
					trellid=name2.indexOf("#");                                                 	
					if(trellid!=-1)                                                             	
					{                                                                           	
						for(j=0;j<channels.length;j++)                                          	
						{                                                                       	                          
		    				if(channels[j]!=null)                                               	                          				
		    				{                                                                   	                          				
		    					if(channels[j].name.compareTo(name2)==0)                        	                           				
		    					{                                                               	                          				
									channels[j].setVisible(false);                              	
		    						break;                                                      	                          				
		    					}                                                               	                          				
		    				}                                                               					
						}//for                                                                  	
					}                                                                           	
					else                                                                        	
					{                                                                           	
						for(j=0;j<privad.length;j++)                                            	
						{                                                                       	                          
		    				if(privad[j]!=null)                                                 	                        				
		    				{                                                                   	                          				
		    					if(privad[j].name.compareTo(name2)==0)                          	                         				
		    					{                                                               	                          				
									privad[j].setVisible(false);                                	
		    						break;                                                      	                          				
		    					}                                                               	                          				
		    				}                                                                					
						}//for                                                                  	
					}//else                                                                     	
				}//else                                                                         	
				                                                                                	
				jada[i].setForeground(Color.black);  
				break;  
			}//blue                           	    			                                              	    	
		}//for 
		
		if(kanal!=null)
		{//kanal			
			if(kanal.desktop.getState())
			{//pandi desktopile
				peaaken.remove(kanal);
				kanal.desk.add(kanal);
				kanal.setVisible(true);
				kanal.desk.setSize(Toolkit.getDefaultToolkit().getScreenSize().width-200, Toolkit.getDefaultToolkit().getScreenSize().height-250);
				kanal.desk.setLocation(130,130);
				kanal.nupp.setForeground(Color.blue);
				kanal.desk.show();
				kanal.in.requestFocus();
			}
			else
			{//võeti desktopilt
				//kanal.desk.dispose();
				kanal.desk.setVisible(false);
				kanal.desk.removeAll();
				kanal.setSize(peaaken.getSize().width-7,peaaken.getSize().height-108); 
				peaaken.add(kanal,-1);
				kanal.setVisible(true);
				kanal.nupp.setForeground(Color.blue);
				kanal.in.requestFocus();
				peaaken.show();
			}
		}
		else
		{//priva
			if(chat.desktop.getState())
			{//pandi desktopile
				peaaken.remove(chat);
				chat.desk.add(chat,-1);
				chat.desk.setSize(Toolkit.getDefaultToolkit().getScreenSize().width-200, Toolkit.getDefaultToolkit().getScreenSize().height-250);
				chat.desk.setLocation(70,70);
				chat.setVisible(true);
				chat.desk.show();
				chat.nupp.setForeground(Color.blue);
				chat.in.requestFocus();
			}
			else
			{//võeti desktopilt
				//chat.desk.dispose();
				chat.desk.setVisible(false);
				chat.desk.removeAll();
				chat.setSize(peaaken.getSize().width-7,peaaken.getSize().height-108); 
				peaaken.add(chat,-1);
				chat.setVisible(true);
				//System.out.println("Elemente="+peaaken.getComponentCount()+"  chat="+chat);
				chat.nupp.setForeground(Color.blue);
				chat.in.requestFocus();
				peaaken.show();
			}
		}
		peaaken.getComponent(0).removeNotify();			
		peaaken.getComponent(0).addNotify();
	}
}



class piiksKuular implements ItemListener{
	channel kanal;
	priva chat;
	int kumb,kumb2;

	piiksKuular(int kum,int kum2,channel chas,priva stat){
		kanal=chas;
		kumb=kum;			
		kumb2=kum2;
		chat=stat;
	}

	public void itemStateChanged(ItemEvent event){
		
		if(kumb==0)
		{//pandi signaal peale
			if(kumb2==0)
			{//menyys
				if(kanal!=null)
				{
					kanal.nupp.beep.setState(kanal.beep.getState());
				}
				else
				{
					chat.nupp.beep.setState(chat.beep.getState());
				}
			}
			else
			{//nupust
				if(kanal!=null)
				{
					kanal.beep.setState(kanal.nupp.beep.getState());
				}
				else
				{
					chat.beep.setState(chat.nupp.beep.getState());
				}
			}
		}
		else
		if(kumb==1)
		{//pandi vilkumine peale
			if(kumb2==0)
			{//menyys
				if(kanal!=null)
				{
					kanal.nupp.flash.setState(kanal.flash.getState());
				}
				else
				{
					chat.nupp.flash.setState(chat.flash.getState());
				}
			}
			else
			{//nupust
				if(kanal!=null)
				{
					kanal.flash.setState(kanal.nupp.flash.getState());
				}
				else
				{
					chat.flash.setState(chat.nupp.flash.getState());
				}
			}
		}
		else
		if(kumb==2)
		{//pandi timer peale
			if(kumb2==0)
			{//menyys
				if(kanal!=null)
				{
					kanal.nupp.timer.setState(kanal.timer.getState());
				}
				else
				{
					chat.nupp.timer.setState(chat.timer.getState());
				}
			}
			else
			{//nupust
				if(kanal!=null)
				{
					kanal.timer.setState(kanal.nupp.timer.getState());
				}
				else
				{
					chat.timer.setState(chat.nupp.timer.getState());
				}
			}
		}
		else
		if(kumb==3)
		{//pandi log peale
			if(kumb2==0)
			{//menyys
				if(kanal!=null)
				{
					kanal.nupp.log.setState(kanal.log.getState());
				}
				else
				{
					chat.nupp.log.setState(chat.log.getState());
				}
			}
			else
			{//nupust
				if(kanal!=null)
				{
					kanal.log.setState(kanal.nupp.log.getState());
				}
				else
				{
					chat.log.setState(chat.nupp.log.getState());
				}
			}
		}
	}
}



class nuppKuular implements ActionListener
{ 
	menuButton nupp;
	priva status;
	channel[] channels;
	priva[] privad;
	Frame peaaken;
 
	nuppKuular(menuButton nup,priva stat,channel[] chas,priva[] privs,Frame pea) 
	{                                                           	
		nupp=nup;
		status=stat;
		channels=chas;
		privad=privs;
		peaaken=pea;
	}                                                           	
	                                                            
	public void actionPerformed(ActionEvent event)              	
	{	                                                   	    	
		Component[] jada=((Panel)peaaken.getComponent(0)).getComponents(); 
		
		boolean edasi=true;
		String name2;
		String name=nupp.getLabel();
		int trellid,i,j;

		for(i=0;i<jada.length;i++)                     	    	
		{                                                  	    	
			if(jada[i].getForeground().equals(Color.blue)) 	    	
			{                                              	    	
				name2=((menuButton)jada[i]).getLabel();
				if(name2.compareTo(name)==0)
				{//vajutati juba aktiivse akna nuppu
					edasi=false;	
				}
				else
				{
					if(name2.compareTo("Status")==0)
					{
						status.setVisible(false);
					}
					else
					{
						trellid=name2.indexOf("#");
						if(trellid!=-1)
						{
							for(j=0;j<channels.length;j++)                              
							{                                                                                                 
		    					if(channels[j]!=null)                                                                         				
		    					{                                                                                             				
		    						if(channels[j].name.compareTo(name2)==0)                                                   				
		    						{                                                                                         				
										if(!channels[j].desktop.getState())
										{
											channels[j].setVisible(false);
		    							}
										break;                                                                                				
		    						}                                                                                         				
		    					}                                                               				
							}//for           
						}
						else
						{
							for(j=0;j<privad.length;j++)                              
							{                                                                                                 
		    					if(privad[j]!=null)                                                                         				
		    					{                                                                                             				
		    						if(privad[j].name.compareTo(name2)==0)                                                   				
		    						{                                                                                         				
										if(!privad[j].desktop.getState())
										{
											privad[j].setVisible(false);
		    							}
										privad[j].setVisible(false);
		    							break;                                                                                				
		    						}                                                                                         				
		    					}                                                                				
							}//for      
						}//else
					}//else
					
					jada[i].setForeground(Color.black);
				}
				break;                                     	    			
			}                                              	    	
		} 
		
		if(nupp.desktop.getState())
		{
			edasi=true;
		}

		if(edasi)
		{			
			if(name.compareTo("Status")==0)                                
			{                                                           
				nupp.setForeground(Color.blue);
				if(!nupp.desktop.getState())
				{
					status.setVisible(true);
					status.setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                                    	    
		  			peaaken.show();
				}
				else
				{
					status.desk.show();
				}
				status.in.requestFocus();
			}                                                           
			else                                                        
			for(i=0;i<channels.length;i++)                              
		    {                                                                                                 
		    	if(channels[i]!=null)                                                                         				
		    	{                                                                                             				
		    		if(channels[i].name.compareTo(name)==0)                                                   				
		    		{                                                                                         				
						nupp.setForeground(Color.blue);
		   				if(!nupp.desktop.getState())
						{
							channels[i].setVisible(true);
							channels[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                                  	
		    				peaaken.show();
						}
						else
						{
							channels[i].desk.show();
						}                      
						edasi=false; 
						channels[i].in.requestFocus();
		    			break;                                                                                				
		    		}                                                                                         				
		    	}//kanali objekt ei olnud null                                                                				
		    }//for                                                                                            				
			if(edasi)
			{
				for(i=0;i<privad.length;i++)                              
		    {                                                                                                 
		    	if(privad[i]!=null)                                                                         				
		    	{                                                                                             				
		    		if(privad[i].name.compareTo(name)==0)                                                   				
		    		{					
						nupp.setForeground(Color.blue);
						if(!nupp.desktop.getState())
						{
							privad[i].setVisible(true);
							if(!privad[i].isValid())
							{//uus priva ei pruugi aknale liidetud olla
								peaaken.add(privad[i],-1);
							}
							privad[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                                   	
		    				peaaken.show();
						}
						else
						{
							privad[i].desk.show();
						}
						edasi=false; 
						privad[i].in.requestFocus();
		    			break;                                                                                				
		    		}                                                                                         				
		    	}//kanali objekt ei olnud null                                                                				
		    }//for                       
			}
		}
		peaaken.getComponent(0).removeNotify();
		peaaken.getComponent(0).addNotify();
	}                                                      	    	
}



class KatkestaKuular implements ActionListener{	//vajutati "Katkesta"
	Dialog raam;

    KatkestaKuular(Dialog raaam){
        raam=raaam;			
	}

   public void actionPerformed(ActionEvent event){
	raam.dispose();
	System.gc();
      }
}



class privaKuular implements ActionListener{
	static PrintStream toserver;
	Dialog raam;
	Frame peaaken;
	static channel[] channels;
	static priva[] privad;
	static priva status;
	Color back;
	Menu windows;
	Applet boss;


   privaKuular(PrintStream to,Frame pea,channel[] chas,Color bac,priva[] privs,priva stat,Menu wins,Applet bos){
		toserver=to;		
		channels=chas;			
		peaaken=pea;
		back=bac;
		privad=privs;
		status=stat;
		windows=wins;
		boss=bos;
	   }

	public void actionPerformed(ActionEvent event){
		if(!status.connect)
		{//ei ole konnectitud
			status.append("\nSa ei ole IRCu logitud...\n",Color.red,status.jutt,status.buffer);
			if(status.nupp.getForeground().equals(Color.black))
			{
				status.nupp.setForeground(Color.red);
			}
			peaaken.getComponent(0).removeNotify();
			peaaken.getComponent(0).addNotify();
		}
		else{
			raam = new Dialog(peaaken,"Join",true);                                                                                                       
			raam.setResizable(false);                                                                                                                     
			raam.addWindowListener(new AknaKuular(raam));                                                                                                 
			                                                                                                                                              
			Label label=new Label("  Sisesta isiku nimi, kellega soovid priva alustada.  ");  
			label.setBackground(back);
			Panel paneel=new Panel();
			TextField kanal=new TextField(10);
			paneel.setBackground(back);                                                                                                                           
			paneel.add(kanal);                                                                                                                            
			Panel nupud=new Panel();                                                                                                                      
			nupud.setBackground(back);                                                                                                                    
			Button ok=new Button("  OK  ");                                                                                                               
			Button cancel=new Button("Katkesta");                                                                                                         
			nupud.add(ok);                                                                                                                                
			nupud.add(cancel);
			ok.setBackground(status.nupp.getBackground());
			cancel.setBackground(status.nupp.getBackground());
			raam.setLayout(new BorderLayout());
			raam.add(label,"North");
			raam.add(paneel,"Center");
			raam.add(nupud,"South");
			
			cancel.addActionListener(new KatkestaKuular(raam));
			ok.addActionListener(new privaOKKuular(toserver,peaaken,channels,privad,status,kanal,raam,windows,null,boss,back));
			kanal.addActionListener(new privaOKKuular(toserver,peaaken,channels,privad,status,kanal,raam,windows,null,boss,back));
			raam.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-150,peaaken.getLocation().y+peaaken.getSize().height/2-70);                                          		
			raam.setSize(300,150);                                                                                                                                                                                                                                                               	                                                                                                                                              
			raam.show();
		}//IRCu logitud
	}
}



class privaOKKuular implements ActionListener
{
	static PrintStream toserver;
	static TextField kanal;
	static Dialog raam;
	static Frame peaaken;
	static channel[] channels;
	static priva[] privad;
	static priva status;
	Menu windows;
	List list;
	Applet boss;
	Color back;

	privaOKKuular(PrintStream to,Frame pea,channel[] chas,priva[] privs,priva stat,TextField kan,Dialog ram,Menu wins,List liist,Applet bos,Color bac)
	{
		toserver=to;		
		channels=chas;			
		peaaken=pea;
		kanal=kan;
		privad=privs;
		status=stat;
		raam=ram;
		windows=wins;
		list=liist;
		boss=bos;
		back=bac;
	}

	public void actionPerformed(ActionEvent event)
	{
		int i,j,trellid;                                                                		                                   
		String nickk;
		try
		{
			if(kanal!=null)
			{
				nickk=kanal.getText();                                                      		                                               
			}
			else
			{
				nickk=list.getSelectedItem();
				if((nickk.indexOf("@")==0)||(nickk.indexOf("+")==0))
				{
					nickk=nickk.substring(1);
				}
			}
System.out.println("(OKkuularis) Nick="+nickk);			
			if(nickk.indexOf("#")==-1)
			{
				//KAOTAN VANA AKNA                                                                 			                                        
			    Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();               		    		                                                                          		
			                                                                                     					                                      
			    boolean edasi=true;                                                              					                                      
			    String name2;                                                                    					                                                                                           		                                                                            
			                                                                                   					                                      
			    for(i=0;i<jada.length;i++)                     	    	                         					                                      
			    {                                                  	    	                     					                                      
			    	if(jada[i].getForeground().equals(Color.blue)) 	    	                     					                                      
			    	{                                              	    	                     					                                      
			    		name2=((menuButton)jada[i]).getLabel();                                  					                                      
			    		                                                                         					                                      
			    		if(name2.compareTo("Status")==0)                                         					                                      
			    		{                                                                	     					                                      
			    			status.setVisible(false);                                    	     					                                      
			    		}                                                                	     					                                      
			    		else                                                             	     					                                      
			    		{                                                                	     					                                      
			    			trellid=name2.indexOf("#");                                  	     					                                      
			    			if(trellid!=-1)                                              	     					                                      
			    			{                                                            	     					                                      
			    				for(j=0;j<channels.length;j++)                           	     					                                      
			    				{                                                        	     					                                      
			        				if(channels[j]!=null)                                	           	                                                
				    				{                                                    	           	                              			       
				    					if(channels[j].name.compareTo(name2)==0)         	           	                              			       
				    					{                                                	           	                               			       
											channels[j].setVisible(false);               	           	                              			       
			        						break;                                       	     					                                      
				    					}                                                	           	                              			       
				    				}                                                    	           	                	         			           
			    				}//for                                                   	     					                                      
			    			}                                                            	     					                                      
			    			else                                                         	     					                                      
			    			{                                                            	     					                                      
			    				for(j=0;j<privad.length;j++)                             	     					                                      
			    				{                                                        	     					                                      
			        				if(privad[j]!=null)                                  	           	                                                
				    				{                                                    	           	                            			           
				    					if(privad[j].name.compareTo(name2)==0)           	           	                              			       
				    					{                                                	           	                             			       
											privad[j].setVisible(false);                 	           	                              			       
			        						break;                                       	     					                                      
				    					}                                                	           	                              			       
				    				}                                                    	           	                 	         			       
			    				}//for                                                   	     					                                      
			    			}//else                                                              					                                      
			    		}//else                                                                  					                                      
			    	jada[i].setForeground(Color.black);                                          					                                      
			    	//jada[i].repaint();                                                         					                                      
			    	break;                                                           					    			                                                                                                            	    			                         
			    	}//if blue                                              	    	         					                                      
			    }//for                                            		                         					                                      
			                                                                                     					                                      
			    for(i=0;i<privad.length;i++)                                          		     					                                      
			    {//kontrollin, et juba ei oleks                                                  					 		                               
			    	if(privad[i]!=null)                                               		     					                                      
			    	{                                                                 		     					                                      
			    		if(privad[i].name.compareTo(nickk)==0)                                   					                                      
			    		{//selline priva juba oli                                                					                                      
							privad[i].nupp.setForeground(Color.blue);                              			                                  
			    			privad[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                              	                                                            
			    			privad[i].setVisible(true);                                          		    		                                                                                                                
			    			edasi=false;                                                         					                                      
			    		}                                                      		             					                                      
			    	}                                                                 		     		    		                                           		
			    }			                                                                     					                                      
System.out.println("Otsin tyhja koha privajaoks edasi="+edasi);			                                                                                       		                                                
			    if(edasi)                                                                        					                                      
			    {                                                                                					                                      
			    	for(i=0;i<privad.length;i++)                                          		 					                                      
			        {//otsin tyhja koha         
			System.out.println("i="+i+"    priva["+i+"]="+privad[i]);
			if(privad[i]!=null)
			{
				System.out.println("nimi="+privad[i].name);
			}
			        	if(privad[i]==null)                                           		     					                                      
			        	{                                                             		     					                                      
			        		break;                                                    		     					                                      
			        	}                                                             		     					                                      
			        	else                                                          		     					                                      
			        	if(privad[i].name.compareTo("")==0)                           		     					                                      
			        	{                                                             		     					                                      
			        		break;                                                    		     					                                      
			        	}                                                             		     					                                      
			        }                                                                 		     					                                      
System.out.println("!Leidsin tyhja koha i="+i+"  boss="+boss+"  nickk="+nickk+"    privad["+i+"]="+privad[i]);			                                                                          		     					                                      
			    	priva fukka=new priva(nickk,boss);
System.out.println("Lõin uue objekti");
					privad[i]=fukka;
					//privad[i]=new priva(nickk,boss);   
System.out.println("omistasin");
System.out.println("Status.nick"+status.nick);
System.out.println("font="+status.font);
System.out.println("text="+status.textColor);
System.out.println("nupp="+status.nupp);
System.out.println("peaaken="+peaaken);

			    	privad[i].nick=status.nick;
					privad[i].font=status.font;
					privad[i].textColor=status.textColor;
					privad[i].buffer=new TextBuffer();
			        privad[i].append("*** Alustasid priva isikuga "+nickk+"\n",new Color(0,170,0),privad[i].jutt,privad[i].buffer);            		    		                                             
			    	                                                                             					                                      
			    	privad[i].nupp.setBackground(status.nupp.getBackground());
					privad[i].nupp.setForeground(Color.blue);                                    					                                      
			    	 ((Panel)peaaken.getComponent(0)).add(privad[i].nupp);                       					                                      
			    	privad[i].in.addActionListener(new inKuular(privad[i],null,status,channels,toserver,peaaken,privad,windows,boss,back));                                                                    
					privad[i].nupp.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));                          
			    	privad[i].nupp.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
					privad[i].close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
					privad[i].choose.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));	                         		                                                                   	                                                
			    	privad[i].jutt.setBackground(status.jutt.getBackground());
					//privad[i].jutt.setForeground(status.jutt.getForeground());
					//privad[i].jutt.setFont(status.jutt.getFont());
					privad[i].in.setBackground(status.in.getBackground());
					privad[i].in.setForeground(status.in.getForeground());
					privad[i].in.setFont(status.in.getFont());
System.out.println("Lisatud peaaknale");
					peaaken.add(privad[i],-1);  
					privad[i].beep.addItemListener(new piiksKuular(0,0,null,privad[i]));
			    	privad[i].nupp.beep.addItemListener(new piiksKuular(0,1,null,privad[i]));
					privad[i].flash.addItemListener(new piiksKuular(1,0,null,privad[i]));
					privad[i].nupp.flash.addItemListener(new piiksKuular(1,1,null,privad[i]));
					privad[i].timer.addItemListener(new piiksKuular(2,0,null,privad[i]));
					privad[i].nupp.timer.addItemListener(new piiksKuular(2,1,null,privad[i]));
					privad[i].log.addItemListener(new piiksKuular(3,0,null,privad[i]));
					privad[i].nupp.log.addItemListener(new piiksKuular(3,1,null,privad[i]));
					privad[i].desktop.addItemListener(new desktopKuular(0,null,privad[i],channels,privad,status,peaaken));
					privad[i].nupp.desktop.addItemListener(new desktopKuular(1,null,privad[i],channels,privad,status,peaaken));
					privad[i].desk.addWindowListener(new kanaliKuular(peaaken,privad[i],null,status,toserver,windows,back));
					windows.add(privad[i].win);                                     					                                      	                                               		                         					                                                                                   		                                          
			    }//edasi                                                                         					                                      
			    //Netscape 4.7 ei toeta repainti()                                                                                        		                                                                                                                                                                       
			  	peaaken.getComponent(0).doLayout();//kuvab lisatud nupud                           			                                  
				peaaken.getComponent(0).removeNotify();		  
				peaaken.getComponent(0).addNotify();//repaindi eest                                			                                  
				peaaken.show();                                                                                                               
			}
			else
			{
				status.append("\n***Hüüdnimi sisaldas lubamatuid märke.\n",Color.red,status.jutt,status.buffer);
				if(status.nupp.getForeground().equals(Color.black))
				{
					status.nupp.setForeground(Color.red);
					peaaken.getComponent(0).removeNotify();
					peaaken.getComponent(0).addNotify();
				}	
			}
			raam.dispose();
		}
		catch(NullPointerException e)
		{//kui listis midagi ei valitud
		}
	}
}



class joinOKKuular implements ActionListener
{
	static PrintStream toserver;
	static TextField kanal;
	static Dialog raam;
	static Frame peaaken;
	static channel[] channels;
	static priva[] privad;
	static priva status;

	joinOKKuular(PrintStream to,Frame pea,channel[] chas,priva[] privs,priva stat,TextField kan,Dialog ram)
	{
		toserver=to;		
		channels=chas;			
		peaaken=pea;
		kanal=kan;
		privad=privs;
		status=stat;
		raam=ram;
	}

	public void actionPerformed(ActionEvent event)
	{
		int trellid,i,j;                                                                                                                      
		boolean edasi=true;                                                                                                                   
		String name2="#"+kanal.getText();//kanal, millega yhinetakse                   		                         			                            
																																						
		for(i=0;i<channels.length;i++)                                                 		      	              			                            
		{//otsin vaba koha                                                             		      	              			                            
			if(channels[i]!=null)                                                      		      	              			                            
			{                                                                          		      	              			                            
				if(channels[i].name.compareTo(name2)==0)                               		         	          			                            
				{//sellise kanaliga oldi juba yhinetud                                 		                      			                     	      
					edasi=false;                                                       		                      			                            
					break;                                                             		                      			                            
				}                                                                      		      	              			                            
			}//kanali objekt ei olnud null                                             		      	                  		                                                       	
		}//for  										                               		                      			                            
		                                                                                                          			                            
		if(!edasi)                                                                     		                      			                            
		{                                                                              		                      			                            
			Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();         		                      			                            
			                                                                           		                      			                            
		    for(i=0;i<jada.length;i++)                     	    	                   		                          		                                                                                       
		    {                                                  	    	               		                          		                                                                                       
		    	if(jada[i].getForeground().equals(Color.blue)) 	    	               		                          		                                                                                       
		    	{                                              	    	               		                          		                                                                                       
		    		name2=((menuButton)jada[i]).getLabel();                            		                          		                                                                                       
		    		                                                                   		                          		                                                                                       
		    		if(name2.compareTo("Status")==0)                                   		                          		                                                                                       
		    		{                                                                  		                          		                                                                                       
		    			status.setVisible(false);                                      		                          		                                                                                       
		    		}                                                                  		                          		                                                                                       
		    		else                                                               		                          		                                                                                       
		    		{                                                                  		                          		                                                                                       
		    			trellid=name2.indexOf("#");                                    		                          		                                                                                       
		    			if(trellid!=-1)                                                		                          		                                                                                       
		    			{                                                              		                          		                                                                                       
		    				for(j=0;j<channels.length;j++)                             		                          		                                                                                       
		    				{                                                          		                          		                                                                                       
		        				if(channels[j]!=null)                                  		                          		             				                                                            
		        				{                                                      		                          		             				                                                            
		        					if(channels[j].name.compareTo(name2)==0)           		                      			              			                                                                
		        					{                                                  		                          		             				                                                            
		    							if(name2.compareTo("#"+kanal.getText())!=0)		                          			                            
										{//võin olla juba samas aknas                  		                      			                            
											channels[j].setVisible(false);             		                          		                                                                                           
		        						}                                              		                      			                            
										break;                                         		                          		             				                                                            
		        					}                                                  		                          		             				                                                            
		        				}                                                      		         			    			                                                                                    
		    				}//for                                                     		                          		                                                                                       
		    			}//trellid                                                     		                          		                                                                                                
		    			else                                                           		                          		                                                                                       
		    			{                                                              		                          		                                                                                       
		    				for(j=0;j<privad.length;j++)                               		                          		                                                                                       
		    				{                                                          		                          		                                                                                       
		        				if(privad[j]!=null)                                    		                          		           				                                                            
		        				{                                                      		                          		             				                                                            
		        					if(privad[j].name.compareTo(name2)==0)             		                          		            				                                                            
		        					{                                                  		                          		             				                                                            
		    							privad[j].setVisible(false);                   		                          		                                                                                       
		        						break;                                         		                          		             				                                                            
		        					}                                                  		                          		             				                                                            
		        				}                                                      		          			    			                                                                                    
		    				}//for                                                     		                          		                                                                                       
		    			}//else                                                        		                          		                                                                                       
		    		}//else                                                            		                          		                                                                                       
		    			                                                               		                          		                                                                                       
		    		jada[i].setForeground(Color.black);                                		                          		                                                                                       
		    		break;                                                             		                      			                            
				}//if blue                                                             		                          		                                                                                                                                 	    			                                                                                                                            
		    }//for                                              	    	           		                          		                                                                                               
		                                                                               		                          		                                                                                       
		    name2="#"+kanal.getText();//kanal, millega yhinetakse                      		                          		                                                                                   
		                                                                               		                          		                                                                                       
		    for(i=0;i<channels.length;i++)                                             		                          		                                                                                       
		    {                                                                          		                          		                                                                                       
			   	if(channels[i]!=null)                                                  		                          		 			                                                                            
			   	{                                                                      		                          		 			                                                                            
			   		if(channels[i].name.compareTo(name2)==0)                           		                          		  			                                                                            
			   		{                                                                  		                          		 			                                                                            
						channels[i].nupp.setForeground(Color.blue);                    		                          		                                                                                       
	  	    			channels[i].setVisible(true);                                  		                          		                                                                                       
		    			channels[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                  		                	                                                                                                 		        			      		                                                                                          
	    				break;                                                         		                          		 			                                                                            
	    			}                                                                  		                          		 			                                                                            
	    		}//kanali objekt ei olnud null                                         		                          		 			                                                                            
	    	}//for                                                                     		                          		 			                                                                            
		    
			peaaken.getComponent(0).removeNotify();																																																																																																 
		    peaaken.getComponent(0).addNotify();                                       		                          		                                                                                       
		    peaaken.show();                                               	           		                          		                                                                                       
		}                                                                              		                      			                            
		else                                                                           		                      			                            
		{//ei olnud juba olemas                                                        		                      			                            
			toserver.println("JOIN "+name2);             		                       		                      			                            
		}                                                                              		                      			                            	                                                                                                          			                            
		raam.dispose();                                                                                           			                                                                                                              			
	}	                                                                                                          
}



class joinKuular implements ActionListener{
	static PrintStream toserver;
	Dialog raam;
	static Frame peaaken;
	static channel[] channels;
	static priva[] privad;
	static priva status;
	Color back;


   joinKuular(PrintStream to,Frame pea,channel[] chas,Color bac,priva[] privs,priva stat){
		toserver=to;		
		channels=chas;			
		peaaken=pea;
		back=bac;
		privad=privs;
		status=stat;
	   }

	public void actionPerformed(ActionEvent event){
		if(!status.connect)
		{//ei ole konnectitud
			status.append("\nSa ei ole IRCu logitud...\n",Color.red,status.jutt,status.buffer);
			if(status.nupp.getForeground().equals(Color.black))
			{
				status.nupp.setForeground(Color.red);
			}
			peaaken.getComponent(0).removeNotify();
			peaaken.getComponent(0).addNotify();
		}
		else{
			raam = new Dialog(peaaken,"Join",true);                                                                                                       
			raam.setResizable(false);                                                                                                                     
			raam.addWindowListener(new AknaKuular(raam));                                                                                                 
			                                                                                                                                              
			Label label=new Label("  Sisesta kanali nimi, millega soovid ühineda.  ");  
			label.setBackground(back);
			Label label2=new Label("#");                                                                                                                  
			Panel paneel=new Panel();  
			TextField kanal=new TextField(20);
			paneel.setBackground(back);                                                                                                                   
			paneel.add(label2);                                                                                                                           
			paneel.add(kanal);                                                                                                                            
			Panel nupud=new Panel();                                                                                                                      
			nupud.setBackground(back);                                                                                                                    
			Button ok=new Button("  OK  ");                                                                                                               
			Button cancel=new Button("Katkesta");
			ok.setBackground(status.nupp.getBackground());
			cancel.setBackground(status.nupp.getBackground());
			nupud.add(ok);                                                                                                                                
			nupud.add(cancel);
			raam.setLayout(new BorderLayout());
			raam.add(label,"North");
			raam.add(paneel,"Center");
			raam.add(nupud,"South");
			
			cancel.addActionListener(new KatkestaKuular(raam));
			ok.addActionListener(new joinOKKuular(toserver,peaaken,channels,privad,status,kanal,raam));
			kanal.addActionListener(new joinOKKuular(toserver,peaaken,channels,privad,status,kanal,raam));
			raam.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-150,peaaken.getLocation().y+peaaken.getSize().height/2-70);                                          		
			raam.setSize(280,150);                                                                                                                        	                                                                                                                                                                                                                                                                                                                                                                                                        
			raam.show();
		}//IRCu logitud
	}
}



class kes_on implements ActionListener{
	PrintStream toserver;
	TextField nick,reason;
	static Dialog raam;
	int kumb;
	String kanal;

	kes_on(PrintStream to,TextField n,Dialog r,int kumbb,String kanaal,TextField rsn){
		toserver=to;
		nick=n;
		raam=r;
		kumb=kumbb;
		kanal=kanaal;
		reason=rsn;
	}

	public void actionPerformed(ActionEvent event){
		String nimi=nick.getText();
		if((nimi.length()>3)&&(kumb==1))
			toserver.println("WHOIS "+nimi);
		if(kumb==0)
			toserver.println("IGNR "+nimi);
		if(kumb==2)
			toserver.println("UNIG "+nimi);
		if(kumb==3)
			toserver.println("KICK "+kanal+" "+nimi+" :"+reason.getText());
		if(kumb==4){
			toserver.println("MODE "+kanal+" +b "+nimi);
			toserver.println("KICK "+kanal+" "+nimi+" :"+reason.getText());
		}
		if(kumb==5)
			toserver.println("MODE "+kanal+" -b "+nimi);
		if(kumb==6)
			toserver.println("TOPIC "+kanal+" :"+nimi);
		if(kumb==7)
			toserver.println("NICK "+nimi);
		raam.dispose();
	}
}



class whoKuular1 implements ActionListener{
	PrintStream toserver;
	int kumb;
	Frame peaaken;
	channel[] channels;
	Color back,buttonback;
	Dialog raam;


   whoKuular1(PrintStream to,int kumbb,Frame pea,channel[] chas,Color bac,Color buttonbac){
		toserver=to;		
		kumb=kumbb;
		channels=chas;			
		peaaken=pea;
		back=bac;
		buttonback=buttonbac;
	   }

	public void actionPerformed(ActionEvent event){

		String kanal="";
		for(int j=0;j<channels.length;j++)                                       	                                 	
		{                                                                                                         	
			if(channels[j]!=null)                                                 			                            				              	
			{                                                                     			                            				              	
				if(channels[j].nupp.getForeground().equals(Color.blue))                          			                             				              	
				{                                                                 			                            				              	              
					kanal=channels[j].name;
	
					break;                                                        			                            				              	
				}                                                                 			                            				              	
			}                                             					    			                  		                              	
		}//for                                            									
	
		String label="Kes on...";
		if(kumb==0) label="Ignoreeri";
		if(kumb==2) label="Aktsepteeri";
		if(kumb==3) label="Viska välja";
		if(kumb==4) label="Keela tuba";
		if(kumb==5) label="Luba tuba";
		if(kumb==6) label="Teema";
		if(kumb==7) label="Nimi";
		raam = new Dialog(peaaken,label,true);
		raam.setResizable(false);
		raam.addWindowListener(new AknaKuular(raam));
		
		TextField reason=new TextField(50);
		Label label2;
		Panel paneel=new Panel();
		paneel.setBackground(back);

		Label kiri=new Label("Sisesta hüüdnimi:",1);
		TextField nick=new TextField(10);
		if(kumb==6)
		{
			kiri.setText("Kirjuta teema:");
			nick=new TextField(50);
			raam.setSize(250,150);
		}
		if(kumb==7) kiri.setText("Sisesta uus hüüdnimi:");
		kiri.setBackground(back);
		
		Panel nupud=new Panel();
		nupud.setBackground(back);
		Button ok=new Button("  OK  ");
		Button cancel=new Button("Katkesta");
		nupud.add(ok);
		nupud.add(cancel);
		ok.setBackground(buttonback);
		cancel.setBackground(buttonback);
		

		if((kumb==3)||(kumb==4)){
			label2=new Label("Põhjus:",1);
			paneel.setLayout(new GridLayout(3,1));
			Panel panel=new Panel();
			panel.setBackground(back);
			panel.add(nick);
			paneel.add(panel);
			paneel.add(label2);
			paneel.add(reason);
			raam.setSize(200,190);
		}
		else
		{
			paneel.add(nick);
			if(kumb!=6)
			{
				raam.setSize(200,150);
			}
		}

		raam.setLayout(new BorderLayout());
		raam.add(kiri,"North");
		raam.add(paneel,"Center");
		raam.add(nupud,"South");
			
		raam.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-100,peaaken.getLocation().y+peaaken.getSize().height/2-100);                                          		
		
		nick.addActionListener(new kes_on(toserver,nick,raam,kumb,kanal,reason));
		ok.addActionListener(new kes_on(toserver,nick,raam,kumb,kanal,reason));
		reason.addActionListener(new kes_on(toserver,nick,raam,kumb,kanal,reason));
		cancel.addActionListener(new KatkestaKuular(raam));
		raam.show();
	}
}



class whoKuular implements ActionListener{
	PrintStream toserver;
	menuList names;
	int kumb;
	channel[] channels;
	priva status;
	Frame peaaken;
	


   whoKuular(PrintStream to,int kumbb,channel[] chas,priva stat,Frame pea){
	   toserver=to;						
	   kumb=kumbb;
	   channels=chas;
	   status=stat;
	   peaaken=pea;
	   }

	public void actionPerformed(ActionEvent event){
	
		if(!status.connect)
		{
			status.append("\nSa ei ole IRCu logitud...\n",Color.red,status.jutt,status.buffer);
			if(status.nupp.getForeground().equals(Color.black))
			{
				status.nupp.setForeground(Color.red);
			}
			peaaken.getComponent(0).removeNotify();
			peaaken.getComponent(0).addNotify();
		}
		else
		{
			String kes="";
			String kanal="";
			if(status.workers!=null)
			{
				kes=status.workers.getSelectedItem();		
			}
			else
			{
				for(int i=0;i<channels.length;i++)
				{
					if(channels[i]!=null)
					{
						if(channels[i].nupp.getForeground().equals(Color.blue))
						{
							kanal=channels[i].nupp.getLabel();
							kes=channels[i].list.getSelectedItem();			
							break;
						}
					}
				}		
			}
			try{
			if(kes.substring(0,1).compareTo("@")==0) kes=kes.substring(1);
			if(kes.substring(0,1).compareTo("+")==0) kes=kes.substring(1);
			
			if(kumb==1)
				toserver.println("WHOIS "+kes);
			if(kumb==0)
				toserver.println("IGNR "+kes);
			if(kumb==2)
				toserver.println("UNIG "+kes);
			if(kumb==3)
				toserver.println("KICK "+kanal+" "+kes);
			if(kumb==4){
				toserver.println("MODE "+kanal+" +b "+kes);
				toserver.println("KICK "+kanal+" "+kes);
			}
			if(kumb==5){
				toserver.println("MODE "+kanal+" +o "+kes);
			}
			if(kumb==6){
				toserver.println("MODE "+kanal+" -o "+kes);
			}
			}
			catch(Exception e){}//võib nullPointeri vista
		}
	}
}



class inKuular implements ActionListener
{   
	TextBuffer buffer;
	Menu windows;
	String name,nick;
	Frame peaaken;
	TextField in;
	PrintStream toserver;
	TextArea jutt;
	menuButton nupp;
	priva[] privad;
	channel[] channels;
	priva chat,status;
	channel kanal;
	int mitu;	//mitu=0 - piirangud puuduvad
				//mitu=1 - 1 kanal + privad(vabalt valitavad)
				//mitu=2 - 1 konkreetne priva	
				//mitu=4 - arco	
	Color modeColor=new Color(0,170,0);	
	Color textColor,back;
	Applet boss;

	inKuular(priva se,channel se2,priva stat,channel[] chas,PrintStream to,Frame pea,priva[] privs,Menu wins,Applet bos,Color bac)
	{
		windows=wins;			status=stat;
		kanal=se2;				chat=se;
		channels=chas;			toserver=to;
		peaaken=pea;			privad=privs;
		boss=bos;				back=bac;			

		mitu=status.mitu;
	}

	public void actionPerformed(ActionEvent event)                                            
	{   		
		if(kanal!=null)
		{
			name=kanal.name;
			jutt=kanal.jutt;
			in=kanal.in;
			nupp=kanal.nupp;
			nick=kanal.nick;
			buffer=kanal.buffer;
			textColor=kanal.textColor;
		}
		else
		{
			buffer=chat.buffer;
			name=chat.name;
			jutt=chat.jutt;
			in=chat.in;
			nupp=chat.nupp;
			nick=chat.nick;
			textColor=chat.textColor;
		}

		String sain=in.getText();                                                             		
		                                                                                      		
		if(sain.length()>0){//pole mõtet tyhja ajada :)                                       		
			if(!status.connect)
			{
				Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();                                                                                             								                                                                                                                                                                                                                                   
				String name2;                                                                                                                                    	
				                                                                                                                                    		                          	
				for(int i=0;i<jada.length;i++)                     	    	                                                                            		                          	
				{                                                  	    	                                                                        		                          	
					if(jada[i].getForeground().equals(Color.blue)) 	    	                                                                        		                          	
					{                                              	    	                                                                        		                          	
						name2=((menuButton)jada[i]).getLabel();                                                                                     		                          	
						                                                                                                                            		                          	
						if(name2.compareTo("Status")==0)                                                                                            		                          	
						{                                                                	                                                        		                          	                                                                                                                                             	
							status.append("\n***Sa ei ole IRCu logitud.\n",Color.red,status.jutt,status.buffer); 
						}                                                                	                                                        		                          	
						else                                                             	                                                        		                          	
						{                                                                	                                                        		                          	
							int trellid=name2.indexOf("#");                                  	                                                        		                          	
							if(trellid!=-1)                                              	                                                        		                          	
							{                                                            	                                                        		                          	
								for(int j=0;j<channels.length;j++)                           	                                                        		                          	
								{                                                        	                                                        		                          	
		    	    				if(channels[j]!=null)                                	                                                                                              
		    	    				{                                                    	                         				                                                      
		    	    					if(channels[j].name.compareTo(name2)==0)         	                         				                                                      
		    	    					{                                                	                          				                                                                                                                                                           		                          	
											channels[j].append("\n***Sa ei ole IRCu logitud.\n",Color.red,channels[j].jutt,channels[j].buffer);
											break;                                       	                                                        		                          	
		    	    					}                                                	                         				                                                      
		    	    				}                                                    	           	         				                                                          
								}//for                                                   	                                                        		                          	
							}                                                            	                                                        		                          	
							else                                                         	                                                        		                          	
							{                                                            	                                                        		                          	
								for(int j=0;j<privad.length;j++)                             	                                                        		                          	
								{                                                        	                                                        		                          	
		    	    				if(privad[j]!=null)                                  	                                                                                              
		    	    				{                                                    	                       				                                                          
		    	    					if(privad[j].name.compareTo(name2)==0)           	                         				                                                      
		    	    					{                                                	                        				                                                                                                                                                           		                          	
											privad[j].append("\n***Sa ei ole IRCu logitud.\n",Color.red,privad[j].jutt,privad[j].buffer);	                                            		      	
				    						break;                                       	                                                        		                          	
		    	    					}                                                	                         				                                                      
		    	    				}                                                    	            	         				                                                      
								}//for                                                   	                                                        		                          	
							}//else                                                                                                                 		                          	
						}//else                                                                                                                     		                          	
					}//if blue                                                                                                                      		                          	
				}//for                                                                                                       								                                                                                                                                                                	 
			}
			else
			{
			int tyhik;                                                                        		
			String abi;                                                                       		
			String aeg="";                                                                    		
			if(nupp.timer.getState())                                                         		
			{                                                                                 		
				Calendar c=Calendar.getInstance();                                            		
				String panen2="";                                                             		
				String answer="";                                                             		
				int tund= c.get(c.HOUR_OF_DAY);                                               		
				if(tund<10) panen2="0";                                                       		
				int minut=c.get(c.MINUTE);                                                    		
				if(minut<10) answer="0";                                                      		
				aeg="["+""+panen2+""+tund+":"+""+answer+""+minut+"]";                         		
			}                                                                                 		
	        try
			{
				if(sain.substring(0,1).compareTo("/")==0)                                         		                       	                       
				{                                                                                 		                                               
					tyhik=sain.indexOf(" ");                                                      		                                               
					String command=sain.substring(1);                                             		                                               
					if(tyhik!=-1)                                                                 		                                               
					{                                                                             		                                               
						command=sain.substring(1,tyhik);                                          		                                               
					}                                                                             		                                               
        	                                                                                                                                       
					if(command.compareTo("me")==0)                                                		                                               
					{                                                                             		                                               
						if(name.compareTo("Status")==0)                                                                                                    
						{                                                     		                                                                       
							if(kanal!=null)
							{
								kanal.append("\n*** Sa ei ole kanalil\n",Color.blue,jutt,buffer);
							}
							else
							{
								chat.append("\n*** Sa ei ole kanalil\n",Color.blue,jutt,buffer);
							}
						}
						else
						{
							
							toserver.println("PRIVMSG "+name+" :ACTION "+sain.substring(4)+"");  
							if(kanal!=null)
							{
								kanal.append(aeg+"***"+nick+" "+sain.substring(4)+"\n",Color.magenta,jutt,buffer);                   		                                               
							}
							else
							{
								chat.append(aeg+"***"+nick+" "+sain.substring(4)+"\n",Color.magenta,jutt,buffer); 
							}
						}
					}                                                                             		                                               
					else                                                                          		                                               
					if((command.compareTo("join")==0)||(command.compareTo("j")==0))                                             		                                       
					{                                                                             		                                               
						if(mitu==0)
						{
							int trellid,i,j;
							boolean edasi=true;
							tyhik=sain.indexOf(" ");
							String name2=sain.substring(tyhik+1);//kanal, millega yhinetakse                                                                        	
							
							for(i=0;i<channels.length;i++)                                                       	
							{//otsin vaba koha                                                                   	
								if(channels[i]!=null)                                                            	
								{                                                                                	
									if(channels[i].name.compareTo(name2)==0)                                        	
									{//sellise kanaliga oldi juba yhinetud                                                                            	
										edasi=false;
										break;
									}                                                                            	
								}//kanali objekt ei olnud null                                                   	                                                                         	
							}//for  										 
	
							if(!edasi)
							{
								Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();                                       
																																		 
								for(i=0;i<jada.length;i++)                     	    	                                                                                                                                    
								{                                                  	    	                                                                                                                                
									if(jada[i].getForeground().equals(Color.blue)) 	    	                                                                                                                                
									{                                              	    	                                                                                                                                
										name2=((menuButton)jada[i]).getLabel();                                                                                                                                             
																																																							
										if(name2.compareTo("Status")==0)                                                                                                                                                    
										{                                                                                                                                                                                   
											status.setVisible(false);                                                                                                                                                       
										}                                                                                                                                                                                   
										else                                                                                                                                                                                
										{                                                                                                                                                                                   
											trellid=name2.indexOf("#");                                                                                                                                                     
											if(trellid!=-1)                                                                                                                                                                 
											{                                                                                                                                                                               
												for(j=0;j<channels.length;j++)                                                                                                                                              
												{                                                                                                                                                                           
													if(channels[j]!=null)                                                                     				                                                                
													{                                                                                         				                                                                
														if(channels[j].name.compareTo(name2)==0)                                               				                                                                
														{                                                                                     				                                                                
															if(name2.compareTo(sain.substring(tyhik+1))!=0)
															{//võin olla juba samas aknas
																channels[j].setVisible(false);                                                                                                                                  
															}
															break;                                                                            				                                                                
														}                                                                                     				                                                                
													}                                                               				                                                                                        
												}//for                                                                                                                                                                      
											}//trellid                                                                                                                                                                               
											else                                                                                                                                                                            
											{                                                                                                                                                                               
												for(j=0;j<privad.length;j++)                                                                                                                                                
												{                                                                                                                                                                           
													if(privad[j]!=null)                                                                     				                                                                
													{                                                                                         				                                                                
														if(privad[j].name.compareTo(name2)==0)                                               				                                                                
														{                                                                                     				                                                                
															privad[j].setVisible(false);                                                                                                                                    
															break;                                                                            				                                                                
														}                                                                                     				                                                                
													}                                                                				                                                                                        
												}//for                                                                                                                                                                      
											}//else                                                                                                                                                                         
										}//else                                                                                                                                                                             
																																																							
										jada[i].setForeground(Color.black);                                                                                                                                                 
										break;  
									}//if blue                                                                                                                                                                                                                        	    			                                                                                                                            
								}//for                                              	    	                                                                                                                                    
																																																							
								name2=sain.substring(tyhik+1);//kanal, millega yhinetakse                                                                                                                                   
																																																							
								for(i=0;i<channels.length;i++)                                                                                                                                                              
								{                                                                                                                                                                                           
									if(channels[i]!=null)                                                                         			                                                                                
									{                                                                                             			                                                                                
										if(channels[i].name.compareTo(name2)==0)                                                   			                                                                                
										{                                                                                         			                                                                                
											channels[i].nupp.setForeground(Color.blue);                                                                                                                                     
											channels[i].setVisible(true);                                                                                                                                                   
											channels[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                              	                                                                                                             			                                                                                                
											break;                                                                                			                                                                                
										}                                                                                         			                                                                                
									}//kanali objekt ei olnud null                                                                			                                                                                
								}//for                                                                                            			                                                                                
																																																																																																					  
								peaaken.getComponent(0).removeNotify();
								peaaken.getComponent(0).addNotify();                                                                                                                                                        
								peaaken.show();                                               	                                                                                                                            
							}
							else
							{//ei olnud juba olemas
								toserver.println("JOIN "+name2); 
							}
						}//mitu==0
						else
						{
							status.append("\n*** Sul ei ole ôigust ühineda kanalitega\n",Color.blue,jutt,buffer);
						}
					}                                                                                                                                  
					else                                                                          		                                               
					if((command.compareTo("topic")==0)||(command.compareTo("t")==0))                                             		               
					{                                                                             		                                               
						tyhik=sain.indexOf(" ");                                                  		                                               
						toserver.println("TOPIC "+name+" :"+sain.substring(tyhik+1));             		                                               
					}                                                                                                                                  
					else                                                                          		                                               
					if((command.compareTo("msg")==0)&&(mitu!=2)&&(mitu!=4))                                             		                                   
					{//priva sõnum ilma aknata                                                                             		                       
						tyhik=sain.indexOf(" ");                                                  		                                               
						toserver.println("PRIVMSG "+sain.substring(tyhik+1));                                                                          
						status.append("\n"+aeg+" --Saatsid priva sõnumi--"+sain.substring(tyhik+1)+"\n",modeColor,status.jutt,status.buffer);                                         
					}                                                                                                                                  
					else                                                                          		                                               
					if(command.compareTo("op")==0)                                                		                                               
					{//op blah blah                                                               		                                               
						tyhik=sain.indexOf(" ");                                                  		                                               
						abi=sain.substring(tyhik+1);//nimed kes tehti opiks                         		                                               
						tyhik=abi.indexOf(" ");                                                  		                                               
						while(tyhik!=-1)                                                          		                                               
						{                                                                         		                                               
							toserver.println("MODE "+name+" +o "+abi.substring(0,tyhik));         		                                               
							tyhik=abi.indexOf(" ");                                               		                                               
						}                                                                         		                                               
						toserver.println("MODE "+name+" +o "+abi);                                		                                               
					}                                                                             		                                               
					else                                                                          		                                               
					if(command.compareTo("deop")==0)                                              		                                               
					{                                                                             		                                               
						tyhik=sain.indexOf(" ");                                                  		                                               
						abi=sain.substring(tyhik+1);//nimed kes tehti opiks                         		                                               
						tyhik=abi.indexOf(" ");                                                  		                                               
						while(tyhik!=-1)                                                          		                                               
						{                                                                         		                                               
							toserver.println("MODE "+name+" -o "+abi.substring(0,tyhik));         		                                               
							tyhik=abi.indexOf(" ");                                               		                                               
						}                                                                         		                                               
						toserver.println("MODE "+name+" -o "+abi);                                		                                               
					}                                                                             		                                               
					else                                                                          		                                               
					if((command.compareTo("clear")==0)||(command.compareTo("c")==0))                                             		               
					{                                                                             		                                               
						if(kanal!=null)
						{
							kanal.buffer=new TextBuffer();
						}
						else
						{
							chat.buffer=new TextBuffer();
						}
						jutt.setText("");                                                         		                                               
					}                                                                             		                                               
					else                                                                          		                                               
					if(((command.compareTo("query")==0)||(command.compareTo("q")==0))&&(mitu!=2)&&(mitu!=4))                                             		   
					{//nõuab priva alustamist QUERY <nick> {jutt}                                 		                                               
	        	                                                                		                                                               
						tyhik=sain.indexOf(" ");                                                                                                       
						if(tyhik!=-1)                                                                                                                  
						{                                                                                                                              
							sain=sain.substring(tyhik+1);                                               		                                       
							tyhik=sain.indexOf(" ");                                              		                                               
							int i,j,trellid;                                                                		                                   
							String nickk="";                                                      		                                               
							                                                                      		                                               
							if(tyhik==-1)                                                         		                                               
							{                                                                     		                                               
								nickk=sain;                                                       		                                               
							}                                                                     		                                               
							else                                                                  		                                               
							{                                                                     		                                               
								nickk=sain.substring(0,tyhik);                                    		                                               
							}                                                                     																														
				                                                                                                                                       
							//KAOTAN VANA AKNA                                                                                                         
			    			Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();                                                                                             		
							                                                                                                                           
							boolean edasi=true;                                                                                                        
							String name2;                                                                                                              
			    			String name=nupp.getLabel();                                                                                                                                 
							                                                                                                                           
							for(i=0;i<jada.length;i++)                     	    	                                                                   
							{                                                  	    	                                                               
								if(jada[i].getForeground().equals(Color.blue)) 	    	                                                               
								{                                              	    	                                                               
									name2=((menuButton)jada[i]).getLabel();                                                                            
									                                                                                                                   
									if(name2.compareTo("Status")==0)                                                                                   
									{                                                                	                                               
										status.setVisible(false);                                    	                                               
									}                                                                	                                               
									else                                                             	                                               
									{                                                                	                                               
										trellid=name2.indexOf("#");                                  	                                               
										if(trellid!=-1)                                              	                                               
										{                                                            	                                               
											for(j=0;j<channels.length;j++)                           	                                               
											{                                                        	                                               
		    	                				if(channels[j]!=null)                                	                                               
		    	                				{                                                    	                         				       
		    	                					if(channels[j].name.compareTo(name2)==0)         	                         				       
		    	                					{                                                	                          				       
		    	            							channels[j].setVisible(false);               	                         				       
							    						break;                                       	                                               
		    	                					}                                                	                         				       
		    	                				}                                                    	           	         				           
											}//for                                                   	                                               
										}                                                            	                                               
										else                                                         	                                               
										{                                                            	                                               
											for(j=0;j<privad.length;j++)                             	                                               
											{                                                        	                                               
		    	                				if(privad[j]!=null)                                  	                                               
		    	                				{                                                    	                       				           
		    	                					if(privad[j].name.compareTo(name2)==0)           	                         				       
		    	                					{                                                	                        				       
		    	            							privad[j].setVisible(false);                 	                         				       
							    						break;                                       	                                               
		    	                					}                                                	                         				       
		    	                				}                                                    	            	         				       
											}//for                                                   	                                               
										}//else                                                                                                        
									}//else                                                                                                            
								jada[i].setForeground(Color.black);                                                                                    
								//jada[i].repaint();                                                                                                   
			    				break;                                                           				                                                                                                                	    			                         
								}//if blue                                              	    	                                                   
							}//for                                            		                                                                   
							                                                                                                                           
							for(i=0;i<privad.length;i++)                                          		                                               
							{//kontrollin, et juba ei oleks                                                   		                                   
								if(privad[i]!=null)                                               		                                               
								{                                                                 		                                               
									if(privad[i].name.compareTo(nickk)==0)                                                                             
									{//selline priva juba oli                                                                                          
										privad[i].nupp.setForeground(Color.blue);
										privad[i].setSize(peaaken.getSize().width-7,peaaken.getSize().height-108);                              	                                                                
			    						privad[i].setVisible(true);                                                                                    	                                                                                           
										peaaken.getComponent(0).removeNotify();
										peaaken.getComponent(0).addNotify();                                                                           
										edasi=false;                                                                                                   
									}                                                      		                                                       
			    				}                                                                 		                                                    		
							}			                                                                                                               
				                                                                                                                                       
							if(edasi)                                                                                                                  
							{                                                                                                                          
								for(i=0;i<privad.length;i++)                                          		                                           
							    {//otsin tyhja koha                                               		                                               
							    	if(privad[i]==null)                                           		                                               
							    	{                                                             		                                               
							    		break;                                                    		                                               
							    	}                                                             		                                               
							    	else                                                          		                                               
							    	if(privad[i].name.compareTo("")==0)                           		                                               
							    	{                                                             		                                               
							    		break;                                                    		                                               
							    	}                                                             		                                               
							    }                                                                 		                                               
							                                                                      		                                               
								privad[i]=new priva(nickk,boss);                                    		                                                   
								privad[i].nick=nick;            
								privad[i].buffer=new TextBuffer();
								privad[i].font=status.font;
								privad[i].textColor=status.textColor;
								
			    			    privad[i].append("*** Alustasid priva isikuga "+nickk+"\n",modeColor,privad[i].jutt,privad[i].buffer);                                                             
								                                                                                                                       
								privad[i].nupp.setForeground(Color.blue);
								privad[i].nupp.setBackground(status.nupp.getBackground());
								 ((Panel)peaaken.getComponent(0)).add(privad[i].nupp);                                                                 
			    				privad[i].in.addActionListener(new inKuular(privad[i],null,status,channels,toserver,peaaken,privad,windows,boss,back));                                                                        
								privad[i].nupp.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));                       
		    	            	windows.add(privad[i].win);
								privad[i].nupp.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
								privad[i].close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
								privad[i].choose.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));                                                                                       
								privad[i].jutt.setBackground(status.jutt.getBackground());
								privad[i].in.setBackground(status.in.getBackground());
								privad[i].in.setForeground(status.in.getForeground());
								privad[i].in.setFont(status.in.getFont());
								peaaken.add(privad[i],-1);
								privad[i].beep.addItemListener(new piiksKuular(0,0,null,privad[i]));
								privad[i].nupp.beep.addItemListener(new piiksKuular(0,1,null,privad[i]));
								privad[i].flash.addItemListener(new piiksKuular(1,0,null,privad[i]));
								privad[i].nupp.flash.addItemListener(new piiksKuular(1,1,null,privad[i]));
								privad[i].timer.addItemListener(new piiksKuular(2,0,null,privad[i]));
								privad[i].nupp.timer.addItemListener(new piiksKuular(2,1,null,privad[i]));
								privad[i].log.addItemListener(new piiksKuular(3,0,null,privad[i]));
								privad[i].nupp.log.addItemListener(new piiksKuular(3,1,null,privad[i]));
								privad[i].desktop.addItemListener(new desktopKuular(0,null,privad[i],channels,privad,status,peaaken));
								privad[i].nupp.desktop.addItemListener(new desktopKuular(1,null,privad[i],channels,privad,status,peaaken));
								privad[i].desk.addWindowListener(new kanaliKuular(peaaken,privad[i],null,status,toserver,windows,back));
							}//edasi                                                                                                                   
				             
							//Netscape 4.7 ei toeta repainti()                                                                                     
							peaaken.getComponent(0).doLayout();//kuvab lisatud nupud                                                               
							peaaken.getComponent(0).removeNotify();
							peaaken.getComponent(0).addNotify();//repaindi eest                                                                    
							peaaken.show();																														   

							if(tyhik!=-1)                                                         		                                               
							{//mingi jutt pandi ka kohe kaasa                                     		                                               
								privad[i].append("<"+nick+"> "+sain.substring(tyhik+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);		                                               
								toserver.println("PRIVMSG "+nickk+" :"+sain.substring(tyhik+1));  		                                               
							}                                                                                                                          
						}//>6                                                                         		                                           
						else                                                                      		                                               
						{                                                                         		                                               
							if(kanal!=null)
							{
								kanal.append("\n*** Puuduvad vajalikud parameetrid\n",Color.blue,jutt,buffer);
							}
							else
							{
								chat.append("\n*** Puuduvad vajalikud parameetrid\n",Color.blue,jutt,buffer);
							}
						}                                                                         		                                               
					}
					else                                                                          		                                               
					if(command.compareTo("quit")==0)                                                		                                               
					{					
						toserver.println(sain.substring(1));     
						status.append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,status.jutt,status.buffer);                                                                                                                                       
						status.connect=false; 
						int i;
						for(i=0;i<privad.length;i++)                                                                                                         
						{                                                                                                                                    
							if(privad[i]!=null)                                                                                                              
							{                                                                                                                                
								if(privad[i].name.compareTo("")!=0)                                                                                          
								{                                                                                                                            
									privad[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,privad[i].jutt,privad[i].buffer);	                                                                                               
								}                                                                                                                            
							}                                                                                                                                
						}                                                                                                                                    
						                                                                                                                                     
						for(i=0;i<channels.length;i++)                                                                                                       
						{                                                                                                                                    
							if(channels[i]!=null)                                                                                                            
							{                                                                                                                                
								if(channels[i].name.compareTo("")!=0)                                                                                        
								{                                                                                                                            
									channels[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,channels[i].jutt,channels[i].buffer);	                                                                                      
								}                                                                                                                            
							}                                                                                                                                
						}                                                                                                                                    
					}
					else                                                                          		                                               
					{                                                                             		                                               
						toserver.println(sain.substring(1));                                      		                                               
					}                                                                             		                                               
				                                                                                  		                                               
				}// /KÄSK                                                                         		                                               
				else{                                                                                                                                  
					if(name.compareTo("Status")==0)                                                                                                    
					{                                                     		                                                                                		                                                                       
						if(kanal!=null)
						{
							kanal.append("\n*** Sa ei ole kanalil\n",Color.blue,jutt,buffer);
						}
						else
						{
							chat.append("\n*** Sa ei ole kanalil\n",Color.blue,jutt,buffer);
						}
					}                                                     		                                                                       
					else                                                  		                                                                       
					{                                                     		                                                                       
						toserver.println("PRIVMSG "+name+" :"+sain);      		                                                                       
						if(kanal!=null)
						{
							kanal.append(aeg+"<"+nick+"> "+sain+"\n",textColor,jutt,buffer);
						}
						else
						{
							chat.append(aeg+"<"+nick+"> "+sain+"\n",textColor,jutt,buffer);
						}       		                                                                       
					}                                                     		                                                                       
				}
			}//try
			catch(Exception e)
			{
				status.append("\nTekkis viga sisestusel: "+e+"\n",Color.red,status.jutt,status.buffer);
				if(status.nupp.getForeground().equals(Color.black))
				{
					status.nupp.setForeground(Color.red);
				}
				peaaken.getComponent(0).removeNotify();
				peaaken.getComponent(0).addNotify();
			}
			                                                                                  		                        
			in.setText("");
			}//oleme ikka yhenduses
		}//miski jutt                                                                         		
	}                                                                                         		                                                                                                                                                             
}



class priva extends Panel implements ClipboardOwner, KeyListener//, ActionListener
{//kanali objekt
	TextArea jutt; 	//jutt siia
	menuButton nupp;		//priva tähistav nupp
	TextField in;		//sisestatav tekst
	String name,nick;						//priva nimi
	String[] mem=new String[20];
	String[] memUp=new String[20];
	boolean connect;		//kas oleme yhenduses	
	int i=0;
	int j=-1;
	int last=0;//kuhu sisestati viimati
	Menu win;
	MenuItem choose,close;
	CheckboxMenuItem beep,flash,timer,desktop,log;
	Frame desk;
	static boolean aktiivne;
	TextBuffer buffer;//kfc
	static Font font;
	Color textColor;
	String kestvus;
	int mitu;
	menuList workers;
	Clipboard clipboard=new Clipboard("System");
	//Clipboard clipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
	static boolean copy=false;
	
	priva(String nimi,Applet boss)
	{
	System.out.println("Loon uue priva");	
		name=nimi;

		choose=new MenuItem("Vali");
		beep=new CheckboxMenuItem("Piiks");
		flash=new CheckboxMenuItem("Vilgub");
		timer=new CheckboxMenuItem("Timer");
		desktop=new CheckboxMenuItem("Desktopil");
		log=new CheckboxMenuItem("Log");
		close=new MenuItem("Sulge");

		win=new Menu(name);
		win.add(choose);
		win.add(beep);
		win.add(flash);
		win.add(timer);
		win.add(desktop);		
		win.addSeparator();
		win.add(close);
		jutt=new TextArea("",20,100,1);
		//jutt.setEditable(false);
		nupp=new menuButton();
		
		nupp.setLabel(name);
		in=new TextField(100);
		
		this.setLayout(new BorderLayout());
		this.add(jutt,"Center");
		this.add(in,"South");

		in.addKeyListener(new keyMem(this,null,1));


		jutt.addKeyListener(this);
		/*
		jutt.addKeyListener(new KeyListener()
		{                                                 
			boolean copy=false;

			public void keyPressed(KeyEvent e)
			{                                 
				if(e.getKeyCode()==KeyEvent.VK_CONTROL)
				{
					copy=true;
				}
			}
			public void keyTyped(KeyEvent e)
			{                                             									                                      									
			}
			public void keyReleased(KeyEvent e)
			{                                  
				System.out.println("copy="+copy+"   oli="+e.getKeyCode());
				if((e.getKeyCode()==KeyEvent.VK_C)&&(copy))
				{
					String srcData = jutt.getText();
					if (srcData != null) {
						StringSelection contents = new StringSelection(srcData);
						System.out.println("sain: "+contents);
						clipboard.setContents(contents, super);
					}
					
					System.out.println("kopeerisin");
				}
				if(e.getKeyCode()==KeyEvent.VK_CONTROL)
				{
					copy=false;
				}
			}
		});                                               									
		*/
		desk=new Frame(name);

		try{
			Image icon=boss.getImage(boss.getCodeBase(),"aw.gif");
			desk.setIconImage(icon);
		}
		catch(Exception e)
		{
			System.out.println("Privas kala="+e);
		}
		
		kestvus="LOG "+name+" ("+date();
	}


	public static String date(){
		String mitmes1="", tund1="", minut1="", sekund1="", kuu1=""; 
		Calendar c=Calendar.getInstance();

		int kuu=c.get(c.MONTH)+1;
		if(kuu<10) kuu1="0";
		int aasta=c.get(c.YEAR);
		int mitmes=c.get(c.DAY_OF_MONTH);
		if(mitmes<10) mitmes1="0";
		int tund= c.get(c.HOUR_OF_DAY);
		if(tund<10) tund1="0";
		int minut=c.get(c.MINUTE);
		if(minut<10) minut1="0";
		int sekund=c.get(c.SECOND);
		if(sekund<10) sekund1="0";

		return mitmes1+mitmes+"."+kuu1+kuu+"."+aasta+"  "+tund1+tund+":"+minut1+minut+" -> ";
	}


	public void lostOwnership(Clipboard clipboard, Transferable contents) {
       System.out.println("Clipboard contents replaced");
       }


public void keyPressed(KeyEvent e)
			{                                 
				if(e.getKeyCode()==KeyEvent.VK_CONTROL)
				{
					copy=true;
				}
			}
			public void keyTyped(KeyEvent e)
			{                                             									                                      									
			}
			public void keyReleased(KeyEvent e)
			{                                  
				System.out.println("copy="+copy+"   oli="+e.getKeyCode());
				if((e.getKeyCode()==KeyEvent.VK_C)&&(copy))
				{
					String srcData = jutt.getSelectedText();
					//String srcData = "halleluujahhh";//jutt.getText();
					if (srcData != null) {
						StringSelection contents = new StringSelection(srcData);
						
						try{
							clipboard.setContents(contents, this);
							Transferable content = clipboard.getContents(this);
							System.out.println("sain: "+(String)content.getTransferData(DataFlavor.stringFlavor));
						}
						catch(Exception ee)
						{
							System.out.println("Kala: "+ee);
						}
						 
					}
					
					System.out.println("kopeerisin");
				}
				if(e.getKeyCode()==KeyEvent.VK_CONTROL)
				{
					copy=false;
				}
			}


	/*
	public void actionPerformed(ActionEvent evt) {
        String cmd = evt.getActionCommand();
System.out.println("cmd="+cmd);
        if (cmd.equals("copy")) { 
           // Implement Copy operation
           String srcData = jutt.getText();
           if (srcData != null) {
                StringSelection contents = new StringSelection(srcData);
System.out.println("sain: "+contents);
                clipboard.setContents(contents, this);
            }
        } 
	}
*/

	protected static void append(String text,Color color,TextArea jutt,TextBuffer buffer)
	{
		buffer.setTextStyle(new TextStyle(font,color,false));
		buffer.append(text);
		jutt.setRichText(buffer.toRichText(RichTextStyle.DEFAULT_DOCUMENT_STYLE));
		jutt.end_of_file();//jp.kyasu
	}
}



class blinking extends Thread{
	Frame peaaken;
	boolean[] luba;
	channel kanal;
	priva chat;

	blinking(Frame pea,boolean[] uba,channel kana,priva cht){
		peaaken=pea;
		luba=uba;
		kanal=kana;
		chat=cht;
	}

	public void run()
	{	
		//luba[0] - kas peaaken aktiivne (false --> ei ole aktiivne)
		//luba[1] - ega peaaken ei vilgu (true --> ei vilgu)
		if(kanal!=null)
		{
			if(kanal.desktop.getState())
			{//aken desktopil
				while(!kanal.aktiivne)
				{//ja ei ole aktiivne
					kanal.desk.setTitle("");
					try
					{
						sleep(800);
					}
					catch(InterruptedException e)
					{
					}
					kanal.desk.setTitle(kanal.name);
					try
					{
						sleep(800);
					}
					catch(InterruptedException e)
					{
					}
				}//while
			}
			else
			{
				if(luba[1])
				{//peaaken veel ei vilgu
					luba[1]=false;//nyyd vilgub

					while(!luba[0])
					{//peaaken ei ole aktiivne
						peaaken.setTitle("");
						try
						{
							sleep(800);
						}
						catch(InterruptedException e)
						{
						}
						peaaken.setTitle("AW Chat");
						try
						{
							sleep(800);
						}
						catch(InterruptedException e)
						{
						}
					}//while
				}//peaaken ei vilgu veel
			}//else
		}//if kanal
		else
		{//priva
			if(chat.desktop.getState())
			{//aken desktopil
				while(!chat.aktiivne)
				{//ja ei ole aktiivne
					chat.desk.setTitle("");
					try
					{
						sleep(800);
					}
					catch(InterruptedException e)
					{
					}
					chat.desk.setTitle(chat.name);
					try
					{
						sleep(800);
					}
					catch(InterruptedException e)
					{
					}
				}//while
			}
			else
			{
				if(luba[1])
				{//peaaken veel ei vilgu
					luba[1]=false;//nyyd vilgub

					while(luba[0]==false)
					{//peaaken ei ole aktiivne
						peaaken.setTitle("");
						try
						{
							sleep(800);
						}
						catch(InterruptedException e)
						{
						}
						peaaken.setTitle("AW Chat");
						try
						{
							sleep(800);
						}
						catch(InterruptedException e)
						{
						}
					}//while
				}//peaaken ei vilgu veel
			}//else
		}//else priva

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



class channel extends Panel
{//kanali objekt
	TextArea jutt;	//kanali jutt siia
	menuList list;				//kanalil olevad nimed
	menuButton nupp;		//kanalit tähistav nupp
	TextField in;			//sisestatav tekst
	String name,nick;						//kanali nimi
	String[] mem=new String[20];
	String[] memUp=new String[20];	
	int i=0;
	int j=-1;
	int last=0;//kuhu sisestati viimati
	Menu win;
	MenuItem choose,close;
	CheckboxMenuItem beep,flash,timer,desktop,log;
	Frame desk;
	TextBuffer buffer;//kfc
	static boolean aktiivne;
	static Font font;
	Color textColor;
	String kestvus;//jutuajamise algus ja lõpp
	
	channel(String nimi,Applet boss)
	{
		name=nimi;
		
		choose=new MenuItem("Vali");
		beep=new CheckboxMenuItem("Piiks");
		flash=new CheckboxMenuItem("Vilgub");
		timer=new CheckboxMenuItem("Timer");
		desktop=new CheckboxMenuItem("Desktopil");
		log=new CheckboxMenuItem("Log");
		close=new MenuItem("Sulge");
		
		win=new Menu(name);
		win.add(choose);
		win.add(beep);
		win.add(flash);
		win.add(timer);
		win.add(desktop);
		win.add(log);
		win.addSeparator();
		win.add(close);

		jutt=new TextArea("",20,85,1);	//kanali jutt siia
		jutt.setEditable(false);
		list=new menuList();					//kanalil olevad nimed
		nupp=new menuButton();		//kanalit tähistav nupp
		in=new TextField(100);	
		
		nupp.setLabel(name);
		this.setLayout(new BorderLayout());
		this.add(jutt,"Center");
		this.add(list,"East");
		this.add(in,"South");

		in.addKeyListener(new keyMem(null,this,0));
		desk=new Frame(name);
		Image icon=boss.getImage(boss.getCodeBase(),"aw.gif");
		desk.setIconImage(icon);
		kestvus="LOG "+name+" ("+date();
	}


	public static String date(){
		String mitmes1="", tund1="", minut1="", sekund1="", kuu1=""; 
		Calendar c=Calendar.getInstance();

		int kuu=c.get(c.MONTH)+1;
		if(kuu<10) kuu1="0";
		int aasta=c.get(c.YEAR);
		int mitmes=c.get(c.DAY_OF_MONTH);
		if(mitmes<10) mitmes1="0";
		int tund= c.get(c.HOUR_OF_DAY);
		if(tund<10) tund1="0";
		int minut=c.get(c.MINUTE);
		if(minut<10) minut1="0";
		int sekund=c.get(c.SECOND);
		if(sekund<10) sekund1="0";

		return mitmes1+mitmes+"."+kuu1+kuu+"."+aasta+"  "+tund1+tund+":"+minut1+minut+" -> ";
	}


	protected static void append(String text,Color color,TextArea jutt,TextBuffer buffer)
	{
		buffer.setTextStyle(new TextStyle(font,color,false));
		buffer.append(text);
		jutt.setRichText(buffer.toRichText(RichTextStyle.DEFAULT_DOCUMENT_STYLE));
		//jutt.setCaretPosition(10000);
		jutt.end_of_file();//jp.kyasu
	}
}



class addKuular implements ActionListener{
PrintStream toserver;
Dialog raam;

 
	addKuular(PrintStream to,Frame peaaken,Color back){
		toserver=to;
		
		String nimi="Lisa Töötaja";
		//if(kumb==0) nimi="Muuda andmeid";
		raam = new Dialog(peaaken,"Lisa töötaja",true);
		raam.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-100,peaaken.getLocation().y+peaaken.getSize().height/2-75);                                          		

		raam.setBackground(back);
		raam.setSize(250,150);
		Label kiri=new Label("Sisesta uus hüüdnimi.",1);
		//Label kiriVaru=new Label("Sisesta varu hüüdnimi.",1);
		Label kiriNimi=new Label("Sisesta lisatava nimi.",1);
		//Label kiri12=new Label("Parooli pikkuseks vähemalt 5 tähemärki!",1);
		Label kiri1=new Label("Hüüdnime pikkus peab olema 3-9 tähemärki !",1);
		//Label kiri2=new Label("Sisesta parool.",1);
		//Label kiri3=new Label("Korda parooli.",1);
		
		Panel hoiatus=new Panel();
		//hoiatus.setLayout (new GridLayout (2,1));
		hoiatus.add(kiri1);
		//hoiatus.add(kiri12);
		//TextField nickVaru=new TextField();
		final TextField nimeLahter=new TextField();
		final TextField nick=new TextField();
		//TextField psw1=new TextField();
		//TextField psw2=new TextField();
		//psw1.setEchoChar('*');
		//psw2.setEchoChar('*');
		Panel paneel=new Panel();
		paneel.setLayout (new GridLayout (2,2));
		paneel.add(kiri);
		paneel.add(nick);
		//paneel.add(kiriVaru);
		//paneel.add(nickVaru);
		paneel.add(kiriNimi);
		paneel.add(nimeLahter);
		//paneel.add(kiri2);
		//paneel.add(psw1);
		//paneel.add(kiri3);
		//paneel.add(psw2);
		Panel nupud=new Panel();
		Button ok=new Button("  OK  ");
		Button cancel=new Button("Katkesta");
		nupud.add(ok);
		nupud.add(cancel);
		raam.add(hoiatus,"North");
		raam.add(paneel,"Center");
		raam.add(nupud,"South");

		ok.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent event){
				String nimi=nimeLahter.getText();
				String nickk=nick.getText();
				if((nimi.length()>2)&&(nickk.length()>2))
				{
					toserver.println("LISA "+nimi+" "+nickk);
				}
				raam.setVisible(false);
			}
		});
		
		cancel.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent event){
				raam.setVisible(false);
			}
		});
	}

	public void actionPerformed(ActionEvent event){
		raam.show();
	}
}


class action extends Thread
{
	static Frame peaaken=new Frame("AW Chat");
	static String vastus,msg,name,abi,abi2,command, server,aeg,nickk,forcedMsg,forcedPriva,forcedChannel,nimi11,mail11;
	static String[] nick=new String[1];
	static int i,ii,j,place,tyhik,tyhik2,koht,koolon,trellid,hyyd,elemente,mitu;//mitu=0 - piirangud puuduvad
																				//mitu=1 - 1 kanal + privad(vabalt valitavad)
																				//mitu=2 - 1 konkreetne priva
																				//mitu=4 - arco
	static boolean edasi;
	static boolean[] luba=new boolean[2];
	static channel[] channels=new channel[10];
	static priva[] privad=new priva[10];
	static priva status;
	static String[] teated=new String[504];
	static PrintStream toserver;
	static BufferedReader fromserver;
	static Socket s;
	static TextField uus=new TextField(10);
	static Button ok=new Button("  OK  ");
	static Dialog warning,login;
	static TextField nick1,nimi1,mail,server2,boort;
	static Color buttonback,back,back2,fore;//tulevad lehelt
	static Font font;
	static Applet boss;
	static MenuItem exit = new MenuItem("Disconnect");
	static MenuItem plug = new MenuItem("Connect");
	static Color modeColor=new Color(0,170,0);	
	static CheckboxMenuItem canSee;
	menuList workers;



	action(Applet bos)
	{
		boss=bos;
	}

	public static String date()
	{
		Calendar c=Calendar.getInstance();
		String panen2="";
		String answer="";
		int tund= c.get(c.HOUR_OF_DAY);
		if(tund<10) panen2="0";
		int minut=c.get(c.MINUTE);
		if(minut<10) answer="0";
		return "["+""+panen2+""+tund+":"+""+answer+""+minut+"]";
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


	public void run()
	{
		status=new priva("Status",boss);
//======================== KÄSKUDE VASTUSED =========================		
		
		//teated[1] - said IRCu sisse
		teated[1]="tere";
		teated[301]="Soovitud isik on hetkel ära:";			//äraoleku põhjus
		teated[303]="On kanalil: ";							//jah/ei
		teated[305]="Sa oled märgitud saabunuks.";
		teated[306]="Sa oled märgitud hetkel lahkunuks.";
		/*
		teated[311]	- WHOIS
		teated[312]
		teated[313]
		teated[317]
		teated[318] - WHOIS lõpp
		*/
		teated[331]="Kanalil puudub teema.";
		teated[332]="*** Kanali teemaks on: ";
		teated[341]="Isik on kanalile kutsutud.";
		teated[353]="Nimed";				//ei tohi tyhjaks jätta
		teated[366]="Nimede listi lõpp.";
		teated[376]="End of /MOTD command.";
		teated[381]="Sa oled nüüd IRC operaator.";
		teated[394]="Userite lõpp";
		teated[395]="Kedagi ei ole sisse logitud.";

//=========================== VEATEATED ===============================

		teated[401]="Soovitud isik ei ole hetkel IRCus.";
		teated[402]="Sellist serverit ei leitud.";
		teated[403]="Sellist kanalit ei leitud.";
		teated[404]="Ei saa saata kanalile.";								//Sent to a user who is either (a) not on a channel
																			//which is mode +n or (b) not a chanop (or mode +v) on
																			//a channel which has mode +m set and is trying to send
																			//a PRIVMSG message to that channel.

		teated[405]="Ei saa ühineda kanaliga - sa oled ühinenud liiga paljude kanalitega.";	
		teated[406]="Sellist hüüdnime ei leitud.";							//Returned by WHOWAS to indicate there is no history
																			//information for that nickname.
		teated[412]="Polnud midagi saata.";

		teated[421]="Tundmatu käsk.";
		teated[423]="Ei leitud soovitud administratiivinfot";				//Returned by a server in response to an ADMIN message
																			//when there is an error in finding the appropriate
																			//information.

		teated[431]="Ei leitud hüüdnime.";									//Returned when a nickname parameter expected for a
																			//command and isn't found.
		teated[432]="Hüüdnimi sisaldas lubamatuid tähemärke.";
		teated[433]="Hüüdnimi on juba kasutuses.";	
		teated[436]="Hüüdnimede kattumine...";								//Returned by a server to a client when it detects a
																			//nickname collision (registered of a NICK that
																			//already exists by another server).

		teated[441]="Isik ei olnud kanalil";								//Returned by the server to indicate that the target
																			//user of the command is not on the given channel.

		teated[442]="Sa ei ole selle kanali liige";
		teated[443]="Isik on juba kanalil.";								//Returned when a client tries to invite a user to a
																			//channel they are already on.

		teated[461]="Puuduvad vajalikud parameetrid.";
		teated[462]="Sa ei saa uuesti registreeruda.";						//Returned by the server to any link which tries to
																			//change part of the registered details (such as
																			//password or user details from second USER message).
		
		teated[464]="Vale parool.";											//Returned to indicate a failed attempt at registering
																			//a connection for which a password was required and
																			//was either not given or incorrect.	
		
		teated[465]="Sa ei saa selle serveriga ühineda.";					//Returned after an attempt to connect and register
																			//yourself with a server which has been setup to
																			//explicitly deny connections to you.
		teated[467]="Kanalil on juba parool.";
		teated[471]="Ei saa ühineda kanaliga - kanal on täis (+l).";
		teated[472]="Tundmatu MODE parameeter.";
		teated[473]="Ei saa ühineda kanaliga - ainult kutsutud (+i)";
		teated[474]="Ei saa ühineda kanaliga - bannitud (+b).";
		teated[475]="Ei saa ühineda kanaliga - vale kanali parool (+k).";
		teated[477]="Kanal ei toeta seda käsku.";
		teated[481]="Juurdepääs keelatud, sa ei ole IRC operaator.";			//Any command requiring operator privileges to operate
																			//must return this error to indicate the attempt was unsuccessful.

		teated[482]="Sa ei ole kanali operaator.";
		teated[483]="SA EI SAA TAPPA SERVERIT!";		//Any attempts to use the KILL command on a server
														//are to be refused and this error returned directly
														//to the client.
		teated[491]="Sinu host ei toeta sellist käsku.";	//If a client sends an OPER message and the server has
														// not been configured to allow connections from the
														//client's host as an operator, this error must be returned.
		teated[501]="Tundmatu MODE parameeter.";		//Returned by the server to indicate that a MODE
														//message was sent with a nickname parameter and that
														//the a mode flag sent was not recognized.
		teated[502]="Sa ei saa määrata MODE teiste userite jaoks.";
		
		luba[0]=true;//peaaken ei vilgu
		luba[1]=true;//ja on aktiivne
		server="irc.estpak.ee";
		String host=boss.getParameter("host");
		int port=new Integer(boss.getParameter("port")).intValue();
		
		MenuBar mb = new MenuBar();
		Menu seaded = new Menu("Seaded");
		MenuItem fontt = new MenuItem("Font");
		
		seaded.add(fontt);
		mb.add(seaded);

		Menu commands = new Menu("Käsud");	
		MenuItem whois = new MenuItem("Kes on...");
		MenuItem edit = new MenuItem("Muuda nime");
		MenuItem topic = new MenuItem("Pane teema");
		MenuItem join = new MenuItem("Join");
		MenuItem chat = new MenuItem("Priva");
		MenuItem kick = new MenuItem("Viska välja");
		MenuItem ban = new MenuItem("Keela tuba");
		MenuItem unban = new MenuItem("Luba tuba");
		MenuItem ignore = new MenuItem("Ignoreeri");
		MenuItem unignore = new MenuItem("Aktsepteeri");
		canSee = new CheckboxMenuItem("Saadaval");
		MenuItem add = new MenuItem("Lisa töötaja");
		MenuItem erase = new MenuItem("Kustuta töötaja");

		if(boss.getParameter("mode")!=null)
		{
			try
			{
				mitu=new Integer(boss.getParameter("mode")).intValue();
			}
			catch(NumberFormatException e)
			{
				mitu=0;
			}
		}
		else
		{
			mitu=0;
		}
		plug.setEnabled(false);

		commands.add(whois);
		commands.add(edit);
		commands.add(topic);
		commands.add(unban);
		commands.add(unignore);
		if(mitu==0) commands.add(join);
		if(mitu!=2) commands.add(chat);
		commands.addSeparator();
		commands.add(exit);
		//commands.add(plug);
		commands.addSeparator();
		commands.add(kick);		
		commands.add(ban);
		commands.add(ignore);		
		mb.add(commands);

		Menu windows=new Menu("Aknad");
		mb.add(windows);

		Menu help = new Menu("Abi");
		mb.add(help);
		MenuItem hielp = new MenuItem("Abi");
		help.add(hielp);

		hielp.addActionListener(new ActionListener(){
			public void actionPerformed(ActionEvent event){
				try
				{
					//URL url=new URL("http://aw.struktuur.ee/risto/temp/abi.html");
					URL url=new URL(boss.getCodeBase().toString()+"abi.html");
					boss.getAppletContext().showDocument(url,"AW Chat - Abi");
				}
				catch(java.net.MalformedURLException e)
				{
				}
			}
		});

		//settingud lehelt
		
		forcedChannel=boss.getParameter("channel");
		forcedMsg=boss.getParameter("message");
		forcedPriva=boss.getParameter("privat");

		if(forcedChannel==null) forcedChannel="";
		if(forcedMsg==null) forcedMsg="";
		if(forcedPriva==null) forcedPriva="";

		Color back=getColor(boss.getParameter("windowcolor")); 
		Color back2=getColor(boss.getParameter("backcolor")); 
		Color fore=getColor(boss.getParameter("textcolor")); 
		Color buttonback=getColor(boss.getParameter("buttoncolor"));
		
	
		if(back==null) back=new Color(189,210,220);//akendetaust
		if(back2==null) back2=new Color(238,238,238);//kanalite ja privade taust
		if(fore==null) fore=new Color(0,0,0);//teksti värv
		if(buttonback==null) buttonback=back;//nuputaust
		

		font=new Font("TimesRoman",Font.PLAIN,16);//font
		nick[0]=boss.getParameter("user");
		mail11="risto@struktuur.ee";
		nimi11="Risto";

		Panel privat=new Panel();
		privat.setLayout(new FlowLayout(FlowLayout.LEFT));
		privat.setBackground(back);
		privat.add(status.nupp);
		status.nupp.setForeground(Color.blue);
		status.nupp.setBackground(buttonback);

		peaaken.setLayout(new BorderLayout());
		peaaken.setMenuBar(mb);
		peaaken.add(privat,"North");
		peaaken.add(status,"Center"); 
		peaaken.add(status,-1); 
		peaaken.setSize(Toolkit.getDefaultToolkit().getScreenSize().width-200, Toolkit.getDefaultToolkit().getScreenSize().height-250);
		peaaken.setLocation(100,100);								
		peaaken.setBackground(back2);
		status.jutt.setBackground(back2);
		status.textColor=fore;
		status.font=font;
		status.in.setFont(font);
		status.in.setBackground(back2);
		status.in.setForeground(fore);	
		status.buffer=new TextBuffer();
		Image icon=boss.getImage(boss.getCodeBase(),"aw.gif");
		peaaken.setIconImage(icon);
		peaaken.show();

		status.mitu=mitu;
		edasi=true;
		boolean arcomees=false;
		
		if(nick[0].compareTo("")==0)
		{//ei ole nicki saanud
			login=new Dialog(peaaken,"IRCu logimine",true);
			login.addWindowListener(new destroy(boss));
			login.setBackground(back);
			login.setLayout(new BorderLayout());
			nick1=new TextField(10);
			nimi1=new TextField(25);
			mail=new TextField(30);
			boort=new TextField(4);
			boort.setText("6667");
			server2=new TextField(25);
			server2.setText("irc.estpak.ee");
			if(mitu==4)
			{
				server2.setEnabled(false);
			}
			Button okk=new Button("  OK  ");
			Panel all=new Panel();
			all.setLayout(new GridLayout(8,1));
			Panel panel1=new Panel();
			panel1.add(nick1);
			Panel panel2=new Panel();
			panel2.add(nimi1);
			Panel panel3=new Panel();
			panel3.add(mail);
			Panel panel4=new Panel();
			panel4.add(server2);
			panel4.add(boort);
			Panel nupud=new Panel();
			okk.setBackground(buttonback);
			nupud.add(okk);

			Label yx=new Label("Hüüdnimi",1);
			Label kax=new Label("Täisnimi",1);
			Label kolm=new Label("E-mail",1);
			Label neli=new Label("Server",1);

			all.add(yx);
			all.add(panel1);
			all.add(kax);
			all.add(panel2);
			all.add(kolm);
			all.add(panel3);
			all.add(neli);
			all.add(panel4);
			
			login.add(all,"North");
			login.add(nupud,"South");
			login.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-150,peaaken.getLocation().y+peaaken.getSize().height/2-130);                                          		
			login.setSize(300,340);

			okk.addActionListener(new ActionListener()
			{
				public void actionPerformed(ActionEvent e)
				{
					nick[0]=nick1.getText();

					//if(nick[0].compareTo("")!=0)
					//{
						nimi11=nimi1.getText();
						mail11=mail.getText();
						if(nimi11.compareTo("")==0)
						{
							nimi11=nick[0];
						}
						login.dispose();
					//}
				}
			});
			
			login.show();
		}
		

		try
		{
			server=server2.getText();
			status.append("Ühendan...\n",status.textColor,status.jutt,status.buffer);
			//s=new Socket(host,port);
			s=new Socket("media.elkdata.ee",10001);
			toserver=new PrintStream(s.getOutputStream());
			fromserver=new BufferedReader(new InputStreamReader(s.getInputStream()));
		
			status.append("Initsialiseerin...\n",status.textColor,status.jutt,status.buffer);	

			toserver.println(server+" "+boort.getText());

			toserver.println("NICK "+nick[0]);
			if(mail11.compareTo("")!=0)
			{
				toserver.println("USER "+nick[0]+" "+mail11+" "+server+" : "+nimi11+" (applet)");
			}
			else
			{
				toserver.println("USER "+nick[0]+" "+s.getInetAddress().getHostName()+" "+server+" : "+nimi11+" (applet)");
			}

			exit.addActionListener(new Väljumine(toserver,s,status,channels,privad,exit,this));
			status.connect=true;
		}
		catch(java.net.ConnectException e)
		{
			status.append("\nEi suutnud ühendust luua...\n",Color.red,status.jutt,status.buffer);
			status.append("\nDeemon ei tööta...\n",Color.red,status.jutt,status.buffer);
			edasi=false;
			exit.setEnabled(false);
			plug.setEnabled(true);
			status.connect=false;
		}
		catch(Exception e)
		{
			status.append("\nEi suutnud ühendust luua...\n",Color.red,status.jutt,status.buffer);
			edasi=false;
			exit.setEnabled(false);
			plug.setEnabled(true);
			status.connect=false;
		}
		/*
		catch(java.security.AccessControlException e)
		{IE ei toeta seda
			status.jutt.append("\nEi suutnud ühendust luua...\n");
			status.jutt.append("\nServerile ligipääs keelatud...\n");
		}
		*/
		
		if(edasi)
		{	
			ok.addActionListener(new ActionListener()
			{
				public void actionPerformed(ActionEvent e)
				{
					toserver.println("NICK "+uus.getText());
					warning.dispose();
					nick[0]=uus.getText();
					status.nick=nick[0];
				}
			});

			uus.addActionListener(new ActionListener()
			{
				public void actionPerformed(ActionEvent e)
				{
					toserver.println("NICK "+uus.getText());
					warning.dispose();
					nick[0]=uus.getText();
					status.nick=nick[0];
				}
			});
				
			status.nick=nick[0];
			whois.addActionListener(new whoKuular1(toserver,1,peaaken,channels,back,buttonback));//kes on
			ignore.addActionListener(new whoKuular1(toserver,0,peaaken,channels,back,buttonback));//ignoreeri
			unignore.addActionListener(new whoKuular1(toserver,2,peaaken,channels,back,buttonback));//aktsepteeri
			kick.addActionListener(new whoKuular1(toserver,3,peaaken,channels,back,buttonback));//viska välja
			ban.addActionListener(new whoKuular1(toserver,4,peaaken,channels,back,buttonback));//keela tuba
			unban.addActionListener(new whoKuular1(toserver,5,peaaken,channels,back,buttonback));//luba tuba
			topic.addActionListener(new whoKuular1(toserver,6,peaaken,channels,back,buttonback));//teema
			edit.addActionListener(new whoKuular1(toserver,7,peaaken,channels,back,buttonback));//muuda nime
			chat.addActionListener(new privaKuular(toserver,peaaken,channels,back,privad,status,windows,boss));//alusta priva
			join.addActionListener(new joinKuular(toserver,peaaken,channels,back,privad,status));//alusta priva
		}

		status.nupp.addActionListener(new nuppKuular(status.nupp,status,channels,privad,peaaken));
		status.nupp.close.setEnabled(false);
		status.in.addActionListener(new inKuular(status,null,status,channels,toserver,peaaken,privad,windows,boss,back));
		peaaken.addWindowListener(new AknaKuular2(peaaken,toserver,s,luba,channels,privad,this,back));
		peaaken.addComponentListener(new AknaKuular2(peaaken,toserver,s,luba,channels,privad,this,back));
		windows.add(status.win);
		//status.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,status.nupp,windows));
		status.close.setEnabled(false);
		status.choose.addActionListener(new nuppKuular(status.nupp,status,channels,privad,peaaken));
		status.beep.addItemListener(new piiksKuular(0,0,null,status));
		status.nupp.beep.addItemListener(new piiksKuular(0,1,null,status));
		//status.flash.addItemListener(new piiksKuular(1,0,null,status));
		//status.nupp.flash.addItemListener(new piiksKuular(1,1,null,status));
		status.nupp.flash.setEnabled(false);
		status.flash.setEnabled(false);
		status.timer.addItemListener(new piiksKuular(2,0,null,status));
		status.nupp.timer.addItemListener(new piiksKuular(2,1,null,status));
		//status.desktop.addItemListener(new desktopKuular(0,null,status,channels,privad,status,peaaken));
		//status.nupp.desktop.addItemListener(new desktopKuular(1,null,status,channels,privad,status,peaaken));
		status.nupp.desktop.setEnabled(false);
		status.desktop.setEnabled(false);
		status.beep.setEnabled(false);
		status.nupp.beep.setEnabled(false);
		status.nupp.log.setEnabled(false);
		status.log.setEnabled(false);
		fontt.addActionListener(new fondiKuular(channels,privad,status,peaaken,back,buttonback));
		boolean first=false;

		while(edasi)
		{
			try
			{
				System.gc();
				yield();
				vastus=fromserver.readLine();
				System.out.println("vastus="+vastus);
				if((vastus==null)&&(status.connect))
				{	
					aeg=date();
					status.append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,status.jutt,status.buffer);
					status.connect=false;
					for(i=0;i<privad.length;i++)
					{
						if(privad[i]!=null)
						{
							if(privad[i].name.compareTo("")!=0)
							{
								privad[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,privad[i].jutt,privad[i].buffer);
							}
						}
					}

					for(i=0;i<channels.length;i++)
					{
						if(channels[i]!=null)
						{
							if(channels[i].name.compareTo("")!=0)
							{
								channels[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,channels[i].jutt,channels[i].buffer);			
							}
						}
					}
					s.close();
					exit.setEnabled(false);
					break;
				}
				else
				{
//status.append("Vastus="+vastus+"\n",Color.red,status.jutt,status.buffer);
				
					koolon=vastus.indexOf(":");
					if(koolon==0)
					{
						vastus=vastus.substring(1);//võtan eest kooloni ära
					}

					try
					{//selekteerime numbriga teate välja    <server> xxx blah :jutt
						tyhik=vastus.indexOf(" ");
						place=new Integer(vastus.substring(tyhik+1,tyhik+4)).intValue();

						edasi=false;
					
						if(place!=332)
						{//numbriga teade alati status aknasse (jutt[0]) va. teema;
							if(teated[place]==null)
							{//ei viitsind kõike eesti keelde panna
								if(place==0)
								{
									if(vastus.substring(tyhik+5,tyhik+9).compareTo("LISA")==0)
									{
										workers.add(vastus.substring(tyhik+10));
									}
									else
									if(vastus.substring(tyhik+5,tyhik+9).compareTo("VOTA")==0)
									{
										workers.remove(vastus.substring(tyhik+10));
									}
									else
									if(vastus.substring(tyhik+5,tyhik+9).compareTo("LIST")==0)
									{
										final Dialog kustuta=new Dialog(peaaken,"Kustuta",true);
										kustuta.setSize(200,200);
										kustuta.setBackground(back);
										kustuta.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-150,peaaken.getLocation().y+peaaken.getSize().height/2-100);                                          		

										Label kiri=new Label("Vali kustutatav",1);
										final List eraser=new List(5);
										Panel kesk=new Panel();
										Panel nupud=new Panel();
										
										Button ok=new Button("  OK  ");
										Button cancel=new Button("Katkesta");
										nupud.add(ok);
										nupud.add(cancel);

										ok.addActionListener(new ActionListener(){
											public void actionPerformed(ActionEvent event){
												toserver.println("VOTA "+eraser.getSelectedItem());
												kustuta.dispose();
											}
										});
										
										cancel.addActionListener(new ActionListener(){
											public void actionPerformed(ActionEvent event){
												kustuta.dispose();
											}
										});

										vastus=fromserver.readLine();

										while(vastus.compareTo(".")!=0)
										{
											eraser.add(vastus);
											vastus=fromserver.readLine();
										}
										kesk.add(eraser);
										kustuta.add(kiri,"North");
										kustuta.add(kesk,"Center");
										kustuta.add(nupud,"South");
										kustuta.show();
									}
								}
								else
								{
									koolon=vastus.indexOf(nick[0]);
									if(koolon!=-1)
									{
										msg=vastus.substring(koolon+nick[0].length()+1);
									}
									else
									{
										msg=vastus.substring(tyhik+5);
									}
								}
							}
							else
							if(place==1)
							{//said sisse
								msg=vastus;
							}
							else
							if(place==353)
							{//yhinesid kanaliga ja nyyd said names listi
								trellid=vastus.indexOf("#");
								koolon=vastus.indexOf(":");
								name=vastus.substring(trellid,koolon-1);
								abi=vastus.substring(koolon+1);//nimed

								for(i=0;i<channels.length;i++)
								{//saan teada koha
									if(channels[i]!=null)
									{
										if(channels[i].name.compareTo(name)==0)
										{
											break;
										}
									}//kanali objekt ei olnud null
								}//for
								
								tyhik=abi.indexOf(" ");
								while(tyhik!=abi.lastIndexOf(" "))
								{//(irc.estpak.ee 353 tammer = #meie3 :tammer @Wahvel )
									channels[i].list.add(abi.substring(0,tyhik));
									abi=abi.substring(tyhik+1);
									tyhik=abi.indexOf(" ");
								}
								channels[i].list.add(abi.substring(0,tyhik));
								
								if(mitu!=4)
								{//arcol pole vaja näha
									status.append("\n"+vastus.substring(trellid)+"\n",status.textColor,status.jutt,status.buffer);
								
									if(status.nupp.getForeground().equals(Color.black))
									{
										status.nupp.setForeground(Color.red);
									}
								}
							}
							else
							{
								if(place==303)
								{//ISON vastus
									koolon=vastus.indexOf(":");
									if(vastus.length()>koolon+2)
									{
										msg=teated[place]+" jah";
									}
									else
									{
										msg=teated[place]+" ei";
									}
								}
								else
								if(place==301)
								{//isik on ära
									msg=teated[place]+vastus.substring(vastus.indexOf(":"));
								}
								else
								if((place==433)||(place==432))
								{//logimisel hyydnimi ei kõlvanud
									warning=new Dialog(peaaken,"!!!",true);
									status.append("\n"+teated[place]+"\n",status.textColor,status.jutt,status.buffer);
									
									if(status.nupp.getForeground().equals(Color.black))
									{
										status.nupp.setForeground(Color.red);
									}
									msg="";
									warning.setLayout(new BorderLayout());
									abi="   Hüüdnimi kasutuses, sisesta uus.   ";
									if(place==432)
									{
										abi="  Hüüdnimi sisaldas lubamatuid märke, sisesta uus.  ";
									}
									Label kiri=new Label(abi);
									kiri.setBackground(back);
									
									Panel panel1=new Panel();
									Panel panel2=new Panel();
									panel1.setBackground(back);
									panel2.setBackground(back);
									panel1.add(uus);
									ok.setBackground(buttonback);
									panel2.add(ok);

									warning.add(kiri,"North");
									warning.add(panel1,"Center");
									warning.add(panel2,"South");
									warning.setLocation(peaaken.getLocation().x+peaaken.getSize().width/2-(kiri.getSize().width+20)/2-50,peaaken.getLocation().y+peaaken.getSize().height/2-70);                                          		

									warning.setSize(250,150);
									warning.show();
								}
								else
								msg=teated[place];
							}
							Color bbb=status.textColor;
							if(place>400)
							{//veateade
								bbb=Color.blue;
							}
							if((mitu!=4)||(place>400)||((place>301)&&(place<318)))
							status.append("\n"+msg+"\n",bbb,status.jutt,status.buffer);
						
							if(!status.nupp.getForeground().equals(Color.blue))
							{//ei olnud aktiivne
								if(status.nupp.getForeground().equals(Color.black))
								{
									status.nupp.setForeground(Color.red);
								}
									
								if(status.beep.getState())
								{
									Toolkit.getDefaultToolkit().beep();
								}
							}
						}
						else
						{//teema  <server> 332 Nick #kanal :teema
							
							trellid=vastus.indexOf("#");
							koolon=vastus.indexOf(":");
							name=vastus.substring(trellid,koolon-1);
							for(i=0;i<channels.length;i++)
							{
								if(channels[i]!=null)
								{
									if(channels[i].name.compareTo(name)==0)
									{
										channels[i].append(teated[332]+vastus.substring(koolon+1)+"\n",modeColor,channels[i].jutt,channels[i].buffer);
										break;
									}
								}//kanali objekt ei olnud null
							}//for
						}//teema
					
					}//try
					catch(NumberFormatException e)
					{//oli mingi käsk
						edasi=true;
					}
				}
				
/*===================== MINGI TEGEVUS ==============================
Registreerimine	:		NICK, USER, PASS
Kanali operatsioonid:	MODE, KICK, PART, QUIT, JOIN, TOPIC, NAMES, LIST, INVITE, OPER
Informatsiooni käsud:	INFO, ADMIN, TRACE, VERSION, STATS, LINKS, TIME 
Vestluse käsud:			PRIVMSG, NOTICE
Useri põhised:			WHO, WHOIS, WHOWAS 
Muud võimalused:		ISON, WALLOPS, AWAY, ME
*/

				if(edasi)
				{//siia peaks jõudma PRIVMSG, NOTICE, JOIN, NICK, MODE, PART, QUIT, KICK, PING, PONG
					tyhik=vastus.indexOf(" ");
					abi=vastus.substring(tyhik+1);
					tyhik=abi.indexOf(" ");

					if(tyhik!=-1)
					{
						command=abi.substring(0,tyhik);//sain käsu kätte   <server> KÄSK blah
					}
					else
					{//PING :irc.estpak.ee
						command=vastus.substring(0,5);
					}
//System.out.println("Command="+command);
//status.append("Command="+command+"\n",Color.red,status.jutt,status.buffer);

					
					aeg="";

					if(command.compareTo("PRIVMSG")==0)
					{	//Nick!~andmed/mail PRIVMSG #kanal :tere
						//Nick!~andmed/mail PRIVMSG Nick2 :tere
						//Nick!~andmed/mail PRIVMSG #kanal :ACTION mõtleb
						
						trellid=vastus.indexOf("#");
						koolon=vastus.indexOf(" :")+1;
						hyyd=vastus.indexOf("!");
						nickk=vastus.substring(0,hyyd);//priva nimi

						if((trellid<koolon)&&(trellid!=-1))
						{//jutt kanalile
							name=vastus.substring(trellid,koolon-1);//kanali nimi
							for(i=0;i<channels.length;i++)
							{
								if(channels[i]!=null)
								{
									if(channels[i].name.compareTo(name)==0)
									{

										if(channels[i].nupp.timer.getState())
										{
											aeg=date();
										}

										if(vastus.substring(koolon+1).length()>8){
											abi=vastus.substring(koolon+1);

											if(abi.substring(0,8).compareTo("ACTION ")==0)
											{
												channels[i].append(aeg+"*** "+nickk+" "+vastus.substring(koolon+9,vastus.length()-1)+"\n",Color.magenta,channels[i].jutt,channels[i].buffer);	
											}
											else
											{
												channels[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",channels[i].textColor,channels[i].jutt,channels[i].buffer);
											}
										}
										else
										{
											channels[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",channels[i].textColor,channels[i].jutt,channels[i].buffer);
										}
										if(!channels[i].nupp.getForeground().equals(Color.blue))
										{
											if(channels[i].nupp.getForeground().equals(Color.black))
											{
												channels[i].nupp.setForeground(Color.red);
											}
									
											if(channels[i].beep.getState())
											{
												Toolkit.getDefaultToolkit().beep();
											}
										}
										if(channels[i].flash.getState())
										{
											//luba[0] - kas peaaken aktiivne (false --> ei ole aktiivne)
											//luba[1] - ega peaaken ei vilgu (true --> ei vilgu)
											Thread blinking=new blinking(peaaken,luba,channels[i],null);
											blinking.start();	
										}
										break;
									}
								}//kanali objekt ei olnud null
							}//for	
						}
						else
						{//priva
							for(i=0;i<privad.length;i++)
							{
								if(privad[i]!=null)
								{
									if(privad[i].name.compareTo(nickk)==0)
									{
										if(privad[i].nupp.timer.getState())
										{
											aeg=date();
										}

										if(vastus.substring(koolon+1).length()>8){
											abi=vastus.substring(koolon+1);

											if(abi.substring(0,8).compareTo("ACTION ")==0)
											{
												privad[i].append(aeg+"*** "+nickk+" "+vastus.substring(koolon+9,vastus.length()-1)+"\n",Color.magenta,privad[i].jutt,privad[i].buffer);	
											}
											else
											{
												privad[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);
											}
										}
										else
										{
											privad[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);
										}	

										if(!privad[i].nupp.getForeground().equals(Color.blue))
										{
											if(privad[i].nupp.getForeground().equals(Color.black))
											{
												privad[i].nupp.setForeground(Color.red);
											}
									
											if(privad[i].beep.getState())
											{
												Toolkit.getDefaultToolkit().beep();
											}
										}
										if(privad[i].flash.getState())
										{
											Thread blinking=new blinking(peaaken,luba,null,privad[i]);
											blinking.start();	
										}
										edasi=false;
										break;
									}
								}//kanali objekt ei olnud null
							}//for
							
							if(edasi)
							{//sinuga akustati priva
								for(i=0;i<privad.length;i++)
								{//otsin vaba koha
									if(privad[i]!=null)
									{
										if(privad[i].name.compareTo("")==0)
										{
											break;
										}
									}//kanali objekt ei olnud null
									else
									{
										break;
									}
								}//for
								privad[i]=new priva(nickk,boss);
								privad[i].nick=nick[0];
								privad[i].buffer=new TextBuffer();
								privad[i].textColor=status.textColor;
								privad[i].font=status.font;
								privad[i].nupp.setForeground(Color.red);
								privad[i].jutt.setBackground(status.jutt.getBackground());
								privad[i].in.setBackground(status.in.getBackground());
								privad[i].in.setForeground(status.in.getForeground());
								privad[i].in.setFont(status.in.getFont());
								privad[i].nupp.setBackground(buttonback);
								privat.add(privad[i].nupp);
								
								if(vastus.substring(koolon+1).length()>8){
									abi=vastus.substring(koolon+1);

									if(abi.substring(0,8).compareTo("ACTION ")==0)
									{
										privad[i].append(aeg+"*** "+nickk+" "+vastus.substring(koolon+9,vastus.length()-1)+"\n",Color.magenta,privad[i].jutt,privad[i].buffer);	
									}
									else
									{
										privad[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);
									}
								}
								else
								{
									privad[i].append(aeg+"<"+nickk+">"+vastus.substring(koolon+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);
								}	

								//privad[i].append("<"+nickk+">"+vastus.substring(koolon+1)+"\n",privad[i].textColor,privad[i].jutt,privad[i].buffer);
								privat.doLayout();
								privad[i].in.addActionListener(new inKuular(privad[i],null,status,channels,toserver,peaaken,privad,windows,boss,back));
								privad[i].nupp.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));
								privad[i].nupp.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
								windows.add(privad[i].win);
								privad[i].close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[i].nupp,windows,back));
								privad[i].choose.addActionListener(new nuppKuular(privad[i].nupp,status,channels,privad,peaaken));
								privad[i].beep.addItemListener(new piiksKuular(0,0,null,privad[i]));
								privad[i].nupp.beep.addItemListener(new piiksKuular(0,1,null,privad[i]));
								privad[i].flash.addItemListener(new piiksKuular(1,0,null,privad[i]));
								privad[i].nupp.flash.addItemListener(new piiksKuular(1,1,null,privad[i]));
								privad[i].timer.addItemListener(new piiksKuular(2,0,null,privad[i]));
								privad[i].nupp.timer.addItemListener(new piiksKuular(2,1,null,privad[i]));
								privad[i].log.addItemListener(new piiksKuular(3,0,null,privad[i]));
								privad[i].nupp.log.addItemListener(new piiksKuular(3,1,null,privad[i]));
								privad[i].desktop.addItemListener(new desktopKuular(0,null,privad[i],channels,privad,status,peaaken));
								privad[i].nupp.desktop.addItemListener(new desktopKuular(1,null,privad[i],channels,privad,status,peaaken));
								privad[i].desk.addWindowListener(new kanaliKuular(peaaken,privad[i],null,status,toserver,windows,back));
								Toolkit.getDefaultToolkit().beep();
								Thread blinking=new blinking(peaaken,luba,null,privad[i]);
								blinking.start();	
							}
						}//priva
					}
					else
					if(command.compareTo("NOTICE")==0)
					{
						hyyd=vastus.indexOf("!");
						tyhik=vastus.indexOf(" ");
						koolon=vastus.indexOf(" :")+1;
						if(tyhik<hyyd)
						{//server noticed
							status.append("\n"+vastus.substring(koolon+1)+"\n",status.textColor,status.jutt,status.buffer);
							if(!status.nupp.getForeground().equals(Color.blue))
							{//ei olnud aktiivne                                    
								if(status.nupp.getForeground().equals(Color.black)) 			
								{                                                   			
									status.nupp.setForeground(Color.red);           			
								}                                                   			
									                                                			
								if(status.beep.getState())                          			
								{                                                   			
									Toolkit.getDefaultToolkit().beep();             			
								}                                                   			
							}                                                       			
							privat.removeNotify();
							privat.addNotify();
						}
						else
						{
							nickk=vastus.substring(0,hyyd);

							Component[] jada=((Panel)peaaken.getComponent(0)).getComponents();                                                                                             								                                                                                                                                                                                                                                   
							String name2;                                                                                                                                    	
							                                                                                                                                                              	
							for(i=0;i<jada.length;i++)                     	    	                                                                                                      	
							{                                                  	    	                                                                                                  	
								if(jada[i].getForeground().equals(Color.blue)) 	    	                                                                                                  	
								{                                              	    	                                                                                                  	
									name2=((menuButton)jada[i]).getLabel();                                                                                                               	
									                                                                                                                                                      	
									if(name2.compareTo("Status")==0)                                                                                                                      	
									{                                                                	                                                                                  	
										if(status.nupp.timer.getState())                                                                                                                  	
										{                                                                                                                                                 	
											aeg=date();                                                                                                                                   	
										}                                                                                                                                                 	
										
										status.append(aeg+"<<<"+nickk+">>>"+vastus.substring(koolon+1)+"\n",Color.orange,status.jutt,status.buffer);                                   	                                  	             
									}                                                                	                                                                                  	
									else                                                             	                                                                                  	
									{                                                                	                                                                                  	
										trellid=name2.indexOf("#");                                  	                                                                                  	
										if(trellid!=-1)                                              	                                                                                  	
										{                                                            	                                                                                  	
											for(j=0;j<channels.length;j++)                           	                                                                                  	
											{                                                        	                                                                                  	
							    				if(channels[j]!=null)                                	                                                                                      
							    				{                                                    	                         				                                              
							    					if(channels[j].name.compareTo(name2)==0)         	                         				                                              
							    					{                                                	                          				                                              
														if(channels[j].nupp.timer.getState())                                                                                                 
														{                                                                                                                                 	
															aeg=date();                                                                                                                   	
														}                                                                                                                                 	
														channels[j].append(aeg+"<<<"+nickk+">>>"+vastus.substring(koolon+1)+"\n",Color.orange,channels[j].jutt,channels[j].buffer);    				                                  	
							    						break;                                       	                                                                                  	
							    					}                                                	                         				                                              
							    				}                                                    	           	         				                                                  
											}//for                                                   	                                                                                  	
										}                                                            	                                                                                  	
										else                                                         	                                                                                  	
										{                                                            	                                                                                  	
											for(j=0;j<privad.length;j++)                             	                                                                                  	
											{                                                        	                                                                                  	
							    				if(privad[j]!=null)                                  	                                                                                      
							    				{                                                    	                       				                                                  
							    					if(privad[j].name.compareTo(name2)==0)           	                         				                                              
							    					{                                                	                        				                                              
														if(privad[j].nupp.timer.getState())                                                                                                   
														{                                                                                                                                 	
															aeg=date();                                                                                                                   	
														}                                                                                                                                 	
														privad[j].append(aeg+"<<<"+nickk+">>>"+vastus.substring(koolon+1)+"\n",Color.orange,privad[j].jutt,privad[j].buffer);	                                                  	
							    						break;                                       	                                                                                  	
							    					}                                                	                         				                                              
							    				}                                                    	            	         				                                              
											}//for                                                   	                                                                                  	
										}//else                                                                                                                                           	
									}//else                                                                                                                                               	
								}//if blue                                                                                                                                                	
							}//for 
						}//else
					}
					else
					if(command.compareTo("JOIN")==0)
					{
						hyyd=vastus.indexOf("!");
						nickk=vastus.substring(0,hyyd);//kes yhineb
						trellid=vastus.indexOf("#");
						name=vastus.substring(trellid);//kanal, millega yhineti
							
						if(nick[0].compareTo(nickk)==0)                                                          
						{//ise yhinesid kanaliga    				
							String name2;                                                                        	
						                                                                                         
							Component[] jada=privat.getComponents();                                             	
							for(i=0;i<jada.length;i++)                     	    	                             	
							{                                                  	    	                         	      
								if(jada[i].getForeground().equals(Color.blue)) 	    	                         	      
								{                                              	    	                         	      
									name2=((menuButton)jada[i]).getLabel();                                      	      
									                                                                             	
									if(name2.compareTo("Status")==0)                                             	                                 
									{                                                                            	                                 	
										status.setVisible(false);                                                	                                 	                                                                             
									}                                                                            	                                 	
									else                                                                         	                                 	
									{                                                                            	                                 	
										trellid=name2.indexOf("#");                                              	                                 	
										if(trellid!=-1)                                                          	                                 	
										{                                                                        	                                 	
											for(j=0;j<channels.length;j++)                                       	                                 	
		                    				{                                                                                                         	
		                        				if(channels[j]!=null)                                                     				              	
		                        				{                                                                         				              	
		                        					if(channels[j].name.compareTo(name2)==0)                               				              	
		                        					{                                                                     				              	              
														channels[j].setVisible(false);                           	                                 	
		                        						break;                                                            				              	
		                        					}                                                                     				              	
							    				}                                                               		                              	
											}//for                                                               	                                 	
										}                                                                        	                                 	
										else                                                                     	                                 	
										{                                                                        	                                 	
											for(j=0;j<privad.length;j++)                                         	                                 	
		                    				{                                                                                                         	
		                        				if(privad[j]!=null)                                                     				              	
		                        				{                                                                         				              	
		                        					if(privad[j].name.compareTo(name2)==0)                               				              	
		                        					{                                                                     				              	                
														privad[j].setVisible(false);                             	                                 	
		                        						break;                                                            				              	
		                        					}                                                                     				              	
							    				}                                                                		                              	
											}//for                                                               	                                 	
										}//else                                                                  	
									}//else	                                                                     	          
									jada[i].setForeground(Color.black);
									break;                                     	    			                 	      
								}//if blue                                              	    	             	                  
							}//for                                                                               	           
						                                                                                         
							for(i=0;i<channels.length;i++)                                                       	
							{//otsin vaba koha                                                                   	
								if(channels[i]!=null)                                                            	
								{                                                                                	
									if(channels[i].name.compareTo("")==0)                                        	
									{                                                                            	
										break;                                                                   	
									}                                                                            	
								}//kanali objekt ei olnud null                                                   	
								else                                                                             	
								{                                                                                	
									break;                                                                       	
								}                                                                                	
							}//for                                                                               	                                                                                        
							channels[i]=new channel(name,boss); 
							channels[i].buffer=new TextBuffer();
							channels[i].textColor=status.textColor;

							channels[i].font=status.font;
							channels[i].in.addActionListener(new inKuular(null,channels[i],status,channels,toserver,peaaken,privad,windows,boss,back));
							channels[i].nupp.addActionListener(new nuppKuular(channels[i].nupp,status,channels,privad,peaaken));
						    channels[i].nupp.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,channels[i].nupp,windows,back));
                                                                                     
							channels[i].nick=nick[0];
							if(forcedMsg.compareTo("")!=0)
							{
								channels[i].append("\n  "+forcedMsg+"\n\n",modeColor,channels[i].jutt,channels[i].buffer);
							}
							channels[i].append("*** Ühinesid kanaliga "+name+"\n",modeColor,channels[i].jutt,channels[i].buffer);
						                                                                                         
							channels[i].nupp.setForeground(Color.blue);
							channels[i].nupp.setBackground(buttonback);
							privat.add(channels[i].nupp);                                                        	
							channels[i].in.setText("");                                                          	
							channels[i].jutt.setBackground(status.jutt.getBackground());
							channels[i].in.setBackground(status.in.getBackground());
							channels[i].in.setForeground(status.in.getForeground());
							channels[i].in.setFont(status.in.getFont());
							channels[i].list.setBackground(status.in.getBackground());
							channels[i].list.setForeground(status.in.getForeground());
							channels[i].list.setFont(status.in.getFont());
							peaaken.add(channels[i],-1);                                                         	
							
							privat.doLayout();
							peaaken.show();

							channels[i].list.whois.addActionListener(new whoKuular(toserver,1,channels,status,peaaken));//kes on
							channels[i].list.ignore.addActionListener(new whoKuular(toserver,0,channels,status,peaaken));//ignoreerin
							channels[i].list.unignore.addActionListener(new whoKuular(toserver,2,channels,status,peaaken));//aktsepteeri
							channels[i].list.kick.addActionListener(new whoKuular(toserver,3,channels,status,peaaken));//kick
							channels[i].list.ban.addActionListener(new whoKuular(toserver,4,channels,status,peaaken));//ban
							channels[i].list.op.addActionListener(new whoKuular(toserver,5,channels,status,peaaken));//op
							channels[i].list.deop.addActionListener(new whoKuular(toserver,6,channels,status,peaaken));//un op
						
							windows.add(channels[i].win);
							channels[i].close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,channels[i].nupp,windows,back));
							channels[i].choose.addActionListener(new nuppKuular(channels[i].nupp,status,channels,privad,peaaken));
							channels[i].beep.addItemListener(new piiksKuular(0,0,channels[i],null));
							channels[i].nupp.beep.addItemListener(new piiksKuular(0,1,channels[i],null));
							channels[i].flash.addItemListener(new piiksKuular(1,0,channels[i],null));
							channels[i].nupp.flash.addItemListener(new piiksKuular(1,1,channels[i],null));
							channels[i].timer.addItemListener(new piiksKuular(2,0,channels[i],null));
							channels[i].nupp.timer.addItemListener(new piiksKuular(2,1,channels[i],null));
							channels[i].log.addItemListener(new piiksKuular(3,0,channels[i],null));
							channels[i].nupp.log.addItemListener(new piiksKuular(3,1,channels[i],null));
							channels[i].list.addActionListener(new privaOKKuular(toserver,peaaken,channels,privad,status,null,null,windows,channels[i].list,boss,back));
							channels[i].list.chat.addActionListener(new privaOKKuular(toserver,peaaken,channels,privad,status,null,null,windows,channels[i].list,boss,back));
							channels[i].desktop.addItemListener(new desktopKuular(0,channels[i],null,channels,privad,status,peaaken));
							channels[i].nupp.desktop.addItemListener(new desktopKuular(1,channels[i],null,channels,privad,status,peaaken));
							channels[i].desk.addWindowListener(new kanaliKuular(peaaken,null,channels[i],status,toserver,windows,back));
						}                                                                                        	
						else                                                                                     	
						{                                                                                        	
							for(i=0;i<channels.length;i++)                                                       	
							{                                                                                    	
								if(channels[i]!=null)                                                            	
								{                                                                                	
									if(channels[i].name.compareTo(name)==0)                                      	
									{                                                                            	
										if(channels[i].nupp.timer.getState())                                    	
										{                                                                        	
											aeg=date();                                                          	
										}                                                                        	
										                                                                         	
										channels[i].append(aeg+"*** "+nickk+" ühines kanaliga\n",modeColor,channels[i].jutt,channels[i].buffer);          	
										//channels[i].jutt.setRichText(createRichText(aeg+"*** "+nickk+" ühines kanaliga\n","green",channels[i].buffer));
										channels[i].list.add(nickk);	                                         	
										if(!channels[i].nupp.getForeground().equals(Color.blue))
										{
											if(channels[i].nupp.getForeground().equals(Color.black))
											{
												channels[i].nupp.setForeground(Color.red);
											}
									
											if(channels[i].beep.getState())
											{
												Toolkit.getDefaultToolkit().beep();
											}
										}
										if(channels[i].flash.getState())
										{
											Thread blinking=new blinking(peaaken,luba,channels[i],null);
											blinking.start();	
										}
										break;                                                                   	
									}                                                                            	
								}//kanali objekt ei olnud null                                                   	
							}//for                                                                               	
						}                                                                                        	
					}
					else
					if(command.compareTo("MODE")==0)
					{/* Nick!~andmed/mail MODE #kanal +/-parameeter)
											 Channel modes                                    
								------------------------                                      
							ModeChar	Effects on channels                                   
							~~~~~~~~	~~~~~~~~~~~~~~~~~~~                                   
							b <person>	ban somebody, <person> in "nick!user@host" form       
							i		channel is invite-only                                    
						                                                                      
						l <number>	channel is limited, <number> users allowed max            
							m		channel is moderated, (only chanops can talk)             
							n		external /MSGs to channel are not allowed                 
							o <nickname>	makes <nickname> a channel operator               
							p		channel is private                                        
							s		channel is secret                                         
							t		topic limited, only chanops may change it                 
							k <key>		set secret key for a channel                          
						                                                                      
								User modes                                                    
								-------------------                                           
							ModeChar	Effects on nicknames                                  
							~~~~~~~~	~~~~~~~~~~~~~~~~~~~~                                  
							i		makes you invisible to anybody that does                  
									not know the exact spelling of your nickname              
							o		IRC-operator status, can only be set                      
									by IRC-ops with OPER                                      
							s		receive server notices                                    
							v		gives a user a voice on a moderated channel               
						*/
							
							trellid=vastus.indexOf("#");
							if(trellid!=-1)
							{//tammer MODE tammer :+i   sellistel juhtudel pole mõtet sisse minna
								hyyd=vastus.indexOf("!");                                                                                                                                                                      
								nickk=vastus.substring(0,hyyd);                                                                                                                                                                
								abi=vastus.substring(trellid);                                                                                                                                                                 
								tyhik=abi.indexOf(" ");                                                                                                                                                                        
								name=abi.substring(0,tyhik);//kanal                                                                                                                                                            
									                                                                                                                                                                                           
								for(i=0;i<channels.length;i++)                                                                                                                                                                 
								{//saan teada koha                                                                                                                                                                             
									if(channels[i]!=null)                                                                                                                                                                      
									{                                                                                                                                                                                          
										if(channels[i].name.compareTo(name)==0)                                                                                                                                                
										{                                                                                                                                                                                      
											break;                                                                                                                                                                             
										}                                                                                                                                                                                      
									}//kanali objekt ei olnud null                                                                                                                                                             
								}//for                                                                                                                                                                                         
								                                                                                                                                                                                               
								if(channels[i].nupp.timer.getState())                                                                                                                                                          
								{                                                                                                                                                                                              
									aeg=date();                                                                                                                                                                                
								}                                                                                                                                                                                              
								                                                                                                                                                                                               
								channels[i].append(aeg+"***"+nickk+" pani MODE "+abi.substring(tyhik+1)+"\n",modeColor,channels[i].jutt,channels[i].buffer);                                                                                                                  
								//channels[i].jutt.setRichText(createRichText(aeg+nickk+" pani MODE "+abi.substring(tyhik+1)+"\n","green",channels[i].buffer));

								if(!channels[i].nupp.getForeground().equals(Color.blue))
								{
									if(channels[i].nupp.getForeground().equals(Color.black))
									{
										channels[i].nupp.setForeground(Color.red);
									}
								
									if(channels[i].beep.getState())
									{
										Toolkit.getDefaultToolkit().beep();
									}
								}
								if(channels[i].flash.getState())
								{
									Thread blinking=new blinking(peaaken,luba,channels[i],null);
									blinking.start();	
								}
								tyhik=vastus.indexOf("+");
	
								if(tyhik==-1)                                                                                                                                                                                  
								{//alguses on IP de vahel miinused                                                                                                                                                                                              
									tyhik=vastus.indexOf(" -")+1;                                                                                                                                                                 
									edasi=false;                                                                                                                                                                               
								}                                                                                                                                                                                              
								abi=vastus.substring(tyhik+1,tyhik+2);//MODE parameeter                                                                                                                                        
							                                                                                                                                                                                               
								if((abi.compareTo("o")==0)||(abi.compareTo("v")==0)||(abi.compareTo("b")==0))                                                                                                                  
								{	                                                                                                                                                                                           
									abi2=vastus.substring(tyhik+1); //(xxx nimi1 nimi2 nimi3 )                                                                                                                                    
									tyhik=abi2.indexOf(" ");                                                                                                                                                                   
									abi2=abi2.substring(tyhik+1);//nimede loetelu                                                                                                                                              
									elemente=channels[i].list.getItemCount();                                                                                                                                                  
								                                                                                                                                                                                           
									for(ii=0;ii<tyhik;ii++)                                                                                                                                                                    
									{//teen iga nime korral läbi                                                                                                                                                               
										tyhik2=abi2.indexOf(" ");                                                                                                                                                              
										if(tyhik2==-1)                                                                                                                                                                         
										{//viimane nimi                                                                                                                                                                        
											nickk=abi2;                                                                                                                                                                        
										}                                                                                                                                                                                      
										else                                                                                                                                                                                   
										{                                                                                                                                                                                      
											nickk=abi2.substring(0,tyhik2);                                                                                                                                                    
											abi2=abi2.substring(tyhik2+1);                                                                                                                                                     
										}                                                                                                                                                                                      
								                                                                                                                                                                                               
										for(j=0;j<elemente;j++)                                                                                                                                                                
										{                                                                                                                                                                                      
											name=channels[i].list.getItem(j);  
											if((name.compareTo(nickk)==0)||                                                                                                                                                    
											(name.compareTo("@"+nickk)==0)||                                                                                                                                                   
											(name.compareTo("+"+nickk)==0))                                                                                                                                                    
											{//leiti isik                                                                                                                                                                      
												if(abi.compareTo("o")==0)                                                                                                                                                      
												{                                                                                                                                                                              
													if(edasi)                                                                                                                                                                  
													{                                                                                                                                                                          
														channels[i].list.replaceItem("@"+nickk,j);                                                                                                                             
													}                                                                                                                                                                          
													else                                                                                                                                                                       
													{                                                                                                                                                                          
														if(name.compareTo("@"+nickk)==0)
														{
															channels[i].list.replaceItem(nickk,j);                                                                                                                                 
														}
													}                                                                                                                                                                          
												}//o                                                                                                                                                                           
												else                                                                                                                                                                           
												if(abi.compareTo("v")==0)                                                                                                                                                      
												{                                                                                                                                                                              
													if(edasi)                                                                                                                                                                  
													{                                                                                                                                                                          
														channels[i].list.replaceItem("+"+nickk,j);                                                                                                                             
													}                                                                                                                                                                          
													else                                                                                                                                                                       
													{                                                                                                                                                                          
														if(name.compareTo("+"+nickk)==0)
														{//muidu võib -o ka + eest kustutada
															channels[i].list.replaceItem(nickk,j);                                                                                                                                 
														}                                                                                                                                 
													}                                                                                                                                                                          
												}//v                                                                                                                                                                           
												else                                                                                                                                                                           
												if(abi.compareTo("b")==0)                                                                                                                                                      
												{                                                                                                                                                                              
													if(edasi)                                                                                                                                                                  
													{                                                                                                                                                                          
														channels[i].list.remove(j);                                                                                                                                            
													}                                                                                                                                                                          
													if(nickk.compareTo(nick[0])==0)                                                                                                                                               
													{//sina ise                                                                                                                                                                
														channels[i].append("\nSind banniti sellelt kanalilt, sa ei saa sinna enne naasta, kui keegi võtab pandud MODE maha.\n",Color.red,channels[i].jutt,channels[i].buffer);                                          
													}                                                                                                                                                                          
												}//b                                                                                                                                                                           
												break;                                                                                                                                                                         
											}                                                                                                                                                                                  
										}                                                                                                                                                                                      
									}//mitu nime                                                                                                                                                                               
								}//o, v, b
							}//if trellid
							else
							{
								if(mitu!=4)
								{
									status.append("\n"+vastus+"\n",modeColor,status.jutt,status.buffer);
									if(!status.nupp.getForeground().equals(Color.blue))
									{
										if(status.nupp.getForeground().equals(Color.black))
										{
											status.nupp.setForeground(Color.red);
										}
										
										if(status.beep.getState())
										{
											Toolkit.getDefaultToolkit().beep();
										}
									}
								}

								if(!first)
								{//oled õigustega IRCus
									first=true;
									if(mitu==4)
									{
										toserver.println("ARCO "+nick[0]);
										vastus=fromserver.readLine();
										if(vastus.compareTo(".")==0)
										{//töötaja
											try
											{
												//BufferedReader sisend = new BufferedReader (new FileReader (boss.getCodeBase()+"Klient.txt"));
												InputStream sisend=new URL(boss.getCodeBase()+"worker.txt").openConnection().getInputStream();
												int rida=1;
												byte[] array=new byte[0];
												byte[] array2;
												
												while(rida>0)
												{
													array2=array;
													array=new byte[array2.length+1];
													for(i=0;i<array2.length;i++) 
													{
														array[i]=array2[i]; 
													}
													rida=sisend.read();
													array[i]=(new Integer(rida)).byteValue();
												}
												status.append(new String(array),Color.black,status.jutt,status.buffer);
												sisend.close();
											}//try
											catch (IOException e) 
											{
											}

											commands.add(canSee);
											commands.add(add);
											commands.add(erase);
											commands.remove(join);
											arcomees=true;

											erase.addActionListener(new ActionListener(){
												public void actionPerformed(ActionEvent event){
													toserver.println("ANNA");
												}
											});

											add.addActionListener(new addKuular(toserver,peaaken,back));

											canSee.addItemListener(new ItemListener()
											{
												public void itemStateChanged(ItemEvent e)
												{
													if(canSee.getState())
													{
														toserver.println("1111 "+nick[0]);	
													}
													else
													{
														toserver.println("0000 "+nick[0]);	
													}
												}
											});
										}
										else
										{//klient
											try
											{
												//BufferedReader sisend = new BufferedReader (new FileReader (boss.getCodeBase()+"Klient.txt"));
												InputStream sisend=new URL(boss.getCodeBase()+"Klient.txt").openConnection().getInputStream();
												int rida=1;
												byte[] array=new byte[0];
												byte[] array2;
												
												while(rida>0)
												{
													array2=array;
													array=new byte[array2.length+1];
													for(i=0;i<array2.length;i++) 
													{
														array[i]=array2[i]; 
													}
													rida=sisend.read();
													array[i]=(new Integer(rida)).byteValue();
												}
												status.append(new String(array),Color.black,status.jutt,status.buffer);
												sisend.close();
											}//try
											catch (IOException e) 
											{
											}
											commands.remove(join);
											commands.remove(kick);
											commands.remove(ban);
											commands.remove(unban);
											commands.remove(chat);
											commands.remove(topic);

											workers=new menuList();
											workers.popups.remove(workers.op);
											workers.popups.remove(workers.deop);
											workers.popups.remove(workers.chat);
											workers.popups.remove(workers.kick);
											workers.popups.remove(workers.ban);
											workers.setBackground(back2);
											workers.setFont(font);
											workers.setForeground(fore);

											vastus=fromserver.readLine();
											while(vastus.compareTo(".")!=0)
											{
												workers.add(vastus);
												vastus=fromserver.readLine();
											}
											status.workers=workers;
											status.add(status.workers,"East");
											status.in.setVisible(false);
											
											peaaken.show();
											
											workers.whois.addActionListener(new whoKuular(toserver,1,channels,status,peaaken));//kes on
											workers.ignore.addActionListener(new whoKuular(toserver,0,channels,status,peaaken));//ignoreerin
											workers.unignore.addActionListener(new whoKuular(toserver,2,channels,status,peaaken));//aktsepteeri
											workers.addActionListener(new privaOKKuular(toserver,peaaken,channels,privad,status,null,null,windows,workers,boss,back));

										}
									}

									msg=vastus;
									if(arcomees)
									{
										toserver.println("JOIN #ARCO arx");
									}
									if(forcedChannel.compareTo("")!=0)
									{//oli ette antud kanal, millega peab yhinema
										toserver.println("JOIN "+forcedChannel);
									}
									if(forcedPriva.compareTo("")!=0)
									{//ette antud priva, millega peab kohe liituma
										privad[0]=new priva(forcedPriva,boss);                                    		                                                   
										privad[0].nick=nick[0];
										privad[0].buffer=new TextBuffer();
										privad[0].textColor=status.textColor;
										privad[0].font=status.font;
										if(forcedMsg.compareTo("")!=0)
										{
											privad[0].append("\n  "+forcedMsg+"\n\n",modeColor,privad[0].jutt,privad[0].buffer);
										}
										privad[0].append("*** Alustasid priva isikuga "+forcedPriva+"\n",modeColor,privad[0].jutt,privad[0].buffer);                                                             
										
										status.nupp.setForeground(Color.black);                                                                                                                       
										privad[0].nupp.setForeground(Color.blue); 
										privad[0].nupp.setBackground(buttonback);
										privat.add(privad[0].nupp);                                                                 
										privad[0].in.addActionListener(new inKuular(privad[0],null,status,channels,toserver,peaaken,privad,windows,boss,back));                                                                        
										privad[0].nupp.addActionListener(new nuppKuular(privad[0].nupp,status,channels,privad,peaaken));                       
										windows.add(privad[0].win);
										privad[0].nupp.close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[0].nupp,windows,back));
										privad[0].close.addActionListener(new closeKuular(peaaken,status,channels,privad,toserver,privad[0].nupp,windows,back));
										privad[0].choose.addActionListener(new nuppKuular(privad[0].nupp,status,channels,privad,peaaken));                                                                                       
										privad[0].jutt.setBackground(back2);
										privad[0].in.setBackground(back2);
										privad[0].in.setForeground(fore);
										privad[0].in.setFont(font);
										peaaken.add(privad[0],-1);
										privad[0].beep.addItemListener(new piiksKuular(0,0,null,privad[0]));
										privad[0].nupp.beep.addItemListener(new piiksKuular(0,1,null,privad[0]));
										privad[0].flash.addItemListener(new piiksKuular(1,0,null,privad[0]));
										privad[0].nupp.flash.addItemListener(new piiksKuular(1,1,null,privad[0]));
										privad[0].timer.addItemListener(new piiksKuular(2,0,null,privad[0]));
										privad[0].nupp.timer.addItemListener(new piiksKuular(2,1,null,privad[0]));
										privad[0].log.addItemListener(new piiksKuular(3,0,null,privad[0]));
										privad[0].nupp.log.addItemListener(new piiksKuular(3,1,null,privad[0]));
										privad[0].desktop.addItemListener(new desktopKuular(0,null,privad[0],channels,privad,status,peaaken));
										privad[0].nupp.desktop.addItemListener(new desktopKuular(1,null,privad[0],channels,privad,status,peaaken));
										privad[0].desk.addWindowListener(new kanaliKuular(peaaken,privad[0],null,status,toserver,windows,back));
																																					  
										//Netscape 4.7 ei toeta repainti()                                                                                     
										privat.doLayout();//kuvab lisatud nupud                                                               
										privat.removeNotify();
										privat.addNotify();//repaindi eest                                                                    
										peaaken.show();																														   

									}
								}//first
							}
					}
					else
					if(command.compareTo("NICK")==0)
					{//Nick!~andmed/mail NICK :Nick2
						hyyd=vastus.indexOf("!");
						koolon=vastus.indexOf(" :")+1;
						nickk=vastus.substring(0,hyyd);//vana nimi
						name=vastus.substring(koolon+1);//uus nimi

						for(i=0;i<channels.length;i++)
							{
								if(channels[i]!=null)
								{
									elemente=channels[i].list.getItemCount();
									if(channels[i].nupp.timer.getState())
									{
										aeg=date();
									}

									for(j=0;j<elemente;j++)
									{
										abi=channels[i].list.getItem(j);
										if(abi.compareTo(nickk)==0)
										{
											channels[i].list.replaceItem(name,j);
											channels[i].append(aeg+"*** "+nickk+" nimeks on nüüd "+name+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											
											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											}
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
										else
										if(abi.compareTo("@"+nickk)==0)
										{
											channels[i].list.replaceItem("@"+name,j);
											channels[i].append(aeg+"*** "+nickk+" nimeks on nüüd "+name+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											
											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											}
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
										else
										if(abi.compareTo("+"+nickk)==0)
										{//leiti isik
											channels[i].list.replaceItem("+"+name,j);
											channels[i].append(aeg+"*** "+nickk+" nimeks on nüüd "+name+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											//channels[i].jutt.setRichText(createRichText(aeg+"*** "+nickk+" nimeks on nüüd "+name+"\n","green",channels[i].buffer));

											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											}
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
									}//for elemente
									if(nick[0].compareTo(nickk)==0)
									{//ise muutsid
										channels[i].nick=name;
									}
								}//kanali objekt ei olnud null
							}//for

							if(nick[0].compareTo(nickk)==0)
							{//ise muutsid
								nick[0]=name;
								status.nick=name;
								//status.jutt.setRichText(createRichText("\n*** Muutsid nime, uus nimi on "+name+"\n","green",status.buffer));
								status.append("\n*** Muutsid nime, uus nimi on "+name+"\n",modeColor,status.jutt,status.buffer);
								if(!status.nupp.getForeground().equals(Color.blue))
								{
									if(status.nupp.getForeground().equals(Color.black))
									{
										status.nupp.setForeground(Color.red);
									}
								
									if(status.beep.getState())
									{
										Toolkit.getDefaultToolkit().beep();
									}
								}   
								for(i=0;i<privad.length;i++)
								{
									if(privad[i]!=null)
									{
										privad[i].nick=name;
									}
								}
							}
					}
					else
					if(command.compareTo("KICK")==0)
					{//Nick!~andmed/mail KICK #kanal Nick2 :põhjus
						hyyd=vastus.indexOf("!");
						nickk=vastus.substring(0,hyyd);//kickija
						trellid=vastus.indexOf("#");
						abi=vastus.substring(trellid);
						tyhik=abi.indexOf(" ");
						name=abi.substring(0,tyhik);//kanal
						koolon=abi.indexOf(" :")+1;

						if(koolon==-1)
						{//kickimise põhjust polnud
							abi2=abi.substring(tyhik+1);//kickitav
						}
						else
						{
							abi2=abi.substring(tyhik+1,koolon-1);//kickitav
						}

						for(i=0;i<channels.length;i++)
						{//saan teada koha
							if(channels[i]!=null)
							{
								if(channels[i].name.compareTo(name)==0)
								{
									if(channels[i].nupp.timer.getState())
									{
										aeg=date();
									}	
									channels[i].append(aeg+" ***"+nickk+" kickis "+abi2+" välja.\n",modeColor,channels[i].jutt,channels[i].buffer);	
									
									if(!channels[i].nupp.getForeground().equals(Color.blue))
									{
										if(channels[i].nupp.getForeground().equals(Color.black))
										{
											channels[i].nupp.setForeground(Color.red);
										}
								
										if(channels[i].beep.getState())
										{
											Toolkit.getDefaultToolkit().beep();
										}
									}  
									if(channels[i].flash.getState())
									{
										Thread blinking=new blinking(peaaken,luba,channels[i],null);
										blinking.start();	
									}
									elemente=channels[i].list.getItemCount();
									for(j=0;j<elemente;j++)
									{
										name=channels[i].list.getItem(j);
										if(name.compareTo(abi2)==0)
										{
											channels[i].list.remove(j);
											break;
										}
										else
										if(name.compareTo("@"+abi2)==0)
										{
											channels[i].list.remove(j);
											break;
										}
										else
										if(name.compareTo("+"+abi2)==0)
										{//leiti isik
											channels[i].list.remove(j);
											break;
										}
									}//for elemente
								}
							}//kanali objekt ei olnud null
						}//for
						if(nick[0].compareTo(abi2)==0)
						{//ise olid kickitav
							channels[i].append("\nUps, paistab, et sind kickiti välja, naasmiseks ühine uuesti kanaliga.\n",Color.red,channels[i].jutt,channels[i].buffer);
						}
					}
					else
					if(command.compareTo("PART")==0)
					{//Nick!~andmed/mail PART #kanal :Nick
						hyyd=vastus.indexOf("!");
						nickk=vastus.substring(0,hyyd);
						trellid=vastus.indexOf("#");
						koolon=vastus.indexOf(" :")+1;
						name=vastus.substring(trellid,koolon-1);

						for(i=0;i<channels.length;i++)
						{//saan teada koha
							if(channels[i]!=null)
							{
								if(channels[i].name.compareTo(name)==0)
								{
									if(channels[i].nupp.timer.getState())
									{	
										aeg=date();
									}
									
									elemente=channels[i].list.getItemCount();
									for(j=0;j<elemente;j++)
									{
										name=channels[i].list.getItem(j);
										if(name.compareTo(nickk)==0)
										{
											channels[i].list.remove(j);
											break;
										}
										else
										if(name.compareTo("@"+nickk)==0)
										{
											channels[i].list.remove(j);
											break;
										}
										else
										if(name.compareTo("+"+nickk)==0)
										{//leiti isik
											channels[i].list.remove(j);
											break;
										}
									}//for elemente

									channels[i].append(aeg+"***"+nickk+" lahkus kanalilt\n",modeColor,channels[i].jutt,channels[i].buffer);
									
									if(!channels[i].nupp.getForeground().equals(Color.blue))
									{
										if(channels[i].nupp.getForeground().equals(Color.black))
										{
											channels[i].nupp.setForeground(Color.red);
										}
								
										if(channels[i].beep.getState())
										{
											Toolkit.getDefaultToolkit().beep();
										}
									}
									if(channels[i].flash.getState())
									{
										Thread blinking=new blinking(peaaken,luba,channels[i],null);
										blinking.start();	
									}
									break;
								}
							}//kanali objekt ei olnud null
						}//for
							
						if(nick[0].compareTo(nickk)==0)
						{//ise lõpetasid
							channels[i].name="";
							if(!channels[i].desktop.getState())
							{
								peaaken.remove(channels[i]);
								privat.remove(channels[i].nupp);
								if(channels[i].nupp.getForeground().equals(Color.blue))
								{
									status.nupp.setForeground(Color.blue);
									status.setVisible(true);
								}
								privat.doLayout();
								privat.removeNotify();
								privat.addNotify();
								peaaken.show();
							}
						}
					}
					else
					if(command.compareTo("QUIT")==0)
					{
						hyyd=vastus.indexOf("!");
						koolon=vastus.indexOf(" :")+1;
						
						nickk=vastus.substring(0,hyyd);
						abi="";
						if(vastus.length()>koolon+2)
						{
							abi=vastus.substring(koolon);//põhjus, kui on					
						}

						for(i=0;i<channels.length;i++)
							{
								if(channels[i]!=null)
								{
									elemente=channels[i].list.getItemCount();
									if(channels[i].nupp.timer.getState())
									{
										aeg=date();
									}

									for(j=0;j<elemente;j++)
									{
										name=channels[i].list.getItem(j);
										if(name.compareTo(nickk)==0)
										{
											channels[i].list.remove(j);
											
											channels[i].append(aeg+"*** "+nickk+" väljus IRCust "+abi+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											}
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
										else
										if(name.compareTo("@"+nickk)==0)
										{
											channels[i].list.remove(j);
											channels[i].append(aeg+"*** "+nickk+" väljus IRCust "+abi+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											
											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											}
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
										else
										if(name.compareTo("+"+nickk)==0)
										{//leiti isik
											channels[i].list.remove(j);
											channels[i].append(aeg+"*** "+nickk+" väljus IRCust "+abi+"\n",modeColor,channels[i].jutt,channels[i].buffer);
											
											if(!channels[i].nupp.getForeground().equals(Color.blue))
											{
												if(channels[i].nupp.getForeground().equals(Color.black))
												{
													channels[i].nupp.setForeground(Color.red);
												}
								
												if(channels[i].beep.getState())
												{
													Toolkit.getDefaultToolkit().beep();
												}
											} 
											if(channels[i].flash.getState())
											{
												Thread blinking=new blinking(peaaken,luba,channels[i],null);
												blinking.start();	
											}
											break;
										}
									}//for elemente
								}//kanali objekt ei olnud null
							}//for

							if(nick[0].compareTo(nickk)==0)
							{//ise väljusid
								s.close();
								try
								{
									this.interrupt();
								}
								catch(java.lang.SecurityException ee)
								{
									try
									{
										this.destroy();
									}
									catch(java.lang.NoSuchMethodError eee)
									{
										System.gc();
									}
								}
							}
					}
					else
					if(command.compareTo("PONG")==0)
					{
						status.append(vastus.substring(vastus.indexOf(" "))+"\n",modeColor,status.jutt,status.buffer);
						
						if(!status.nupp.getForeground().equals(Color.blue))
						{
							if(status.nupp.getForeground().equals(Color.black))
							{
								status.nupp.setForeground(Color.red);
							}
								
							if(status.beep.getState())
							{
								Toolkit.getDefaultToolkit().beep();
							}
						}   
					}
					else
					if(command.compareTo("TOPIC")==0)
					{//Wahvel!~tammer@80-235-61-241-dsl.trt.estpak.ee TOPIC #meie3 :: shallallalal
						trellid=vastus.indexOf("#");
						koolon=vastus.indexOf(" :")+1;
						hyyd=vastus.indexOf("!");
						nickk=vastus.substring(0,hyyd);
						name=vastus.substring(trellid,koolon-1);

						for(i=0;i<channels.length;i++)
							{//saan teada koha
								if(channels[i]!=null)
								{
									if(channels[i].name.compareTo(name)==0)
									{
										if(channels[i].nupp.timer.getState())
										{
											aeg=date();
										}
										channels[i].append(aeg+"*** "+nickk+" muutis teemat: "+vastus.substring(koolon+1)+"\n",modeColor,channels[i].jutt,channels[i].buffer);	
										
										if(!channels[i].nupp.getForeground().equals(Color.blue))
										{
											if(channels[i].nupp.getForeground().equals(Color.black))
											{
												channels[i].nupp.setForeground(Color.red);
											}
								
											if(channels[i].beep.getState())
											{
												Toolkit.getDefaultToolkit().beep();
											}
										}
										if(channels[i].flash.getState())
										{
											Thread blinking=new blinking(peaaken,luba,channels[i],null);
											blinking.start();	
										}
										break;
									}
								}//kanali objekt ei olnud null
							}//for
					}
					else
					if(command.compareTo("PING ")==0)
					{//PING
						trellid=vastus.indexOf(" ");
						toserver.println("PONG "+vastus.substring(trellid+2));
					}
					else
					if(command.compareTo(":Closing")==0)
					{//siia jõuab ainult Netscape 4.7
						aeg=date();
						status.append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,status.jutt,status.buffer);                  

						status.connect=false;                                                           
						for(i=0;i<privad.length;i++)                                                    
						{                                                                               
							if(privad[i]!=null)                                                         
							{                                                                           
								if(privad[i].name.compareTo("")!=0)                                     
								{                                                                       
									privad[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,privad[i].jutt,privad[i].buffer);   
								}                                                                       
							}                                                                           
						}                                                                               
						                                                                                
						for(i=0;i<channels.length;i++)                                                  
						{                                                                               
							if(channels[i]!=null)                                                       
							{                                                                           
								if(channels[i].name.compareTo("")!=0)                                   
								{  
									channels[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,channels[i].jutt,channels[i].buffer);		
								}                                                                       
							}                                                                           
						}                                                                               
						s.close();     
						try
						{
							this.interrupt();
						}
						catch(java.lang.SecurityException ee)
						{
							try
							{
								this.destroy();
							}
							catch(java.lang.NoSuchMethodError e)
							{
								System.gc();
							}
						}
						break;	                                                                        
					}
					else
					{//muu jutt, kui akent pole, saada statusesse
						status.append("\n"+vastus+"\n",status.textColor,status.jutt,status.buffer);
						
						if(!status.nupp.getForeground().equals(Color.blue))
						{//ei olnud aktiivne                                    
							if(status.nupp.getForeground().equals(Color.black)) 			
							{                                                   			
								status.nupp.setForeground(Color.red);           			
							}                                                   			
						                                                			
							if(status.beep.getState())                          			
							{                                                   			
								Toolkit.getDefaultToolkit().beep();             			
							}                                                   			
						}          
					}
				
				}//edasi
				privat.removeNotify();//kui seda ei tee läheb IE segi
				privat.addNotify();
				
			}//try
			catch(IOException e)
			{
				aeg=date();
				status.append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,status.jutt,status.buffer);
				status.connect=false;
				for(i=0;i<privad.length;i++)                                                    
				{                                                                               
					if(privad[i]!=null)                                                         
					{                                                                           
						if(privad[i].name.compareTo("")!=0)                                     
						{                                                                       
							privad[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,privad[i].jutt,privad[i].buffer);	
						}                                                                       
					}                                                                           
				}                                                                               
				                                                                                
				for(i=0;i<channels.length;i++)                                                  
				{                                                                               
					if(channels[i]!=null)                                                       
					{                                                                           
						if(channels[i].name.compareTo("")!=0)                                   
						{                                                                       
							channels[i].append("\n"+aeg+" Ühendus serveriga katkes...\n",Color.red,channels[i].jutt,channels[i].buffer);	
						}                                                                       
					}                                                                           
				} 
				exit.setEnabled(false);
				try
				{
					s.close();                                                                      
				}
				catch(IOException ee)
				{
				}
				try
				{
					this.interrupt();
				}
				catch(java.lang.SecurityException ee)
				{
					try
					{
						this.destroy();
					}
					catch(java.lang.NoSuchMethodError eee)
					{
						System.gc();
					}
				}
				break;                                                                          
			}
			
			catch(Exception e)
			{
				status.append("\nTekkis viga vastuvõtmisel (käsk: "+command+"): "+e+"\n",Color.red,status.jutt,status.buffer);
				status.append("\nSaabus teks: "+vastus+"\n",Color.red,status.jutt,status.buffer);
				
				if(!status.nupp.getForeground().equals(Color.blue))
				{//ei olnud aktiivne                                    
					if(status.nupp.getForeground().equals(Color.black)) 			
					{                                                   			
						status.nupp.setForeground(Color.red);           			
					}                                                   			
						                                                			
					if(status.beep.getState())                          			
					{                                                   			
						Toolkit.getDefaultToolkit().beep();             			
					}                                                   			
				}                                                       			
				privat.removeNotify();
				privat.addNotify();
			}
			
			edasi=true;
		}//while		
	}
}



class buttonTriger implements ActionListener,MouseListener
{
	Thread see;

	buttonTriger(Thread se)
	{
		see=se;
	}

	public void actionPerformed(ActionEvent event)
	{
		see.start();
	}

	public void mousePressed(MouseEvent e)	{} 
	public void mouseReleased(MouseEvent e) {} 
	public void mouseEntered(MouseEvent e) {}
	public void mouseExited(MouseEvent e) {}
	public void mouseClicked(MouseEvent e) 
	{
		see.start();
	}
}



public class IRC extends Applet{
Button nupp=new Button();
Frame aken;
Thread see;
Image icon;

public void destroy()
{
	((action)see).peaaken.dispose();


	try
	{
		see.interrupt();
	}
	catch(Exception e)
	{
		try
		{
			see.destroy();
		}
		catch(Exception ee){}
	}
	System.exit(0);

}


public void init()
{	
	String text=getParameter("buttontext");
	if(text==null) text="";
	String ikoon=getParameter("icon");
	if(text==null) text="";
	if(ikoon==null) ikoon="";

	see=new action(this);

	if(text.compareTo("")!=0)
	{//nupp
		nupp.setLabel(text);
		this.add(nupp);
		nupp.addActionListener(new buttonTriger(see));
	}
	else
	if(ikoon.compareTo("")!=0)
	{//ikoon
		try
		{
			URL urll=new URL(getParameter("icon"));     			 
			icon=this.getImage(urll);  
		}
		catch(Exception e)
		{
		}
		this.addMouseListener(new buttonTriger(see));
	}
	else
	{//kohe
		see.start();
	}	
}

	public void paint(Graphics g) 
	{ 
		g.drawImage(icon, 0, 0, this); 
	}
}