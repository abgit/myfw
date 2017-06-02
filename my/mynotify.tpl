{% if allitems %}
 <li class="dropdown">
    <a data-toggle="dropdown" class="dropdown-toggle"{% if onshow %} onClick="if($('#nfcounter').text().trim()){setTimeout(function(){ {{ onshow }} },10);}"{% endif %}>
        <i class="{{ icon }}">
        </i>
        <span class="label label-danger" style="background-color:rgba(255,0,0,1.00)" id="{{ name }}counter">{{ counter ? counter }}</span>
    </a>
    <div class="popup dropdown-menu dropdown-menu-right">

        {% if title %}
        <div class="popup-header">

            {% if buttonleft %}
            <a class="pull-left"{% if buttonleft.onclick %} onClick="{{buttonleft.onclick}}"{% endif %}{% if buttonleft.href %} href="{{buttonleft.href}}"{% endif %}>
                <i class="{{buttonleft.icon}}"></i>
            </a>
            {% endif %}

            <span style="{{ buttonleft == false ? 'margin-left:42px;' }}{{ buttonright == false ? 'margin-right:42px;' }}"  >{{ title }}</span>

            {% if buttonright %}
            <a class="pull-right"{% if buttonright.onclick %} onClick="{{buttonright.onclick}}"{% endif %}{% if buttonright.href %} href="{{buttonright.href}}"{% endif %}>
                <i class="{{buttonright.icon}}"></i>
            </a>
            {% endif %}
        </div>
        {% endif %}

        <ul class="popup-messages" id="{{name}}msgs">
{% endif %}
            {% if values %}
                {% for val in values %}
                <li class="{{ ( unreadkey and val[ unreadkey ] ) ? 'unread' }}">
                
                    <a {% if itemaction.onclick or itemaction.onclickdefault %}onclick="{{ itemaction.onclick|link( val ) }}"{% endif %}>
                        {% if itemthumb.key and val[ itemthumb.key ] %}
                            <img class="user-face notifthumb{{ val[ itemthumb.classkey ] }}" alt="" src="{{ val[ itemthumb.key ] }}">
                        {% endif %}

                        {% set title = val[ itemtitle.key ] %}
                        {% if itemtitle.replace %}
                            {% set title = val[ itemtitle.key ]|replace( itemtitle.replace ) %}
                        {% endif %}

                        <strong style="padding-right:0px;">{{title|t(30) }} {% if itemlabel and val[ itemlabel.key ] %}<span style="float:right"><span class="label label-info">{{ itemlabel.prefix ? itemlabel.prefix|raw }}{{ val[ itemlabel.key ]|t(8) }}{{ itemlabel.sufix ? itemlabel.sufix|raw }}</span></span>{% endif %}</strong><span>{{val[ itemdescription.key ]|t(35) }}</span>
                    </a>
                </li>
                {% endfor %}

                {% if itemmore %}
                    <li><a style="text-align:center" {% if itemmore.onclick %} onClick="{{itemmore.onclick}}"{% endif %}{% if itemmore.href %} href="{{itemmore.href}}"{% endif %}><strong>{{ itemmore.label }}</strong></a></li>
                {% endif %}
            {% else %}
                <li><a style="text-align:center"><span>{{ emptymsg }}</span></a></li>
            {% endif %}

{% if allitems %}
        </ul>
    </div>
  </li>
{% endif %}