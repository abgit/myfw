
    {% if customheader %}
        <div class="page-header">
				<div class="page-title">
					<h3>{{ customheader.title }}<small>{{ customheader.subtitle }}</small></h3>
				</div>
                {{ customheader.custom|raw }}
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

<div{% if id %} id="{{ id }}"{% endif %} class="callout {{ classname|default( 'callout-info' ) }} fade in" style="margin-bottom:0px">

    {% if closebutton %}
        <button type="button" class="close" data-dismiss="alert" onclick="{{ closebutton.onclick }}">×</button>
    {% endif %}


    {% for el in elements %}

        {% if el.type == 'title' %}
            <h5 id="{{ id }}t">{{ el.text }}</h5>        
        
        {% elseif el.type == 'message' %}
            <p id="{{ id }}m">{{ el.text }}</p>

        {% elseif el.type == 'small' %}
            <br><small style="color:#999">{{ el.text }}</small>

        {% elseif el.type == 'button' %}
            <a style="margin:5px 3px 0px 0px{% if el.colorbackground %};background-color:{{el.colorbackground}}{%endif%}{% if el.color %};color:{{el.color}}{%endif%}" type="button" class="btn {{el.class}}" {%if el.onclick %}onclick="{{ el.onclick }}"{% endif %}><i class="{{ el.icon }}"></i> {{ el.label }}</a>            

        {% endif %}

    {% endfor %}
</div>

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
