import java.awt.*; 
import java.awt.List; 
import java.awt.event.*; 
import java.applet.Applet; 
import java.io.*; 
import java.net.*; 
 
 
public class list extends Applet 
{ 
	List members=new List(14); 
	Checkbox o=new Checkbox("o"); 
	Checkbox v=new Checkbox("v"); 
	Checkbox f=new Checkbox("f"); 
	Socket s; 
	Button add2,del2; 
	TextField error=new TextField(); 
	PrintStream to; 
	BufferedReader from; 
	 
 
	public void init() 
	{ 
		String host=getParameter("host"); 
		int port=new Integer(getParameter("port")).intValue(); 
		setBackground(Color.lightGray); 
		setLayout(new BorderLayout(5,5)); 
 
		members.setMultipleMode(true); 
 
		Panel vasak=new Panel(); 
		Panel parem=new Panel(); 
		vasak.setLayout(new BorderLayout(5,5)); 
		parem.setLayout(new BorderLayout(5,5)); 
		 
		Label kiri1=new Label("List: "+getParameter("nimi"),1); 
		Label kiri2=new Label("Listi liikmed",1); 
 
		Panel flags=new Panel(); 
		flags.setLayout(new GridLayout(5,1)); 
		flags.add(o); 
		flags.add(v); 
		flags.add(f); 
		 
		Panel empty=new Panel(); 
 
		vasak.add(kiri1,"North"); 
		vasak.add(empty,"West"); 
		vasak.add(flags,"Center"); 
		//vasak.add(stuff,"South"); 
 
		Panel edit2=new Panel(); 
		Button add2=new Button("Lisa"); 
		Button del2=new Button("Kustuta"); 
		edit2.add(add2); 
		edit2.add(del2); 
 
		Panel mem=new Panel(); 
		mem.add(members); 
 
		Button save=new Button("Salvesta"); 
		Panel nupud=new Panel(); 
		nupud.add(save); 
 
		Panel bottom=new Panel(); 
		bottom.setLayout(new BorderLayout()); 
 
		bottom.add(nupud,"Center"); 
		bottom.add(error,"South"); 
 
		parem.add(kiri2,"North"); 
		parem.add(mem,"Center"); 
		parem.add(edit2,"South"); 
 
		add(vasak,"West"); 
		add(parem,"Center"); 
		add(bottom,"South"); 
 
 
		try 
		{ 
			int tyhik; 
			s=new Socket(host,port); 
			to=new PrintStream(s.getOutputStream()); 
			from=new BufferedReader(new InputStreamReader(s.getInputStream())); 
	
			to.println("LIST"); 
			 
			//SAAME AKTIIVSE LISTI FLAGID 
			to.println(getParameter("nimi")); 
 
			String vastus=from.readLine(); //1. listi flagid 
			if(vastus.compareTo("")!=0) 
			{ 
				tyhik=vastus.indexOf(" "); 
				while(tyhik!=-1) 
				{ 
					if(vastus.substring(0,tyhik).compareTo("o")==0) 
					{ 
						o.setState(true); 
					} 
					else 
					if(vastus.substring(0,tyhik).compareTo("f")==0) 
					{ 
						f.setState(true); 
					} 
					else 
					if(vastus.substring(0,tyhik).compareTo("v")==0) 
					{ 
						v.setState(true); 
					} 
					vastus=vastus.substring(tyhik+1); 
					tyhik=vastus.indexOf(" "); 
				} 
 
				if(vastus.compareTo("o")==0) 
				{ 
					o.setState(true); 
				} 
				else 
				if(vastus.compareTo("f")==0) 
				{ 
					f.setState(true); 
				} 
				else 
				if(vastus.compareTo("v")==0) 
				{ 
					v.setState(true); 
				} 
			} 
 
//============================= SAAN LISTI LIIKMED ===========================================00 			 
			vastus=from.readLine(); 
			while(vastus.compareTo(".")!=0) 
			{ 
				members.add(vastus); 
				vastus=from.readLine(); 
			} 
 
			del2.addActionListener(new kuular(0,this)); 
			add2.addActionListener(new kuular(1,this)); 
			save.addActionListener(new saveKuular());
		} 
		catch(IOException e) 
		{ 
			error.setText("Ei saa deemoniga yhendust: "+e);
		}	 
	} 
 
	
	class saveKuular implements ActionListener 
	{
		public void actionPerformed(ActionEvent e) 
		{
			String[] jada=members.getItems();
			try
			{
				for(int i=0;i<jada.length;i++)
				{
					to.println(jada[i]);
				}
				to.println(".");

				String in=from.readLine();
				
				if(in.compareTo(".")==0)
				{
					in="";
					if(o.getState())
					{
						in="o";
					}

					if(v.getState())
					{
						in+=" v";
					}

					if(f.getState())
					{
						in+=" f";
					}
		
					to.println(in);
					in=from.readLine();
				
					if(in.compareTo(".")==0)
					{
						error.setText("Objekt salvestatud.");
					}
					else
					{
						error.setText("Flagide salvestamine ebaonnestus: "+in);
					}
				}
				else
				{
					error.setText("Salvestamine ebaonnestus: "+in);
				}
			}
			catch(IOException ee)
			{
				error.setText("Viga salvestamisel: "+ee);
			}
		}
	}


	
	class kuular implements ActionListener 
	{ 
		int kumb; 
		Applet see; 
		Frame lisa; 
 
		kuular(int kum,Applet se) 
		{ 
			kumb=kum; 
			see=se; 
 
			lisa=new Frame("Lisa"); 
			lisa.setBackground(Color.lightGray); 
			Label kiri=new Label("Sisesta lisatava hüüdnimi (9)",1); 
			Panel paneel=new Panel(); 
			final TextField nimi=new TextField(10); 
			paneel.add(nimi); 
 
			Panel nupud=new Panel(); 
			Button ok=new Button(" OK "); 
			Button cancel=new Button("Katkesta"); 
			nupud.add(ok); 
			nupud.add(cancel); 
 
			lisa.add(kiri,"North"); 
			lisa.add(paneel,"Center"); 
			lisa.add(nupud,"South"); 
			lisa.setSize(180,140); 
			lisa.setLocation(200,200); 
 
			cancel.addActionListener(new ActionListener() 
			{ 
				public void actionPerformed(ActionEvent e) 
				{ 
					see.setEnabled(true); 
					lisa.setVisible(false); 
				} 
			}); 
 
			ok.addActionListener(new ActionListener() 
			{ 
				public void actionPerformed(ActionEvent e) 
				{ 
					String[] jada5=members.getItems(); 
					String lisatav=nimi.getText(); 
					boolean lisamine=true; 
 
					for(int i=0;i<jada5.length;i++) 
					{ 
						if(jada5[i].compareTo(lisatav)==0) 
						{ 
							lisamine=false; 
							break; 
						} 
					} 
		 
					see.setEnabled(true); 
					lisa.setVisible(false); 
		 
					if(lisamine) 
					{ 
						members.add(lisatav); 
						error.setText(""); 
					} 
					else 
					{ 
						error.setText("Oli juba olemas!"); 
					} 
				} 
			}); 
		} 
	 
		public void actionPerformed(ActionEvent e) 
		{ 
			if(kumb==0) 
			{ 
				int[] jada=members.getSelectedIndexes(); 
				                                          
				for(int i=jada.length-1;i>-1;i--)            	 
				{                                         	 
					members.remove(jada[i]);                       	 
				}   
				error.setText(""); 
			} 
			else 
			{ 
				see.setEnabled(false); 
				lisa.show(); 
			} 
		} 
 
	}; 
} 
