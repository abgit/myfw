
    {% if init %}

                <div class="chat" id="{{id}}box">
                    <div id="{{id}}msgs">

    {% endif %}
    					{% for msg in elements %}
                            <div class="message {{ msg.me ? 'reversed' }}">
                                <img class="message-img" width="40" height="40"  alt="" src="{{ cdn }}{{ msg.thumb }}">
                                <div class="message-body">
                                    {% if msg.image %}
                                        <img src="{{ cdn }}{{ msg.image }}" width="{{ image.w|default(300) }}" height="{{ image.h|default(200) }}" />
                                    {% else %}
                                        {{msg.content|t(2000)}}
                                    {% endif %}
                                    <span class="attribution">{{msg.owner|t(40)}}, {{msg.when|ago}}</span>
                                </div>
                            </div>
                        {% endfor %}

    {% if init %}

                    </div>
                    <div class="message {{ wait.me ? 'reversed' }}" id="{{id}}wait" style="visibility:hidden">
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
                            <button onClick="$('#{{id}}wait').css('visibility','visible');myfwsubmit('{{ message.url }}','',{msg:$('#{{ id }}msg').val()})" class="btn btn-primary btn-loading" type="button" style="margin-left:5px; padding:6px 12px">{{ message.caption|default( 'Submit message' ) }}</button>
                        {% endif %}

                        {% for button in buttons %}
                            <a style="margin-left:5px;padding: 6px 12px;" type="button" class="btn btn-{{ button.class|default( 'primary' ) }}" onclick="{{ button.onclick }}">{% if button.icon %}<i class="{{ button.icon }}"></i>{% endif %} {{ button.label }}</a>
                        {% endfor %}

            {% if transloadit or message or buttons %}
                    </div>
                </div>
            {% endif %}

    {% endif %}