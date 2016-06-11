
        <div class="row">
        <div class="col-md-12 text-right">
                                                {% for menu in elements %}

                                                    {% if menu.type == 0 %}
                                                        <a style="margin-left:2px;margin-top:6px" class="btn {{ menu.class ? menu.class : 'btn-default'}}" {% if menu.color %} style="color:{{menu.color}}"{% endif %}{% if menu.href %} href="{{ menu.href }}"{% endif %}{% if menu.onclick %} onclick="{{ menu.onclick }}"{% endif %}><i class="{{ menu.icon }}"></i> <span id="menulab{{ name }}{{menu.id}}">{{ menu.label }}</span></a>

                                                    {% elseif menu.type == 3 %}
                                                        {% for i in 1..menu.it %}<br />{% endfor %}

                                                    {% elseif menu.type == 1 %}
                                                    <div class="btn-group" style="margin-left:2px;margin-top:6px">
                                                    <a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown"><i class="{{ menu.icon }}"></i> {{menu.label}} <span class="caret"></span></a>
    													<ul class="dropdown-menu icons-right dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                <li><a{% if submenu.color %} style="color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick }}"{% endif %}><i class="{{ submenu.icon }}"></i> {{ submenu.label }}</a></li>
                                                            {% endfor %}
    													</ul>
    											    </div>

                                                    {% elseif menu.type == 2 %}
                                                    <div class="btn-group" style="margin-left:2px;margin-top:6px">
                                                    <a href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown"><i class="{{ menu.icon }}"></i> <span id="menbl{{ menu.id }}">{{menu.label}}</span> <span class="caret"></span></a>
    													<ul class="dropdown-menu icons-left dropdown-menu-right">
                                                            {% for submenu in menu.options %}
                                                                <li {{submenu.selected ? 'class="active"' }} id="menl{{ menu.id }}{{ loop.index0 }}"><a{% if submenu.color %} style="color:{{submenu.color}}"{% endif %}{% if submenu.href %} href="{{ submenu.href }}"{% endif %}{% if submenu.onclick %} onclick="{{ submenu.onclick }}"{% endif %}><i id="meni{{ menu.id }}{{ loop.index0 }}" class="icon-checkmark" style="visibility:{{ submenu.selected ? 'visible' : 'hidden' }}"></i> {{ submenu.label }}</a></li>
                                                            {% endfor %}
    													</ul>
    											    </div>

                                                    {% endif %}

                                                {% endfor %}
        </div></div>

