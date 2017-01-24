<!-- List of debts of Coop members -->
<script type="text/javascript">
var formSubmitting = false;
var setFormSubmitting = function() { formSubmitting = true; };

window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        if (formSubmitting) {
            return undefined;
        }

        var confirmationMessage = 'It looks like you have been editing something. '
                                + 'If you leave before saving, your changes will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
};
</script>
<div class="section">
	<div class="title">
		<h3>חובות</h3>
		<p>רשימת כל החברים בעלי חוב (לחיוב או לזיכוי):</p>
		<h3>ניתן לערוך הערות ישירות מהטבלה, וכדי לשמור אותם יש ללחוץ על "אישור" בסוף הדף</h3>
	</div>
	<div class="content">
		{if $users != null}
		<div class="list">
		<form action="" method="POST" class="form validate_me" onsubmit="setFormSubmitting()">
		<table>
			<th>שם</th>
			<th>חוב</th>
			<th> </th>
			<tr>
				<td></td>
				<td></td>
				<td rowspan="{$users|@count}"><input type="submit" value="אישור" /></td>
			</tr>
			{foreach from=$users item=row}
			{if $row.user_comments != NULL}
			<tr>
				<td><a href="{$public_path}/manager/edit-user/id/{$row.user_id}">{$row.user_first_name|escape:"html"|stripslashes} {$row.user_last_name|escape:"html"|stripslashes}</a></td>
				<td><input type="text" name="{$row.user_id}" size="70" value="{$row.user_comments|escape:"html"|stripslashes}" /></td>
			</tr>
			{/if}
			{/foreach}
		</table>	
			
		</form>
		</div>
		{else}
		<p>אין אף משתמש כרגע.</p>
		{/if}
	</div>
</div>

