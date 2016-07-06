
{% if allitems %}
    {% if elements.tmenu %}
        <div class="row" style="margin-bottom:20px;">
        <div class="col-md-12 text-right">
                                                {% for menu in elements.tmenu %}

                                                    {% if menu.type == 0 %}
                                                        <a style="margin-left:2px;margin-top:6px" class="btn {{ menu.class ? menu.class : 'btn-default'}}" {% if menu.color %} style="color:{{menu.color}}"{% endif %}{% if menu.href %} href="{{ menu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if menu.onclick %} onclick="{{ menu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ menu.icon }}"></i> {{ menu.label }}</a>

                                                    {% elseif menu.type == 3 %}
                                                        {% for i in 1..menu.it %}<br />{% endfor %}

                                                    {% elseif menu.type == 1 %}
                                                    <div class="btn-group" style="margin-left:2px;margin-top:6px">
                                                    <a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown"><i class="{{ menu.icon }}"></i> {{menu.label}} <span class="caret"></span></a>
    													<ul class="dropdown-menu icons-right dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                <li><a{% if submenu.color %} style="color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ submenu.icon }}"></i> {{ submenu.label }}</a></li>
                                                            {% endfor %}
    													</ul>
    											    </div>

                                                    {% elseif menu.type == 2 %}
                                                    <div class="btn-group" style="margin-left:2px;margin-top:6px">
                                                    <a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown"><i class="{{ menu.icon }}"></i> <span id="menbl{{ menu.id }}">{{menu.label}}</span> <span class="caret"></span></a>
    													<ul class="dropdown-menu icons-left dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                <li {{submenu.selected ? 'class="active"' }} id="menl{{ menu.id }}{{ submenu.id }}"><a{% if submenu.color %} style="color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i id="meni{{ menu.id }}{{ submenu.id }}" class="icon-checkmark" style="visibility:{{ submenu.selected ? 'visible' : 'hidden' }}"></i> {{ submenu.label }}</a></li>
                                                            {% endfor %}
    													</ul>
    											    </div>

                                                    {% endif %}

                                                {% endfor %}
        </div></div>
    {% endif %}

    <div class="row" id="{{name}}">
{% endif %}

        {% for value in values %}
						  <div class="col-md-{{ size }}{%if sizeoffset %} col-md-offset-{{sizeoffset}}{% endif %}" id="{{ name }}{{ value[ key ] }}">
                                <div class="block task task-low" style="margin-bottom:30px{% if elements.back %};background-size:100% 100%;background-image:url({{ cdn }}{{ value[ elements.back.key ] }}){% endif %}">
									<div class="with-padding"{% if action %} onclick="{{ action|replaceurl( value, tags ) }}" style="margin: 0px;cursor:pointer;min-height:150px"{% else %} style="min-height:150px"{% endif %}>

                                        {% if elements.thumb.static or value[ elements.thumb.key ] or elements.thumb.default %}

    										<div class="col-sm-{{ elements.thumb.size }}" style="text-align: center;">
                                                
                                                {% if elements.thumb.onclick %}
                                                    <a onclick="{{ elements.thumb.onclick|replaceurl( value, tags ) }}">
                                                {% endif %}

                                                <img class="pt{{ value[ elements.thumb.classkey ] }}" style="margin-top:10px;margin-left:-10px" src="{% if elements.thumb.static %}{{ cdn }}{{ elements.thumb.key|raw }}{% else %}{{ cdn }}{{ value[ elements.thumb.key ]|default( elements.thumb.default ) }}{% endif %}">

                                                {% if elements.thumb.onclick %}
                                                    </a>
                                                {% endif %}
                                            </div>

                                            {% set class2 = elements.info ? 'col-sm-' ~ ( 12 - 3 - elements.thumb.size ) : 'col-sm-' ~ ( 12 - elements.thumb.size ) %}
                                        {% else %}
                                            {% set class2 = elements.info ? 'col-sm-9' : 'col-sm-12' %}
                                        {% endif %}

    										<div class="{{ class2 }} task-description">

                                                    {% if elements.title.onclick %}
                                                        <a onclick="{{ elements.title.onclick|replaceurl( value, tags ) }}">
                                                    {% endif %}

    										        {% if elements.title %}
                                            		    <span class="task-d">{% if elements.title.static %}{{ elements.title.key|raw }}{% else %}{{ value[ elements.title.key ]|t(45) }}{% endif %}</span>
    										        {% endif %}

                                                    {% if elements.title.onclick %}
                                                        </a>
                                                    {% endif %}

    										        {% if elements.reference %}
                                                        <span class="text-success" style="font-size:10px">{{ value[ elements.reference.key ]|t(40) }}</span>
    										        {% endif %}

                                                    {% if elements.description.onclick %}
                                                        <a onclick="{{ elements.description.onclick|replaceurl( value, tags ) }}">
                                                    {% endif %}

    										        {% if elements.description %}
    												    <span>{% if elements.description.static %}{{ elements.description.key|raw }}{% else %}{{ value[ elements.description.key ]|t(150) }}{% endif %}</span>
    										        {% endif %}

                                                    {% if elements.description.onclick %}
                                                        </a>
                                                    {% endif %}
    										</div>
                                            
                                            {% if elements.info %}
    										<div class="col-sm-3 task-panel">
    											<div class="task-info" style="margin-top:10px">

                                                    {% for info in elements.info %}

                                                        {% if info.type == 0 %}

                                                            {% if value[ info.key ] is defined %}

                                                                <span id="pi{{ info.key }}{{ value[ key ] }}" {{ ( info.depends and not value[ info.depends ] ) ? 'class="hide"' }}>
            												    {% if info.class %}
                                                                    <span id="pic{{ info.key }}{{ value[ key ] }}" class="label {{ info.classreplacekey ? ( value[ info.classreplacekey ]|replaceonly( info.class )|default( info.defaultclass ) ) : info.class }}">
                                                                {% endif %}
                                                        
                                                                {% set val = value[ info.key ] %}
                                                                {% if info.filter == 'rnumber' %}
                                                                    {% set val = val|rnumber(true) %}
                                                                {% endif %}
                                                        
                                                                {{ info.prefix|raw }}{{ val|t(20) }}{{ info.sufix|raw }}{% if info.extrakey %}{{ value[ info.extrakey ]|t(20) }}{{ info.extrasufix|raw }}{% endif %}

            												    {% if info.class %}
                                                                    </span>
                                                                {% endif %}                                                        
                                                               </span>

                                                            {% elseif info.defaultvalue %}

                                                                <span>
            												    {% if info.defaultclass %}
                                                                    <span class="label {{info.defaultclass}}">
                                                                {% endif %}
                                                        
                                                                {{ info.defaultprefix }}{{ info.defaultvalue }}{{ info.defaultsufix }}

            												    {% if info.defaultclass %}
                                                                    </span>
                                                                {% endif %}                                                        
                                                                </span>
                                                            {% endif %}

                                                        {% elseif info.type == 1 %}

                                                            {% if value[ info.key ] %}

                                                                {% if info.onclick %}
                                                                    <a onclick="{{ info.onclick|replaceurl( value, tags ) }}">
                                                                {% endif %}

                                                                <img class="infothumb" width="{{ info.size|default(40) }}" height="{{ info.size|default(40) }}" style="max-width:{{ info.size|default(40) }}px;" alt="" src="{{ cdn }}{{ value[ info.key ] }}">

                                                                {% if info.onclick %}
                                                                    </a>
                                                                {% endif %}

                                                            {% endif %}

                                                        {% endif %}
                                                    {% endfor %}
    											</div>
    										</div>
                                            {% endif %}
									</div>
									<div class="panel-footer" style="min-height:38px">
                                        {% if elements.status %}
                                            <div class="pull-left">
                                                    <span>
                                            {% for status in elements.status %}
                                                    {% if status.type == 1 %}
                                                        <span>{{ status.prefix|raw }}{{ value[ status.key ]|t(90)}}{{ ( status.sufixsingular and value[ status.key ] == 1 ) ? status.sufixsingular|raw : status.sufix|raw }}</span>
                                                    {% elseif status.type == 2 %}
                                                    
                                                        {% if status.icon %}
                                                            {% set iconval = status.icon %}
                                                        {% else %}
                                                            {% set iconval = value[ status.key ]|replace( status.icons ) %}
                                                        {% endif %}
                                                    
                                                        {% if status.depends %}
                                                            {% set statusdisplay = value[ status.depends ] %}
                                                        {% else %}
                                                            {% set statusdisplay = 1 %}
                                                        {% endif %}

                                                        <i id="pani{{ value[ key ] }}{{ status.key }}" {{ statusdisplay != 1 ? 'style="display:none"' }} class="{{ iconval }}{{ status.keyclass ? ' panicon' ~ value[ status.keyclass ] }}"></i>
                                                    {% elseif status.type == 0 %}
                                                        <span>{{ status.sep }}</span>

                                                    {% elseif status.type == 3 %}


                                                        {% if value[ status.key ] is defined %}

                                                            <span style="vertical-align:middle;margin-right:6px;" class="label label-mini label-{{ status.classreplacekey ? ( value[ status.classreplacekey ]|replaceonly( status.class )|default( status.defaultclass )|default( 'info' ) ) : status.class|default( 'info' ) }}">

                                                            {% set val = value[ status.key ] %}
                                                            {% if status.filter == 'rnumber' %}
                                                                {% set val = val|rnumber(true) %}
                                                            {% endif %}
                                                        
                                                            {{ status.prefix|raw }}{{ val|t(20) }}{{ status.sufix|raw }}

        												    {% if status.extrakey %}
                                                                {{ value[ status.extrakey ]|t(20) }}{{ status.extrasufix }}
                                                            {% endif %}

                                                            </span>

                                                        {% elseif status.defaultvalue %}

                                                            <span style="padding:0.2em 0.6em 0.3em; vertical-align:middle;margin-right:6px;" class="label {{status.defaultclass}}">
                                                            {{ status.defaultprefix }}{{ status.defaultvalue }}{{ status.defaultsufix }}
                                                            </span>

                                                        {% endif %}


                                                    {% endif %}

                                            {% endfor %}
                                                    </span>
                                            </div>
                                        {% endif %}
										<div class="pull-right">
											<ul class="footer-icons-group" id="{{panel.tag}}b">

                                                {% for menu in elements.menu %}

                                                    {% if menu.type == 0 %}
                                                    
                                                        <li id="pm{{ menu.id }}{{ value[ key ] }}"><a style="font-size:12px;{% if menu.color %}color:{{menu.color}}{% endif %}"{% if menu.href %} href="{{ menu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if menu.onclick %} onclick="{{ menu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ menu.icon }}" style="margin-right:2px"> </i>
                                                        
                                                            <span id="pms{{ value[ key ] }}">{% if menu.depends and not value[ menu.depends ] %}{{ menu.label }}{% else %}{{menu.dependsLabelPrefix|raw }}{{ value[ menu.dependsValueKey ] }}{{ menu.dependsLabelSufix|raw }}{% endif %}</span>
                                                        </a></li>

                                                    {% elseif menu.type == 1 %}
                                                    <li class="dropup"><a style="font-size:12px;" href="#" class="dropdown-toggle" data-toggle="dropdown">{{ menu.label }} <i class="{{ menu.icon|default('icon-arrow-up2') }}"></i></a>
    													<ul class="dropdown-menu icons-right dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                
                                                                {% if submenu.depends %}
                                                                    {% set submenudisplay = value[ submenu.depends ] %}
                                                                {% else %}
                                                                    {% set submenudisplay = 1 %}
                                                                {% endif %}
                                                            
                                                                {% set optiondisabled = ( submenu.disabled or ( submenu.disableddepends and value[ submenu.disableddepends ] ) ) %}

                                                                <li class="{{ optiondisabled ? 'disabled' }}{{ submenu.keyclass ? ' panmc' ~ submenu.id ~ value[ submenu.keyclass ] }}" id="panm{{ value[key] }}{{ loop.index0 }}" {{ submenudisplay != 1 ? 'style="display:none"' }}><a{% if submenu.color %} style="font-size:12px;color:{{submenu.color}}"{% endif %}{% if submenu.href and not optiondisabled %} href="{{ submenu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if submenu.onclick and not optiondisabled %} onclick="{{ submenu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ submenu.icon }}"></i> {{ submenu.label }}</a></li>
                                                            {% endfor %}
    													</ul>
    											    </li>

                                                    {% endif %}

                                                {% endfor %}
                                            </ul>
										</div>
									</div>
								</div>
                          </div>
    {% endfor %}

{% if allitems %}
    </div>
                                {% if more and values|length == perpage %}
                                    <input type="hidden" value="{{ offset }}" id="{{ name }}moreos">
                                    <button id="{{ name }}more" style="margin:0px 0px 0px 3px; float:right" onclick="myfwsubmit('{{ more.to }}','Loading',{os:$('#{{ name }}moreos').val()})" class="btn btn-default" type="button"><i class="icon-arrow-down11"></i> {{ more.label }}</button>
                                {% endif %}
{% endif %}