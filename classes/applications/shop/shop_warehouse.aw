<?php
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_POPUP_SEARCH_CHANGE,CL_SHOP_WAREHOUSE, on_popup_search_change)

@tableinfo aw_shop_warehouses index=aw_oid master_table=objects master_index=brother_of
@classinfo syslog_type=ST_SHOP_WAREHOUSE relationmgr=yes maintainer=kristo prop_cb=1

@default table=objects

@default group=general_sub

	@property name type=textbox rel=1 trans=1
	@caption Nimi
	@comment Objekti nimi

	@property comment type=textbox
	@caption Kommentaar
	@comment Vabas vormis tekst objekti kohta

	@property status type=status trans=1 default=1
	@caption Aktiivne
	@comment Kas objekt on aktiivne


@default group=general_settings

	@property conf type=relpicker reltype=RELTYPE_CONFIG table=aw_shop_warehouses field=aw_config 
	@caption Seaded

	@property order_center type=relpicker reltype=RELTYPE_ORDER_CENTER table=objects field=meta method=serialize
	@caption Tellimiskeskkond tellimuste jaoks

	@property category_entry_form type=relpicker reltype=RELTYPE_CAT_ENTRY_FORM table=objects field=meta method=serialize
	@caption Kategooria lisamise vorm

	@property status_calc_type type=chooser table=aw_shop_warehouses field=aw_status_calc_type
	@caption Laoseisu arvestus


@default group=productgroups

	@property productgroups_toolbar type=toolbar no_caption=1 store=no

	@property productgroups_list type=table store=no no_caption=1 
	@caption Tootegruppide nimekiri 


@default group=products

	@property products_toolbar type=toolbar no_caption=1 store=no

	@layout prod_split type=hbox width=20%:80%

		@layout prod_left type=vbox parent=prod_split

			@layout prod_tree_lay type=vbox closeable=1 area_caption=Toodete&nbsp;puu parent=prod_left
	
				@property prod_tree type=treeview parent=prod_tree_lay store=no no_caption=1

			@layout prod_left_search type=vbox parent=prod_left area_caption=Otsing closeable=1

				@property prod_s_name type=textbox store=no captionside=top size=30 parent=prod_left_search
				@caption Nimi 
				
				@property prod_s_code type=textbox store=no captionside=top size=30 parent=prod_left_search
				@caption Kood
				
				@property prod_s_barcode type=textbox store=no captionside=top size=30 parent=prod_left_search
				@caption Ribakood
				
				@property prod_s_cat type=textbox store=no captionside=top size=30 parent=prod_left_search
				@caption Kategooria
				
				@property prod_s_count type=select store=no captionside=top parent=prod_left_search
				@caption Laoseis

				@property prod_s_price_from type=textbox store=no captionside=top size=30 parent=prod_left_search
				@caption Hind alates
				
				@property prod_s_pricelist type=textbox store=no captionside=top size=30  parent=prod_left_search
				@caption Hinnakiri
				
				@property prod_s_sbt type=submit store=no captionside=top  parent=prod_left_search value="Otsi"
				@caption Otsi
				

		@property products_list type=table store=no no_caption=1  parent=prod_split
		@caption Toodete nimekiri 

@default group=packets

	@property packets_toolbar type=toolbar no_caption=1 group=packets store=no

	@layout packets_split type=hbox width=20%:80%

		@layout packets_left type=vbox parent=packets_split

			@layout packets_tree_lay type=vbox closeable=1 area_caption=Pakettide&nbsp;puu parent=packets_left
	
				@property packets_tree type=treeview parent=packets_tree_lay store=no no_caption=1

			@layout packets_left_search type=vbox parent=packets_left area_caption=Otsing closeable=1

				@property packets_s_name type=textbox store=no captionside=top size=30 parent=packets_left_search
				@caption Nimi 
				
				@property packets_s_code type=textbox store=no captionside=top size=30 parent=packets_left_search
				@caption Kood
				
				@property packets_s_barcode type=textbox store=no captionside=top size=30 parent=packets_left_search
				@caption Ribakood
				
				@property packets_s_cat type=textbox store=no captionside=top size=30 parent=packets_left_search
				@caption Kategooria
				
				@property packets_s_count type=select store=no captionside=top parent=packets_left_search
				@caption Laoseis

				@property packets_s_price_from type=textbox store=no captionside=top size=30 parent=packets_left_search
				@caption Hind alates
				
				@property packets_s_pricelist type=textbox store=no captionside=top size=30  parent=packets_left_search
				@caption Hinnakiri
				
				@property packets_s_sbt type=submit store=no captionside=top  parent=packets_left_search value="Otsi"
				@caption Otsi
				

		@property packets_list type=table store=no no_caption=1  parent=packets_split
		@caption Pakettide nimekiri 


@default group=storage_income

	@property storage_income_toolbar type=toolbar no_caption=1 store=no

	@layout storage_income_split type=hbox width=20%:80%

		@layout storage_income_left type=vbox parent=storage_income_split

			@layout storage_income_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_income_left
	
				@property storage_income_tree type=treeview parent=storage_income_tree_lay store=no no_caption=1

			@layout storage_income_left_search type=vbox parent=storage_income_left area_caption=Otsing closeable=1

				@property storage_income_s_acquiredby type=textbox store=no captionside=top size=30 parent=storage_income_left_search
				@caption Hankija
				
				@property storage_income_s_type type=chooser store=no captionside=top size=30 parent=storage_income_left_search
				@caption T&uuml;&uuml;p
				
				@property storage_income_s_number type=textbox store=no captionside=top size=30 parent=storage_income_left_search
				@caption Number
				
				@property storage_income_s_status type=textbox store=no captionside=top size=30 parent=storage_income_left_search
				@caption Staatus
				
				@property storage_income_s_from type=date_select store=no captionside=top parent=storage_income_left_search
				@caption Alates

				@property storage_income_s_to type=date_select store=no captionside=top size=30 parent=storage_income_left_search
				@caption Kuni
				
				@property storage_income_s_article type=textbox store=no captionside=top size=30  parent=storage_income_left_search
				@caption Artikkel
				
				@property storage_income_s_art_cat type=textbox store=no captionside=top size=30  parent=storage_income_left_search
				@caption Artikli kategooria
				
				@property storage_income_s_sbt type=submit store=no captionside=top  parent=storage_income_left_search value="Otsi"
				@caption Otsi
				

		@property storage_income type=table store=no no_caption=1  parent=storage_income_split
		@caption Sissetulekud


@default group=storage_export

	@property storage_export_toolbar type=toolbar no_caption=1 store=no

	@layout storage_export_split type=hbox width=20%:80%

		@layout storage_export_left type=vbox parent=storage_export_split

			@layout storage_export_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_export_left
	
				@property storage_export_tree type=treeview parent=storage_export_tree_lay store=no no_caption=1

			@layout storage_export_left_search type=vbox parent=storage_export_left area_caption=Otsing closeable=1

				@property storage_export_s_acquiredby type=textbox store=no captionside=top size=30 parent=storage_export_left_search
				@caption Hankija
				
				@property storage_export_s_type type=chooser store=no captionside=top size=30 parent=storage_export_left_search
				@caption T&uuml;&uuml;p
				
				@property storage_export_s_number type=textbox store=no captionside=top size=30 parent=storage_export_left_search
				@caption Number
				
				@property storage_export_s_status type=textbox store=no captionside=top size=30 parent=storage_export_left_search
				@caption Staatus
				
				@property storage_export_s_from type=date_select store=no captionside=top parent=storage_export_left_search
				@caption Alates

				@property storage_export_s_to type=date_select store=no captionside=top size=30 parent=storage_export_left_search
				@caption Kuni
				
				@property storage_export_s_article type=textbox store=no captionside=top size=30  parent=storage_export_left_search
				@caption Artikkel
				
				@property storage_export_s_art_cat type=textbox store=no captionside=top size=30  parent=storage_export_left_search
				@caption Artikli kategooria
				
				@property storage_export_s_sbt type=submit store=no captionside=top  parent=storage_export_left_search value="Otsi"
				@caption Otsi
				

		@property storage_export type=table store=no no_caption=1  parent=storage_export_split
		@caption V&auml;ljaminekud


@default group=storage_movements

	@property storage_movements_toolbar type=toolbar no_caption=1 store=no

	@layout storage_movements_split type=hbox width=20%:80%

		@layout storage_movements_left type=vbox parent=storage_movements_split

			@layout storage_movements_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_movements_left
	
				@property storage_movements_tree type=treeview parent=storage_movements_tree_lay store=no no_caption=1

			@layout storage_movements_left_search type=vbox parent=storage_movements_left area_caption=Otsing closeable=1

				@property storage_movements_s_warehouse type=textbox store=no captionside=top size=30 parent=storage_movements_left_search
				@caption Ladu
				
				@property storage_movements_s_number type=textbox store=no captionside=top size=30 parent=storage_movements_left_search
				@caption Number
				
				@property storage_movements_s_status type=textbox store=no captionside=top size=30 parent=storage_movements_left_search
				@caption Staatus
				
				@property storage_movements_s_from type=date_select store=no captionside=top parent=storage_movements_left_search
				@caption Alates

				@property storage_movements_s_to type=date_select store=no captionside=top size=30 parent=storage_movements_left_search
				@caption Kuni
				
				@property storage_movements_s_article type=textbox store=no captionside=top size=30  parent=storage_movements_left_search
				@caption Artikkel
				
				@property storage_movements_s_art_cat type=textbox store=no captionside=top size=30  parent=storage_movements_left_search
				@caption Artikli kategooria
				
				@property storage_movements_s_sbt type=submit store=no captionside=top  parent=storage_movements_left_search value="Otsi"
				@caption Otsi
				

		@property storage_movements type=table store=no no_caption=1  parent=storage_movements_split
		@caption V&auml;ljaminekud


@default group=storage_writeoffs

	@property storage_writeoffs_toolbar type=toolbar no_caption=1 store=no

	@layout storage_writeoffs_split type=hbox width=20%:80%

		@layout storage_writeoffs_left type=vbox parent=storage_writeoffs_split

			@layout storage_writeoffs_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_writeoffs_left
	
				@property storage_writeoffs_tree type=treeview parent=storage_writeoffs_tree_lay store=no no_caption=1

			@layout storage_writeoffs_left_search type=vbox parent=storage_writeoffs_left area_caption=Otsing closeable=1

				@property storage_writeoffs_s_warehouse type=textbox store=no captionside=top size=30 parent=storage_writeoffs_left_search
				@caption Ladu
				
				@property storage_writeoffs_s_number type=textbox store=no captionside=top size=30 parent=storage_writeoffs_left_search
				@caption Number
				
				@property storage_writeoffs_s_status type=textbox store=no captionside=top size=30 parent=storage_writeoffs_left_search
				@caption Staatus
				
				@property storage_writeoffs_s_from type=date_select store=no captionside=top parent=storage_writeoffs_left_search
				@caption Alates

				@property storage_writeoffs_s_to type=date_select store=no captionside=top size=30 parent=storage_writeoffs_left_search
				@caption Kuni
				
				@property storage_writeoffs_s_article type=textbox store=no captionside=top size=30  parent=storage_writeoffs_left_search
				@caption Artikkel
				
				@property storage_writeoffs_s_art_cat type=textbox store=no captionside=top size=30  parent=storage_writeoffs_left_search
				@caption Artikli kategooria
				
				@property storage_writeoffs_s_sbt type=submit store=no captionside=top  parent=storage_writeoffs_left_search value="Otsi"
				@caption Otsi
				

		@property storage_writeoffs type=table store=no no_caption=1  parent=storage_writeoffs_split
		@caption Mahakandmised


@default group=status_status

	@property storage_status_toolbar type=toolbar no_caption=1 store=no

	@layout storage_status_split type=hbox width=20%:80%

		@layout storage_status_left type=vbox parent=storage_status_split

			@layout storage_status_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_status_left
	
				@property storage_status_tree type=treeview parent=storage_status_tree_lay store=no no_caption=1

			@layout storage_status_left_search type=vbox parent=storage_status_left area_caption=Otsing closeable=1

				@property storage_status_s_name type=textbox store=no captionside=top size=30 parent=storage_status_left_search
				@caption Nimi
				
				@property storage_status_s_code type=textbox store=no captionside=top size=30 parent=storage_status_left_search
				@caption Kood
				
				@property storage_status_s_barcode type=textbox store=no captionside=top size=30 parent=storage_status_left_search
				@caption Ribakood
				
				@property storage_status_s_category type=date_select store=no captionside=top parent=storage_status_left_search
				@caption Kategooria

				@property storage_status_s_status type=date_select store=no captionside=top size=30 parent=storage_status_left_search
				@caption Laoseis
				
				@property storage_status_s_price type=textbox store=no captionside=top size=30  parent=storage_status_left_search
				@caption Hind
				
				@property storage_status_s_pricelist type=textbox store=no captionside=top size=30  parent=storage_status_left_search
				@caption Hinnakiri
				
				@property storage_status_s_below_min type=checkbox ch_value=1 store=no captionside=top size=30  parent=storage_status_left_search
				@caption Alla miinimumi
				
				@property storage_status_s_sbt type=submit store=no captionside=top  parent=storage_status_left_search value="Otsi"
				@caption Otsi
				

		@property storage_status type=table store=no no_caption=1  parent=storage_status_split
		@caption Laoseis


@default group=status_prognosis

	@property storage_prognosis_toolbar type=toolbar no_caption=1 store=no

	@layout storage_prognosis_split type=hbox width=20%:80%

		@layout storage_prognosis_left type=vbox parent=storage_prognosis_split

			@layout storage_prognosis_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_prognosis_left
	
				@property storage_prognosis_tree type=treeview parent=storage_prognosis_tree_lay store=no no_caption=1

			@layout storage_prognosis_left_search type=vbox parent=storage_prognosis_left area_caption=Otsing closeable=1

				@property storage_prognosis_s_name type=textbox store=no captionside=top size=30 parent=storage_prognosis_left_search
				@caption Nimi
				
				@property storage_prognosis_s_code type=textbox store=no captionside=top size=30 parent=storage_prognosis_left_search
				@caption Kood
				
				@property storage_prognosis_s_barcode type=textbox store=no captionside=top size=30 parent=storage_prognosis_left_search
				@caption Ribakood
				
				@property storage_prognosis_s_category type=date_select store=no captionside=top parent=storage_prognosis_left_search
				@caption Kategooria

				@property storage_prognosis_s_status type=date_select store=no captionside=top size=30 parent=storage_prognosis_left_search
				@caption Laoseis
				
				@property storage_prognosis_s_price type=textbox store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption Hind
				
				@property storage_prognosis_s_pricelist type=textbox store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption Hinnakiri
				
				@property storage_prognosis_s_below_min type=checkbox ch_value=1 store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption Alla miinimumi
				
				@property storage_prognosis_s_date type=date_select ch_value=1 store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption Kuup&auml;ev
				
				@property storage_prognosis_s_sales_order_status type=chooser ch_value=1 store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption M&uuml;&uuml;gitellimuste staatus

				@property storage_prognosis_s_purchase_order_status type=chooser ch_value=1 store=no captionside=top size=30  parent=storage_prognosis_left_search
				@caption Ostutellimuste staatus
				
				@property storage_prognosis_s_sbt type=submit store=no captionside=top  parent=storage_prognosis_left_search value="Otsi"
				@caption Otsi
				

		@property storage_prognosis type=table store=no no_caption=1  parent=storage_prognosis_split
		@caption Laoseis


@default group=status_inventories

	@property storage_inventories_toolbar type=toolbar no_caption=1 store=no

	@layout storage_inventories_split type=hbox width=20%:80%

		@layout storage_inventories_left type=vbox parent=storage_inventories_split

			@layout storage_inventories_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=storage_inventories_left
	
				@property storage_inventories_tree type=treeview parent=storage_inventories_tree_lay store=no no_caption=1

			@layout storage_inventories_left_search type=vbox parent=storage_inventories_left area_caption=Otsing closeable=1

				@property storage_inventories_s_name type=textbox store=no captionside=top size=30 parent=storage_inventories_left_search
				@caption Nimi
				
				@property storage_inventories_s_from type=date_select store=no captionside=top parent=storage_inventories_left_search
				@caption Alates

				@property storage_inventories_s_to type=date_select store=no captionside=top size=30 parent=storage_inventories_left_search
				@caption Kuni
				
				@property storage_inventories_s_sbt type=submit store=no captionside=top  parent=storage_inventories_left_search value="Otsi"
				@caption Otsi
				

		@property storage_inventories type=table store=no no_caption=1  parent=storage_inventories_split
		@caption Inventuurid


@default group=purchase_orders

	@property purchase_orders_toolbar type=toolbar no_caption=1 store=no

	@layout purchase_orders_split type=hbox width=20%:80%

		@layout purchase_orders_left type=vbox parent=purchase_orders_split

			@layout purchase_orders_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=purchase_orders_left
	
				@property purchase_orders_tree type=treeview parent=purchase_orders_tree_lay store=no no_caption=1

			@layout purchase_orders_left_search type=vbox parent=purchase_orders_left area_caption=Otsing closeable=1

				@property purchase_orders_s_purchaser type=textbox store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Hankija
				
				@property purchase_orders_s_number type=textbox store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Number

				@property purchase_orders_s_status type=chooser store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Staatus
				
				@property purchase_orders_s_from type=date_select store=no captionside=top parent=purchase_orders_left_search
				@caption Alates

				@property purchase_orders_s_to type=date_select store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Kuni
				
				@property purchase_orders_s_art type=textbox store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Artikkel

				@property purchase_orders_s_art_cat type=textbox store=no captionside=top size=30 parent=purchase_orders_left_search
				@caption Artikli kategooria

				@property purchase_orders_s_sbt type=submit store=no captionside=top  parent=purchase_orders_left_search value="Otsi"
				@caption Otsi
				

		@property purchase_orders type=table store=no no_caption=1  parent=purchase_orders_split
		@caption Ostutellimused


@default group=sell_orders

	@property sell_orders_toolbar type=toolbar no_caption=1 store=no

	@layout sell_orders_split type=hbox width=20%:80%

		@layout sell_orders_left type=vbox parent=sell_orders_split

			@layout sell_orders_tree_lay type=vbox closeable=1 area_caption=Filtreeri parent=sell_orders_left
	
				@property sell_orders_tree type=treeview parent=sell_orders_tree_lay store=no no_caption=1

			@layout sell_orders_left_search type=vbox parent=sell_orders_left area_caption=Otsing closeable=1

				@property sell_orders_s_buyer type=textbox store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Ostja
				
				@property sell_orders_s_number type=textbox store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Number

				@property sell_orders_s_status type=chooser store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Staatus
				
				@property sell_orders_s_from type=date_select store=no captionside=top parent=sell_orders_left_search
				@caption Alates

				@property sell_orders_s_to type=date_select store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Kuni
				
				@property sell_orders_s_art type=textbox store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Artikkel

				@property sell_orders_s_art_cat type=textbox store=no captionside=top size=30 parent=sell_orders_left_search
				@caption Artikli kategooria

				@property sell_orders_s_sbt type=submit store=no captionside=top  parent=sell_orders_left_search value="Otsi"
				@caption Otsi
				

		@property sell_orders type=table store=no no_caption=1  parent=sell_orders_split
		@caption M&uuml;&uuml;gitellimused




/////////////////////////////////////////////////////////////////////////// OLDER, PLEASE REVIEW
#otsing
@property find_name type=textbox store=no group=order_confirmed,order_unconfirmed
@caption Nimi

@property find_start type=date_select group=order_confirmed,order_unconfirmed
@caption Alates

@property find_end type=date_select store=no group=order_confirmed,order_unconfirmed
@caption Kuni

@property do_find type=submit store=no group=order_confirmed,order_unconfirmed
@caption Otsi



@groupinfo order caption="Tellimused"
@groupinfo order_unconfirmed parent=order caption="Kinnitamata"
@groupinfo order_confirmed parent=order caption="Kinnitatud"
@groupinfo order_undone parent=order caption="T&auml;itmata tellimused"
@groupinfo order_orderer_cos parent=order caption="Tellijad"
@groupinfo order_search parent=order caption="Otsing" submit_method=get

@property order_undone_tb type=toolbar store=no group=order_undone no_caption=1
@property order_undone type=table store=no group=order_undone no_caption=1

@property order_unconfirmed_toolbar type=toolbar no_caption=1 group=order_unconfirmed store=no
@property order_unconfirmed type=table store=no group=order_unconfirmed no_caption=1

@property order_confirmed_toolbar type=toolbar no_caption=1 group=order_confirmed store=no
@property order_confirmed type=table store=no group=order_confirmed no_caption=1

@layout hbox_oc type=hbox group=order_orderer_cos 

@property order_orderer_cos_tree type=text store=no parent=hbox_oc group=order_orderer_cos no_caption=1
@property order_orderer_cos type=table store=no parent=hbox_oc group=order_orderer_cos no_caption=1

@property osearch_uname type=textbox group=order_search store=no
@caption Tellija kasutajanimi

@property osearch_pname type=textbox group=order_search store=no
@caption Tellija isikunimi

@property osearch_oname type=textbox group=order_search store=no
@caption Organisatsiooni nimi

@property osearch_oid type=textbox size=8 group=order_search store=no
@caption Tellimuse ID

@property osearch_prodname type=textbox group=order_search store=no
@caption Toote nimi

@property osearch_from type=chooser group=order_search store=no
@caption Otsi staatuse j&auml;rgi

@property osearch_odates type=date_select group=order_search store=no
@caption Tellimuse ajavahemik (alates)

@property osearch_odatee type=date_select group=order_search store=no
@caption Tellimuse ajavahemik (kuni)

@property osearch_hidden type=hidden value=1 group=order_search store=no
@caption Otsing

@property osearch_submit type=submit group=order_search
@caption Otsi

@property osearch_table type=table group=order_search no_caption=1
@caption Tulemuste tabel

// search tab
@groupinfo search caption="Otsing" submit_method=get

@groupinfo search_search caption="Otsing" parent=search submit_method=get

@default group=search_search

@property search_tb type=toolbar store=no no_caption=1
@caption Otsingu toolbar

@property search_form type=callback callback=callback_get_search_form submit_method=get store=no
@caption Otsinguvorm

@property search_res type=table store=no no_caption=1
@caption Otsingu tulemused

@property search_cur_ord_text type=text store=no no_caption=1
@caption Hetke tellimus text

@property search_cur_ord type=table store=no no_caption=1
@caption Hetke tellimus tabel


@groupinfo order_current parent=search caption="Pakkumine"

@property order_current_toolbar type=toolbar no_caption=1 group=order_current store=no
@property order_current_table type=table store=no group=order_current no_caption=1

@property order_current_org type=popup_search field=meta method=serialize group=order_current clid=CL_CRM_COMPANY
@caption Tellija organisatsioon

@property order_current_person type=popup_search field=meta method=serialize group=order_current clid=CL_CRM_PERSON
@caption Tellija isik

@property order_current_form type=callback callback=callback_get_order_current_form store=no group=order_current
@caption Tellimuse info vorm



// general subs
	@groupinfo general_sub parent=general caption="&Uuml;ldine"
	@groupinfo general_settings parent=general caption="Seaded"
	@groupinfo productgroups caption="Tootegrupid" submit=no parent=general

@groupinfo articles caption="Artiklid"

	@groupinfo products caption="Artiklid" submit=no parent=articles
	@groupinfo packets caption="Paketid" submit=no parent=articles

@groupinfo storage caption="Muutused"

	@groupinfo storage_income parent=storage caption="Sissetulekud" 
	@groupinfo storage_export parent=storage caption="V&auml;ljaminekud"
	@groupinfo storage_movements parent=storage caption="Liikumised" submit=no
	@groupinfo storage_writeoffs parent=storage caption="Mahakandmised" submit=no

@groupinfo status caption="Laoseis"

	@groupinfo status_status caption="Laoseis" parent=status
	@groupinfo status_prognosis caption="Prognoos" parent=status
	@groupinfo status_inventories caption="Inventuurid" parent=status

@groupinfo purchases caption="Tellimused"

	@groupinfo purchase_orders caption="Ostutellimused" parent=purchases
	@groupinfo sell_orders caption="M&uuml;&uuml;gitellimused" parent=purchases

////////// reltypes
@reltype CONFIG value=1 clid=CL_SHOP_WAREHOUSE_CONFIG
@caption konfiguratsioon

@reltype PRODUCT value=2 clid=CL_SHOP_PRODUCT
@caption toode

@reltype PACKET value=2 clid=CL_SHOP_PACKET
@caption pakett

@reltype STORAGE_INCOME value=3 clid=CL_SHOP_WAREHOUSE_RECEPTION
@caption lao sissetulek

@reltype STORAGE_EXPORT value=4 clid=CL_SHOP_WAREHOUSE_EXPORT
@caption lao v&auml;jaminek

@reltype ORDER value=5 clid=CL_SHOP_ORDER
@caption tellimus

@reltype ORDER_CENTER value=6 clid=CL_SHOP_ORDER_CENTER
@caption tellimiskeskkond

@reltype EMAIL value=7 clid=CL_ML_MEMBER
@caption saada tellimused

@reltype CFGMANAGER value=8 clid=CL_CFGMANAGER
@caption Seadete haldur

@reltype CAT_ENTRY_FORM value=9 clid=CL_CFGFORM
@caption Kategooria lisamise vorm

@reltype PURCHASE_ORDER value=10 clid=CL_SHOP_PURCHASE_ORDER
@caption Ostutellimus

@reltype SELL_ORDER value=11 clid=CL_SHOP_SELL_ORDER
@caption M&uuml;&uuml;gitellimus

@reltype INVENTORY value=12 clid=CL_SHOP_WAREHOUSE_INVENTORY
@caption Inventuur

*/

class shop_warehouse extends class_base
{
	function shop_warehouse()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse",
			"clid" => CL_SHOP_WAREHOUSE
		));
	}
	
	function callback_on_load($arr)
	{
		if(is_oid($arr["request"]["id"]) && $this->can("view", $arr["request"]["id"]))
		{
			$obj = obj($arr["request"]["id"]);
			if($cfgmanager = $obj->get_first_conn_by_reltype("RELTYPE_CFGMANAGER"))
			{
				$this->cfgmanager = $cfgmanager->prop("to");
			}
		}
	}

	function get_property($arr)
	{
		if (!$this->_init_view($arr))
		{
			return PROP_OK;
		}
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "find_name":
			case "find_start":
			case "find_end":
				$search_data = $arr["obj_inst"]->meta("search_data");
				$prop["value"] = $search_data[$prop["name"]];
				break;

			case "osearch_from":
				$prop["options"] = array(
					0 => t("k&otilde;ik"),
					1 => t("kinnitatud"),
					2 => t("kinnitamata"),
				);
			case "osearch_uname":
			case "osearch_pname":
			case "osearch_oname":
			case "osearch_oid":
			case "osearch_prodname":
			case "osearch_odates":
			case "osearch_odatee":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
				
			case "osearch_table":
				$this->do_search_tbl($arr);
				break;
				
			case "products_toolbar":
				$this->mk_prod_toolbar($arr);
				break;

			case "productgroups_toolbar":
				$this->mk_prodg_toolbar($arr);
				break;

			case "productgroups_list":
				$this->do_prodg_list($arr);
				break;
				
			case "packets_toolbar":
				$this->mk_pkt_toolbar($arr);
				break;

			case "storage_list":
				$this->do_storage_list_tbl($arr);
				break;

			case "storage_income_toolbar":
				$this->mk_storage_income_toolbar($arr);
				break;

			case "storage_export_toolbar":
				$this->mk_storage_export_toolbar($arr);
				break;

			case "order_unconfirmed_toolbar":
				$this->mk_order_unconfirmed_toolbar($arr);
				break;

			case "order_undone":
				$this->do_order_undone_tbl($arr);
				break;
			case "order_undone_tb":
				$this->do_order_undone_tb($arr);
				break;
			case "order_unconfirmed":
				$this->do_order_unconfirmed_tbl($arr);
				break;

			case "order_confirmed_toolbar":
				$this->mk_order_confirmed_toolbar($arr);
				break;

			case "order_confirmed":
				$this->do_order_confirmed_tbl($arr);
				break;

			case "order_orderer_cos":
				$this->do_order_orderer_cos_tbl($arr);
				break;

			case "order_orderer_cos_tree":
				$this->do_order_orderer_cos_tree($arr);
				break;

			case "order_current_toolbar":
				$this->do_order_cur_tb($arr);
				break;

			case "order_current_table":
			case "search_cur_ord":
				$this->save_ord_cur_tbl($arr);
				$this->do_order_cur_table($arr);
				break;

			case "search_res":
				$this->do_search_res_tbl($arr);
				break;

			case "search_tb":
				$this->do_search_tb($arr);
				break;
	
			case "search_cur_ord_text":
				$data["value"] = t("<br><br>Hetkel pakkumises olevad tooted:");
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "find_name":
				unset($arr["request"]["rawdata"]["rawdata"]);
				$arr["obj_inst"]->set_meta("search_data" , $arr["request"]);
				break;
			case "storage_income":
				$this->save_storage_inc_tbl($arr);
				break;

			case "storage_export":
				$this->save_storage_exp_tbl($arr);
				break;

			case "order_unconfirmed":
				$this->save_order_unconfirmed_tbl($arr);
				break;

			case "products_list":
				$this->do_del_prod($arr);
				break;

			case "order_current_table":
			case "search_cur_ord":
				$this->save_ord_cur_tbl($arr, true);
				break;

			case "order_current_org":
				if ($arr["obj_inst"]->prop("order_current_org") != $arr["request"]["order_current_org"])
				{
					$this->upd_ud = true;
				}
				break;

			case "order_current_person":
				if ($arr["obj_inst"]->prop("order_current_person") != $arr["request"]["order_current_person"])
				{
					$this->upd_ud = true;
				}
				break;
		}
		return $retval;
	}	

	function save_ord_cur_tbl($arr, $is_post = false)
	{
		$oc = obj($arr["obj_inst"]->prop("order_center"));
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$awa = new aw_array($arr["request"]["quant"]);
		foreach($awa->get() as $iid => $quantx)
		{
			$quantx = new aw_array($quantx);
			foreach($quantx->get() as $x => $quant)
			{
				$soc->set_item(array(
					"iid" => $iid,
					"quant" => $quant,
					"oc" => $oc,
					"it" => $x,
				));
			}
		}

		if ($is_post)
		{
			// also, if we got a discount element, save that as well
			$soc = get_instance(CL_SHOP_ORDER_CENTER);

			$arr["obj_inst"]->set_meta(
				"order_cur_discount", 
				$soc->get_discount_from_order_data($arr["obj_inst"]->prop("order_center"), $arr["request"]["user_data"])
			);

			$arr["obj_inst"]->set_meta("order_cur_pages", $arr["request"]["pgnr"]);
		}
	}
	
	function do_search_tbl($arr)
	{
		$srch = $arr["request"];
		if(!$srch["osearch_hidden"])
		{
			return;
		}
		$fields = array(
			"id" => t("Tellimus"),
			"prodname" => t("Toode"),
			"uname" => t("Kasutaja"),
			"pname" => t("Isik"),
			"oname" => t("Organisatsioon"),
			"odate" => t("Telliti"),
		);
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		foreach($fields as $key => $val)
		{
			$t->define_field(array(
				"name" => $key,
				"caption" => $val,
				"sortable" => 1,
			));
		}
		if($srch["osearch_uname"])
		{
			$z .= " AND user.name LIKE '%".$srch["osearch_uname"]."%'";
		}
		if($srch["osearch_pname"])
		{
			$z .= " AND isik.name LIKE '%".$srch["osearch_pname"]."%'";
		}
		if($srch["osearch_oname"])
		{
			$z .= " AND com.name LIKE '%".$srch["osearch_pname"]."%'";
		}
		if($srch["osearch_oid"])
		{
			$z .= " AND objects.oid = '".$srch["osearch_oid"]."'";
		}
		if(is_array($srch["osearch_odates"]))
		{
			$d = $srch["osearch_odates"];
			$d_ts = mktime(0, 0, 0, $d["month"], $d["day"], $d["year"]);
			if ($d_ts > 100)
			{
				$z .= " AND objects.created > ".$d_ts;
			}
		}
		if(is_array($srch["osearch_odatee"]))
		{
			$d = $srch["osearch_odatee"];
			$d_ts = mktime(23, 59, 59, $d["month"], $d["day"], $d["year"]);
			if ($d_ts > 30000)
			{
				$z .= " AND objects.created < ".$d_ts;
			}
		}
		if($srch["osearch_from"] == 1)
		{
			$z .= " AND so.confirmed = 1";
		}
		elseif($srch["osearch_from"] == 2)
		{
			$z .= " AND so.confirmed = 0";
		}

		$lim = " LIMIT 200 ";
		if ($srch["osearch_prodname"] != "")
		{
			$lim = "";
		}
		$q = "
			SELECT
				objects.oid AS id,
				isik.name AS pname,
				isik.oid AS pname_id,
				user.name AS uname,
				user.oid AS uname_id,
				com.name AS oname,
				com.oid AS oname_id,
				objects.created AS odate,
				so.confirmed as confirmed
			FROM 
				objects
				LEFT JOIN aw_shop_orders so ON (so.aw_oid = objects.oid)
				LEFT JOIN objects isik ON (so.aw_orderer_person = isik.oid)
				LEFT JOIN objects com ON (so.aw_orderer_company = com.oid)
				LEFT JOIN aliases isik2user ON (isik2user.target = isik.oid AND isik2user.reltype = 2)
				LEFT JOIN users ON (isik2user.source = users.oid)
				LEFT JOIN objects user ON (users.oid = user.oid)
			WHERE
				objects.status > 0 AND
				isik.status > 0 AND
				com.status > 0 AND
				user.status > 0 AND
				objects.parent = ".$this->order_fld."
				$z
				GROUP BY objects.created DESC
				$lim	
		";
		$this->db_query($q);
		$vars = array("return_url" => get_ru());
		//$t->table_header = t("<center>Leiti ".$this->num_rows()." kirjet</center>");
		$mt = 0;
		while($w = $this->db_next())
		{
			$this->save_handle();
			if($srch["osearch_prodname"])
			{
				$z2 = " AND objects.name LIKE '%".$srch["osearch_prodname"]."%'";
			}
			$q2 = "
			SELECT objects.name AS name, objects.oid AS id
			FROM objects 
				LEFT JOIN aw_shop_products prod ON (objects.oid = prod.aw_oid)
				LEFT JOIN aliases order2prod ON (order2prod.target = objects.oid AND order2prod.reltype = 1)
			WHERE
				objects.oid > 0 AND
				order2prod.source = '".$w["id"]."'
				$z2
			";
			$this->db_query($q2);
			
			if($this->num_rows() == 0)
			{
				$this->restore_handle();
				continue;
			}
			$e = array();
			while($w2 = $this->db_next())
			{
				$e[$w2["id"]] = html::get_change_url($w2["id"], $vars, $w2["name"]);
			}
			$this->restore_handle();
			$t->define_data(array(
				"id" => html::get_change_url($w["id"], $vars, $w["id"]),
				"pname" => html::get_change_url($w["pname_id"], $vars, $w["pname"]),
				"uname" => html::get_change_url($w["uname_id"], $vars, $w["uname"]),
				"oname" => html::get_change_url($w["oname_id"], $vars, $w["oname"]),
				"prodname" => implode(", ", $e),
				"odate" => date("d-m-Y", $w["odate"]),
			));
			$mt++;
		}
		$t->table_header = t("<center>Leiti ".$mt." kirjet</center>");
		
	}

	function do_order_cur_tb($data)
	{
		$tb =& $data["prop"]["toolbar"];

		/*$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Salvesta"),
			"url" => "javascript:document.changeform.submit()"
		));*/

		$url = $this->mk_my_orb("gen_order", array("id" => $data["obj_inst"]->id(), "html" => 1));
		$url = "window.open('$url','offer','width=700,height=600,toolbar=0,location=0,menubar=1,scrollbars=1')";
		$tb->add_button(array(
			"name" => "confirm",
			"img" => "pdf_upload.gif",
			"tooltip" => t("Genereeri HTML pakkumine"),
			"onClick" => $url,
			"url" => "#"
		));

		$tb->add_button(array(
			"name" => "mail",
			"img" => "save.gif",
			"tooltip" => t("Saada meilile"),
			"action" => "send_cur_order"
		));

		$tb->add_button(array(
			"name" => "clear",
			"img" => "new.gif",
			"tooltip" => t("Uus pakkumine"),
			"action" => "clear_order"
		));
	}

	function _init_order_cur_table(&$t)
	{
		if ($_GET["group"] == "order_current")
		{
			$t->define_field(array(
				"name" => "page",
				"caption" => t("Lehek&uuml;lg"),
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "quantity",
			"caption" => t("Kogus"),
			"align" => "center"
		));
	}

	function do_order_cur_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_cur_table($t);

		$pgnr = $arr["obj_inst"]->meta("order_cur_pages");

		// stick the order in the table
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->get_cart(obj($arr["obj_inst"]->prop("order_center")));
		foreach($soc->get_items_in_cart() as $iid => $quant)
		{
			$item = obj($iid);
			$t->define_data(array(
				"page" => html::textbox(array(
					"name" => "pgnr[$iid]",
					"value" => $pgnr[$iid],
					"size" => 5
				)),
				"name" => html::href(array(
					"caption" => $item->name(),
					"url" => $this->mk_my_orb("change", array("id" => $iid), $item->class_id())
				)),
				"quantity" => html::textbox(array(
					"name" => "quant[$iid]",
					"value" => is_array($quant) ? $quant[0]["items"] : $quant,
					"size" => 5
				))
			));
		}

		$t->set_default_sortby("page");
		$t->sort_by();
	}


	function _init_undone_tbl(&$t,$cl)
	{
		$t->define_field(array(
			"name" => "code",
			"caption" => t("Kood"),
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "product",
			"caption" => t("Toode"),
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("M&otilde;&otilde;t&uuml;hik"),
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "packaging",
			"caption" => t("Pakend"),
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "amount",
			"caption" => t("Tellitav Kogus"),
			"align" => "center",
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "date",
			"caption" => t("Soovitav tarne t&auml;itmine"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		
		$t->define_field(array(
			"name" => "order_date",
			"caption" => t("Tellimuse kuup&auml;ev"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
/*
		$t->define_field(array(
			"name" => "bill",
			"caption" => t("Tellimuse kuup&auml;ev"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
*/		
		if(!$cl)$t->define_field(array(
			"name" => "client",
			"caption" => t("Klient"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		
		$t->define_field(array(
			"name" => "order",
			"caption" => t("Tellimuse nr."),
			"align" => "center",
			"chgbgcolor" => "color",
		));
	}
	
	function __br_sort($a, $b)
	{
		if(!($this->can("view" , $a) && $this->can("view" , $b))) return 1;
		$p1 = obj($a);
		$p2 = obj($b);
		if($p1->name() > $p2->name()) return 1;
		return -1;
	}
	
	/**
		@attrib name=unsent_table
		@param client optional type=id acl=view
	**/
	function unsent_table($arr)
	{
		classload("vcl/table");
		$arr["prop"]["vcl_inst"] = new aw_table(array(
			"layout" => "generic"
		));
		$arr["cl"] = 1;
		$this->do_order_undone_tbl($arr);
		return $arr["prop"]["vcl_inst"]->draw();
	}
	

	/**
		@attrib name=undone_xls
		@param undone_xls optional type=id acl=view
	**/
	function undone_xls($arr)
	{
		classload("vcl/table");
		$arr["prop"]["vcl_inst"] = new aw_table(array(
			"layout" => "generic"
		));
		$arr["xls"] = 1;
		$this->do_order_undone_tbl($arr);
		header("Content-type: application/csv");
		header("Content-disposition: inline; filename=undone.csv;");
		die($arr["prop"]["vcl_inst"]->get_csv_file());
	}
	
	function do_order_undone_tb(&$arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_button(array(
			"name" => "xls",
			"tooltip" => t("Exceli-tabeli vormis"),
			"url" => $this->mk_my_orb("undone_xls", array(
				"id" => $arr["obj_inst"]->id(),
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE)
		));
		if ($arr["request"]["group"] != "order_undone")
		{
			$tb->add_button(array(
				"name" => "confirm",
				"img" => "save.gif",
				"tooltip" => t("Kinnita tellimused"),
				"action" => "confirm_orders",
				"confirm" => t("Oled kindel, et soovid valitud tellimused kinnitada?"),
			));
		}
	}
	
	function do_order_undone_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$cl = $arr["cl"];
		$xls = $arr["xls"];
		$this->_init_undone_tbl($t,$cl);

		// list orders from order folder
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
//			"confirmed" => 0
		);
		if($arr["client"])
		{
			$filter["orderer_company"] = $arr["client"];
		}
		if($cl)
		{
			$filter["createdby"] = aw_global_get("uid");
		}
		$ol = new object_list($filter);
		
		$undone_products = array();
		$ord_data = array();
		foreach($ol->arr() as $o)
		{
			foreach($o->meta("ord_item_data") as $id => $items)
			{
				foreach($items as $item)
				{
					if($item["unsent"])
					{
						$ord_data[$o->id()][$id] = $item;
						$undone_products[$id][$o->id()] = $item["unsent"];
						break;
					}
				}
			}
		}
		$upkeys = array_keys($undone_products);
		usort($upkeys, array(&$this, "__br_sort"));
		foreach($upkeys as $product)
		{
			$order = $undone_products[$product];
			if(!$this->can("view" , $product)) continue;
			$product_obj = obj($product);
			$unit = "";
			if($this->can("view", $product_obj->prop("uservar1")))
			{
				$cls_obj = obj($product_obj->prop("uservar1"));
				$unit = $cls_obj->name();
			}
			if(!$xls) $t->define_data(array(
				"product" => $cl?$product_obj->name():html::get_change_url($product, array("return_url" => get_ru()) , $product_obj->name()),
				"code" => $product_obj->prop("user2"),
				"unit" => $unit,
				"packaging" => $product_obj->prop("user1"),
			));
			
			$prod_count = 0;
			
			foreach($order as $key => $amount)
			{
				if(!$this->can("view" , $key)) continue;
				$order = obj($key);
				$client = "";
				if($this->can("view" , $order->prop("orderer_company")))
				{
					$client_o = obj($order->prop("orderer_company"));
					$client = html::get_change_url($order->prop("orderer_company"), array("return_url" => get_ru()) , $client_o->name());
				}
				
		
				$t->define_data(array(
					"product" => (!$xls)?"":($cl?$product_obj->name():html::get_change_url($product, array("return_url" => get_ru()) , $product_obj->name())),
					"code" => (!$xls)?"":($product_obj->prop("user2") ? $product_obj->prop("user2") : " "),
					"unit" => (!$xls)?"":($unit ? $unit : " "),
					"packaging" => (!$xls)?"":($product_obj->prop("user1") ? $product_obj->prop("user1") : " "),
					"order" => $cl?html::href(array("url" => $key, "caption" => $key)):html::get_change_url($key, array("return_url" => get_ru() , "group" => "items") , $key),
					"client" => $client,
					"amount" => $amount,
					"color" => $order->prop("confirmed")?"":"#CCFFCC",
					
		//			"packaging" => $ord_data[$order->id()][$product]["user1"],
					"date" => $ord_data[$order->id()][$product]["duedate"],
					"bill" => $ord_data[$order->id()][$product]["bill"],
					"order_date" => date("d.m.Y" , $order->created()),
				));
				$prod_count+=$amount;
			}
			

			if(!$xls) $t->define_data(array(
				"product" => t("Kokku:"),
				"amount" => "<b>".$prod_count."</b>",

			));
		}

		$t->set_sortable(false);

//		$t->set_default_sortby("modified");
//		$t->set_default_sorder("DESC");
//		$t->sort_by();
	}

	function do_del_prod($arr)
	{
		$awa = new aw_array($arr["request"]["sel"]);
		foreach($awa->get() as $oid)
		{
			$o = obj($oid);
			$o->delete();
		}
	}

	function mk_prod_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "crt_".$this->prod_type_fld,
			"tooltip" => t("Uus")
		));

		$this->_req_add_itypes($tb, $this->prod_type_fld, $data);

		$tb->add_menu_item(array(
			"parent" => "crt_".$this->prod_type_fld,
			"text" => t("Lisa kaust"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			), CL_MENU)
		));


		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			"url" => "javascript:document.changeform.submit()"
		));

		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Lisa korvi"),
			"action" => "add_to_cart"
		));
		
		$tb->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"tooltip" => t("Kopeeri"),
			"action" => "copy_products"
		));
		
		$tb->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"tooltip" => t("L&otilde;ika"),
			"action" => "cut_products"
		));
	
		$tb->add_button(array(
			"name" => "paste",
			"img" => "paste.gif",
			"tooltip" => t("Kleebi"),
			"url" => $this->mk_my_orb("paste_products", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			))
			//"action" => "paste_products"
		));
	}
	
	function mk_prodg_toolbar(&$prop)
	{
		$tb =& $prop["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa uus tootegrupp"),
			"url" => $this->mk_my_orb("new", array(
				"parent" => $this->prod_type_fld,
				"return_url" => get_ru(),
				"cfgform" => $this->prod_type_cfgform,
			), CL_WEBFORM),
		));
		
		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			"action" => "remove_prod_type",
		));
	}
	
	function do_prodg_list($arr)
	{
		$this->_init_do_prodg_list($arr);
		$t = &$arr["prop"]["vcl_inst"];
		$ol = new object_list(array(
			"parent" => $this->prod_type_fld,
			"class_id" => CL_WEBFORM,
		));
		foreach($ol->arr() as $obj)
		{
			$t->define_data(array(
				"id" => $obj->id(),
				"name" => $obj->name(),
				"change" => html::get_change_url($obj->id(), array(
					"group" => "form",
					"return_url" => get_ru(),
				), t("Muuda")),
			));
		}
	}
	
	function _init_do_prodg_list($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
		));
		$t->define_chooser(array(
			"field" => "id",
		));
	}

	function _req_add_itypes(&$tb, $parent, &$data)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_MENU, CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() != CL_MENU)
			{
				$tb->add_menu_item(array(
					"parent" => "crt_".$parent,
					"text" => $o->name(),
					"link" => $this->mk_my_orb("new", array(
						"item_type" => $o->id(),
						"parent" => $this->prod_tree_root,
						"alias_to" => $data["obj_inst"]->id(),
						"reltype" => 2, //RELTYPE_PRODUCT,
						"return_url" => get_ru(),
						"cfgform" => $o->prop("sp_cfgform"),
						"object_type" => $o->prop("sp_object_type")
					), CL_SHOP_PRODUCT)
				));
			}
			else
			{
				$tb->add_sub_menu(array(
					"parent" => "crt_".$parent,
					"name" => "crt_".$o->id(),
					"text" => $o->name()
				));
				$this->_req_add_itypes($tb, $o->id(), $data);
			}
		}
	}

	function _get_prod_tree(&$arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("prod_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "prods",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("prod_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));
	}

	function _get_products_list(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$this->_init_prod_list_list_tbl($tb, $arr["obj_inst"]);

		// get items 
		if (!$_GET["tree_filter"])
		{
			$ot = new object_list();
		}
		else
		{
			$ot = new object_list(array(
				"parent" => $_GET["tree_filter"],
				"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
				"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
			));
			$tb->set_caption(sprintf(t("Artiklid kategoorias %s"), obj($_GET["tree_filter"])->path_str(array("start_at" => $this->config->prop("prod_fld")))));
		}

		classload("core/icons");

		//$ol = $ot->to_list();
		$ol = $ot->arr();
		foreach($ol as $o)
		{

			if ($o->class_id() == CL_MENU)
			{
				$tp = t("Kaust");
			}
			else
			if (is_oid($o->prop("item_type")))
			{
				$tp = obj($o->prop("item_type"));
				$tp = $tp->name();
			}
			else
			{
				$tp = "";
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$put = "";
			if ($o->class_id() != CL_MENU)
			{
				$put = html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("Vii lattu")
				));
			}

			$name = $o->path_str(array("to" => $this->prod_fld));
			if ($o->class_id() == CL_MENU)
			{
				$name = html::href(array(
					"url" => aw_url_change_var("tree_filter", $o->id()),
					"caption" => $name
				));
			}

			$tb->define_data(array(
				"icon" => html::img(array("url" => icons::get_icon_url($o->class_id(), $o->name()))),
				"name" => html::obj_change_url($o), //$name,
				"cnt" => $o->prop("item_count"),
				"item_type" => $tp,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"return_url" => get_ru()
					), $o->class_id()),
					"caption" => t("Muuda")
				)),
				"get" => $get,
				"put" => $put,
				"del" => html::checkbox(array(
					"name" => "sel[]",
					"value" => $o->id()
				)),
				"is_menu" => ($o->class_id() == CL_MENU ? 0 : 1),
				"ord" => html::textbox(array(
					"name" => "set_ord[".$o->id()."]",
					"value" => $o->ord(),
					"size" => 5
				)).html::hidden(array(
					"name" => "old_ord[".$o->id()."]",
					"value" => $o->ord()
				)),
				"hidden_ord" => $o->ord()
			));
		}

		$tb->set_numeric_field("hidden_ord");				
		$tb->set_default_sortby(array("is_menu", "hidden_ord"));
	}

	function _init_prod_list_list_tbl(&$t, $o)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "code",
			"caption" => t("Kood"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "last_purchase_price",
			"caption" => t("Ostuhind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "price_fifo",
			"caption" => t("FIFO"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sales_price",
			"caption" => t("M&uuml;&uuml;gihind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount1",
			"caption" => t("Kogus"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit1",
			"caption" => t("&Uuml;hik"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount2",
			"caption" => t("Kogus 2"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit2",
			"caption" => t("&Uuml;hik 2"),
			"align" => "center"
		));


		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

/*		$conf = obj($o->prop("conf"));
		if (!$conf->prop("no_count"))
		{
			$t->define_field(array(
				"sortable" => 1,
				"name" => "cnt",
				"caption" => t("Kogus laos"),
				"align" => "center",
				"type" => "int"
			));

			$t->define_field(array(
				"name" => "get",
				"caption" => t("V&otilde;ta laost"),
				"align" => "center"
			));

			$t->define_field(array(
				"name" => "put",
				"caption" => t("Vii lattu"),
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));*/

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _init_view(&$arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			//$arr["prop"]["value"] =  t("VIGA: konfiguratsioon on valimata!");
			return false;
		}
		$this->config = obj($arr["obj_inst"]->prop("conf"));
		//"prod_type_cfgform", 
		$checks = array("prod_fld", "pkt_fld", "reception_fld", "export_fld", "prod_type_fld", "order_fld", "buyers_fld");
		foreach($checks as $check)
		{
			if(!$this->config->prop($check))
			{
				return false;
			}
		}
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on pakettide kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on sissetulekute kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on v&auml;jaminekute kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete t&uuml;&uuml;pide kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellimuste kataloog valimata!";
		//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellijate kataloog valimata!";

		$this->prod_fld = $this->config->prop("prod_fld");
		$this->prod_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("prod_fld");

		$this->pkt_fld = $this->config->prop("pkt_fld");
		$this->pkt_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("pkt_fld");

		$this->prod_type_cfgform = $this->config->prop("prod_type_cfgform");
		$this->reception_fld = $this->config->prop("reception_fld");
		$this->export_fld = $this->config->prop("export_fld");
		$this->prod_type_fld = $this->config->prop("prod_type_fld");
		$this->order_fld = $this->config->prop("order_fld");
		$this->buyers_fld = $this->config->prop("buyers_fld");
		$this->prod_conf_folder = $this->config->prop("prod_conf_folder");

		return true;
	}

	function mk_pkt_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_pkt",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_pkt",
			"text" => t("Lisa pakett"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->pkt_tree_root,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 2, //RELTYPE_PACKET,
				"return_url" => get_ru()
			), CL_SHOP_PACKET)
		));

		$tb->add_button(array(
			"name" => "del",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud"),
			"url" => "javascript:document.changeform.submit()"
		));

		$tb->add_button(array(
			"name" => "save",
			"img" => "save.gif",
			"tooltip" => t("Lisa korvi"),
			"action" => "add_to_cart"
		));
		
		$tb->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"tooltip" => t("Kopeeri"),
			"action" => "copy_products"
		));
		
		$tb->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"tooltip" => t("L&otilde;ika"),
			"action" => "cut_products"
		));
	
		$tb->add_button(array(
			"name" => "paste",
			"img" => "paste.gif",
			"tooltip" => t("Kleebi"),
			"url" => $this->mk_my_orb("paste_products", array(
				"parent" => $this->prod_tree_root,
				"return_url" => get_ru(),
			))
			//"action" => "paste_products"
		));
	}

	function _get_packets_tree($arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("pkt_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "pkts",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("pkt_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));
	}

	function _get_packets_list(&$arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$this->_init_pkt_list_list_tbl($tb, $arr["obj_inst"]);

		// get items 
		$ot = new object_tree(array(
			"parent" => $this->pkt_tree_root,
			"class_id" => array(CL_MENU,CL_SHOP_PACKET),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->pkt_fld)),
				"cnt" => $o->prop("item_count"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"return_url" => get_ru()
					), CL_SHOP_PACKET),
					"caption" => t("Muuda")
				)),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => t("Vii lattu")
				))
			));
		}
	}

	function _init_pkt_list_list_tbl(&$t, $o)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "code",
			"caption" => t("Kood"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "last_purchase_price",
			"caption" => t("Ostuhind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "price_fifo",
			"caption" => t("FIFO"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sales_price",
			"caption" => t("M&uuml;&uuml;gihind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount1",
			"caption" => t("Kogus"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit1",
			"caption" => t("&Uuml;hik"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount2",
			"caption" => t("Kogus 2"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit2",
			"caption" => t("&Uuml;hik 2"),
			"align" => "center"
		));


		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

/*		$conf = obj($o->prop("conf"));
		if (!$conf->prop("no_count"))
		{
			$t->define_field(array(
				"sortable" => 1,
				"name" => "cnt",
				"caption" => t("Kogus laos"),
				"align" => "center",
				"type" => "int"
			));

			$t->define_field(array(
				"name" => "get",
				"caption" => t("V&otilde;ta laost"),
				"align" => "center"
			));

			$t->define_field(array(
				"name" => "put",
				"caption" => t("Vii lattu"),
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "change",
			"caption" => t("Muuda"),
			"align" => "center"
		));*/

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function do_storage_list_tbl(&$arr)
	{
		$this->_init_storage_list_tbl($arr["prop"]["vcl_inst"]);

		$tr = $this->get_packet_folder_list(array("id" => $arr["obj_inst"]->id()));
		$items = $this->get_packet_list(array(
			"id" => $arr["obj_inst"]->id(),
			"parent" => $tr[1]->ids()
		));
		foreach($items as $i)
		{
			if ($i->class_id() == CL_SHOP_PACKET)
			{
				$type = t("Pakett");
				$name = $i->path_str(array("to" => $this->config->prop("pkt_fld")));
			}
			else
			{
				$type = "";
				if (is_oid($i->prop("item_type")))
				{
					$type_o = obj($i->prop("item_type"));
					$type = $type_o->name();
				}
				$name = $i->path_str(array("to" => $this->config->prop("prod_fld")));
			}

			$get = "";
			if ($i->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => t("V&otilde;ta laost")
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $name,
				"type" => $type,
				"count" => $i->prop("item_count"),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => t("Vii lattu")
				))
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_list_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "count",
			"caption" => t("Laoseis"),
			"type" => "int",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => t("V&otilde;ta laost"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => t("Vii lattu"),
			"align" => "center"
		));

	}

	function _get_storage_income(&$arr)
	{
		$this->_init_storage_income_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_INCOME")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_income_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "number",
			"caption" => t("number")
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "acquirer",
			"caption" => t("Hankija"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "relations",
			"caption" => t("Seosed"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function save_storage_inc_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_WAREHOUSE_RECEPTION);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_income_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_reception",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_reception",
			"text" => t("Lisa sissetulek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->reception_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 3, //RELTYPE_STORAGE_INCOME,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_RECEPTION)
		));
	}


	function _get_storage_export(&$arr)
	{
		$this->_init_storage_export_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_EXPORT")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_EXPORT)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_export_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "number",
			"caption" => t("number")
		));

		$t->define_field(array(
			"name" => "type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "acquirer",
			"caption" => t("Hankija"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "relations",
			"caption" => t("Seosed"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function save_storage_exp_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_WAREHOUSE_EXPORT);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm export
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_export_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_export",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_export",
			"text" => t("Lisa v&auml;ljaminek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->export_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 4, //RELTYPE_STORAGE_EXPORT,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_EXPORT)
		));
	}

	/** creates a new export object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_export

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_export($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->export_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_EXPORT);
		$e->set_name(sprintf(t("Lao v&auml;ljaminek: %s"), $p->name()));
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => "RELTYPE_PRODUCT",
		));

		// also connect the export to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => "RELTYPE_STORAGE_EXPORT",
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "export",
			"return_url" => $this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_export"
			))
		), CL_SHOP_WAREHOUSE_EXPORT);
	}

	/** creates a new reception object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_reception

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_reception($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->reception_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_RECEPTION);
		$e->set_name(sprintf(t("Lao sissetulek: %s"), $p->name()));
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => "RELTYPE_PRODUCT",
		));

		// also connect the reception to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => "RELTYPE_STORAGE_INCOME",
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "income",
			"return_url" => $this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_income"
			))
		), CL_SHOP_WAREHOUSE_RECEPTION);
	}

	function mk_order_unconfirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => t("Uus tellimus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => t("Lisa tellimus"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 5, //RELTYPE_ORDER,
				"return_url" => get_ru()
			), CL_SHOP_ORDER)
		));
		$tb->add_button(array(
			"name" => "confirm",
			"img" => "save.gif",
			"tooltip" => t("Kinnita tellimused"),
			"action" => "confirm_orders",
			"confirm" => t("Oled kindel, et soovid valitud tellimused kinnitada?"),
		));
		$tb->add_button(array(
			"name" => "print",
			"tooltip" => t("Prindi tellimused"),
			"img" => "print.gif",
			"url" => "javascript:document.changeform.target='_blank';javascript:submit_changeform('print_orders')",
//			"url" => $this->mk_my_orb("print_orders", array(
//				"id" => $arr["obj_inst"]->id(),
//				"return_url" => get_ru()
//			), CL_ORDERS_MANAGER)
		));
	}

	function do_order_unconfirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_unconfirmed_tbl($t);

		// list orders from order folder

		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 0
		);
		$search_data = $arr["obj_inst"]->meta("search_data");
		if($search_data["find_name"])
		{
			$filter [] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_SHOP_ORDER.orderer_person.name" => "%".$search_data["find_name"]."%",
					"CL_SHOP_ORDER.orderer_company.name" => "%".$search_data["find_name"]."%",
					"name" => "%".$search_data["find_name"]."%",
			)));
		}

		if((date_edit::get_timestamp($search_data["find_start"]) > 1)|| (date_edit::get_timestamp($search_data["find_end"]) > 1))
		{
			if(date_edit::get_timestamp($search_data["find_start"]) > 1)
			{
				$from = date_edit::get_timestamp($search_data["find_start"]);
			}
			else
			{
				 $from = 0;
			}
			if(date_edit::get_timestamp($search_data["find_end"]) > 1)
			{
				$to = date_edit::get_timestamp($search_data["find_end"]);
			}
			else
			{
				$to = time()*666;
			}
			$filter["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 24*3600));
		}

		$ol = new object_list($filter);

		foreach($ol->arr() as $o)
		{
			$mb = $o->modifiedby();
			if (is_oid($o->prop("orderer_person")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_person = obj($o->prop("orderer_person"));
				$mb = $_person->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_person")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}

			if (is_oid($o->prop("orderer_company")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_comp = obj($o->prop("orderer_company"));
				$mb .= " / ".$_comp->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_company")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}
			$color = "";
			$pd_data = $o->meta("ord_item_data");
			foreach($pd_data as $prod)
			{
				foreach($prod as $p)
				{
					if($p["unsent"])
					{
						$color = "#BBCCEE";
						break;
					}
				}
				if($color) break;
			}
			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->name(),
				"modifiedby" => $mb,
				"modified" => $o->created(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"group" => "items",
						"return_url" =>get_ru(),// urlencode(aw_ini_get("baseurl").aw_global_get("REQUEST_URI")),
					), CL_SHOP_ORDER),
					"caption" => t("Vaata")
				)),
				"confirm" => html::checkbox(array(
					"name" => "confirm[".$o->id()."]",
					"value" => 1
				)),
				"price" => $o->prop("sum"),
				"color" => $color,
			));
		}
		$t->set_default_sortby("modified");
		$t->set_default_sorder("DESC");
		$t->sort_by();
	}

	function _init_order_unconfirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
			"chgbgcolor" => "color",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
/*
		$t->define_field(array(
			"name" => "confirm",
			"caption" => t("Kinnita"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
*/
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Kes"),
			"align" => "center",
			"sortable" => 1,
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Millal"),
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center",
			"sortable" => 1,
			"chgbgcolor" => "color",
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center",
			"chgbgcolor" => "color",
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

	}

	function mk_order_confirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => t("Uus tellimus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => t("Lisa tellimus"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => 5, //RELTYPE_ORDER,
				"return_url" => get_ru()
			), CL_SHOP_ORDER)
		));
		$tb->add_button(array(
			"name" => "print",
			"tooltip" => t("Prindi tellimused"),
			"img" => "print.gif",
			"url" => "javascript:document.changeform.target='_blank';javascript:submit_changeform('print_orders')",
//			"url" => $this->mk_my_orb("print_orders", array(
//				"id" => $arr["obj_inst"]->id(),
//				"return_url" => get_ru()
//			), CL_ORDERS_MANAGER)
		));
	}

	function do_order_confirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_confirmed_tbl($t);

		// list orders from order folder

		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 1
		);
		$search_data = $arr["obj_inst"]->meta("search_data");
		if($search_data["find_name"])
		{
			$filter [] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_SHOP_ORDER.orderer_person.name" => "%".$search_data["find_name"]."%",
					"CL_SHOP_ORDER.orderer_company.name" => "%".$search_data["find_name"]."%",
			)));
		}

		if((date_edit::get_timestamp($search_data["find_start"]) > 1)|| (date_edit::get_timestamp($search_data["find_end"]) > 1))
		{
			if(date_edit::get_timestamp($search_data["find_start"]) > 1)
			{
				$from = date_edit::get_timestamp($search_data["find_start"]);
			}
			else
			{
				 $from = 0;
			}
			if(date_edit::get_timestamp($search_data["find_end"]) > 1)
			{
				$to = date_edit::get_timestamp($search_data["find_end"]);
			}
			else
			{
				$to = time()*666;
			}
			$filter["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($from - 1), ($to + 24*3600));
		}

		$ol = new object_list($filter);


		foreach($ol->arr() as $o)
		{
			$mb = $o->modifiedby();
			if (is_oid($o->prop("orderer_person")) && $this->can("view", $o->prop("orderer_person")))
			{
				$_person = obj($o->prop("orderer_person"));
				$mb = $_person->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_person")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}

			if (is_oid($o->prop("orderer_company")) && $this->can("view", $o->prop("orderer_company")))
			{
				$_comp = obj($o->prop("orderer_company"));
				$mb .= " / ".$_comp->name();
			}
			else
			if (is_oid($o->prop("oc")))
			{
				$oc = obj($o->prop("oc"));
				if (($pp = $oc->prop("data_form_company")))
				{
					$_ud = $o->meta("user_data");
					$mb = $_ud[$pp];
				}
			}
			$t->define_data(array(
				"id" => $o->id(),
				"name" => $o->name(),
				"madeby" => $mb,
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"group" => "items",
						"return_url" => get_ru(),
					), CL_SHOP_ORDER),
					"caption" => t("Vaata")
				)),
				"price" => $o->prop("sum")
			));
		}
	}

	function _init_order_confirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "madeby",
			"caption" => t("Kes"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Kinnitas"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Millal"),
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));

	}

	function save_order_unconfirmed_tbl(&$arr)
	{
		$re = get_instance(CL_SHOP_ORDER);

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function _init_order_orderer_cos_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
		));
		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kes"),
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => t("Millal"),
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata"),
		));
	}

	function do_order_orderer_cos_tbl($arr)
	{
		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_order_orderer_cos_tbl($t);

		// get orders by orderer
		if ($arr["request"]["tree_worker"])
		{
			$ol = new object_list(array(
				"class_id" => CL_SHOP_ORDER,
				"orderer_person" => $arr["request"]["tree_worker"]
			));
		}
		else
		if ($arr["request"]["tree_company"])
		{
			// get workers for co
			$co = obj($arr["request"]["tree_company"]);
			$ids = array();
			$con = new connection();
			foreach($con->find(array("from.class_id" => CL_CRM_PERSON, "to" => $co->id())) as $c)
			{
				$ids[] = $c["from"];
			}
			if (!count($ids))
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_SHOP_ORDER,
					"orderer_person" => $ids
				));
			}
		}
		else
		if ($arr["request"]["tree_code"])
		{
			// get workers for co
			$categories = new object_list(array(
				"parent" => $this->buyers_fld,
				"class_id" => CL_CRM_SECTOR,
				"kood" => $arr["request"]["tree_code"]."%"
			));
			$ids = array();
			for($cat = $categories->begin(); !$categories->end(); $cat = $categories->next())
			{
				foreach($cat->connections_to(array("from.class_id" => CL_CRM_COMPANY)) as $c)
				{
					$co = $c->from();
					foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
					{
						$ids[] = $c->prop("to");
					}
				}
			}

			if (count($ids) < 1)
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_SHOP_ORDER,
					"orderer_person" => $ids
				));
			}
		}
		else
		{
			$ol = new object_list();
		}

		$oinst = get_instance(CL_SHOP_ORDER);
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"name" => $o->name(),
				"price" => $o->prop("sum"),
				"who" => $oinst->get_orderer($o),
				"when" => $o->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
					"caption" => t("Vaata")
				))
			));
		}
	}

	function do_order_orderer_cos_tree(&$arr)
	{
		// get categories
		$categories = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_SECTOR,
		));

		$all_cos = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_COMPANY
		));
		$this->all_cos_ids = $all_cos->names();

		$tv = $this->get_vcl_tree_from_cat_list($categories);

		// now, add all remaining cos as top level items
		foreach($this->all_cos_ids as $co_id => $con)
		{
			$tv->add_item(0, array(
				"name" => $con,
				"id" => "nocode_co".$co_id,
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co_id, aw_url_change_var("tree_worker", NULL)))
			));

			$co = obj($co_id);
			// now all people for that company
			foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
			{
				$tv->add_item("nocode_co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => "nocode_wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}

	
		$arr["prop"]["value"] = $tv->finalize_tree();
	}

	function get_vcl_tree_from_cat_list($categories)
	{
		// now, gotst to make tree out of them. 
		// algorithm is: sort by length, add the shortest to first level, then start adding by legth
		// prop: kood
		$ta = array();
		$ids = array();
		for($o = $categories->begin(); !$categories->end(); $o = $categories->next())
		{
			$ta[$o->prop("kood")] = $o;
			$ids[] = $o->id();
		}
		uksort($ta, array(&$this, "__ta_sb_cb"));

		// get all companies with these categories
		$cos = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"pohitegevus" => $ids
		));
		$this->cos_by_code = array();
		for($o = $cos->begin(); !$cos->end(); $o = $cos->next())
		{
			// get all type rels
			foreach($o->connections_from(array("to.class_id" => CL_CRM_SECTOR)) as $c)
			{
				$s = $c->to();
				$this->cos_by_code[$s->prop("kood")][] = $o;
			}
		}

		// now, start adding things to the tree.
		$tv = get_instance("vcl/treeview");
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "shwhordcos",
			"persist_state" => true
		));

		$this->_req_filter_and_add($tv, $ta, "", 0);

		return $tv;
	}

	function _req_filter_and_add(&$tv, $ta, $filter_code, $parent)
	{
		$nta = array();

		$fclen = strlen($filter_code);
		$minl = 1000;
		$cpta = $ta;

		foreach($cpta as $code => $code_o)
		{
			if (substr($code, 0, $fclen) == $filter_code && $code != $filter_code)
			{
				$nta[$code] = $code_o;
				if (strlen($code) < $minl)
				{
					$minl = strlen($code);
				}
			}
		}

		if (count($nta) < 1)
		{
			// we reached the end of the tree, add cos now
			$this->_do_add_cos_by_code($tv, $filter_code);
			return;
		}

		uksort($nta, array(&$this, "__ta_sb_cb"));

		reset($nta);
		list($code, $code_o) = each($nta);
		while (strlen($code) == $minl)
		{
			$tv->add_item($parent, array(
				"name" => $code_o->name(),
				"id" => $code,
				"url" => aw_url_change_var("tree_code", $code, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", NULL)))
			));

			// now find the children for this. 
			// how to do this? simple, filter the list by the start of this code and sort and insert smallest length, 
			// lather, rinse, repeat
			$this->_req_filter_and_add($tv, $nta, $code, $code);

			list($code, $code_o) = each($nta);
		}
	}

	function _do_add_cos_by_code(&$tv, $code)
	{
		if (!is_array($this->cos_by_code[$code]))
		{
			return;
		}
		foreach($this->cos_by_code[$code] as $co)
		{
			$tv->add_item($code, array(
				"name" => $co->name(),
				"id" => $code."co".$co->id(),
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co->id(), aw_url_change_var("tree_worker", NULL)))
			));
			unset($this->all_cos_ids[$co->id()]);

			// now all people for that company
			foreach($co->connections_from(array("type" => "RELTYPE_WORKER")) as $c)
			{
				$tv->add_item($code."co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => $code."wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}
	}

	function __ta_sb_cb($a, $b)
	{
		return ($a == $b ? 0 : ((strlen($a) < strlen($b)) ? -1 : 1));
	}

	function callback_pre_edit($arr)
	{
		if (!$arr["obj_inst"]->prop("order_current_org") && 
			is_oid($arr["obj_inst"]->prop("order_current_person")) && 
			$this->can("view", $arr["obj_inst"]->prop("order_current_person"))
		)
		{
			// get the org from the person 
			$pers = obj($arr["obj_inst"]->prop("order_current_person"));
			$conn = reset($pers->connections_from(array(
				"type" => "RELTYPE_WORK"
			)));
			if ($conn)
			{
				$arr["obj_inst"]->set_prop("order_current_org", $conn->prop("to"));
				$tmp = $arr["obj_inst"]->meta("popup_search[order_current_org]");
				$tmp[$conn->prop("to")] = $conn->prop("to");
				$arr["obj_inst"]->set_meta("popup_search[order_current_org]", $tmp);
				$arr["obj_inst"]->save();
			}
		}
	}

	function callback_pre_save($arr)
	{
		if ($arr["request"]["group"] == "order_current")
		{
			$arr["obj_inst"]->set_meta("order_cur_ud", $arr["request"]["user_data"]);
		}

		if ($this->upd_ud)
		{
			$this->do_update_user_data(array(
				"oid" => $arr["obj_inst"]->id()
			));
		}
	}

	function callback_get_order_current_form($arr)
	{
		$ret = array();

		$o = $arr["obj_inst"];
		$cud = $o->meta("order_cur_ud");

		// get order center
		if (!$o->prop("order_center"))
		{
			return $ret;
		}
		$oc = obj($o->prop("order_center"));
		$oc_i = $oc->instance();

		$props = $oc_i->get_properties_from_data_form($oc, $cud);

		if ($arr["no_data"])
		{
			return $props;
		}

		if (($pp = $oc->prop("data_form_person")) && is_oid($o->prop("order_current_person")))
		{
			$po = obj($o->prop("order_current_person"));
			$props[$pp]["value"] = $po->name();
			$props[$pp]["type"] = "hidden";
			$props[$pp."_show"] = $props[$pp];
			$props[$pp."_show"]["type"] = "text";
		}

		if (($pp = $oc->prop("data_form_company")) && $o->prop("order_current_org"))
		{
			$po = obj($o->prop("order_current_org"));
			$props[$pp]["value"] = $po->name();
			$props[$pp]["type"] = "hidden";
			$props[$pp."_show"] = $props[$pp];
			$props[$pp."_show"]["type"] = "text";
		}

		return $props;
	}

	function do_search_res_tbl($arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			return;
		}
		$conf = obj($arr["obj_inst"]->prop("conf"));
		if (!$conf->prop("search_form"))
		{
			return;
		}
		$sf = obj($conf->prop("search_form"));
		$sf_i = $sf->instance();

		$sf_i->get_search_result_table(array(
			"ob" => $sf,
			"t" => &$arr["prop"]["vcl_inst"],
			"request" => $arr["request"]
		));

		// add select column
		$arr["prop"]["vcl_inst"]->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	/** finishes the order 

		@attrib name=gen_order

		@param id required type=int acl=view
		@param user_data optional
		@param html optional
	**/
	function gen_order($arr)
	{
		$ordid = $this->make_cur_order_id($arr);

		return $this->mk_my_orb("gen_pdf", array(
			"id" => $ordid,
			"html" => $arr["html"],
		), CL_SHOP_ORDER);
	}

	function make_cur_order_id($arr)
	{
		$o = obj($arr["id"]);
		$oc = $o->prop("order_center");
		error::raise_if(!$oc, array(
			"id" => ERR_NO_OC,
			"msg" => t("shop_warehouse::gen_order(): no order center object selected!")
		));

		$soc = get_instance(CL_SHOP_ORDER_CART);
		if (!aw_global_get("wh_order_cur_order_id"))
		{
			$ordid = $soc->do_create_order_from_cart($oc, $arr["id"], array(
				"pers_id" => $o->prop("order_current_person"),
				"com_id" => $o->prop("order_current_org"),
				"user_data" => $o->meta("order_cur_ud"),
				"discount" => $o->meta("order_cur_discount"),
				"prod_paging" => $o->meta("order_cur_pages"),
				"no_send_mail" => 1
			));
			aw_session_set("wh_order_cur_order_id", $ordid);
		}
		return aw_global_get("wh_order_cur_order_id");
	}

	function callback_get_search_form($arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			return;
		}
		$conf = obj($arr["obj_inst"]->prop("conf"));
		if (!$conf->prop("search_form"))
		{
			return;
		}
		$sf = obj($conf->prop("search_form"));
		$sf_i = $sf->instance();
		return $sf_i->get_callback_properties($sf);
	}

	function do_search_tb($arr)
	{
		$tb =& $arr["prop"]["toolbar"];

		$tb->add_button(array(
			"name" => "add_to_order",
			"img" => "import.gif",
			"tooltip" => t("Lisa pakkumisse"),
			"action" => "add_to_cart"
		));

		$tb->add_button(array(
			"name" => "go_to_order",
			"img" => "save.gif",
			"tooltip" => t("Moodusta pakkumine"),
			"url" => $this->mk_my_orb("change", array("id" => $arr["obj_inst"]->id(), "group" => "order_current"))
		));
	}

	/** message handler for the MSG_POPUP_SEARCH_CHANGE message so we can update
		the person/company listboxes when one changes
	**/
	function on_popup_search_change($arr)
	{
		if ($arr["prop"] == "order_current_org")
		{
			$this->do_update_persons_from_org($arr);
		}
		else
		{
			$this->do_update_orgs_from_person($arr);
		}

		$this->do_update_user_data(array(
			"oid" => $arr["oid"]
		));	
	}

	function do_update_user_data($arr)
	{
		// also update the data form data, based on the property maps from the order center
		// first org
		if(!is_oid($arr["oid"]) || !$this->can("view", $arr["oid"]))
		{
			return;
		}
		$o = obj($arr["oid"]);

		$oc = get_instance(CL_SHOP_ORDER_CENTER);
		$personmap = $oc->get_property_map($o->prop("order_center"), "person");
		$orgmap = $oc->get_property_map($o->prop("order_center"), "org");

		$cud = $o->meta("order_cur_ud");

		// get selected person object
		if (($ps = $o->prop("order_current_person")))
		{
			$person = obj($ps);
			$ps_props = $person->get_property_list();

			foreach($personmap as $data_f_prop => $person_o_prop)
			{
				if ($ps_props[$person_o_prop]["type"] == "relmanager")
				{
					$tmp = $person->prop($person_o_prop);
					if (is_oid($tmp))
					{
						$tmp = obj($tmp);
						$cud[$data_f_prop] = $tmp->name();
					}
				}
				else
				{
					$cud[$data_f_prop] = $person->prop($person_o_prop);
				}
			}
		}

		if (($org = $o->prop("order_current_org")))
		{
			$org = obj($org);
			$org_props = $org->get_property_list();

			foreach($orgmap as $data_f_prop => $org_o_prop)
			{
				if ($org_props[$org_o_prop]["type"] == "relmanager")
				{
					$tmp = $org->prop($org_o_prop);
					if (is_oid($tmp))
					{
						$tmp = obj($tmp);
						$cud[$data_f_prop] = $tmp->name();
					}
				}
				else
				{
					$cud[$data_f_prop] = $org->prop($org_o_prop);
				}
			}
		}

		$o->set_meta("order_cur_ud", $cud);
		$o->save();
	}

	function do_update_persons_from_org($arr)
	{
		$o = obj($arr["oid"]);
		$cur_co = $o->prop($arr["prop"]);
		if (!is_oid($cur_co))
		{
			return;
		}

		$workers = array();

		$co = get_instance(CL_CRM_COMPANY);
		$co->get_all_workers_for_company(obj($cur_co), &$workers, true);

		$pop = get_instance("vcl/popup_search");
		$pop->set_options(array(
			"obj" => $o,
			"prop" => "order_current_person",
			"opts" => $workers
		));
	}

	function do_update_orgs_from_person($arr)
	{
		$o = obj($arr["oid"]);
		$cur_person = $o->prop($arr["prop"]);

		if (!is_oid($cur_person))
		{
			return;
		}

		$ps = get_instance(CL_CRM_PERSON);
		$cos = $ps->get_all_employers_for_person(obj($cur_person));

		$pop = get_instance("vcl/popup_search");
		$pop->set_options(array(
			"obj" => $o,
			"prop" => "order_current_org",
			"opts" => $cos
		));
	}

	///////////////////////////////////////////////
	// warehouse public interface functions      //
	///////////////////////////////////////////////

	/** returns an object_tree of warehouse folders

		@attrib param=name api=1

		@param id required

	**/
	function get_packet_folder_list($arr)
	{
		$o = obj($arr["id"]);
		$config = obj($o->prop("conf"));
		$ot = new object_tree(array(
			"parent" => $config->prop("pkt_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE),
			"sort_by" => "objects.jrk"
		));
		return array(obj($config->prop("pkt_fld")), $ot);
	}	

	/** Returns a list of packets/products in the warehouse $id, optionally under folder $parent

		@attrib param=name api=1

		@param id required type=int
			Warehouse object id
		@param parent optional type=var
			Parent folder id or array of parent folders
		@param only_active optional type=bool
			To get only active packets/products
		@param no_subitems optional type=bool
			If true, sub-products are not requested

		@returns Array of packet/product objects
			
	**/
	function get_packet_list($arr)
	{
		enter_function("shop_warehouse::get_packet_list");
		
		$wh = obj($arr["id"]);
		$conf = obj($wh->prop("conf"));

		$status = array(STAT_ACTIVE, STAT_NOTACTIVE);
		if (!empty($arr["only_active"]))
		{
			$status = STAT_ACTIVE;
		}

		$ret = array();

		if($conf->prop("no_packets") != 1 && !is_array($arr['parent']))
		{
			$po = obj((!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("pkt_fld")));
			if ($po->is_brother())
			{
				$po = $po->get_original();
			}
	
			$ol = new object_list(array(
				"parent" => $po->id(),
				"class_id" => CL_SHOP_PACKET,
				"status" => $status
			));
			$ret = $ol->arr();
		}
		
		if (is_array($arr['parent']))
		{
			$parent = $arr['parent'];
		}
		else
		{
			$po = obj((!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("prod_fld")));	
			if ($po->is_brother())
			{
				$po = $po->get_original();
			}
			$parent = $po->id();
		}
		
		enter_function("warehouse::object_list");
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => CL_SHOP_PRODUCT,
			"status" => $status
		));
		$ret = array_merge($ret, $ol->arr());
		exit_function("warehouse::object_list");
		if(!$conf->prop("sell_prods") && empty($arr["no_subitems"]))
		{
			// now, let the classes add sub-items to the list
			$tmp = array();
			foreach($ret as $o)
			{
				$inst = $o->instance();
				foreach($inst->get_contained_products($o) as $co)
				{
					$tmp[] = $co;
				}
			}
			$ret = $tmp;
		}
		exit_function("shop_warehouse::get_packet_list");
		return $ret;
	}

	/** Gives the folder oid where the orders are saved

		@attrib name=get_order_folder params=pos api=1

		@param id required type=object acl=view
			Warehouse object
	**/
	function get_order_folder($w)
	{
		error::raise_if(!$w->prop("conf"), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_order_folder(%s): the warehouse has not configuration object set!"), $w->id())
		));

		$conf = obj($w->prop("conf"));
		$tmp = $conf->prop("order_fld");

		error::raise_if(empty($tmp), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_order_folder(%s): the warehouse configuration has no order folder set!"), $w->id())
		));

		return $tmp;
	}

	/** Returns the products folder id

		@attrib name=get_products_folder params=pos api=1

		@param id required type=object acl=view
			Warehouse object
	**/
	function get_products_folder($w)
	{
		error::raise_if(!$w->prop("conf"), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_products_folder(%s): the warehouse has not configuration object set!"), $w)
		));

		$conf = obj($w->prop("conf"));
		$tmp = $conf->prop("prod_fld");

		error::raise_if(empty($tmp), array(
			"id" => ERR_FATAL,
			"msg" => sprintf(t("shop_warehouse::get_products_folder(%s): the warehouse configuration has no products folder set!"), $w)
		));

		return $tmp;
	}
	/** adds the selected items to the basket

		@attrib name=add_to_cart api=1

		@param id required type=int acl=view
		@param sel optional
		@param group optional
	**/
	function add_to_cart($arr)
	{
		$adc = array();
		foreach(safe_array($arr["sel"]) as $_id)
		{
			$adc[$_id] = 1;
		}
		$warehouse = obj($arr["id"]);
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->submit_add_cart(array(
			"oc" => $warehouse->prop("order_center"),
			"add_to_cart" => $adc
		));

		$this->do_save_prod_ord($arr);

		return $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"tree_filter" => $arr["tree_filter"],
			"group" => $arr["group"]
		));
	}

	/** cuts the selected items
		@attrib name=cut_products params=name all_args=1
	**/
	function cut_products($arr)
	{
		$_SESSION["shop_warehouse"]["copy_products"] = null;
		$_SESSION["shop_warehouse"]["cut_products"] = $arr["sel"];
		return $_SESSION["aw_session_track"]["server"]["ru"];
	}
	
	/** copys the selected items
		@attrib name=copy_products params=name all_args=1
	**/
	function copy_products($arr)
	{
		$_SESSION["shop_warehouse"]["cut_products"] = null;
		$_SESSION["shop_warehouse"]["copy_products"] = $arr["sel"];
		return $_SESSION["aw_session_track"]["server"]["ru"];
	}

	/** pastes items to menu
		@attrib name=paste_products params=name all_args=1
	**/
	function paste_products($arr)
	{
		if(is_oid($arr["parent"]) && $this->can("add" , $arr["parent"]))
		{
			foreach($_SESSION["shop_warehouse"]["cut_products"] as $id)
			{
				$o = obj($id);
				$o->set_parent($arr["parent"]);
				$o->save();
			}
			foreach($_SESSION["shop_warehouse"]["copy_products"] as $id)
			{
				$o = obj($id);
				$new_o = new object();
				$new_o->set_class_id($o->class_id());
				foreach($o->get_property_list() as $prop => $val)
				{
					$new_o->set_prop($prop , $o->prop($prop));
				}
				$new_o->set_name($o->name());
				$new_o->set_parent($arr["parent"]);
				$new_o->save();
			}
		}
		$_SESSION["shop_warehouse"]["copy_products"] = null;
		$_SESSION["shop_warehouse"]["cut_products"] = null;
		return $arr["return_url"];
	}

	/** checks if the company $id is a manager company for  warehouse $wh

	**/
	function is_manager_co($wh, $id)
	{
		if (!$wh->prop("conf"))
		{
			return false;
		}
		$conf = obj($wh->prop("conf"));
		$awa = new aw_array($conf->prop("manager_cos"));
		$mc = $awa->get();

		$mc = $this->make_keys($mc);
		if ($mc[$id])
		{
			return true;
		}
		return false;
	}

	/** sends the current order to the orderer's e-mail

		@attrib name=send_cur_order api=1

	**/
	function sent_cur_order($arr)
	{
		$ordid = $this->make_cur_order_id($arr);

		$ordo = obj($ordid);

		// get e-mail address from order
		$o = obj($arr["id"]);
		$oc = obj($o->prop("order_center"));
		$mail_to_el = $oc->prop("mail_to_el");
		$ud = $o->meta("order_cur_ud");
		$to = str_replace("&gt;", "", str_replace("&lt;", "", $ud[$mail_to_el]));
		if ($to == "")
		{
			return;
		}

		$so = get_instance(CL_SHOP_ORDER);
		$html = $so->gen_pdf(array(
			"id" => $ordid,
			"html" => 1,
			"return" => 1
		));

		$us = get_instance(CL_USER);
		$cur_person = obj($us->get_current_person());

		$froma = "automatweb@automatweb.com";
		if (is_oid($cur_person->prop("email")))
		{
			$tmp = obj($cur_person->prop("email"));
			$froma = $tmp->prop("mail");
		}

		$fromn = $cur_person->prop("name");

		$awm = get_instance("protocols/mail/aw_mail");
		$awm->create_message(array(
			"froma" => $froma,
			"fromn" => $fromn,
			"subject" => sprintf(t("Tellimus laost %s"), $o->name()),
			"to" => $to,
			"body" => strip_tags(str_replace("<br>", "\n",$html)),
		));
		$awm->htmlbodyattach(array(
			"data" => $html
		));
		$awm->gen_mail();
		
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "order_current"));
	}

	/** clears the current order 

		@attrib name=clear_order api=1

	**/
	function clear_order($arr)
	{
		$o = obj($arr["id"]);
		$oc = obj($o->prop("order_center"));
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$soc->clear_cart($oc);

		$o->set_prop("order_current_person", "");
		$o->set_prop("order_current_org", "");
		$o->set_meta("order_cur_ud", "");
		$o->set_meta("order_cur_discount", "");
		$o->set_meta("order_cur_pages", "");
		$o->save();

		aw_session_del("wh_order_cur_order_id");

		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "search_search"));
	}

	function do_save_prod_ord($arr)
	{
		foreach(safe_array($arr["old_ord"]) as $oid => $o_ord)
		{
			if ($arr["set_ord"][$oid] != $o_ord)
			{
				$o = obj($oid);
				$o->set_ord($arr["set_ord"][$oid]);
				$o->save();
			}
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "order")
		{
			return false;
		}

		if ($arr["id"] == "storage" && $arr["obj_inst"]->prop("conf.no_count") == 1)
		{
			return false;
		}
		return true;
	}

	function callback_mod_reforb($arr)
	{
		$arr["tree_filter"] = $_GET["tree_filter"];
	}

	/** returns a list of config forms that can be used to enter products

		@comment
			takes warehouse oid as parameter

	**/
	function get_prod_add_config_forms($arr)
	{
		$wh = obj($arr["warehouse"]);
		$conf_id = $wh->prop("conf");
		$ret = array();
		if(is_oid($conf_id) && $this->can("view", $conf_id))
		{
			$conf = obj($conf_id);
			$this->_req_get_prod_add_config_forms($conf->prop("prod_type_fld"), $ret, "sp_cfgform");
		}
		return $ret;
	}

	/** returns a list of config forms that can be used to enter product packagings
	
		@comment
			takes warehouse oid as parameter

	**/
	function get_prod_packaging_add_config_forms($arr)
	{
		$wh = obj($arr["warehouse"]);
		$conf_id = $wh->prop("conf");
		$ret = array();
		if(is_oid($conf_id) && $this->can("view", $conf_id))
		{
			$conf = obj($conf_id);
			$this->_req_get_prod_add_config_forms($conf->prop("prod_type_fld"), $ret, "packaging_cfgform");
		}
		return $ret;
	}

	function _req_get_prod_add_config_forms($parent, &$ret, $prop)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_MENU, CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			if ($o->class_id() != CL_MENU)
			{
				if (is_oid($cf_id = $o->prop($prop)) && $this->can("view", $cf_id))
				{
					$ret[$cf_id] = $cf_id;
				}
			}
			else
			{
				$this->_req_get_prod_add_config_forms($o->id(), $ret, $prop);
			}
		}
	}

	/**

		@attrib name=confirm_orders all_args=1

	**/
	function confirm_orders($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$re = get_instance(CL_SHOP_ORDER);
			foreach($arr["sel"] as $id => $one)
			{
				$re->do_confirm(obj($id));
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => $arr["group"]));
	}

	/**

		@attrib name=print_orders all_args=1

	**/
	function print_orders($arr)
	{
		$res = "";
//		fopen("http://games.swirve.com/utopia/login.htm");
//		die();
$oo = get_instance(CL_SHOP_ORDER);
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			foreach($arr["sel"] as $id)
			{;

				if($this->can("view", $id))
				{
					$res.='<DIV style="page-break-after:always">';
					$res .= $oo->request_execute(obj($id));
					$res.='</DIV>';
				}

/*				$link =  $this->mk_my_orb("print_orders", array("print_id" => $id));
				$res.= '<script name= javascript>window.open("'.$link.'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=800, width=720")</script>';
				//"<script language='javascript'>setTimeout('window.close()',10000);window.print();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }</script>";
*/			}
				$res.= "<script name= javascript>setTimeout('window.close()',10000);window.print();</script>";
		}
//		elseif($this->can("view", $arr["print_id"]))
//		{
//			
//			$res .= $oo->request_execute(obj($arr["print_id"]));
//			$res .= "
//				<script language='javascript'>
//					setTimeout('window.close()',5000);
//					window.print();
//				//	if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }
//				</script>
//			";
//		}
		else
		{
			$res .= t("Pole midagi printida");
		}

//		$res .= "<script language='javascript'>setTimeout('window.close()',10000);window.print();if (navigator.userAgent.toLowerCase().indexOf('msie') == -1) {window.close(); }</script>";

		die($res);
	}

	function _get_category_entry_form($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_CFGFORM,
			"site_id" => array(),
			"lang_id" => array(),
			"subclass" => CL_MENU
		));
		$arr["prop"]["options"] = array("" => t("--vali--")) + $ol->names();
	}

	function _get_status_calc_type($arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->get_status_calc_options();
	}

	function do_db_upgrade($t, $f)
	{
		switch($f)
		{
			case "aw_status_calc_type":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_storage_export_tree($arr)
	{
		return $this->_get_storage_income_tree($arr);
	}

	function _get_storage_income_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$disp = $arr["request"]["disp"];
		$t->add_item(0, array(
			"id" => "sl",
			"url" => aw_url_change_var("disp", "sl"),
			"name" => $disp == "sl" ? "<b>".t("Saatelehed")."</b>" : t("Saatelehed")
		));

			$t->add_item("sl", array(
				"id" => "sl_unc",
				"url" => aw_url_change_var("disp", "sl_unc"),
				"name" => $disp == "sl_unc" ? "<b>".t("Kinnitamata")."</b>" : t("Kinnitamata")
			));

			$t->add_item("sl", array(
				"id" => "sl_conf",
				"url" => aw_url_change_var("disp", "sl_conf"),
				"name" => $disp == "sl_conf" ? "<b>".t("Kinnitatud")."</b>" : t("Kinnitatud")
			));

		$t->add_item(0, array(
			"id" => "bl",
			"url" => aw_url_change_var("disp", "bl"),
			"name" => $disp == "bl" ? "<b>".t("Arved")."</b>" : t("Arved")
		));
		
			$t->add_item("bl", array(
				"id" => "bl_unc",
				"url" => aw_url_change_var("disp", "bl_unc"),
				"name" => $disp == "bl_unc" ? "<b>".t("Kinnitamata")."</b>" : t("Kinnitamata")
			));

			$t->add_item("bl", array(
				"id" => "bl_conf",
				"url" => aw_url_change_var("disp", "bl_conf"),
				"name" => $disp == "bl_conf" ? "<b>".t("Kinnitatud")."</b>" : t("Kinnitatud")
			));
	}

	function _get_storage_movements_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "create_reception",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_reception",
			"text" => t("Lisa sissetulek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->reception_fld,
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 3, //RELTYPE_STORAGE_INCOME,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_RECEPTION)
		));

		$tb->add_save_button();
		$tb->add_delete_button();
	}

	function _get_storage_movements_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$disp = $arr["request"]["disp"];
		$t->add_item(0, array(
			"id" => "sl",
			"url" => aw_url_change_var("disp", "sl"),
			"name" => $disp == "sl" ? "<b>".t("Saatelehed")."</b>" : t("Saatelehed")
		));

			$t->add_item("sl", array(
				"id" => "sl_unc",
				"url" => aw_url_change_var("disp", "sl_unc"),
				"name" => $disp == "sl_unc" ? "<b>".t("Kinnitamata")."</b>" : t("Kinnitamata")
			));

			$t->add_item("sl", array(
				"id" => "sl_conf",
				"url" => aw_url_change_var("disp", "sl_conf"),
				"name" => $disp == "sl_conf" ? "<b>".t("Kinnitatud")."</b>" : t("Kinnitatud")
			));
	}

	function _init_storage_movements_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "number",
			"caption" => t("number")
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "warehouse",
			"caption" => t("Ladu"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_storage_movements(&$arr)
	{
		$this->_init_storage_movements_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_INCOME")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _get_storage_writeoffs_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "create_reception",
			"tooltip" => t("Uus")
		));

		$tb->add_menu_item(array(
			"parent" => "create_reception",
			"text" => t("Lisa sissetulek"),
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->reception_fld,
				"alias_to" => $arr["obj_inst"]->id(),
				"reltype" => 3, //RELTYPE_STORAGE_INCOME,
				"return_url" => get_ru()
			), CL_SHOP_WAREHOUSE_RECEPTION)
		));

		$tb->add_save_button();
		$tb->add_delete_button();
	}

	function _get_storage_writeoffs_tree($arr)
	{
		return $this->_get_storage_movements_tree($arr);
	}

	function _get_storage_writeoffs(&$arr)
	{
		$this->_init_storage_movements_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_STORAGE_INCOME")) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = t("Sissetulek kinnitatud");
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => t("Vaata"),
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _get_storage_status_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "refresh",
			"tooltip" => t("Uuenda"),
			"url" => "javascript:window.location.reload()",
			"img" => "refresh.gif",
		));
	}

	function _get_storage_status_tree($arr)
	{
		return $this->_get_prod_tree($arr);
	}

	function _init_storage_status_tbl($t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t("&nbsp;"),
			"sortable" => 0,
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "code",
			"caption" => t("Kood"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "last_purchase_price",
			"caption" => t("Ostuhind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "price_fifo",
			"caption" => t("FIFO"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sales_price",
			"caption" => t("M&uuml;&uuml;gihind"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount1",
			"caption" => t("Kogus"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit1",
			"caption" => t("&Uuml;hik"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "amount2",
			"caption" => t("Kogus 2"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "unit2",
			"caption" => t("&Uuml;hik 2"),
			"align" => "center"
		));


		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => t("T&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_storage_status(&$arr)
	{
		$this->_init_storage_status_tbl($arr["prop"]["vcl_inst"]);
	}

	function _get_storage_prognosis_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "refresh",
			"tooltip" => t("Uuenda"),
			"url" => "javascript:window.location.reload()",
			"img" => "refresh.gif",
		));
	}

	function _get_storage_prognosis_tree($arr)
	{
		return $this->_get_prod_tree($arr);
	}


	function _get_storage_prognosis(&$arr)
	{
		$this->_init_storage_status_tbl($arr["prop"]["vcl_inst"]);
	}

	function _get_storage_inventories_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_SHOP_WAREHOUSE_INVENTORY), $arr["obj_inst"]->id(), 12, array("warehouse" => $arr["obj_inst"]->id()));
		$tb->add_save_button();
		$tb->add_delete_button();	
	}

	function _get_storage_inventories_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$disp = $arr["request"]["disp"];

		$t->add_item(0, array(
			"id" => "unc",
			"url" => aw_url_change_var("disp", "unc"),
			"name" => $disp == "unc" ? "<b>".t("Kinnitamata")."</b>" : t("Kinnitamata")
		));

		$t->add_item(0, array(
			"id" => "conf",
			"url" => aw_url_change_var("disp", "conf"),
			"name" => $disp == "conf" ? "<b>".t("Kinnitatud")."</b>" : t("Kinnitatud")
		));
	}

	function _get_storage_inventories(&$arr)
	{
		$this->_init_storage_inventories_tbl($arr["prop"]["vcl_inst"]);
		$ol = new object_list(array(
			"class_id" => CL_SHOP_WAREHOUSE_INVENTORY,
			"lang_id" => array(),
			"site_id" => array(),
			"warehouse" => $arr["obj_inst"]->id()
		));
		$arr["prop"]["vcl_inst"]->data_from_ol($ol, array("change_col" => "name"));
	}

	private function _init_storage_inventories_tbl($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_purchase_orders_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_SHOP_PURCHASE_ORDER), $arr["obj_inst"]->id(), 10, array("warehouse" => $arr["obj_inst"]->id()));
		$tb->add_save_button();
		$tb->add_delete_button();
	}

	function _get_purchase_orders_tree($arr)
	{
		$this->_get_storage_inventories_tree($arr);
	}


	function _get_purchase_orders(&$arr)
	{
		$this->_init_purchase_orders_tbl($arr["prop"]["vcl_inst"]);
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PURCHASE_ORDER,
			"lang_id" => array(),
			"site_id" => array(),
			"warehouse" => $arr["obj_inst"]->id()
		));
		$arr["prop"]["vcl_inst"]->data_from_ol($ol, array("change_col" => "number"));
	}

	private function _init_purchase_orders_tbl($t)
	{
		$t->define_field(array(
			"name" => "number",
			"caption" => t("Number"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "purchaser",
			"caption" => t("Hankija"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "rels",
			"caption" => t("Seosed"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_sell_orders_toolbar($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_SHOP_SELL_ORDER), $arr["obj_inst"]->id(), 11, array("warehouse" => $arr["obj_inst"]->id()));
		$tb->add_save_button();
		$tb->add_delete_button();
	}

	function _get_sell_orders_tree($arr)
	{
		$this->_get_storage_inventories_tree($arr);
	}


	function _get_sell_orders(&$arr)
	{
		$this->_init_sell_orders_tbl($arr["prop"]["vcl_inst"]);
		$ol = new object_list(array(
			"class_id" => CL_SHOP_SELL_ORDER,
			"lang_id" => array(),
			"site_id" => array(),
			"warehouse" => $arr["obj_inst"]->id()
		));
		$arr["prop"]["vcl_inst"]->data_from_ol($ol, array("change_col" => "number"));
	}

	private function _init_sell_orders_tbl($t)
	{
		$t->define_field(array(
			"name" => "number",
			"caption" => t("Number"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"name" => "seller",
			"caption" => t("Ostja"),
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "created",
			"caption" => t("Kuup&auml;ev"),
			"align" => "center",
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "rels",
			"caption" => t("Seosed"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "sum",
			"caption" => t("Summa"),
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "status",
			"caption" => t("Staatus"),
			"align" => "center"
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}
}

?>
