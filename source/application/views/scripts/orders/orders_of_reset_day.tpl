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
        <div class="content list">
		<br /><br />                
		{if !$orders}
		<a>אין הזמנות לתאריך {$date|date_format:"%d/%m/%y"}.</a>
		{else}
		<h1> הזמנות לתאריך {$date|date_format:"%d/%m/%y"}:</h1>
         <a href="/duty/download-reset-date-csv/date/{$date}"><button>הורד CSV</button></a>  
                    <table>
			<th>שם</th>
			<th>טלפון</th>
			<th>תאריך עדכון אחרון</th>
			<th>סכום משוער</th>
			{foreach from=$orders item=order}
			<tr>
				<td><a href="{$public_path}/duty/view-order/id/{$order.order_id}">{$order.user_first_name|escape:"html"|stripslashes} {$order.user_last_name|escape:"html"|stripslashes}</a></td>
				<td>{$order.user_phone|escape:"html"|stripslashes}</td>
				<td>{$order.order_last_edit|date_format:"%d/%m/%y %H:%M"}</td>
				<td>₪{$order.total|string_format:"%.2f"}</td>
			</tr>
			{/foreach}
		</table>	
		{/if}
		
	</div>
</div>

