

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
    <li{% if stat.class %} class="{{ stat.class }}"{% endif %}>
        <div class="statistics-info">
            {% if stat.icon %}<a class="bg-{{stat.type}}"><i class="{{stat.icon}}"></i></a>{% endif %}
            <strong>{{ stat.addonpre|replace({' ': '&nbsp;'})|raw }}

                    {% if stat.onclick %}
                        <a onClick="{{ stat.onclick }}">
                    {% endif %}
                    
                    <span id="st{{ stat.key }}"{% if stat.islabel %} class="label label-stats label-{{ stat.type }}"{% endif %}>{{values[ stat.key ]|t(15)}}</span>

                    {% if stat.onclick %}
                        </a>
                    {% endif %}

                    {{ stat.addonpos|replace({' ': '&nbsp;'})|raw }}
            </strong>
        </div>
        <div class="progress progress-micro">
            <div style="width:{{ stat.percentage|default(100) }}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ stat.percentage }}" role="progressbar" class="progress-bar progress-bar-{{ stat.type }}">
                <span class="sr-only">{{ stat.percentage|default(100) }}%</span>
            </div>
        </div>
        <span>{{ stat.label|raw }}</span>
    </li>
{% endfor %}
</ul>


{% if hidebutton %}                        
						</div>
					</div>
				</div>
{% endif %}        
