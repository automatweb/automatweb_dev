/*
** Applet2.java
**
** Second of the two applets in inter-applet comminucation
**
** Bipin Patwardhan
*/

import java.applet.*;
import java.awt.*;

public class Applet2 extends Applet {
	String data;

	public void init() {
		data = null;
	}

	public void showData(String text) {
		data = text;
		repaint();
	}

	public void paint(Graphics g) {
		if ( data != null )
			g.drawString("Data->" + data, 5, 20);
		else
			g.drawString("No Data", 5, 20);
	}
}

/* End : Applet2.java */

