<div class="block_leave_comment">
<div class="form">

{%if submitted or (errors|length > 0) %}
	{%if valid is null %}
	    <blockquote class="full" style="border-left-color:#F90; background-color:#FFFAB7">Form submitted but not validated</blockquote>
    {% elseif warningmsg %}
	    <blockquote class="full" style="border-left-color:#F90; background-color:#FEECC2"><h6>Warning</h6>{{ warningmsg|raw }}</blockquote>

	{% elseif valid %}
	    <blockquote class="full" style="border-left-color:#090; background-color:#ECFFEC">{{ validmsg|raw }}</blockquote>
	{% else %}
	    <blockquote class="full"><h6>Some errors found</h6><ul class="list_3"><li>{{ errors|join('</li><li>')|raw }}</li></ul></blockquote>
    {% endif %}

{% endif %}

{% if hide == false %}

	{% if renderaction %}
		<form action="{{ action }}" method="post" name="{{ name }}" id="{{ name }}">
	{% endif %}

		{% for el in elements %}
        
        	{% if el.type == 'text' %}
            	<div class="one_third">
                	<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>
                    <div class="field">
                    	<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" type="text" value="{{el.value}}" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
					</div>
				</div>

        	{% elseif el.type == 'password' %}
            	<div class="one_third">
                	<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>
                    <div class="field">
                    	<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" type="password" value="" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
					</div>
				</div>

			{% elseif el.type == 'textarea' %}
        		<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>
                <div class="textarea">
                	<textarea {{el.disabled ? 'disabled="disabled" '}}{{el.readonly ? 'readonly="readonly" '}}name="{{name ~ el.name}}" cols="1" rows="1" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{el.value}}</textarea>
				</div>

			{% elseif el.type == 'select' %}
	            <div class="one_third">
                	<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>
                    <select {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
					{% for opv, opl in el.options %}
                        <option {{el.value == opv ? 'selected '}}value="{{ opv }}">{{ opl }}</option>
					{% endfor %}
                    </select>
				</div>                    

			{% elseif el.type == 'multiple' %}
				<div class="one_half">
                	<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>
                    <table border="0">
                    	<tr>
                        	<td>
								<select {{el.disabled ? 'disabled="disabled" '}}style="height:auto;width:200px" size="10" multiple="multiple" id="{{name ~ el.name}}" name="{{name ~ el.name}}" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
            			        {% for opv, opl in el.options %}
                        			<option {{el.value == opv ? 'selected '}}value="{{ opv }}">{{ opl }}</option>
			                    {% endfor %}
            			        </select>
							</td>
							<td align="left" style="vertical-align:top">
								<a class="general_button big type_5" onclick="$('#{{name ~ el.name}} option').prop('selected',true);return false;" /><span>select all</span><a /><br/>
								<a class="general_button big type_5" onclick="$('#{{name ~ el.name}} option').prop('selected',false);return false;" /><span>clear</span><a /><br/>
							</td>
						</tr>
					</table>
				</div>

			{% elseif el.type == 'checkbox' %}
				<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" {{el.value == 'on' ? 'checked '}}type="checkbox" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{el.label|raw}}

			{% elseif el.type == 'hidden' %}
            	<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}"  type="hidden" value="{{el.value}}" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                
			{% elseif el.type == 'checkboxgroup' %}
				<p class="label">{{ el.label }} {{el.rules.required ? '<span>(required)</span>'}} </p>                
		        {% for opv, opl in el.options %}
					<input {{el.disabled ? 'disabled="disabled" '}}id="{{name ~ el.name ~ opv}}" name="{{name ~ el.name ~ opv}}" {{opv in el.value|split(';') ? 'checked '}}type="checkbox" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}><label for="{{name ~ el.name ~ opv}}">{{opl}}</label>
					{{ (el.settings.separator and loop.revindex0) ? el.settings.separator|raw }}
				{% endfor %}

			{% elseif el.type == 'separator' %}
				<div class="clearboth"></div>

			{% elseif el.type == 'separatorline' %}
				<div class="line_2" style="margin:10px 0px 15px;"></div>

			{% elseif el.type == 'captcha' %}
				<div class="one_half">
	                <p class="label">Security code <span>(required)<br />Please confirm 5 character code (letters only)<br />Click on image to see a different code</span></p>
                	<script type="text/javascript">document.write('<a href="#" onclick="$(\'#cap{{name ~ el.name}}\').attr(\'src\', \'/captcha/\'+Math.floor(89999999*Math.random()+10000000));return false;"><img id="cap{{name ~ el.name}}" width="200" height="100" src="/captcha/'+Math.floor(89999999*Math.random()+10000000)+'" alt="c" /></a>');</script>
                	<div class="field">
                		<input name="{{name ~ el.name}}" type="text" size="20" maxlength="20">
					</div>
                </div>

			{% elseif el.type == 'submit' and rendersubmit %}
				<div class="button" style="{% if el.position == 'left' %}float:left;{% elseif el.position == 'right' %}float:right;margin-left:10px{% endif %}">
	                <input {{el.disabled ? 'disabled="disabled" '}}type="submit" name="{{name ~ el.name}}" class="general_button" value="{{el.label}}" {%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
					<input type="hidden" name="{{csrfname}}" value="{{csrf}}"/>
                </div>

			{% elseif el.type == 'imageupload' %}

            	{% if el.options.order == 1 %}
                	<input type="hidden" id="transloaditid" /><input type="hidden" id="transloaditidw" /><input type="hidden" id="transloaditidh" />
					<script type="text/javascript">
					    $(function() {
					      $('form#{{name}}').transloadit({
					        wait: true,
							autoSubmit: false,
							processZeroFiles: false,
					        triggerUploadOnFileSelection: true,
							onStart: function(assembly){
								$('#' + $('#transloaditid').val() ).val('');
							},
						    onSuccess: function(assembly) {
								
								var cbutton = '#' + $('#transloaditid').val();
								var cfcontainer = cbutton + 'FC';
								var cfhvalue    = cbutton + 'V';
					
								if( 'results' in assembly && 'upflash' in assembly.results ){
									$( cfcontainer ).html('<div id="' + assembly.assembly_id + '"><p><table><tr><td><div id="d' + assembly.assembly_id + '"></div></td><td style="vertical-align:middle">&nbsp;<input type="button" class="general_button type_5" value="remove file" onClick="$(\'#' + assembly.assembly_id + '\').remove();$(\'' + cfhvalue + '\').val(\'\');$(\'' + cbutton + '\').show();"></td></tr></table></p></div>');
									$( cfhvalue ).val( '{{tpl.clientGetTag}}/'+assembly.results.upflash[0].id + '-' + assembly.results.upflash[0].name );
									swfobject.embedSWF(assembly.results.upflash[0].ssl_url, 'd' + assembly.assembly_id, $('#transloaditidw').val(), $('#transloaditidh').val(), "9.0.0", false, {}, { scale: "exactfit" });
								}else if( 'results' in assembly && 'upimage' in assembly.results ){
									$( cfcontainer ).html('<div id="' + assembly.assembly_id + '"><p><table><tr><td><img src="' + assembly.results.upimage[0].ssl_url + '" width="' + assembly.results.upimage[0].meta.width + '" height="' + assembly.results.upimage[0].meta.height +'" /></td><td style="vertical-align:middle">&nbsp;<input type="button" class="general_button type_5" value="remove file" onClick="$(\'#' + assembly.assembly_id + '\').remove();$(\'' + cfhvalue + '\').val(\'\');$(\'' + cbutton + '\').show();"></td></tr></table></p></div>');
									$( cfhvalue ).val( '{{tpl.clientGetTag}}/'+assembly.results.upimage[0].id + '-' + assembly.results.upimage[0].name );
								}else if( 'results' in assembly && 'upvideo' in assembly.results ){
									$( cfcontainer ).html('<div id="' + assembly.assembly_id + '"><p><table><tr><td><video id="v' + assembly.assembly_id + '" class="video-js vjs-default-skin"></video></td><td style="vertical-align:middle">&nbsp;<input type="button" class="general_button type_5" value="remove file" onClick="$(\'#' + assembly.assembly_id + '\').remove();$(\'' + cfhvalue + '\').val(\'\');$(\'' + cbutton + '\').show();"></td></tr></table></p></div>');
									$( cfhvalue ).val( '{{tpl.clientGetTag}}/'+assembly.results.upvideo[0].id + '-' + assembly.results.upvideo[0].name + ';' + '{{tpl.clientGetTag}}/'+assembly.results.upvideomp4[0].id + '-' + assembly.results.upvideomp4[0].name + ';' + '{{tpl.clientGetTag}}/'+assembly.results.upvideowebm[0].id + '-' + assembly.results.upvideowebm[0].name + ';' + '{{tpl.clientGetTag}}/'+assembly.results.upvideoflash[0].id + '-' + assembly.results.upvideoflash[0].name );
									var mplayer = videojs("v"+assembly.assembly_id, {"controls": true, "autoplay": false, "poster": assembly.results.upvideo[0].ssl_url, "width": assembly.results.upvideo[0].meta.width, "height": assembly.results.upvideo[0].meta.height }, function(){});
									mplayer.src([{ type: "video/mp4", src: assembly.results.upvideomp4[0].ssl_url },{ type: "video/webm", src: assembly.results.upvideowebm[0].ssl_url },{ type: "video/flv", src: assembly.results.upvideoflash[0].ssl_url }]);
								}
								$('#' + $('#transloaditid').val() ).hide();
		  					}
					       });
   						 });
					</script>                
                {% endif %}
				
                <div id="{{ name ~ el.name }}FC">
                
                	{% if el.value %}

								<div id="{{ el.name }}PID">
								<p>

                                    {% if el.value|split(';')|length > 1 %}
                                    
                                    	{% set upvideo = el.value|split(';') %}
                                        <table><tr><td>
                                    	<video id="v{{ (name ~ el.name)|md5 }}" class="video-js vjs-default-skin"
                                        controls width="{{ el.options.width }}" height="{{ el.options.height }}" poster="//{{ tpl.cdnuser ~ '/' ~ upvideo[0] }}">
											<source src="//{{ tpl.cdnuser ~ '/' ~ upvideo[1] }}" type='video/mp4' />
											<source src="//{{ tpl.cdnuser ~ '/' ~ upvideo[2] }}" type='video/webm' />
											<source src="//{{ tpl.cdnuser ~ '/' ~ upvideo[3] }}" type='video/flv' />
                                        </video>
										<script type="text/javascript">videojs("v{{ (name ~ el.name)|md5 }}");</script>
										</td><td style="vertical-align:middle">&nbsp;
										<input {{el.disabled ? 'disabled="disabled" '}}type="button" class="general_button type_5" value="remove file" onClick="$('#{{ el.name }}PID').remove();$('#{{name ~ el.name}}V').val('');$('#{{name ~ el.name}}').show();">
                                        </td></tr></table>
                                    {% elseif el.value|extension == 'jpg' or el.value|extension == 'jpeg' or el.value|extension == 'png' or el.value|extension == 'gif'%}
                                        <table><tr><td>
										<img src="//{{ tpl.cdnuser ~ '/' ~ el.value }}" width="{{ el.options.width }}" height="{{ el.options.height }}" />
										</td><td style="vertical-align:middle">&nbsp;
										<input {{el.disabled ? 'disabled="disabled" '}}type="button" class="general_button type_5" value="remove file" onClick="$('#{{ el.name }}PID').remove();$('#{{name ~ el.name}}V').val('');$('#{{name ~ el.name}}').show();">
                                        </td></tr></table>
                                    {% elseif el.value|extension == 'swf' %}
                                        <table><tr><td>
                                    	<div id="{{ el.name }}" style="width:{{ el.options.width }};height:{{ el.options.height }}"></div>
										<script type="text/javascript">swfobject.embedSWF("//{{ tpl.cdnuser ~ '/' ~ el.value }}", "{{ el.name }}", {{ el.options.width }}, {{ el.options.height }}, "9.0.0", false, {}, { scale: "exactfit" });</script>
										</td><td style="vertical-align:middle">&nbsp;
										<input {{el.disabled ? 'disabled="disabled" '}}type="button" class="general_button type_5" value="remove file" onClick="$('#{{ el.name }}PID').remove();$('#{{name ~ el.name}}V').val('');$('#{{name ~ el.name}}').show();">
                                        </td></tr></table>
                                    {% endif %}

								</p>
                                </div>

                    {% endif %}
				</div>
				<input type="hidden" name="{{ name ~ el.name }}" value="{{ el.value }}" id="{{name ~ el.name}}V"/>
				<input {% if el.value %} style="display:none;" {% endif %} {{el.disabled ? 'disabled="disabled" '}}type="file" name="{{name ~ el.name}}N" id="{{name ~ el.name}}" onChange="$('#transloaditid').val($(this).attr('id'));$('#transloaditidw').val('{{el.options.width}}');$('#transloaditidh').val('{{el.options.height}}');$('form#{{name}}').data('transloadit.uploader')._options['signature']='{{el.options.signature}}';$('form#{{name}}').data('transloadit.uploader')._options['params']=JSON.parse('{{el.options.params}}');" />            	
            {% endif %}        

    {% endfor %}

	{% if renderaction %}
		</form>
	{% endif %}

{% endif %}
<div class="clearboth"></div>
</div>
</div>