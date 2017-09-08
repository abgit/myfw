                        
    {% if elements %}
    <div class="breadcrumb-line">
	    <ul class="breadcrumb">
        {% for element in elements %}
             <li {% if not element.href %}class="active"{% endif %}>{% if element.href %}<a href="{{ element.href }}">{% endif %}{{ element.label }}{% if element.href %}</a>{% endif %}</li>
        {% endfor %}
        </ul>
    </div>
    {% endif %}
