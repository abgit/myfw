
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
		<form action="{{ action }}" method="post" name="{{ name }}" id="{{ name }}" role="form"{% if target %} target="{{ target }}"{% endif %}>
	{% endif %}

	{% if ismodal %}
            <div id="{{ modal.id }}" class="modal" tabindex="-1" role="dialog"{{ modal.static ? ' data-keyboard="false" data-backdrop="static"'}}>
				<div class="modal-dialog{{ modal.class ? ' ' ~ modal.class }}"{% if modal.width %} style="max-width:{{modal.width}}px"{% endif %}>
					<div class="modal-content">
						<div class="modal-header" style="background-color:#3B5998">
							{% if modal.closebutton %}<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>{% endif %}
							<h4 class="modal-title">{% if modal.icon %}<i class="{{ modal.icon }}"></i>{% endif %}&nbsp;{{ modal.title }}</h4>
						</div>
    					<div class="modal-body with-padding" style="padding:20px 21px 0px 21px">
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

            {% elseif not el.isbutton %}
                <div class="form-group">
            {% endif %}
        

        	{% if el.type == 'text' %}

                    {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %}
                    {% if el.prevent %}
                        <p class="form-control-static">{{ el.value|default( preventmsg ) }}</p>
                    {% else %}

                        {% if el.addonpre or el.addonpos %}<div class="input-group">{% endif %}
                        {% if el.addonpre %}<span class="input-group-addon">{{ el.addonpre }}</span> {% endif %}
                        <input class="form-control" {{el.disabled ? 'disabled="disabled" '}}{{el.placeholder ? 'placeholder=' ~ el.placeholder }} name="{{name ~ el.name}}" id="{{name ~ el.name}}" type="text" value="{{el.value}}">
                        {% if el.addonpos %}<span class="input-group-addon">{{ el.addonpos }}</span>{% endif %}
                        {% if el.addonpre or el.addonpos %}</div>{% endif %}
                        {% if el.addonend %}<span class="label label-block label-primary text-center">{{ el.addonend }}</span>{% endif %}

                        {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help|nl2br}}</span>{% endif %}

                    {% endif %}

        	{% elseif el.type == 'bitcoin' %}

                    {% if el.label or el.rules.required %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %}
                    {% if el.prevent %}
                        <p class="form-control-static">{{ el.value|default( preventmsg ) }}</p>
                    {% else %}

                        <div class="input-group">
                            <span class="input-group-addon">&#3647;</span>
                            <input onkeyup="$('.aux{{name ~ el.name}}').each( function(){ $(this).text( $(this).attr( 'data-symb' ) + ' ' + ( parseFloat(0+$('#{{name ~ el.name}}').val().replace(',','.')) * $(this).attr( 'data-val' )  ).toFixed(2)   ); });" class="form-control" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" id="{{name ~ el.name}}" type="text" value="{{el.value|toBTC|toround|nozero}}">
                        </div>
                        <span class="label label-block label-primary text-center">
                            {% for cur in el.currencies %}
                                <span class="aux{{name ~ el.name}}" data-symb="{{ cur.symbol }}" data-val="{{ cur.rate }}">{{ cur.symbol }} {{ ( cur.rate * el.value|toBTC )|number_format(2, '.', '') }}</span>
                                {% if not loop.last %}
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                {% endif %}
                            {% endfor %}
                        </span>

                        {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help|raw|nl2br}}</span>{% endif %}
                    {% endif %}
    
        	{% elseif el.type == 'password' %}
                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <input class="form-control" {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" type="password" value=""{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'textarea' %}
                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                	<textarea class="form-control" {{el.disabled ? 'disabled="disabled" '}}{{el.readonly ? 'readonly="readonly" '}}name="{{name ~ el.name}}" id="{{name ~ el.name}}" cols="1" rows="{{ el.rows }}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{ isajax ? el.value|replace({"\n\r": "%0DMYFW", "\n": "%0DMYFW", "\r": "%0DMYFW"}) : el.value }}</textarea>
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'select' %}
                    {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %}

                    {% if el.prevent %}
                        <p class="form-control-static">{{ el.options[ el.value ]|default( preventmsg ) }}</p>
                    {% else %}

                        <select class="form-control" style="width:auto" {{el.disabled ? 'disabled="disabled" '}}name="{{el.name}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>
    					{% for opv, opl in el.options %}
                            <option {{el.value == opv ? 'selected '}}value="{{ opv }}">{{ opl }}</option>
    					{% endfor %}
                        </select>

                        {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

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
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'checkbox' %}
				<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" {{el.value == 'on' ? 'checked '}}type="checkbox"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>{{el.label|raw}}
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'hidden' %}
            	<input {{el.disabled ? 'disabled="disabled" '}}name="{{ el.name }}" type="hidden" value="{{el.value}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>

			{% elseif el.type == 'cameratag' %}
                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
            	<div style="width:321px;height:241px;"><camera name="{{ name ~ el.name }}" id="{{ name ~ el.name }}" data-app-id="{{ el.appid }}" data-maxlength="{{ el.maxlength|default(30) }}"></div>
                {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'cameratagvideo' %}
                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                <div style="width:321px;height:241px;">
                    <video width="100%" controls style="width:100%;height:auto;" id="{{name ~ el.name}}" data-uuid="{{ el.value }}"></video>
                </div>

			{% elseif el.type == 'ziggeo' %}
                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
            	<div style="width:321px;height:241px;" id="{{ name ~ el.name }}d"></div>
                <input type="hidden" name="{{ name ~ el.name }}" id="{{ name ~ el.name }}">
                {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}


			{% elseif el.type == 'checkboxgroup' %}
                {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %}

                {% if el.prevent %}
                    <p class="form-control-static">{{ el.options|intersect(el.value)|values|default( preventmsg ) }}</p>
                {% else %}

                    {% if el.settings.cols == 1 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-12" %}
                    {% elseif el.settings.cols == 2 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-6 col-xs-6" %}
                    {% elseif el.settings.cols == 3 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-4 col-xs-6" %}
                    {% elseif el.settings.cols == 4 %}
                        {% set chkgseparate = true %}
                        {% set chkgcss      = "col-md-3 col-xs-6" %}
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

                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}
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
                    <h6 class="heading{% if el.hr %} heading-hr{% endif %}{% if el.align %} text-{{ el.align }}{% endif %}">
                        {% if el.title %}<i class="{{ el.icon|default( 'icon-books' ) }}"></i>{{ el.title }}{% endif %}{% if el.description %}<small class="display-block{% if el.descriptionclass %} {{ el.descriptionclass }}{% endif %}" style="line-height:1.2">{{el.description|nl2br}}</small>{% endif %} 
                    </h6>
                </div>

			{% elseif el.type == 'message' %}
                <div class="callout callout-{{ el.css }} fade in">
				    {% if el.title %}<h5>{{el.title}}</h5>{% endif %}
				    {% if el.description %}
                        <p>{{el.description|raw|nl2br}}</p>
                    {% endif %}
                    {% if el.buttonlabel %}
                        <a type="button" class="btn btn-{{el.buttoncss|default(el.css)}}" style="margin-top:4px;"{% if el.buttononclick %} onclick="{{el.buttononclick}}"{% endif %}{% if el.buttonhref %} href="{{el.buttonhref}}"{% endif %}>
                            {% if el.buttonicon %}<i class="{{ el.buttonicon }}"> </i>{% endif %}
                            {{el.buttonlabel}}
                        </a>
                    {% endif %}            
                </div>

			{% elseif el.type == 'static' %}
            
                {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %} 
                {% if el.showvalue %}<p class="form-control-static" id="{{name ~ el.name}}">{{ el.value|default( 'unknown' )|nl2br }}</p>{% endif %}
                {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help|nl2br}}</span>{% endif %}

			{% elseif el.type == 'button' %}
            
                {% if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{% endif %} 
                <div>
                <input type="button" class="btn btn-default" {% if el.onclick %}onClick="{{el.onclick|raw}}"{% endif %} {% if el.href %}href="{{el.href|raw}}"{% endif %} value="{{ el.labelbutton }}"/>
                </div>
                {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

        	{% elseif el.type == 'bitcoinqrcode' %}
                    {%if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{%endif%}
                    <div>
                    <img id="{{el.name}}" src="{{el.key ? elements[ el.key ].value|bitcoinqrcode( el.acc, el.width ) : el.value}}" width="{{ el.width }}" height="{{ el.width }}"{%for k,v in el.options.html%} {{k}}={{v|json_encode}}{%endfor%}/>
                    </div>
                    <span class="label label-block label-default text-center">{{ el.acc }}</span>

                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

                	<input {{el.disabled ? 'disabled="disabled" '}}name="{{name ~ el.name}}" type="hidden" value="{{el.acc}}">


        	{% elseif el.type == 'staticimage' %}

                    {%if el.label %}<label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>{%endif%}
                    <div{%if el.align %} style="text-align:{{ el.align }}"{% endif %}>
                    <img id="{{el.name}}" {% if el.width %}width="{{ el.width }}"{% endif %} {% if el.height %}height="{{ el.height }}"{% endif %} {% if not el.width and not el.height %}style="height:auto;width:100%"{% endif %} src="{{ el.value }}"{%for k,v in el.options.html%} {{k}}={{v|json_encode}}{%endfor%}/>
                    </div>
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}

        	{% elseif el.type == 'staticmovie' %}

                    <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                    <div>
                    <video width="100%" controls poster="{{ el.value|transloadit( 'upvideo', 'url' ) }}" style="width:100%;height:auto;" id="{{name ~ el.name}}" class="video-js vjs-default-skin">
                        <source src="{{ el.value|transloadit( 'upvideomp4', 'url' ) }}" type="video/mp4">
                        <source src="{{ el.value|transloadit( 'upvideowebm', 'url' ) }}" type="video/webm">
                        <source src="{{ el.value|transloadit( 'upvideoflash', 'url' ) }}" type="video/flv">
                    </video>
                    </div>
                    {% if el.help %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}


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
			{% elseif el.type == 'statistics' %}
                {{ el.obj|raw }}

			{% elseif el.type == 'separatorline' %}
				<div class="line_2" style="margin:10px 0px 15px"></div>

			{% elseif el.type == 'separatorhr' %}
                <hr>

			{% elseif el.type == 'captcha' %}
                <label>Security code</label>
                <script type="text/javascript">document.write('<a href="#" onclick="$(\'#cap{{name ~ el.name}}\').attr(\'src\', \'/captcha/\'+Math.floor(89999999*Math.random()+10000000));return false;"><img id="cap{{name ~ el.name}}" width="200" height="100" src="/captcha/'+Math.floor(89999999*Math.random()+10000000)+'" alt="c" /></a>');</script>
                <input name="{{name ~ el.name}}" type="text" size="20" maxlength="20">
                <span class="help-block">Please confirm 5 character code (letters only) and click on image if you want to see a different code</span>

			{% elseif el.type == 'filestack' %}
            <label>{{ el.label|raw }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
            <div class="row">
                <div class="col-xs-7" style="min-height:{{ el.height }}px">
                    <img onClick="filepicker.pickAndStore({multiple:false,mimetypes:['image/jpg','image/jpeg','image/png','image/bmp'],cropRatio:1,cropForce:true,{{ el.security }}services:['COMPUTER','CONVERT'],conversions: ['crop', 'rotate', 'filter']},{{ el.fsoptions|json_encode }},function(Blobs){myfwsubmit('{{ el.processing }}','Processing ...',{img:Blobs[0].url},false,'info');$('#filestackh{{ name ~ el.name }}').val(Blobs[0].url);});" id="filestacki{{ name ~ el.name }}" style="height:auto;width:100%;max-width:{{ el.width }}px" src="{{ el.value|filestackresize( el.width )|default( el.default ) }}" width="{{ el.width }}" height="{{ el.height }}" />
                    <input type="hidden" id="filestackh{{ name ~ el.name }}" name="{{ name ~ el.name }}" value="{{ el.value|default( '' ) }}"/>
                </div>
                <div class="col-xs-5" style="padding:0px;">
                    <button onClick="filepicker.pickAndStore({multiple:false,mimetypes:['image/jpg','image/jpeg','image/png','image/bmp'],cropRatio:1,cropForce:true,{{ el.security }}services:['COMPUTER','CONVERT'],conversions: ['crop', 'rotate', 'filter']},{{ el.fsoptions|json_encode }},function(Blobs){myfwsubmit('{{ el.processing }}','Processing ...',{img:Blobs[0].url},false,'info');$('#filestackh{{ name ~ el.name }}').val(Blobs[0].url);});" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-image2"></i></button> <button onClick="$('#filestacki{{ name ~ el.name }}').attr('src','{{ el.default }}');$('#filestackh{{ name ~ el.name }}').val('');" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-remove3"></i></button>
                </div>
            </div>


			{% elseif el.type == 'filestackwebcam' %}
                <label>{{ el.label }}{{el.rules.required ? ' <span>(required)</span>'}}</label>
                <div class="row">
                    <div class="col-xs-8">
                        <img onClick="filepicker.pickAndStore({multiple:false,{{ el.security }}services:['VIDEO']},{{ el.fsoptions|json_encode }},function(Blobs){myfwsubmit('{{ el.urlvideo }}','Processing ...',{mov:Blobs[0].url},false,'info');$('#filestackh{{ name ~ el.name }}').val(Blobs[0].url);});" id="filestacki{{ name ~ el.name }}" style="{{ el.value ? 'display:none;' }}height:auto;width:100%;max-width:{{ el.width }}px" src="{{ el.default }}" width="{{ el.width }}" height="{{ el.height }}" />

                        <video {{ not el.value ? 'style="display:none"' }} controls id="filestackv{{ name ~ el.name }}" width="{{ el.width }}" height="{{ el.height }}" style="width:100%;height:auto;"><source src="{{ el.value }}" type="video/mp4"></video>

                        <input type="hidden" id="filestackh{{ name ~ el.name }}" name="{{ name ~ el.name }}" value="{{ el.value|default( '' ) }}"/>
                    </div>
                    <div class="col-xs-4" style="padding:0px;">
                        <button onClick="filepicker.pickAndStore({multiple:false,{{ el.security }}services:['VIDEO']},{{ el.fsoptions|json_encode }},function(Blobs){myfwsubmit('{{ el.urlvideo }}','Processing ...',{mov:Blobs[0].url},false,'info');$('#filestackh{{ name ~ el.name }}').val(Blobs[0].url);});" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-camera5"></i></button> <button onClick="$('#filestacki{{ name ~ el.name }}').attr('src','{{ el.default }}').show();$('#filestackh{{ name ~ el.name }}').val('');$('#filestackv{{ name ~ el.name }}').hide()" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-remove3"></i></button>
                    </div>
                </div>
                {% if el.help %}<span id="help{{name ~ el.name}}" class="help-block">{{el.help|nl2br}}</span>{% endif %}

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
                    <div class="uploader {{ name }}transloaditU" id="{{ name ~ el.name }}U"{{ tvalue is not empty ? ' style="display:none"' }}><input name="{{ name ~ el.name }}N" class="transloaditN"{% if el.disabled %} disabled="disabled"{% else %} onChange="$('#{{ name }}transloaditid').val($(this).attr('id'));$('form#{{name}}').data('transloadit.uploader')._options['myfwmode']=1;$('form#{{name}}').data('transloadit.uploader')._options['exclude']='input:not([name={{ name ~ el.name }}N])';$('form#{{name}}').data('transloadit.uploader')._options['signature']='{{el.options.signature}}';$('form#{{name}}').data('transloadit.uploader')._options['params']=JSON.parse('{{el.options.params}}');"{% endif %} type="file" id="{{name ~ el.name}}" class="styled"><span id="{{ name ~ el.name }}S" class="filename {{ name }}transloaditS" style="-moz-user-select:none">Select file</span><span class="action" style="-moz-user-select:none">Choose File</span></div>

                {% elseif tvalue is empty %}
                    <p class="form-control-static">{{ preventmsg }}</p>

                {% endif %}

            <div class="row {{ name }}transloaditC" id="{{name ~ el.name}}C"{{ tvalue is empty ? ' style="display:none;"' }}>
                <div class="col-xs-7" id="{{name ~ el.name}}P">

                {% if tvalue is not empty %}
                    {% if el.options.mode == 'image' %}
                        {% set imgssl_url = el.value|transloadit( 'upimage', 'ssl_url' ) %}
                        {% set imgwidth   = el.value|transloadit( 'upimage', 'meta', 'width' ) %}
                        {% set imgheight  = el.value|transloadit( 'upimage', 'meta', 'height' ) %}
                        <img style="height:auto;width:100%;max-width:{{ imgwidth }}px" src="{{ imgssl_url }}" width="{{ imgwidth }}" height="{{ imgheight }}" />
                    {% elseif el.options.mode == 'video' %}
                        {% set vposter    = el.value|transloadit( 'upvideo',      'ssl_url' ) %}
                        {% set vmp4       = el.value|transloadit( 'upvideomp4',   'ssl_url' ) %}
                        {% set vwebm      = el.value|transloadit( 'upvideowebm',  'ssl_url' ) %}
                        {% set vflash     = el.value|transloadit( 'upvideoflash', 'ssl_url' ) %}
                        <video controls id="v{{ tvalue }}" class="video-js vjs-default-skin" width="100%" style="width:100%;height:auto;" poster="{{ vposter }}"><source src="{{ vmp4 }}" type="video/mp4"><source src="{{ vwebm }}" type="video/webm"><source src="{{ vflash }}" type="video/flv"></video>
                    {% endif %}
                {% endif %}
                </div>
                <div class="col-xs-5" style="padding:0px;">
                    {% if not el.prevent %}
                        <button onClick="$('#{{name ~ el.name}}C').hide();$('#{{name ~ el.name}}U').show();$('#{{name ~ el.name}}S').text('Select file');$('#{{name ~ el.name}}V').val('');" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-remove3"></i></button>
                    {% endif %}
                </div>
            </div>

                {% if el.help and not el.prevent %}<span class="help-block">{{el.help|nl2br}}</span>{% endif %}
            {% endif %}

        	{% if el.type != 'formgroup' and not el.isbutton %}
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
                        <div class="modal-footer">
								
	{% endif %}
            
        {% if footer %}
            <div style="text-align:right">
        {% endif %}
            
            {% for el in elements if el.isbutton %}
    			{% if el.type == 'submit' and rendersubmit %}
                    <input style="margin-top:3px;" onClick="{%if formtransloadit%}$('form#{{name}}').unbind('submit.transloadit');{% endif %}this.disabled=true;this.value='please wait';this.form.submit();" {{el.disabled ? 'disabled="disabled" '}}type="submit" name="{{name ~ el.name}}" id="{{name}}submit" class="btn btn-success" value="{{el.label}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>

	    		{% elseif el.type == 'ajax' and el.label is not null %}
                    <input style="margin-top:3px;" onClick="myfwformsubmit('{{name}}','{{name ~ el.name}}ajax','{{el.label}}','{{name ~ el.name}}');" {{el.disabled ? 'disabled="disabled" '}}type="button" id="{{name ~ el.name}}ajax" class="btn {{el.css}}" value="{{el.label}}"{%for k,v in el.options.html%} {{k}}={{v|json_encode|raw}}{%endfor%}>

	    		{% elseif el.type == 'ajaxbutton' %}
                    
                    {% if el.href %}
                        <a type="button" href="{{ el.href }}" style="margin-left:5px;margin-top:3px;" class="btn btn-default">{{ el.labelbutton }}</a>
                    {% else %}
                        <input type="button" class="btn {{ el.css|default( 'btn-default' ) }}" style="margin-left:5px;margin-top:3px;"{% if el.onclick %} onClick="{{el.onclick|raw}}"{% endif %} value="{{ el.labelbutton }}"/>
                    {% endif %}

                {% endif %}
            {% endfor %}
            <input type="hidden" name="{{csrfname}}" id="{{csrfname}}" value="{{csrf}}"/>

        {% if footer %}
            </div>
        {% endif %}


	{% if ismodal %}                                
                            {% if closeb %}
                                <button data-dismiss="modal" class="btn btn-{{closeset.class|default('default')}}" style="margin-left:5px;margin-top:3px;">{{closeset.label|default('Close')}}</button>
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
