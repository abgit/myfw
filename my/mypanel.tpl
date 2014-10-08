
{% if allitems %}
    {% if elements.tmenu %}
        <div class="row" style="margin-bottom:10px;">
        <div class="col-md-12 text-right">
                                                {% for menu in elements.tmenu %}

                                                    {% if menu.type == 0 %}
                                                        <a style="margin-left:2px;margin-top:6px" class="btn {{ menu.class ? menu.class : 'btn-default'}}" {% if menu.color %} style="color:{{menu.color}}"{% endif %}{% if menu.href %} href="{{ menu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if menu.onclick %} onclick="{{ menu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ menu.icon }}"></i> {{ menu.label }}</a>

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
                                                                <li {{submenu.selected ? 'class="active"' }} id="menl{{ menu.id }}{{ loop.index0 }}"><a{% if submenu.color %} style="color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i id="meni{{ menu.id }}{{ loop.index0 }}" class="icon-checkmark" style="visibility:{{ submenu.selected ? 'visible' : 'hidden' }}"></i> {{ submenu.label }}</a></li>
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
						  <div class="col-md-6" id="{{ name }}{{ value[ key ] }}">
                                <div class="block task task-low" style="margin-bottom:10px">
									<div class="row with-padding" style="min-height:135px">

                                        {% if value[ elements.thumb.key ] %}

    										<div class="col-sm-3 " style="text-align: center;">
                                                <img style="width:100%;max-width:120px;margin-top:10px" src="{{ value[ elements.thumb.key ] }}">
                                            </div>

                                            {% set class2 = 'col-sm-6' %}
                                        {% else %}
                                            {% set class2 = 'col-sm-9' %}
                                        {% endif %}

    										<div class="{{ class2 }} task-description">
    										        {% if elements.title %}
                                            		    <span class="task-d">{{ value[ elements.title.key ]|t(40) }}</span>
    										        {% endif %}

    										        {% if elements.reference %}
                                                        <span class="text-success" style="font-size:10px">{{ value[ elements.reference.key ]|t(40) }}</span>
    										        {% endif %}

    										        {% if elements.description %}                                            		
    												    <span>{{ value[ elements.description.key ]|t(150)}}</span>
    										        {% endif %}
    										</div>
                                            
    										<div class="col-sm-3">

    											<div class="task-info" style="margin-top:10px">

                                                    {% for info in elements.info %}

                                                        {% if value[ info.key ] %}

                                                            <span>
        												    {% if info.class %}
                                                                <span class="label {{info.class}}">
                                                            {% endif %}
                                                        
                                                            {% set val = value[ info.key ] %}
                                                            {% if info.filter == 'rnumber' %}
                                                                {% set val = val|rnumber(true) %}
                                                            {% endif %}
                                                        
                                                            {{ info.prefix }} {{ val|t(20) }} {{ info.sufix }}

        												    {% if info.class %}
                                                                </span>
                                                            {% endif %}                                                        
                                                            </span>

                                                        {% elseif info.defaultvalue %}

                                                            <span>
        												    {% if info.defaultclass %}
                                                                <span class="label {{info.defaultclass}}">
                                                            {% endif %}
                                                        
                                                            {{ info.defaultprefix }} {{ info.defaultvalue }} {{ info.defaultsufix }}

        												    {% if info.defaultclass %}
                                                                </span>
                                                            {% endif %}                                                        
                                                            </span>

                                                        {% endif %}
                                                    {% endfor %}
    											</div>
    										</div>
									</div>
									<div class="panel-footer" style="min-height:38px">
                                        {% if elements.status %}
                                            <div class="pull-left">
                                                    <span>
                                            {% for status in elements.status %}
                                                    {% if status.type == 1 %}
                                                        {{ value[ status.key ]|t(90)}}
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

                                                        <i id="pani{{ value[ key ] }}{{ status.key }}" {{ statusdisplay != 1 ? 'style="display:none"' }} class="{{ iconval }}"></i>
                                                    {% elseif status.type == 0 %}
                                                        {{ status.sep }} 
                                                    {% endif %}
                                            {% endfor %}
                                                    </span>
                                            </div>
                                        {% endif %}
										<div class="pull-right">
											<ul class="footer-icons-group" id="{{panel.tag}}b">

                                                {% for menu in elements.menu %}

                                                    {% if menu.type == 0 %}
                                                        <li><a style="font-size:12px;{% if menu.color %}color:{{menu.color}}{% endif %}"{% if menu.href %} href="{{ menu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if menu.onclick %} onclick="{{ menu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ menu.icon }}" style="margin-right:2px"></i> {{ menu.label }}</a></li>

                                                    {% elseif menu.type == 1 %}
                                                    <li class="dropup"><a style="font-size:12px;" href="#" class="dropdown-toggle" data-toggle="dropdown">{{ menu.label }} <i class="{{ menu.icon|default('icon-arrow-up2') }}"></i></a>
    													<ul class="dropdown-menu icons-right dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                
                                                                {% if submenu.depends %}
                                                                    {% set submenudisplay = value[ submenu.depends ] %}
                                                                {% else %}
                                                                    {% set submenudisplay = 1 %}
                                                                {% endif %}
                                                            
                                                                <li id="panm{{ value[key] }}{{ loop.index0 }}" {{ submenudisplay != 1 ? 'style="display:none"' }}><a{% if submenu.color %} style="font-size:12px;color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href|replace({ (keyhtml): value[ key ] }) }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick|replace({ (keyhtml): value[ key ] }) }}"{% endif %}><i class="{{ submenu.icon }}"></i> {{ submenu.label }}</a></li>
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
                                    <button id="{{ name }}more" style="margin:0px 0px 0px 3px; float:right" onclick="{{ more.onclick }}" class="btn btn-default" type="button"><i class="icon-arrow-down11"></i> {{ more.label }}</button>
                                {% endif %}
{% endif %}