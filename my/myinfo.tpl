
<div class="row">
<div class="col-lg-12">
<div class="">

{% for el in elements %}

    {% set val = values[ el.key ] %}

    {% if el.type == 'h4' %}
        <h4>{{ val }}</h4>

    {% elseif el.type == 'h5' %}
        <h5>{{ val }}</h5>

    {% elseif el.type == 'text' %}
        <p>{{ val }}</p>

    {% elseif el.type == 'textimage' %}
        {% if values[ el.keyi ] %}
            <img {% if el.imagewidth %}width="{{el.imagewidth}}"{% endif %} align="left" style="max-width:40%;margin:0px 10px 10px 0px" src="{{ values[ el.keyi ] }}">
        {% endif %}
        {% if el.keyt %}
            <h6>{{ values[ el.keyt ] }}</h6>
        {% endif %}
    {{ val }}


    {% elseif el.type == 'sep' %}
        <hr>

    {% elseif el.type == 'list' %}
        <div class="row block-inner">
            <div class="col-sm-12">
                <div class="well">
                    <dl>
                        {% set isempty = true %}
                        {% for opt in el.options if values[ opt.key ] is not empty %}
                            <dt class="{{ opt.class|default('text-info') }}">{{ opt.label }}</dt>
                            <dd>
                            {% if opt.replace %}
                                {% for optvals in values[ opt.key ]|split(';') if optvals is not empty %}
                                        {% if not loop.first %}, {% endif %}
                                        {{ optvals|replace( opt.replace ) }}
                                {% endfor %}
                            {% else %}
                                {{ values[ opt.key ] }}
                            {% endif %}
                            </dd>
                            {% set isempty = false %}
                        {% endfor %}

                        {% if isempty %}
                            {{ emptymsg }}
                        {% endif %}
                        </dl>
                </div>
            </div>
        </div>

    {% endif %}

{% endfor %}
</div></div></div>