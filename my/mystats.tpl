

{% if hidebutton %}
    <div class="visible-xs header-element-toggle">
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
            <a class="bg-{{stat.type}}"><i class="{{stat.icon}}"></i></a>
            <strong>{{ stat.addonpre }}<span id="st{{ stat.key }}">{{values[ stat.key ]|t(10)}}</span>{{ stat.addonpos }}</strong>
        </div>
        <div class="progress progress-micro">
            <div style="width:{{ stat.percentage|default(100) }}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ stat.percentage }}" role="progressbar" class="progress-bar progress-bar-{{ stat.type }}">
                <span class="sr-only">{{ stat.percentage|default(100) }}%</span>
            </div>
        </div>
        <span>{{ stat.label }}</span>
    </li>
{% endfor %}
</ul>


{% if hidebutton %}                        
						</div>
					</div>
				</div>
{% endif %}        
