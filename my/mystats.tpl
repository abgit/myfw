<ul class="statistics">
{% for stat in elements %}
    <li>
        <div class="statistics-info">
            <a class="bg-{{stat.type}}"><i class="{{stat.icon}}"></i></a>
            <strong>{{ stat.addonpre }}<span id="st{{ stat.key }}">{{values[ stat.key ]|t(10)}}</span>{{ stat.addonpos }}</strong>
        </div>
        <div class="progress progress-micro">
            <div style="width:{{ stat.percentage }}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{stat.percentage}}" role="progressbar" class="progress-bar progress-bar-{{stat.type}}">
                <span class="sr-only">{{stat.percentage}}%</span>
            </div>
        </div>
        <span>{{stat.label}}</span>
    </li>
{% endfor %}
</ul>