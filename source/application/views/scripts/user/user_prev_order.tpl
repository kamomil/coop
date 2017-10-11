<!-- prev order -->
<div class="section">
	<div class="title">
		<h3>הזמנה קודמת</h3>
	</div>
	<div class="content">
		<div class="navigate">
			<a href="{$public_path}/user" class="back">חזרה אחורה</a>
			{if $before_price_doc}
		<h3>הזמנה זו התבצעה לפני שהיה תיעוד של מחירי המוצרים לכן הרשימה מופיעה ללא מחיר</h3>	
			{/if}
		</div>
		{include file='common/order_form.tpl'}
		<div class="navigate">
			<a href="{$public_path}/user" class="back">חזרה אחורה</a>
		</div>
	</div>
</div>