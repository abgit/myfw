
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
                                            <th role="columnheader" class="text-center{%if col.class %} {{col.class}}{% endif %}"{%if col.width %} width="{{col.width}}"{% endif %}>{{col.label}}</th>
                                        {% endfor %}
                                        </tr>
				                    </thead>
				                <tbody id="{{ name }}">
                                    <tr{% if values is not empty %} style="display:none"{% endif %} class="odd" id="{{ name }}empty"><td colspan="{{labels|length}}" align="center">{{ emptymsg }}</td></tr>
{% endif %}

                                		{% for val in values %}
                                        <tr class="{{loop.index|odd ? 'odd' : 'even' }}" id="{{ val[ key ] }}">

                                    		{% for col in labels %}
				                            <td{% if col.align or col.class %} class="{{ col.align }} {{col.class}}"{% endif %}>
                                                {% for td in cols[ col.key ] %}

                                                    {% set value = val[ td.kval ] %}

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

                                                    {% elseif td.type == 'url' %}
        				                            	<a{%if td.href %} href="{{ td.href|replace({ (keyhtml): val[ key ] }) }}"{% endif %}{%if td.onclick %} onclick="{{ td.onclick|replace({ (keyhtml): val[ key ] }) }}"{% endif %} style="{% if td.bold %}font-weight:600;{% endif %}display:inline-block;margin-bottom:3px;margin-top:3px;">{{ value|t(60) }}</a>

                                                    {% elseif td.type == 'ago' %}
                                                        <i class="icon-clock"></i> {{ value|ago }}
        				                            	<span style="color:#999999;display:block;font-size:11px;margin:0px 0px 0px 20px;">{{ value }}</span>

                                                    {% elseif td.type == 'fixed' %}
                                                        {% set fixedfilldefault = true %}
                                                        {% for option in td.options if option.value == value %}
                                                            <span class="label label-{{ option.type }}">{{ option.label }}</span>
                                                            {% set fixedfilldefault = false %}
                                                        {% endfor %}
                                                        {% if fixedfilldefault and td.default.label %}
                                                            <span class="label label-{{ td.default.type }}">{{ td.default.label }}</span>
                                                        {% endif %}

                                                    {% elseif td.type == 'menu' %}
					                                    <div class="btn-group">
						                                    <button data-toggle="dropdown" class="btn btn-icon dropdown-toggle" type="button"><i style="font-size:12px" class="{{ td.icon }}"></i></button>
													        <ul class="dropdown-menu icons-right dropdown-menu-right mygridmenu">
                                                            {% for option in td.options %}
        														<li><a{% if option.href %} href="{{ option.href|replace({ (keyhtml): val[ key ] }) }}"{% endif %}{% if option.onclick %} onclick="{{ option.onclick|replace({ (keyhtml): val[ key ] }) }}"{% endif %}><i class="{{ option.icon }}"></i> {{ option.label }}</a></li>
		                                                    {% endfor %}
            		    									</ul>
	    				                                </div>

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