/*
** Applet1.java
**
** First of the two applets in inter-applet comminucation
**
** Bipin Patwardhan
*/

import java.applet.*;
import java.awt.*;

public class Applet1 extends Applet {
	TextField field;

	public void init() {
		setLayout(new BorderLayout());

		add("North", new Button("Send Data"));
		field = new TextField("10", 10);
		add("South", field);
	}

	public boolean action(Event event, Object obj) {
		if ( obj.equals("Send Data") ) {
			AppletContext appletContext = getAppletContext();
			Applet applet2 = appletContext.getApplet("APPLET2");
			((Applet2)applet2).showData(field.getText());

			return true;
		}

		return super.action(event, obj);
	}
}

/* End : Applet1.java */

