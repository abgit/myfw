
{% if allitems %}

    {% if title %}
        <h6 class="heading-hr" style="margin-top:30px;"><i class="{{ title.icon }}"></i> {{ title.label }}</h6>
    {% endif %}

    {% if buttons is not empty %}
    <div style="margin-bottom:10px;" class="row">
        <div class="col-md-12 text-right">
        {% for button in buttons %}
            <a style="margin-left:3px;" type="button" class="btn btn-{{button.class}}" onclick="{{ button.onclick }}"><i class="{{ button.icon }}"></i> {{ button.label }}</a>
        {% endfor %}
        </div>
    </div>
    {% endif %}

    <div style="overflow-y:visible">
				                <div role="grid">
                                    <table id="{{ name }}table" class="table table-bordered dataTable" style="width:100%;max-width:100%;">
				                    <thead>
				                        <tr role="row">
                                		{% for col in labels %}
                                            <th role="columnheader" class="text-center{%if col.class %} {{col.class}}{% endif %}{%if orderby and orderby == col.key %} {{ orderbya == 1 ? 'sorting_asc' : 'sorting_desc' }}{% endif %}"{%if col.width %} width="{{col.width}}"{% endif %}>{{col.label}}</th>
                                        {% endfor %}
                                        </tr>
				                    </thead>
				                <tbody id="{{ name }}">
                                    <tr{% if values is not empty %} style="display:none"{% endif %} class="odd" id="{{ name }}empty"><td colspan="{{labels|length}}" align="center">{{ emptymsg }}</td></tr>
{% endif %}

                                		{% for val in values %}
                                        <tr id="{{ val[ key ] }}">

                                    		{% for col in labels %}
                                            
				                            <td{% if col.align or col.class %} class="{{ col.align }} {{col.class}}"{% endif %}{% if cols[ col.key ][0].type == 'ago' %} nowrap="nowrap"{% endif %}{%if col.onclick %} onclick="{{ col.onclick|replaceurl( val, tags ) }}" style="cursor:pointer"{% endif %}>
                                                {% for td in cols[ col.key ] %}

                                                    {% set value = val[ td.kval ] %}

                                                    {% if value is not empty or td.type == 'menu' or td.type == 'br' or td.type == 'space'  %}

                                                        {% if td.replace %}
                                                            {% set value = value|replace( td.replace ) %}
                                                        {% endif %}

                                                        {% if td.addonpre %}
                                                            {% set value = td.addonpre ~ value %}
                                                        {% endif %}

                                                        {% if td.addopos %}
                                                            {% set value = value ~ td.addonpos %}
                                                        {% endif %}

                                                        {% if td.type == 'simple' %}
                                                            {{ value|nl2space|t(60) }}

                                                        {% elseif td.type == 'h4' %}
                                                            <h4{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %}>{{ value|nl2space|t(60) }}</h4>

                                                        {% elseif td.type == 'h6' %}
                                                            <h6{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %}>{{ value|nl2space|t(60) }}</h6>

                                                        {% elseif td.type == 'span' %}
            				                            	<span{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %} style="display:block;font-size:11px;margin-top:-2px;">{{ value|nl2space|t(60) }}</span>

                                                        {% elseif td.type == 'progress' %}
                                                            <div class="progress">
                                                                {% set prog_val = value|number_format(0) %}
                                                                
                                                                {% if prog_val > 20 %}
                                                                    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ prog_val }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ prog_val }}%;padding-right:5px;margin-right:4px">{{ prog_val }}%</div>
                                                                {% else %}
                                                                    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ prog_val }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ prog_val }}%;padding-right:5px;margin-right:4px"></div>
                                                                    <span style="font-size:11px;vertical-align:-2px;">{{ prog_val }}%</span>
                                                                {% endif %}
                                                            </div>

                                                        {% elseif td.type == 'thumb' %}
                                                        
                                                            {% if td.onclick %}
                				                            	<a onclick="{{ td.onclick|replace({ (keyhtml): val[ key ] }) }}">
                                                            {% endif %}

                                                            <img src="{{ value }}" alt="" class="user-face">

                                                            {% if td.onclick %}
                				                            	</a>
                                                            {% endif %}

                                                        {% elseif td.type == 'image' %}
                                                            <img height="{{ td.height }}" width="{{ td.width }}" src="{{ td.cdn }}{{ value }}{{ td.sufix }}">

                                                        {% elseif td.type == 'label' %}
                                                            <span class="label label-mini {{ ( td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list )|default( 'label-primary' ) }}">{{ value|t(20) }}</span>

                                                        {% elseif td.type == 'url' %}
            				                            	<a class="grid-url"{%if td.onclick %} onclick="{{ td.onclick|replaceurl( val, tags ) }}"{% else %} href="{{ value|replaceurl( val, tags ) }}" target="_blank"{% endif %} style="{% if td.bold %}font-weight:600;{% endif %}">{{ value|t(60) }}</a>

                                                        {% elseif td.type == 'br' %}
                                                            <br />

                                                        {% elseif td.type == 'space' %}
                                                            &nbsp;

                                                        {% elseif td.type == 'ago' %}
                                                            <i class="icon-clock"></i> {{ value|ago }}
            				                            	<span class="hidden-xs" style="color:#999999;display:block;font-size:11px;margin:0px 0px 0px 20px;">{{ value|t(19,'') }}</span>

                                                        {% elseif td.type == 'description' %}
            				                            	<span class="hidden-xs" style="color:#999999;display:block;font-size:11px;margin:0px 0px 0px 20px;">{{ value|nl2space|t(36) }}</span>

                                                        {% elseif td.type == 'info' %}
                                                            <span><strong>{{ td.title }}</strong>{{ value|t(50) }}</span>

                                                        {% elseif td.type == 'fixed' %}
                                                            {% set fixedfilldefault = true %}
                                                            
                                                            {% for option in td.options if ( option.value is defined and option.value == value ) %}
                                                                <span class="label label-{{ option.type }}">{{ option.label ? option.label : value }}</span>
                                                                {% set fixedfilldefault = false %}
                                                            {% endfor %}

                                                            {% set break = 0 %}
                                                            {% for option in td.options if ( break == 0 and option.range is defined and option.range >= value ) %}
                                                                <span class="label label-{{ option.type }}">{{ option.label ? option.label : value }}</span>
                                                                {% set fixedfilldefault = false %}
                                                                {% set break = 1 %}
                                                            {% endfor %}

                                                            {% if fixedfilldefault and td.default.type %}
                                                                <span class="label label-{{ td.default.type }}">{{ td.default.label ? td.default.label : value }}</span>
                                                            {% endif %}

                                                        {% elseif td.type == 'menu' %}
    					                                    <div class="btn-group">
    						                                    <button data-toggle="dropdown" class="btn btn-icon dropdown-toggle" type="button"><i style="font-size:12px" class="{{ td.icon }}"></i></button>
    													        <ul class="dropdown-menu icons-right dropdown-menu-right mygridmenu">
                                                                {% for option in td.options %}
                                                                    {% set optiondisabled = ( option.disabled and val[ option.disableddepends ] ) %}
            														<li{{ optiondisabled ? ' class="disabled"' }} id="{{ val[ key ] }}m{{ loop.index0 }}"><a{% if option.href and not optiondisabled %} href="{{ option.href|replace({ (keyhtml): val[ key ] }) }}"{% endif %}{% if option.onclick and not optiondisabled %} onclick="{{ option.onclick|replace({ (keyhtml): val[ key ] }) }}"{% endif %}><i class="{{ option.icon }}"></i> {{ option.label }}</a></li>
    		                                                    {% endfor %}
                		    									</ul>
    	    				                                </div>

                                                        {% endif %}
                                                    {% endif %}

                                              {% endfor %}

				                            </td>
                                            {% endfor %}

				                        </tr>
                                        {% endfor %}


{% if allitems %}
                                    </tbody>
                                </table>
                       </div>

                            {% if ( more or buttons is not empty ) %}
                              <div style="margin:8px 0px 5px 0px;text-align:right">

                                {% if values|length > 1 %}
                                    {% for button in buttons %}
                                        <a style="margin-left:3px;margin-top:3px" type="button" class="btn btn-{{button.class}}" onclick="{{ button.onclick }}"><i class="{{ button.icon }}"></i> {{ button.label }}</a>
                                    {% endfor %}
                                {% endif %}

                                {% if more and values|length == perpage %}
                                    <button id="{{ name }}more" style="margin-left:3px;margin-top:3px" onclick="{{ more.onclick }}" class="btn btn-default" type="button"><i class="icon-arrow-down11"></i> {{ more.label }}</button>
                                {% endif %}
                              </div>
                            {% endif %}
        </div>
{% endif %}