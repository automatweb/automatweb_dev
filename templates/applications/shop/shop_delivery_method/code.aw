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

$rows = array(0);
$groups = array();
foreach($args["rows"] as $row)
{
	$rows[$row] = isset($priorities[$row]) ? aw_math_calc::string2float($priorities[$row]) : 0;
	if(isset($parents[$row]))
	{
		foreach($parents[$row] as $parent)
		{
			$rows[$parent] = isset($priorities[$parent]) ? aw_math_calc::string2float($priorities[$parent]) : 0;
		}
	}
	<!-- SUB: ENABLING_TYPE_2_INITIALIZE -->
	$groups[] = array_merge(array($row), isset($parents[$row]) ? $parents[$row] : array());
	$groups_return_default[] = 1;
	<!-- END SUB: ENABLING_TYPE_2_INITIALIZE -->
}
asort($rows);

$cols = array();
foreach(array({VAR:passing_order}) as $k)
{
	$_cols = array();
	if(isset($args[$k]) && is_array($args[$k]))
	{
		foreach($args[$k] as $col)
		{
			foreach(array_merge(array($col), isset($parents[$col]) ? $parents[$col] : array()) as $_col)
			{
				$_cols[$_col] = isset($priorities[$_col]) ? $priorities[$_col] : 0;
			}
		}
		asort($_cols);
		foreach(array_keys($_cols) as $col)
		{
			$cols[$col] = $k;
		}
	}
}

$valid = {VAR:enabled_by_default};
$cnt = 0;
foreach(array_keys($rows) as $row)
{
	foreach(array_keys($cols) as $col)
	{
		switch ($row."_".$col)
		{
			<!-- SUB: HANDLE_CELL -->
			case "{VAR:row}_{VAR:col}":
				<!-- SUB: ENABLING_TYPE_2_HANDLE_CELL -->
				foreach($groups as $group_key => $group)
				{
					$groups_return_default[$group_key] = array_search($row, $group) !== false && {VAR:enable} !== {VAR:enabled_by_default} ? 0 : 1;
				}
				<!-- END SUB: ENABLING_TYPE_2_HANDLE_CELL -->
				<!-- SUB: ENABLING_TYPE_1_HANDLE_CELL -->
				$valid = {VAR:enable};
				<!-- END SUB: ENABLING_TYPE_1_HANDLE_CELL -->
				$cnt++;
				break;
			<!-- END SUB: HANDLE_CELL -->
			default:
				if($row."_".$col !== "0_0" or $cnt === 0)
				{
					$valid = {VAR:enabled_by_default};
				}
				$cnt++;
				break;
		}
	}
}

<!-- SUB: ENABLING_TYPE_1_RETURN -->
return $valid;
<!-- END SUB: ENABLING_TYPE_1_RETURN -->
<!-- SUB: ENABLING_TYPE_2_RETURN -->
return array_sum($groups_return_default) === 0 ? !{VAR:enabled_by_default} : {VAR:enabled_by_default};
<!-- END SUB: ENABLING_TYPE_2_RETURN -->
