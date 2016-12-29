
<div class="row">

{% if profile %}
    <div class="col-lg-{{ meta.size|default( 4 ) }}">

							<div class="thumbnail">
                                {% if values[ profile.thumb.key ] or profile.thumb.default %}
						    	    <div class="thumb">
                                        {% if profile.thumb.onclick %}
                                            <a onClick="{{ profile.thumb.onclick }}">
                                        {% endif %}
                                    
    									<img width="{{ profile.thumb.size }}" height="{{ profile.thumb.size }}" src="{{ profile.thumb.cdn }}{{ values[ profile.thumb.key ]|default( profile.thumb.default ) }}">

                                        {% if profile.thumb.onclick %}
                                        </a>
                                        {% endif %}

                                    </div>
							    {% endif %}						    

						    	<div class="caption text-center">
						    		{% if profile.head.key %}<h6>{{ values[ profile.head.key ] }}</h6>{% endif %}

						    		<h6>{{ profile.desc.keytitle ? values[ profile.desc.keytitle ] }}<small>
                                    
                                    {% if values[ profile.descimg.key ] %}
                                        <img width="{{ profile.descimg.width|default(30) }}" height="{{ profile.descimg.height|default(30) }}" style="display:inline;max-width:{{ profile.descimg.width|default(30) }}px;max-height:{{ profile.descimg.height|default(30) }}px;" src="{{ profile.descimg.cdn }}{{ values[ profile.descimg.key ] }}{{ profile.descimg.sufix }}">&nbsp;
                                    {% endif %}
                                    
                                    {{ values[ profile.desc.keysubtitle ] }}</small></h6>

                                    {% if profile.text %}
                                        <h6><small>{{ profile.text.prefix|raw }}<span id="profiletk{{ profile.text.key }}">{{ values[ profile.text.key ]|default( profile.text.default ) }}</span>{{ ( profile.text.sufixsingular and values[ profile.text.key ] == 1 ) ? profile.text.sufixsingular : profile.text.sufix|raw }}</small></h6>
                                    {% endif %}

                                    {% if profile.icons %}
    					    			<div class="icons-group">
                                            {% for icon in profile.icons if ( icon.depends == false or values[ icon.depends ] )%}
        				                    	<a class="{{ icon.class|default('tip') }}" title="{{ icon.title }}"{% if icon.hrefkey and values[ icon.hrefkey ] %} href="{{ values[ icon.hrefkey ] }}"{% elseif icon.href %} href="{{ icon.href }}"{% endif %} data-original-title="{{ icon.prefix }}{{ values[ icon.key ]}}{{ icon.sufix }}" target="_blank"><i class="{{ icon.icon }}"></i></a>
    			                    	    {% endfor %}
                                        </div>
                                    {% endif %}
						    	</div>
					    	</div>
    </div>
    <div class="col-lg-{{ 12 - meta.size|default( 4 ) }}">
{% else %}
    <div class="col-lg-12">
{% endif %}

<div class="">

{% for el in elements %}

    {% set val = values[ el.key ] %}

    {% if el.type == 'h4' %}
        <h4>{{ val }}</h4>

    {% elseif el.type == 'h5' %}
        <h5>{{ val }}</h5>

    {% elseif el.type == 'text' %}
        <p>{{ val|trim|nl2br }}</p>

    {% elseif el.type == 'textimage' %}
        {% if values[ el.keyi ] %}
            <img {% if el.imagewidth %}width="{{el.imagewidth}}"{% endif %} align="left" style="max-width:40%;margin:0px 10px 10px 0px" src="{{ values[ el.keyi ] }}">
        {% endif %}
        {% if el.keyt %}
            <h6>{{ values[ el.keyt ] }}</h6>
        {% endif %}
        {% if val|trim is empty %}
            <i>{{ el.defaulttext|raw }}</i>
        {% else %}
            {{ val|nl2br }}
        {% endif %}

    {% elseif el.type == 'custom' %}
        {% if el.title %}<h6>{{ el.title }}</h6>{% endif %}{{el.obj|raw}}

    {% elseif el.type == 'sl' %}
        <hr>

    {% elseif el.type == 'se' %}
        <br />

    {% elseif el.type == 'ti' %}
        <h6 class="heading-hr"><i class="{{ el.icon }}"></i> {{ el.label }}</h6>

    {% elseif el.type == 'cameratag' %}
        {% if val %}
        <div>
            <br />
            <video width="320" height="240" controls poster="{% if el.appcdn %}{{ el.appcdn }}{{ val }}_qvga.jpg{% else %}//cameratag.com/downloads/{{ val }}/qvga/thumb.png{% endif %}">
                <source src="{% if el.appcdn %}{{ el.appcdn }}{{ val }}_qvga.mp4{% else %}//cameratag.com/downloads/{{ val }}/qvga/mp4.mp4" type="video/mp4{% endif %}">
            </video>
        </div>
        {% endif %}

    {% elseif el.type == 'filestackmovie' %}

        {% if val %}
            {% set mp4 = ( val|filestackmovie( 'mp4' ) ) %}
            {% if mp4 %}        
                <div>
                    <br />
                    <video width="{{ val|filestackmovie( 'width' ) }}" height="{{ val|filestackmovie( 'height' ) }}" controls poster="{{ val|filestackmovie( 'poster' ) }}">
                        <source src="{{ mp4 }}" type="video/mp4">
                    </video>
                </div>
            {% endif %}
        {% endif %}


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