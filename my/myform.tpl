
{%if submitted or (errors|length > 0) %}
	{%if valid is null %}
        <div class="callout callout-info fade in">
				<button data-dismiss="alert" class="close" type="button">×</button>
				<h5>Form submitted but not validated</h5>
        </div>
    {% elseif warningmsg %}
        <div class="callout callout-warning fade in">
				<button data-dismiss="alert" class="close" type="button">×</button>
				<h5>Warning</h5>
				<p>{{ warningmsg|raw }}</p>
        </div>
	{% elseif valid %}
        <div class="callout callout-success fade in">
				<button data-dismiss="alert" class="close" type="button">×</button>
				<h5>Success</h5>
				<p>{{ validmsg|raw }}</p>
        </div>
	{% else %}
        <div class="callout callout-danger fade in">
				<button data-dismiss="alert" class="close" type="button">×</button>
				<h5>Some errors found</h5>
				<p><ul><li>{{ errors|join('</li><li>')|raw }}</li></ul></p>
        </div>
    {% endif %}
{% endif %}

{% if hide == false %}

	{% if renderaction %}
		<form action="{{ action }}" method="post" name="{{ name }}" id="{{ name }}" role="form">
	{% endif %}

	{% if ismodal %}
            <div id="{{ modal.id }}" class="modal" tabindex="-1" role="dialog"{{ modal.static ? ' data-keyboard="false" data-backdrop="static"'}}>
				<div class="modal-dialog{{ modal.class ? ' ' ~ modal.class }}"{% if modal.width %} style="max-width:{{modal.width}}px"{% endif %}>
					<div class="modal-content">
						<div class="modal-header" style="background-color:#3B5998">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title"><i class="{{ modal.icon }}"></i> {{ modal.title }}</h4>
						</div>
    					<div class="modal-body with-padding" style="padding:30px;">
	{% endif %}


        {% set formsubmitvalue = "Submit" %}
        {% set formtransloadit = 0 %}
        {% set formgroup = -1 %}


		{% for el in elements %}
    

        	{% if el.type == 'formgroup' %}
                <div class="form-group">
                    <div class="row">

                    {% set formgroup    = el.total %}
                    {% set formgroupcss = el.css %}

            {% elseif formgroup > 0 %}
                <div class="{{formgroupcss}}">

            {% else %}
                <div class="form-group">
            {% endif %}
        

        	{% if el.type == 'text' %}

                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    {% if el.prevent %}
                        <p class="form-control-static">{{ el.value|default( preventmsg ) }}</p>
                    {% else %}

                        <input class="form-control" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" id="{{name ~ el.name}}" type="text" value="{{el.value}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                        {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help}}</span>{% endif %}

                    {% endif %}
    
        	{% elseif el.type == 'password' %}
                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <input class="form-control" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" type="password" value=""{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

			{% elseif el.type == 'textarea' %}
                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                	<textarea class="form-control" {{el.disabled ? 'disabled="disabled" '}}{{el.readonly ? 'readonly="readonly" '}}name="{{name ~ el.name}}" id="{{name ~ el.name}}" cols="1" rows="1"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{el.value}}</textarea>
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

			{% elseif el.type == 'select' %}
                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>

                    {% if el.prevent %}
                        <p class="form-control-static">{{ el.options[ el.value ]|default( preventmsg ) }}</p>
                    {% else %}

                        <select class="form-control" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
    					{% for opv, opl in el.options %}
                            <option {{el.value == opv ? 'selected '}}value="{{ opv }}">{{ opl }}</option>
    					{% endfor %}
                        </select>

                        {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

                    {% endif %}

			{% elseif el.type == 'multiple' %}

                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <div>
                    <select data-role="multiselect" multiple="multiple" style="display:none" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}[]"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
					{% for opv, opl in el.options %}
                        <option {{el.value == opv ? 'selected '}}value="{{ opv }}">{{ opl }}</option>
					{% endfor %}
                    </select>
                    </div>
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

			{% elseif el.type == 'checkbox' %}
				<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" {{el.value == 'on' ? 'checked '}}type="checkbox"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{el.label|raw}}
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

			{% elseif el.type == 'hidden' %}
            	<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}"  type="hidden" value="{{el.value}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                
			{% elseif el.type == 'checkboxgroup' %}
                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>

                {% if el.prevent %}
                    <p class="form-control-static">{{ el.options|intersect(el.value)|values|default( preventmsg ) }}</p>
                {% else %}

                    {% if el.settings.cols == 1 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-12" %}
                    {% elseif el.settings.cols == 2 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-6" %}
                    {% elseif el.settings.cols == 3 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-4" %}
                    {% elseif el.settings.cols == 4 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-3" %}
                    {% else %}
                        {% set chkgseparate = false %}
                    {% endif %}

                    <div class="block-inner">
                       {% if chkgseparate %}
                           <div class="row">
                       {% endif %}

    		        {% for opv, opl in el.options %}

                       {% if chkgseparate %}
                            <div class="{{ chkgcss }}">
                       {% endif %}
                        
                        <label class="checkbox-inline">
                            <input style="position:relative;margin-right:1px;" {{el.disabled ? 'disabled="disabled" '}}id="{{name ~ el.name ~ opv}}" name="{{name ~ el.name ~ opv}}" {{opv in el.value|split(';') ? 'checked '}}type="checkbox"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}> {{opl}}
                        </label>

                       {% if chkgseparate %}
                            </div>
                       {% endif %}
                    {% endfor %}

                    {% if chkgseparate %}
                         </div>
                    {% endif %}

                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}
    				</div>

                {% endif %}

			{% elseif el.type == 'separator' %}
				<div class="clearboth"></div>

			{% elseif el.type == 'grid' %}
                {{ el.obj|raw }}

			{% elseif el.type == 'custom' %}
                {{ el.obj|raw }}

			{% elseif el.type == 'formheader' %}
                <div class="block-inner">
                    <h6 class="heading {% if el.options.hr %}heading-hr{% endif %}" style="margin-top:{% if not loop.first %}45px{% else %}15px{% endif %};">
                        <i class="{{el.icon}}"></i>{{el.title}}{% if el.description %}<small class="display-block">{{el.description|nl2br}}</small>{% endif %} 
                    </h6>
                </div>

			{% elseif el.type == 'message' %}
                <div class="callout callout-info fade in">
				    <h5>{{el.title}}</h5>
				    {% if el.description %}<p>{{el.description|nl2br}}</p>{% endif %}
                </div>

			{% elseif el.type == 'static' %}
            
                {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %} 
                {% if el.showvalue %}<p class="form-control-static" id="{{name ~ el.name}}">{{ el.value|default( 'unknown' ) }}</p>{% endif %}
                {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help}}</span>{% endif %}

			{% elseif el.type == 'button' %}
            
                {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %} 
                <div>
                <input type="button" class="btn btn-default" onClick="{{el.onclick|raw}}" value="{{ el.labelbutton }}"/>
                </div>
                {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

        	{% elseif el.type == 'staticimage' %}

                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <div>
                    <img id="{{name ~ el.name}}" {% if not el.options.html.width and not el.options.html.height %}style="height:auto;width:100%"{% endif %} src="{{ el.value }}"{%for k,v in el.options.html%} {{k}}={{v|json_encode}}{%endfor%}/>
                    </div>
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}

        	{% elseif el.type == 'staticmovie' %}

                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <div>
                    <video width="100%" controls poster="{{ el.value|transloadit( 'upvideo', 'url' ) }}" style="width:100%;height:auto;" id="{{name ~ el.name}}" class="video-js vjs-default-skin">
                        <source src="{{ el.value|transloadit( 'upvideomp4', 'url' ) }}" type="video/mp4">
                        <source src="{{ el.value|transloadit( 'upvideowebm', 'url' ) }}" type="video/webm">
                        <source src="{{ el.value|transloadit( 'upvideoflash', 'url' ) }}" type="video/flv">
                    </video>
                    </div>
                    {% if el.help %}<span class="help-block">{{el.help}}</span>{% endif %}


        	{% elseif el.type == 'staticmessage' %}
            <div class="block">
                                    <ul class="media-list">
										<li class="media">
										    <img class="pull-left media-object" src="{{el.icon|gravatar}}" alt="">
											<div class="media-body">
												<div class="clearfix">
													<span class="media-heading">{{ el.title }}</span>
													<span class="media-notice">{{ el.date|ago(0,1) }}</span>
												</div>
                                                {{ el.message|nl2br }}
											</div>
										</li>
									</ul>
</div>
			{% elseif el.type == 'stats' %}
                <ul class="statistics">
                {% for stat in el.stats %}
						    			<li>
						    				<div class="statistics-info">
							    				<a class="bg-{{stat.type|default('info')}}"><i class="{{stat.icon}}"></i></a>
							    				<strong>{{stat.value}}</strong>
							    			</div>
											<div class="progress progress-micro">
												<div style="width:{{ stat.percentage|default(100) }}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{stat.percentage|default(100)}}" role="progressbar" class="progress-bar progress-bar-{{stat.type|default('info')}}">
													<span class="sr-only">{{stat.percentage|default(100)}}%</span>
												</div>
											</div>
											<span>{{stat.title}}</span>
						    			</li>
                {% endfor %}
                </ul>

			{% elseif el.type == 'separatorline' %}
				<div class="line_2" style="margin:10px 0px 15px"></div>

			{% elseif el.type == 'captcha' %}
                <label>Security code</label>
                <script type="text/javascript">document.write('<a href="#" onclick="$(\'#cap{{name ~ el.name}}\').attr(\'src\', \'/captcha/\'+Math.floor(89999999*Math.random()+10000000));return false;"><img id="cap{{name ~ el.name}}" width="200" height="100" src="/captcha/'+Math.floor(89999999*Math.random()+10000000)+'" alt="c" /></a>');</script>
                <input name="{{name ~ el.name}}" type="text" size="20" maxlength="20">
                <span class="help-block">Please confirm 5 character code (letters only) and click on image if you want to see a different code</span>

			{% elseif el.type == 'transloadit' %}

                {% if formtransloadit == 0 %}
                	<input type="hidden" id="{{ name }}transloaditid" />

                    {% if isajax == false %}
    					<script type="text/javascript">myfwtransloadit('{{ name }}'); </script>
                    {% endif %}

                    {% set formtransloadit = 1 %}
                {% endif %}

                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>

                {% set tvalue = el.value|transloadit( '', 'assembly_id' ) %}

                {% if not el.prevent %}
    				<input type="hidden" name="{{ name ~ el.name }}" value="{{ tvalue }}" id="{{name ~ el.name}}V" class="{{ name }}transloaditV"/>
                    <div class="uploader {{ name }}transloaditU" {{ tvalue is not empty ? 'style="display:none"' }} id="{{ name ~ el.name }}U"><input name="{{ name ~ el.name }}N" class="transloaditN" onChange="$('#{{ name }}transloaditid').val($(this).attr('id'));$('form#{{name}}').data('transloadit.uploader')._options['exclude']='input:not([name={{ name ~ el.name }}N])';$('form#{{name}}').data('transloadit.uploader')._options['signature']='{{el.options.signature}}';$('form#{{name}}').data('transloadit.uploader')._options['params']=JSON.parse('{{el.options.params}}');" type="file" id="{{name ~ el.name}}" class="styled"><span id="{{ name ~ el.name }}S" class="filename {{ name }}transloaditS" style="-moz-user-select:none">No file selected</span><span class="action" style="-moz-user-select:none">Choose File</span></div>

                {% elseif tvalue is empty %}
                    <p class="form-control-static">{{ preventmsg }}</p>

                {% endif %}

            <div class="row {{ name }}transloaditC" {{ tvalue is empty ? 'style="display:none"' }} id="{{name ~ el.name}}C">
                <div class="col-xs-10" id="{{name ~ el.name}}P">

                {% if tvalue is not empty %}
                    {% if el.options.mode == 'image' %}
                        {% set imgssl_url = el.value|transloadit( 'upimage', 'ssl_url' ) %}
                        {% set imgwidth   = el.value|transloadit( 'upimage', 'meta', 'width' ) %}
                        {% set imgheight  = el.value|transloadit( 'upimage', 'meta', 'height' ) %}
                        <img style="height:auto;width:100%" src="{{ imgssl_url }}" width="{{ imgwidth }}" height="{{ imgheight }}" />
                    {% elseif el.options.mode == 'video' %}
                        {% set vposter    = el.value|transloadit( 'upvideo',      'ssl_url' ) %}
                        {% set vmp4       = el.value|transloadit( 'upvideomp4',   'ssl_url' ) %}
                        {% set vwebm      = el.value|transloadit( 'upvideowebm',  'ssl_url' ) %}
                        {% set vflash     = el.value|transloadit( 'upvideoflash', 'ssl_url' ) %}
                        <video controls id="v{{ tvalue }}" class="video-js vjs-default-skin" width="100%" style="width:100%;height:auto;" poster="{{ vposter }}"><source src="{{ vmp4 }}" type="video/mp4"><source src="{{ vwebm }}" type="video/webm"><source src="{{ vflash }}" type="video/flv"></video>
                    {% endif %}
                {% endif %}
                </div>
                <div class="col-xs-2">
                    {% if not el.prevent %}
                        <button onClick="$('#{{name ~ el.name}}C').hide();$('#{{name ~ el.name}}U').show();$('#{{name ~ el.name}}S').text('No file selected');$('#{{name ~ el.name}}V').val('');" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-remove3"></i></button>
                    {% endif %}
                </div>
            </div>

                {% if el.help and not el.prevent %}<span class="help-block">{{el.help}}</span>{% endif %}
            {% endif %}

        	{% if el.type != 'formgroup' %}
                {% if formgroup > 1 or formgroup < 1 %}
                        </div>
                {% elseif formgroup == 1 %}
                        </div>
                    </div>
                </div>
                {% endif %}
                {% set formgroup = formgroup-1 %}
            {% endif %}

    {% endfor %}


	{% if ismodal %}
                        </div>
                        <div class="modal-footer" style="margin-bottom:40px">
								
	{% endif %}
            
            {% for el in elements %}
    			{% if el.type == 'submit' and rendersubmit %}

                    <div class="form-actions {% if el.position == 'left' %}text-left{% else %}text-right{% endif %}">
                        <input {%if formtransloadit%} onClick="$('form#{{name}}').unbind('submit.transloadit');" {% endif %} {{el.disabled ? 'disabled="disabled" '}}type="submit" name="{{name ~ el.name}}" id="{{name}}submit" class="btn btn-success" value="{{el.label}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                    </div>

	    		{% elseif el.type == 'ajax' and el.label is not null %}
                    <input style="margin-top:3px;" onClick="myfwformsubmit('{{name}}','{{name ~ el.name}}ajax','{{el.label}}','{{name ~ el.name}}');" {{el.disabled ? 'disabled="disabled" '}}type="button" id="{{name ~ el.name}}ajax" class="btn {{el.css}}" value="{{el.label}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>

                {% endif %}
            {% endfor %}
            <input type="hidden" name="{{csrfname}}" id="{{csrfname}}" value="{{csrf}}"/>

	{% if ismodal %}                                
                            {% if isajax %}
                                <button data-dismiss="modal" class="btn btn-default" style="margin-left:5px;margin-top:3px;">Close</button>
                            {% endif %}
						</div>
                    </div>                            
                </div>
            </div>
	{% endif %}


	{% if renderaction %}
		</form>
	{% endif %}


{% endif %}
