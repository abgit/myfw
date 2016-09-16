
{% if allitems %}

    {% if title %}
        <h6 class="heading-hr"><i class="{{ title.icon }}"></i> {{ title.label }}</h6>
    {% endif %}

    {% if buttons is not empty %}
    <div class="row">
        <div class="col-md-12 text-right">
        {% for button in buttons %}
            <a style="margin-left:3px;" type="button" class="btn btn-{{button.class}}" onclick="{{ button.onclick }}"><i class="{{ button.icon }}"></i> {{ button.label }}</a>
        {% endfor %}
        </div>
    </div>
    {% endif %}
    
    {{ menuhtml|raw }}

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
                                        <tr{% if val[ key ] %} id="{{ val[ key ] }}"{% endif %}{% if rowclass and val[ rowclassdepends ] %} class="{{ rowclass }}"{% endif %}>

                                    		{% for col in labels %}
                                            
				                            <td{% if col.align or col.class %} class="{{ col.class }}{{ col.align ? ' text-' ~ col.align }}"{% endif %}{% if cols[ col.key ][0].type == 'ago' %} nowrap="nowrap"{% endif %}{%if col.onclick %} onclick="{{ col.onclick|replaceurl( val, tags ) }}" style="cursor:pointer"{% endif %}>
                                                {% for td in cols[ col.key ] %}

                                                    {% set value = val[ td.kval ] %}

                                                    {% if value is not empty or td.type == 'menu' or td.type == 'br' or td.type == 'space' or td.type == 'fixed' or td.default %}

                                                        {% if td.replace %}
                                                            {% set value = value|replace( td.replace ) %}
                                                        {% endif %}

                                                        {% set addonpre = td.addonpre %}

                                                        {% set addonpos = ( td.addonposorder ? value|order(false) ) ~ ( value == 1 ? td.addonpossing : td.addonpos ) %}

                                                        {% if td.type == 'simple' %}
                                                            {{ value|nl2space|t( td.truncate|default( 60 ) ) }}

                                                        {% elseif td.type == 'h4' %}
                                                                <h4{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %}>
                                                                    {{ addonpre|raw }}{{ value|nl2space|t(60) }}{{ td.prefix2|raw }}{{ val[ td.kval2 ] }}{{ addonpos|raw }}
                                                                </h4>

                                                        {% elseif td.type == 'h6' %}
                                                            <h6{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %}>{{ addonpre|raw }}{{ value|nl2space|t(60) }}{{ addonpos|raw }}</h6>

                                                        {% elseif td.type == 'span' %}
            				                            	<span{% if td.class %} class="{{ td.class.key ? val[ td.class.key ]|replace( td.class.list ) : td.class.list }}"{% endif %} style="display:block;font-size:11px;margin-top:-2px;">{{ addonpre|raw }}{{ value|nl2space|t(60) }}{{ addonpos|raw }}</span>

                                                        {% elseif td.type == 'progress' %}
                                                            <div class="progress">
                                                                {% set prog_val = value|number_format(0) %}
                                                                
                                                                {% set class = value|intervalmin( td.class )|default( 'info' ) %}
                                                                
                                                                {% if prog_val > 20 %}
                                                                    <div class="progress-bar progress-bar-{{ class }}" role="progressbar" aria-valuenow="{{ prog_val }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ prog_val }}%;margin-right:4px">{{ prog_val }}%</div>
                                                                {% else %}
                                                                    <div class="progress-bar progress-bar-{{ class }}" role="progressbar" aria-valuenow="{{ prog_val }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ prog_val }}%;margin-right:4px"></div>
                                                                    <span style="font-size:11px;vertical-align:-2px;">{{ prog_val }}%</span>
                                                                {% endif %}
                                                            </div>

                                                        {% elseif td.type == 'thumb' %}
                                                        
                                                            {% if td.onclick %}
                				                            	<a onclick="{{ td.onclick|replace({ (keyhtml): val[ key ] }) }}">
                                                            {% endif %}

                                                            <img src="{{ value|default( td.default ) }}" alt="" class="user-face">

                                                            {% if td.onclick %}
                				                            	</a>
                                                            {% endif %}

                                                        {% elseif td.type == 'image' %}
                                                            <img height="{{ td.height }}" width="{{ td.width }}" src="{{ td.cdn }}{{ value }}{{ td.sufix }}">

                                                        {% elseif td.type == 'label' %}
                                                            <span class="label label-mini label-{{ ( td.class.key ? val[ td.class.key ] : value )|replaceonly( td.class.list ? td.class.list : [] )|default( td.class.default ? td.class.default : 'default' ) }}">{{ value|t(20) }}</span>

                                                        {% elseif td.type == 'url' %}
            				                            	<a class="grid-url"{%if td.onclick %} onclick="{{ td.onclick|replaceurl( val, tags ) }}"{% else %} href="{{ value|replaceurl( val, tags ) }}" target="_blank"{% endif %} style="{% if td.bold %}font-weight:600;{% endif %}">{{ value|t( td.truncate|default( 60 ) ) }}</a>

                                                        {% elseif td.type == 'br' %}
                                                            <br />

                                                        {% elseif td.type == 'space' %}
                                                            &nbsp;&nbsp;

                                                        {% elseif td.type == 'ago' %}
                                                            <i class="icon-clock"></i> {{ value|ago }}
            				                            	<span class="hidden-xs" style="color:#999999;display:block;font-size:11px;margin:0px 0px 0px 20px;">{{ ( td.keycustomdate ? val[ td.keycustomdate ] : value )|date( td.dateonly ? "Y-m-d" : "Y-m-d H:i:s" ) }}</span>

                                                        {% elseif td.type == 'description' %}
            				                            	<span class="hidden-xs" style="color:#999999;display:{{ td.inline ? 'inline' : 'block' }};font-size:11px;margin:0px;">{{ addonpre|raw }}{{ value|nl2space|t(36) }}{{ addonpos|raw }}</span>

                                                        {% elseif td.type == 'info' %}
                                                            <span>{% if td.title %}<strong>{{ td.title }}</strong>{% endif %}{{ addonpre|raw }}{{ value|t(50) }}{{ addonpos|raw }}</span>

                                                        {% elseif td.type == 'fixed' %}
                                                            {% set fixedfilldefault = true %}
                                                            
                                                            {% for option in td.options if ( option.value is defined and option.value == value ) %}
                                                                <span{% if option.type %} class="label label-{{ option.type }}"{% endif %}>{{ option.label ? option.label : value }}</span>
                                                                {% set fixedfilldefault = false %}
                                                            {% endfor %}

                                                            {% set break = 0 %}
                                                            {% for option in td.options if ( break == 0 and option.range is defined and option.range >= value ) %}
                                                                <span{% if option.type %} class="label label-{{ option.type }}"{% endif %}>{{ option.label ? option.label : value }}</span>
                                                                {% set fixedfilldefault = false %}
                                                                {% set break = 1 %}
                                                            {% endfor %}

                                                            {% if fixedfilldefault and td.default.type %}
                                                                <span{% if td.default.type %} class="label label-{{ td.default.type }}"{% endif %}>{{ td.default.label ? td.default.label : value }}</span>
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

                                {% if values|length > 4 %}
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