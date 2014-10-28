


	<div class="navbar navbar-inverse" role="navigation" style="background-color:#3B5998">
		<div class="container">

            {% if header.logo or header.toogle %}
			<div class="navbar-header">
				
                <a class="navbar-brand" style="padding-left:14px"{% if header.href %} href="{{ header.href }}"{% endif %}><img src="{{ header.logo }}" alt=""></a>

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
        				<li class="{{ el.class }}" style="vertical-align:top;">
	        				<a href="{{ el.href }}">{{ el.text }}</a>
		        		</li>

                    {% elseif el.type == 'custom' %}
                        {{ el.obj|raw }}                    

                    {% elseif el.type == 'menu' %}
    				<li class="user dropdown">
	    				<a class="dropdown-toggle" data-toggle="dropdown">
		    				<span>{{ el.label }}</span>
			    			<i class="caret"></i>
				    	</a>
    					<ul class="dropdown-menu dropdown-menu-right icons-right">
                            {% for opt in el.options %}
	    					    <li><a {% if opt.onclick %}onClick="{{ opt.onclick }}"{% endif %}{% if opt.href %}href="{{ opt.href }}"{% endif %}><i class="{{ opt.icon }}"></i> {{ opt.label }}</a></li>
			    		    {% endfor %}
                        </ul>
				    </li>


                    {% endif %}
                {% endfor %}
			</ul>
            
            {% if text %}
            <div id="navbar-text" class="nav navbar-nav navbar-right collapse">
			  <p class="navbar-text">
                <i class="{{ text.icon }}"></i> {{ text.message|t(60) }}
              </p>
            </div>
            {% endif %}

		</div>
	</div>