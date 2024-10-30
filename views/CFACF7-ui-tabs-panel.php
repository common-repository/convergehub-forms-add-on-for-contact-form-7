<?php if(!defined( 'ABSPATH')) exit; ?>
<div class="CFACF7">
	<h2>ConvergeHub Forms Integration Settings</h2>
	<fieldset>
	  <legend>
	  Enter your ConvergeHub form credentials below for submission of your Contact Form 7 form and make it work with ConvergeHub Form. All fields with "*" must be filled for the required process.
	  </legend>
		<table class="form-table">
		  <tbody>
		    <tr>
		      <th scope="row">Activate <b>*</b></th>
		      <td>
		        <input type="checkbox" name="CFACF7_enabled" id="CFACF7_enabled" value="1"{enabled}> 
		        <label for="CFACF7_enabled">Enable</label>
		      </td>
		    </tr>
		   
		    <tr>
		      <th scope="row">API Key <b>*</b></th>
		      <td>
		        <input type="text" name="CFACF7_api_key" id="CFACF7_api_key" class="large-text code" value="{api_key}" placeholder="e.g. 1CFGHSEA1XEVY5YO">
		      </td>
		    </tr>
		    <tr>
		      <th scope="row">API Secret <b>*</b></th>
		      <td>
		        <input type="text" name="CFACF7_api_secret" id="CFACF7_api_secret" class="large-text code" value="{api_secret}" placeholder="e.g. uzlhD4BGwUhIM5jxqScCgdzb9WZ_U08uYb1s7pjxQGC7HSgI3d7QPTT">
		      </td>
		    </tr>
		    <tr {error_msg_td}>
		    	 <th scope="row">&nbsp;</th>
		    	 <td><span class="error_cs">{error_msg}</span></td>
		    </tr>
		    <tr id="description_api" {description_api}>
		      <th scope="row">&nbsp;</th>
		      <td> 
		        <p class="description"><b>Please check the Activate option and setup the API settings to activate the Fields options.</b> </p>
		      </td>
		    </tr>
		    <tr id="field_names_wrap" {field_names_wrap}>
		      <th scope="row">Form Fields <b>*</b></th>
		      <td class="valign-top">		 
			      <p class="cf7_field_names">{form_fields_html}</p>
			      <p class="description info">
			      Map the Form field's names and values accordingly.<br>
			      Use the contact form 7 field against the ConvergeHub Form field name.<br>
			      e.g. <strong>Contact Form 7 Form Field Name <i class="icon-arrow-right" style="line-height: 18px;"></i>ConvergeHub Form Field Name </strong> <br>
			      </p>
		      </td>
		    </tr>
		  </tbody>
		</table>
	</fieldset>	
</div>
