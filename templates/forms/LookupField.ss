<select $AttributesHTML>
<% loop $Options %>
	<option value="$Value.XML"<% if $Selected %> selected="selected"<% end_if %><% if $Disabled %> disabled="disabled"<% end_if %>>$Title.XML</option>
<% end_loop %>
</select>

<span class="readonly" id="$ID">$attrValue</span><input type=\"hidden\" name=\"" . $this->name .
			"\" value=\"" . $inputValue . "\" />";