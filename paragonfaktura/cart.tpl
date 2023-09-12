<form action="#" method="POST" id="invoice_bill">
<input type="hidden" value="{$id_cart}" name="invoice_bill_id" id="invoice_bill_id"/>

	<h5>{l s='Select type of document' mod='paragonfaktura'}</h5>
	<label>
		<input type="radio" value="1" name="invoice_bill" {if $type == 1} checked {/if}/>
		{l s='Invoice' mod='paragonfaktura'}
	</label>
	<label>
		<input type="radio" value="2" name="invoice_bill" {if $type == 2} checked {/if} style="margin-left: 30px;"/>
		{l s='Bill' mod='paragonfaktura'}
	</label>
</form>
