
{% if allitems %}

{% if title %}
    <h6 class="heading-hr" style="margin-top:30px;"><i class="{{ title.icon }}"></i> {{ title.label }}</h6>
{% endif %}


{% if buttons is not empty %}
<div style="margin-bottom:10px;" class="row">
    <div class="col-md-12 text-right">
    {% for button in buttons %}
        <a style="margin-top:3px;" type="button" class="btn btn-{{button.class}}" onclick="{{ button.onclick }}"><i class="{{ button.icon }}"></i> {{ button.label }}</a>
    {% endfor %}
    </div>
</div>
{% endif %}


<div class="datatable-tasks" style="padding-bottom:40px; overflow:auto">
				                <div role="grid">

                                    <table class="table table-bordered dataTable">
				                    <thead>
				                        <tr role="row">
                                		{% for col in labels %}
                                            <th role="columnheader" class="text-center"{%if col.width %} width="{{col.width}}"{% endif %}>{{col.label}}</th>
                                        {% endfor %}
                                        </tr>
				                    </thead>
				                    
				                <tbody id="{{ name }}">
{% endif %}
                                    {% if values is empty %}
                                        <tr class="odd" id="{{ name }}empty"><td colspan="{{labels|length}}" align="center">{{ emptymsg }}</td></tr>
                                    {% else %}
                                		{% for val in values %}
                                        <tr class="{{loop.index|odd ? 'odd' : 'even' }}" id="{{ val[ key ] }}">

                                    		{% for col in labels %}
				                            <td{% if col.align %} class="{{ col.align }}"{% endif %}>
                                                {% for td in cols[ col.key ] %}

                                                    {% if td.type == 'simple' %}
                                                        {{ val[ td.kval ] }}

                                                    {% elseif td.type == 'h4' %}
                                                        <h4>{{ val[ td.kval ] }}</h4>

                                                    {% elseif td.type == 'span' %}
        				                            	<span style="color:#999999;display:block;font-size:11px;margin-top:-2px;">{{ val[ td.kval ] }}</span>

                                                    {% elseif td.type == 'url' %}
        				                            	<a {%if td.href %}href="{{ td.href|replace({ (keyhtml): val[ key ] }) }}"{% endif %} {%if td.onclick %}onclick="{{ td.onclick|replace({ (keyhtml): val[ key ] }) }}"{% endif %} style="display:inline-block;font-weight:600;margin-bottom:3px;margin-top:3px;">{{ val[ td.kval ]|t(50) }}</a>

                                                    {% elseif td.type == 'ago' %}
                                                        <i class="icon-clock"></i> {{ val[ td.kval ]|ago }}
        				                            	<span style="color:#999999;display:block;font-size:11px;margin-top:-2px;">{{ val[ td.kval ] }}</span>

                                                    {% elseif td.type == 'fixed' %}
                                                        {% set fixedfilldefault = true %}
                                                        {% for option in td.options if option.value == val[ td.kval ] %}
                                                            <span class="label label-{{ option.type }}">{{ option.label }}</span>
                                                            {% set fixedfilldefault = false %}
                                                        {% endfor %}
                                                        {% if fixedfilldefault and td.default.label %}
                                                            <span class="label label-{{ td.default.type }}">{{ td.default.label }}</span>
                                                        {% endif %}

                                                    {% elseif td.type == 'menu' %}
					                                    <div class="btn-group dropup">
						                                    <button data-toggle="dropdown" data-hover="dropdown" class="btn btn-icon btn-{{ td.classtype }} dropdown-toggle" type="button"><i class="{{ td.icon }}"></i></button>
													        <ul class="dropdown-menu icons-right dropdown-menu-right">
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
                                    {% endif %}

{% if allitems %}
                                    </tbody>
                                </table>

                       </div>

                            {% if more %}
                              <div style="float:right; margin:5px 0px 5px 0px;">
                                <button onclick="{{ more.onclick }}" class="btn btn-default" type="button">{{ more.label }}</button>
                              </div>
                            {% endif %}
				    </div>
{% endif %}