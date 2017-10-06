                        

            <label>{{ label }}</label>

                <div class="chat" id="proposalbox" style="min-height:30px;margin-bottom:0px">

                {% if image %}
                    <img {% if image.width %}width="{{ image.width }}"{% endif %} {% if image.height %}height="{{ image.height }}"{% endif %} src="{{ values }}"/>
                {% else %}
                    {{ values }}
                {% endif %}                
                </div>
                <div class="message-controls" style="margin-top:4px">
                    <div class="pull-right">
                    {% if image %}
                        <a class="btn btn-primary" href="{{ values }}" target="_blank" download>Download image</a>
                    {% else %}
                        <button data-clipboard-text="{{ values|replace({"\n":' ', "\r":' '}) }}" class="btn btn-primary btn-xs clipb" type="button">Copy to clipboard</button>
                    {% endif %}
                    </div>
                </div>
            
