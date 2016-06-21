

{% if hidebutton %}
    <div class="visible-xs visible-sm header-element-toggle">
					<a data-target="#header-statistics" data-toggle="collapse" class="btn btn-primary btn-icon collapsed"><i class="icon-stats"></i></a>
				</div>
                <div class="header-statistics">
					<div id="header-statistics" class="collapse" style="height: 0px;">
						<div class="well">
{% endif %}                        
                        


<ul class="statistics">
{% for stat in elements %}

                    {% set value = values[ stat.key ] %}

                    {% set type = ( stat.typekey ? values[ stat.typekey ] : stat.type )|default( 'default' ) %}

                    {% if stat.classreplace %}
                        {% set type = (stat.classreplacekey ? values[ stat.classreplacekey ] : value)|replaceonly( stat.classreplace )|default( stat.classreplacedefault )|default( 'default' ) %}
                    {% endif %}

    <li{% if stat.class %} class="{{ stat.class }}"{% endif %}>
        <div class="statistics-info">
            {% if stat.icon %}<a class="bg-{{ type }}"><i class="{{stat.icon}}"></i></a>{% endif %}

            {% if stat.keytype == 1 %}
            <strong>
            
                    {% set infopre = stat.addonpre|replace({' ': '&nbsp;'}) %}
            
                    {% if infopre %}<span class="infopre">{{ infopre|raw }}</span>{% endif %}

                    {% if stat.onclick %}
                        <a onClick="{{ stat.onclick }}">
                    {% endif %}

                    {% if stat.replace %}
                        {% set value = value|replaceonly( stat.replace )|default( stat.replacedefault ) %}
                    {% endif %}

                    <span id="st{{ stat.key }}"{% if stat.islabel %} class="label label-stats label-{{ type }}"{% endif %}>{{ value|t(15) }}</span>

                    {% if stat.onclick %}
                        </a>
                    {% endif %}

                    {% set infopos = stat.addonpos|replace({' ': '&nbsp;'}) %}
                    {% if infopos %}<span class="infopos">{{ infopos|raw }}</span>{% endif %}
            </strong>
            {% endif %}
        </div>
        {% if stat.percentagetype %}
            {% set pervalue = ( stat.percentagekey ? values[ stat.percentagekey ] : stat.percentage )|default(0) %}
            <div class="progress {% if stat.percentagetype == 1 %}progress-micro{% endif %}">
                <div style="width:{{ pervalue|default(0)|round(0, 'ceil') }}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ pervalue|default(0)|round }}" role="progressbar" class="progress-bar progress-bar-{{ type }}">
                    <span class="sr-only">{{ pervalue|default(0) }}%</span>
                </div>
            </div>
        {% endif %}
        <span>{{ stat.addonlabelpre|raw }}<span id="st{{ stat.key}}lpre">{{ stat.addonlabelprekey ? values[ stat.addonlabelprekey ]|default( stat.addonlabelprekeydefault )|raw }}</span>{{ stat.label|raw }}<span id="st{{ stat.key}}lpos">{{ stat.addonlabelposkey ? values[ stat.addonlabelposkey ]|default( stat.addonlabelposkeydefault )|raw }}</span>{{ stat.addonlabelpos|raw }}</span>
    </li>
{% endfor %}
</ul>


{% if hidebutton %}                        
						</div>
					</div>
				</div>
{% endif %}        
