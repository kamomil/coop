<!-- List of debts of Coop members -->
<div class="section">
	<div class="title">
		<h3>חובות</h3>
		<p>רשימת כל החברים בעלי חוב (לחיוב או לזיכוי):</p>
		<h3>ניתן לערוך הערות ישירות מהטבלה, וכדי לשמור אותם יש ללחוץ על "אישור" בסוף הדף</h3>
	</div>
	<div class="content">
		{if $users != null}
		<div class="list">
		<form action="" method="POST" class="form validate_me">
		<table>
			<th>שם</th>
			<th>חוב</th>
			{foreach from=$users item=row}
			{if $row.user_comments != NULL}
			<tr>
				<td><a href="{$public_path}/manager/edit-user/id/{$row.user_id}">{$row.user_first_name|escape:"html"|stripslashes} {$row.user_last_name|escape:"html"|stripslashes}</a></td>
				<td><input type="text" name="{$row.user_id}" size="70" value="{$row.user_comments|escape:"html"|stripslashes}" /></td>
			</tr>
			{/if}
			{/foreach}
		</table>	
		<p class="submit">
			<input type="submit" value="אישור" />			
		</p>	
		</form>
		</div>
		{else}
		<p>אין אף משתמש כרגע.</p>
		{/if}
	</div>
</div>

