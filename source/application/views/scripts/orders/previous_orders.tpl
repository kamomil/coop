<link rel="stylesheet" href="{$css_path}/order.css" />

<!-- date picker -->
<link rel="stylesheet" href="{$js_path}/jquery/datepicker/datePicker.css" />
<script src="{$js_path}/jquery/datepicker/date.js" ></script>
<!--[if IE]><script src="{$js_path}/jquery/datepicker/jquery.bgiframe.js" /></script><![endif]-->
<script src="{$js_path}/jquery/datepicker/jquery.datePicker.js" ></script>

<!-- autocomplete -->
<link rel="stylesheet" href="{$js_path}/jquery/jquery-ui/development-bundle/themes/base/jquery.ui.all.css">
<script src="{$js_path}/jquery/jquery-ui/development-bundle/ui/jquery.ui.core.js"></script>
<script src="{$js_path}/jquery/jquery-ui/development-bundle/ui/jquery.ui.widget.js"></script>
<script src="{$js_path}/jquery/jquery-ui/development-bundle/ui/jquery.ui.button.js"></script>
<script src="{$js_path}/jquery/jquery-ui/development-bundle/ui/jquery.ui.position.js"></script>
<script src="{$js_path}/jquery/jquery-ui/development-bundle/ui/jquery.ui.autocomplete.js"></script>
<script src="{$js_path}/autocomplete_extensions.js"></script>

<!-- popup -->
<link rel="stylesheet" href="{$js_path}/jquery/jquery-popupbox/popbox.css">
<script src="{$js_path}/jquery/jquery-popupbox/popbox.js"></script>


<script>var public_path = "{$public_path}";</script>
<script src="{$js_path}/orders.js"></script>

<div class="section">
<h2>{$smarty.template}</h2>
        <div class="content list">
		<br /><br />                
		{if !$reset_days}
		<a>אין הזמנות.</a>
		{else}
                
            <table>
			<th>תאריך</th>
			
			{foreach from=$reset_days item=resetd_arr}
			<tr>
				<td><a href="/duty/orders-of-day/date/{$resetd_arr['order_reset_day']}">הזמנות שאותחלו בתאריך {$resetd_arr['order_reset_day']|date_format:"%d/%m/%y"}</a></td>
			</tr>
			{/foreach}
		</table>	
		{/if}
		
	</div>
</div>

