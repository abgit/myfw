
    {% if init %}

                <div class="chat" id="{{id}}box">
                    <div id="{{id}}msgs">

    {% endif %}
    					{% for msg in values %}
                            <div class="message{{ ( keyme and msg[ keyme ] ) ? ' reversed' }}">
                                {% if keythumb %}
                                    <img class="message-img" width="40" height="40"  alt="" src="{{ cdn }}{{ msg[ keythumb ] }}">
                                {% endif %}

                                <div class="message-body">
                                    {% if msg[ message.imgkey ] %}
                                        <img src="{{ cdn }}{{ msg[ message.imgkey ] }}" width="{{ msg[ message.imgwidth]|default(300) }}" height="{{ msg[ message.imgheight]|default(200) }}" />
                                    {% else %}
                                        {{ msg[ message.key ]|t(2000) }}
                                    {% endif %}
                                    
                                    {% if keyowner or keydate %}
                                    <span class="attribution">
                                        {{msg[ keyowner ]|t(40)}}{% if keyowner and keydate %}, {% endif %}{{msg[ keydate ]|ago}}
                                    </span>
                                    {% endif %}

                                </div>
                            </div>
                        {% endfor %}

    {% if init %}

                    </div>
                    <div class="message {{ wait.me ? 'reversed' }}" id="{{id}}wait" style="display:none">
                        <img class="message-img" width="{{ wait.size }}" height="{{ wait.size }}" alt="" src="{{ wait.thumb }}">
                        <div class="message-body">
                            <span class="typing"></span>
                        </div>
                    </div>
                </div>

            {% if message %}
                <textarea id="{{ id }}msg" placeholder="{{ message.textarea|default( 'Enter your message...' ) }}" cols="1" rows="3" class="form-control"></textarea>
            {% endif %}

            {% if transloadit or message or buttons %}
                <div class="message-controls">
                    <div class="pull-right">
            {% endif %}

                        {% if transloadit %}
                        <div class="uploader" style="width:150px">
                            <input id="{{ id }}msgp" name="{{ id }}msgp" class="btn btn-default btn-loading" type="file" onchange="$('form#{{ formname }}').data('transloadit.uploader')._options['myfwmode']=2;$('form#{{ formname }}').data('transloadit.uploader')._options['myfwmodeopt']={'u':'{{ urlimage }}','w':'#{{ id }}wait'};$('form#{{ formname }}').data('transloadit.uploader')._options['exclude']='input:not([name={{ id }}msgp])';$('form#{{ formname }}').data('transloadit.uploader')._options['signature']='{{ transloadit.signature }}';$('form#{{ formname }}').data('transloadit.uploader')._options['params']=JSON.parse('{{ transloadit.params }}');"></input>
                                <span style="-moz-user-select:none" class="filename">Submit a photo</span>
                                <span style="-moz-user-select:none" class="action">Choose File</span>
                        </div>
                        {% endif %}

                        {% if message %}
                            <button onClick="if($('#{{ id }}msg').val()!==''){myfwsubmit('{{ message.url }}','Sending ...',{msg:$('#{{ id }}msg').val()});$('#{{ id }}msg').val('');}" class="btn btn-primary btn-loading" type="button" style="margin-left:5px; padding:6px 12px">{{ message.caption|default( 'Submit message' ) }}</button>
                        {% endif %}

                        {% for button in buttons %}
                            <a style="margin-left:5px;padding: 6px 12px;" type="button" class="btn btn-{{ button.class|default( 'primary' ) }}" onclick="{{ button.onclick }}">{% if button.icon %}<i class="{{ button.icon }}"></i>{% endif %} {{ button.label }}</a>
                        {% endfor %}

            {% if transloadit or message or buttons %}
                    </div>
                </div>
            {% endif %}

    {% endif %}