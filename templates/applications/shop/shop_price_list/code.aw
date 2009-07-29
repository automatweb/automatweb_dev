$price = $args["price"];
$bonus = $args["bonus"];
$log = array();

$parents = array(
<!-- SUB: PARENTS -->
	"{VAR:id}" => array({VAR:parents}),
<!-- END SUB: PARENTS -->
);

$priorities = array(
<!-- SUB: PRIORITIES -->
	"{VAR:id}" => "{VAR:priority}",
<!-- END SUB: PRIORITIES -->
);

$rows = array();
foreach($args["rows"] as $row)
{
	$rows[$row] = (double)ifset($priorities, $row);
	foreach(safe_array(ifset($parents, $row)) as $parent)
	{
		$rows[$parent] = (double)ifset($priorities, $parent);
	}
}
asort($rows);

foreach(array_keys($rows) as $row)
{
	$_cols = array();
	foreach(array({VAR:passing_order}) as $k)
	{
		$cols = array();
		foreach(safe_array(ifset($args, $k)) as $col)
		{
			foreach(array_merge(array($col), safe_array(ifset($parents, $col))) as $_col)
			{
				$cols[$_col] = (double)ifset($priorities, $_col);
			}
		}
		arsort($cols);
		foreach(array_keys($cols) as $col)
		{
			$_cols[$col] = $k;
		}
	}

	$done = array();
	foreach($_cols as $col => $type)
	{
		switch($row."_".$col)
		{
			<!-- SUB: HANDLE_CELL -->
			case "{VAR:row}_{VAR:col}":
				if(
					empty($done[$type]) &&
					($type !== "default" || empty($done))
				)	// && count(array_intersect($row_cols, array(GENEREERITUD PRIORITEETSEMATE ROW_COLde NÖ IDd))) == 0 && ... )
				{
					<!-- SUB: HANDLE_CELL_ROW_AUTO -->
					<!-- SUB: QUANTITY_CONDITION_START -->
					if(
						<!-- SUB: QUANTITY_CONDITION_FIRST -->
						{VAR:QUANTITY_CONDITION_SINGLE}
						{VAR:QUANTITY_CONDITION_RANGE}
						<!-- END SUB: QUANTITY_CONDITION_FIRST -->
						<!-- SUB: QUANTITY_CONDITION -->
						or
						<!-- SUB: QUANTITY_CONDITION_SINGLE -->
						$args["amount"] == {VAR:quantity}
						<!-- END SUB: QUANTITY_CONDITION_SINGLE -->
						<!-- SUB: QUANTITY_CONDITION_RANGE -->
						$args["amount"] <= {VAR:quantity_to} and $args["amount"] >= {VAR:quantity_from}
						<!-- END SUB: QUANTITY_CONDITION_RANGE -->
						<!-- END SUB: QUANTITY_CONDITION -->
					)
					{
					<!-- END SUB: QUANTITY_CONDITION_START -->
						list($new_price, $new_bonus) = shop_price_list_obj::evaluate_price_list_conditions_auto($price, $bonus, "{VAR:price_formula}", "{VAR:bonus_formula}");
						$log[] = array(
							"condition_id" => "{VAR:condition_id}",
							"type" => "{VAR:type}",
							"diff" => array(
								"price" => $new_price - $price,
								"bonus" => $new_bonus - $bonus,
							),
						);
						$price = $new_price;
						$bonus = $new_bonus;
						$done[$type] = true;
					<!-- SUB: QUANTITY_CONDITION_END -->
					}
					<!-- END SUB: QUANTITY_CONDITION_END -->
					<!-- END SUB: HANDLE_CELL_ROW_AUTO -->
					<!-- SUB: HANDLE_CELL_ROW_CUSTOM -->
			//		{VAR:}::{VAR:}($price, ....);
					<!-- END SUB: HANDLE_CELL_ROW_CUSTOM -->
				}
				break;

			<!-- END SUB: HANDLE_CELL -->	
		}
	}
}

return array(
	"price" => array(
		"in" => $args["price"],
		"out" => $price,
	),
	"bonus" => array(
		"in" => $args["bonus"],
		"out" => $bonus,
	),
	"log" => $log,
);