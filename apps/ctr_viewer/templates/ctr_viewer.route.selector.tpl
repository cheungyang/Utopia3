{css}

<fieldset>
<legend>Route</legend>

<?php $temp={outputs>routes}; if (!empty($temp)): ?>
	<select id="select_route_select" name="select_route_select">
    <?php foreach ({outputs>routes} as $route) :?>
        <?php $selected = {$route} == {outputs>default_value}? "selected=\"selected\"": ""; ?>
		<option name="{$route}" {$selected}>{$route}</option>
    <?php endforeach; ?>
	</select>
<?php endif; ?>

</fieldset>

{js}
{timer}