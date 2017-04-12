
    {% if customheader %}
        <div class="page-header">
                {% if customheader.title or customheader.subtitle %}
    				<div class="page-title">
    					<h3>{{ customheader.title }}

                            {% if customheader.subtitle %}                        
                                <small>{{ customheader.subtitle }}</small>
                            {% endif %}
                        </h3>
    				</div>
                 {% endif %}
                {{ customheader.custom ? customheader.custom|raw }}
        </div>
    {% endif %}                            
                            
                            
{% if elements %}           

    {% if video %}
        <div class="row" style="padding-bottom:30px;">
            <div class="col-md-8">
    {% endif %}


    {% if customsubheader %}
        <div class="page-header">
				<div class="page-title">
					<h3>{{ customsubheader.title }}<small>{{ customsubheader.subtitle }}</small></h3>
				</div>
        </div>
    {% endif %}


    {% if offset %}
        <div class="row"><div class="col-md-{{ 12 - offset - offset }} col-md-offset-{{ offset }}">
    {% endif %}

<div id="msg{{ name }}" class="callout callout-{{ classname|default( 'info' ) }} fade in" style="margin-bottom:0px">

    {% if tip.onclick %}
        <button type="button" class="close" data-dismiss="alert" onclick="$('#msg{{ name }}').hide();{{ tip.onclick }}">×</button>
    {% endif %}

    {% for el in elements %}

        {% if el.type == 'title' %}
            <h5 id="msg{{ name }}t">{{ el.text }}</h5>        

        {% elseif el.type == 'custom' %}
        <div class="well myfwstatsmsg">{{ el.obj|raw }}</div>

        {% elseif el.type == 'message' %}
            <p id="msg{{ name }}m">{% if el.thumb %}<img src="{{ el.thumb }}" /> &nbsp;&nbsp;{% endif %}{{ messageprefix }}{{ el.text }}{{ messagesufix }}</p>

        {% elseif el.type == 'messagetemplate' %}
            <p id="msg{{ name }}t">{{ messageprefix }}{{ include( template_from_string( el.message ) ) }}{{ messagesufix }}</p>

        {% elseif el.type == 'small' %}
            <small style="color:#999">{{ el.text }}</small>

        {% elseif el.type == 'nl' %}
            <br>

        {% elseif el.type == 'button' %}
            <a style="margin:5px 3px 0px 0px{% if el.colorbackground %};background-color:{{el.colorbackground}}{%endif%}{% if el.color %};color:{{el.color}}{%endif%}" type="button" class="btn btn-{{ el.class|default( classname ) }}" {%if el.onclick %}onclick="{{ el.onclick }}"{% endif %}><i class="{{ el.icon }}"></i> {{ el.label }}</a>

        {% elseif el.type == 'labelslist' %}
        {% if el.header %}<p class="myfwlabellisthead">{{ el.header|raw }}{% endif %}
        <div class="well myfwlabellist">
            {% for lab in el.list %}
                <p class="text-{{ lab.class }}"><span class="label label-{{ lab.class }}">{{ lab.label }}</span> {{ lab.description|raw }}</p>
            {% endfor %}
        </div>

        {% endif %}
    {% endfor %}
</div>

    {% if offset %}
        </div></div>
    {% endif %}


    {% if video %}
    </div>
        {% if video.iframe %}
        <div class="col-md-4 text-center" style="padding-top:30px;">
             <iframe src="{{video.src}}" allowtransparency="true" frameborder="0" scrolling="no" class="{{video.class}}" name="{{video.name}}" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen  style="min-height:226px;min-width:100%;width:100px;" scrolling=no></iframe>
        </div>
        {% else %}
        <div class="col-md-4 text-center" style="padding-top:30px;">
            <a href="{{ video.src }}" class="wistia-popover[height=360,playerColor=3b5998,width=640]"><img src="{{ video.thumb }}" alt="" width="100%" style="max-width:400px" /></a>
            <script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/popover-v1.js"></script>
        </div>
        {% endif %}
    </div>
    {% endif %}

{% endif %}
