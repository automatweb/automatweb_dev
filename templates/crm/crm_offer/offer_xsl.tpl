<?xml version="1.0" encoding="iso-8859-1"?>

<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
  <fo:layout-master-set>
    <fo:simple-page-master master-name="cover">
      <fo:region-body margin="1in"/>
    </fo:simple-page-master>
  </fo:layout-master-set>

  <fo:page-sequence master-reference="cover">
    <fo:flow flow-name="xsl-region-body">
      <fo:block 
				font-family="Helvetica" 
				font-size="20px" 
				font-weight="bold" 
				text-align="center"
				space-before="170pt"
				space-before.conditionality="retain">Pakkumine</fo:block>
      <fo:block 
				font-family="Helvetica" 
				font-size="16px" 
				font-weight="bold" 
				text-align="center"
				space-before="50pt"
				space-before.conditionality="retain">{VAR:orderer}</fo:block>
      <fo:block 
				font-family="Helvetica" 
				font-size="16px" 
				font-weight="bold" 
				text-align="center"
				space-before="10pt"
				space-before.conditionality="retain">{VAR:name}</fo:block>


      <fo:block 
				font-family="Helvetica" 
				font-size="15px" 
				font-weight="bold" 
				text-align="right"
				space-before="120pt"
				space-before.conditionality="retain">{VAR:implementor}</fo:block>
      <fo:block 
				font-family="Helvetica" 
				font-size="13px" 
				text-align="right"
				space-before="10pt"
				space-before.conditionality="retain">{VAR:date}</fo:block>

      <fo:block text-align="center" 
				space-before="30pt"
				space-before.conditionality="retain">
		<fo:external-graphic src="url('{VAR:logo}')"/>
		<fo:external-graphic src="url('http://intranet.automatweb.com/orb.aw/class=image/action=show/fastcall=1/file=c23df764837bc5d7da21c950b4825cd8.gif')"
							 content-height="40px" content-width="200px"
		/>
	  </fo:block>

      <fo:block 
				font-family="Helvetica" 
				font-size="10px" 
				text-align="center"
				space-before="50pt"
				space-before.conditionality="retain"
				color="grey">
			Kogu k&amp;auml;esolevas pakkumises sisalduv informatsioon on konfidentsiaalne ning ei kuulu avaldamisele kolmandatele osapooltele!
		</fo:block>

    </fo:flow>
  </fo:page-sequence>
</fo:root>
