<style>
	/* The switch - the box around the slider */
	.switch {
		position: relative;
		display: inline-block;
		width: 60px;
		height: 34px;
	}

	/* Hide default HTML checkbox */
	.switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}

	/* The slider */
	.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .4s;
		transition: .4s;
	}

	.slider:before {
		position: absolute;
		content: "";
		height: 26px;
		width: 26px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
	}

	input:checked + .slider {
		background-color: #27b92a;
	}

	input:focus + .slider {
		box-shadow: 0 0 1px #27b92a;
	}

	input:checked + .slider:before {
		-webkit-transform: translateX(26px);
		-ms-transform: translateX(26px);
		transform: translateX(26px);
	}

	/* Rounded sliders */
	.slider.round {
		border-radius: 34px;
	}

	.slider.round:before {
		border-radius: 50%;
	}
</style>

<h6>{__('Hier können Sie den Abgleich der Rechtstexte je Kundengruppe aktivieren oder deaktivieren')}</h6>
<br />
<form method="post">
	{$jtl_token}
	<input class="form-control" type="hidden" name="kPlugin" value="{$oPlugin->getId()}"/>
	<input class="form-control" type="hidden" name="cPluginTab" value="Kundengruppen-Einstellungen"/>
	<input type="hidden" name="syncGroupSettings" value="1">
	<input class="form-control" type="hidden" name="stepPlugin" value="{$stepPlugin}"/>
	<table class="table table-striped">
		<thead>
		<tr>
			<td>{__("Kundengruppe")}</td>
			<td>{__("Abgleich aktiv?")}</td>
		</tr>
		</thead>
		<tbody>
		{foreach from=$oKundengruppen_arr item=oKundengruppe}
			<tr>
				<td>{$oKundengruppe->cName}</td>
				<td>
					{if (in_array($oKundengruppe->kKundengruppe, $oBlockedGroup_ids))}
						<label class="switch">
							<input type="checkbox" name="activeGroups[]" value="{$oKundengruppe->kKundengruppe}">
							<span class="slider round"></span>
						</label>
					{else}
						<label class="switch">
							<input type="checkbox" name="activeGroups[]" value="{$oKundengruppe->kKundengruppe}" checked {if ($oKundengruppe->cStandard == 'Y')}readonly{/if}>
							<span class="slider round"></span>
						</label>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> {__("Einstellungen speichern")}</button>
</form>