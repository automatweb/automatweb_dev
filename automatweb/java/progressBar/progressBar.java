import java.applet.*; 
import java.awt.*;
import java.math.*;

class Tahvel extends Canvas
{
	int lai,hai,x,y,protsent;
	Applet boss;
	Color font,done;
	boolean status,show;

	Tahvel(Applet bos)
	{
		boss=bos;
	}

	public void paint(Graphics g)
	{
		g.setColor(done);
		g.fillRect(0,0,lai,hai);
		if ((show)&&(protsent!=-1))
		{
			g.setColor(font);
			g.drawString(protsent+" %",x,y);
		}
		
		if ((status)&&(protsent!=-1))
		{
			boss.getAppletContext().showStatus(protsent+" %");
		}
		
	}
}


class watchDog extends Thread 
{ 
	Tahvel bar; 
	Applet boss;
 
	watchDog(Tahvel baar,Applet bos) 
	{ 
		bar=baar;
		boss=bos;
		bar.protsent=-1;
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
		Color generalBack=getColor(boss.getParameter("general_back_color"));
		if(generalBack==null) generalBack=new Color(200,200,200); 
		boss.setBackground(generalBack);
		bar.setBackground(generalBack);

		Color back=getColor(boss.getParameter("background_color"));
		if(back==null) back=new Color(238,238,238); 
		//bar.setBackground(back);
		
		int laius=boss.getSize().width;
		bar.hai=boss.getSize().height;

		if(boss.getParameter("show_text").compareTo("ON")==0)
		{
			bar.show=true;
			Color font=getColor(boss.getParameter("text_color"));
			if(font==null) font=new Color(0,0,0); 
			bar.font=font;
			int size;

			try
			{
				size=new Integer(boss.getParameter("text_size")).intValue(); 
			}
			catch(Exception e)
			{
				size=11;
			}			
			bar.setFont(new Font("TimesRoman",Font.PLAIN,size));
		}
		else
		{
			bar.show=false;
		}

		int time;
		try
		{
			time=new Integer(boss.getParameter("time")).intValue(); 
		}
		catch(Exception e)
		{
			time=11;
		}			

		Color done=getColor(boss.getParameter("done_color"));  	
		if(done==null) done=new Color(138,171,190); 
		bar.done=done;

		if(boss.getParameter("show_status").compareTo("ON")==0)
		{
			bar.status=true;
		}
		else
		{
			bar.status=false;
		}

		try
		{
			bar.x=new Integer(boss.getParameter("textX")).intValue(); 
		}
		catch(Exception e)
		{
			bar.x=40;
		}

			try
		{
			bar.y=new Integer(boss.getParameter("textY")).intValue(); 
		}
		catch(Exception e)
		{
			bar.y=16;
		}

		double samm=laius/100.0;

		try
		{
			sleep(2000);
		}
		catch(Exception e)
		{
		}

		//see hakkab tsyklis olema koos deemoni paringutega
		bar.setBackground(back);
		double prog=samm;

		for (int i=1;i<101;i++,prog+=samm)//tsykli asemel peab i väärtuse saam deemonilt
		{
			bar.lai=Math.round((long)prog);
			bar.protsent=i;
			bar.repaint();
			try//tulevikus pole seda vaja, näitab siis kui vastus saabub
			{
				sleep(125);
			}
			catch(Exception e)
			{
			}
		}

		try
		{
			sleep(1000*time);
		}
		catch(Exception e)
		{
		}
		/*bar.protsent=-1;
		bar.setBackground(generalBack);
		//bar.clear();	//laseme appleti tyhjaks
		bar.repaint();*/

		boss.removeAll();
		bar=new Tahvel(boss);
		bar.protsent=-1;
		bar.setBackground(generalBack);
		boss.add(bar,"Center");
		boss.repaint();
	}
}


class progressBar extends Applet 
{
	Thread watchDog;

	public void destroy() 
	{
		try
		{
			watchDog.interrupt();
		}
		catch(Exception e)
		{
			try
			{
				watchDog.destroy();
			}
			catch(Exception ee)
			{
			}
		}
	}

	public void init() 
	{
		this.setLayout(new BorderLayout());
		Tahvel bar=new Tahvel(this);
		this.add(bar,"Center");

		watchDog=new watchDog(bar,this); 
		watchDog.start();
	}
}
