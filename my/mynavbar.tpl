


	<div class="navbar navbar-inverse" role="navigation" style="background-color:#3B5998">
		<div class="container">

            {% if header.logo or header.toogle %}
			<div class="navbar-header">
				
                <a class="navbar-brand" style="padding-left:14px;padding-top:12px"{% if header.href %} href="{{ header.href }}"{% endif %}><img src="{{ cdn }}{{ header.logo }}" alt=""></a>

                {% if header.toogle %}
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-icons">
					<span class="sr-only">Toggle navbar</span>
					<i class="icon-grid3"></i>
				</button>
                {% endif %}
			</div>
            {% endif %}

			<ul class="nav navbar-nav navbar-right collapse in" id="navbar-icons">
				
                {% for el in elements %}
                    
                    {% if el.type == 'item' %}
        				<li class="{{ el.class }}{{ el.active ? ' navbaractive' }}" style="vertical-align:top;{{ el.style }}" id="navbar{{ el.id }}">
	        				<a {{ el.urlobj|urlobj }}>{{ el.text|t(50) }}</a>
		        		</li>

                    {% elseif el.type == 'custom' %}
                        {{ el.obj|raw }}                    

                    {% elseif el.type == 'menu' %}
    				<li class="user dropdown{{ el.active ? ' navbaractive' }}" id="navbar{{ el.id }}">
	    				<a class="dropdown-toggle" data-toggle="dropdown">
		    				<span id="{{ el.htmlid }}">{{ el.label }}</span>
			    			<i class="caret"></i>
                          
                            {% if el.thumb %}<img id="{{ el.thumbid }}" width="40" height="40" src="{{ el.thumb }}" style="margin-right:4px;margin-top:1px">{% endif %}

				    	</a>
    					<ul class="dropdown-menu dropdown-menu-right icons-right">
                            {% for opt in el.options %}
	    					    <li id="navbari{{ opt.id }}" class="{{ opt.active ? 'active' }}"><a {% if opt.onclick %}onClick="{{ opt.onclick }}"{% endif %}{% if opt.href %}href="{{ opt.href }}"{% endif %}><i class="{{ opt.icon }}"></i> {{ opt.label }}</a></li>
			    		    {% endfor %}
                        </ul>
				    </li>


                    {% endif %}
                {% endfor %}
			</ul>
            
            {% if text %}
            <div id="navbar-text" class="nav navbar-nav navbar-right collapse in hidden-sm hidden-xs">
			  <p class="navbar-text {{ text.thumbclass }}">
                {% if text.icon %}<i class="{{ text.icon }}"></i>{% endif %}{% if text.thumb %}<img id="navbarimg" width="40" height="40" src="{{ text.thumb }}" style="margin-right:4px">{% endif %}<span id="navbartxt">{{ text.message|t(60) }}</span>
              </p>
            </div>
            {% endif %}

		</div>
	</div>
