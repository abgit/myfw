
    {% if init %}

                <div class="chat" id="{{windowid}}">
                    <div id="{{id}}msgs">

    {% endif %}
    					{% for msg in values %}
                            <div class="message{{ ( init and keyme and msg[ keyme ] ) ? ' reversed' }}{{ ( init == false and selfid ) ? ' ' ~ selfid }}">
                                {% if keythumb %}
                                    <img class="message-img" width="40" height="40" src="{{ msg[ keythumb ]|default( keythumbdefault ) }}">
                                {% endif %}

                                <div class="message-body chatadmin">
                                    {% if msg[ message.key ] %}
                                        {{ msg[ message.key ]|t(15000)|nl2br }}

                                    {% elseif msg[ message.imgkey ] %}
                                        <img src="{{ cdn }}{{ msg[ message.imgkey ] }}" class="img-responsive" width="{{ msg[ message.imgwidth ] }}" height="{{ msg[ message.imgheight ] }}" />

                                    {% elseif msg[ message.moviekeythumb ] %}
                                        <video poster="{{ cdn }}{{ msg[ message.moviekeythumb ] }}" width="{{ msg[ message.moviekeywidth ] }}" height="{{ msg[ message.moviekeyheight ] }}" controls>
                                          {% if message.moviekeymp4 %}<source src="{{ cdn }}{{ msg[ message.moviekeymp4 ] }}" type="video/mp4">{% endif %}
                                          {% if message.moviekeyogg %}<source src="{{ cdn }}{{ msg[ message.moviekeyogg ] }}" type="video/ogg">{% endif %}
                                          {% if message.moviekeywebm %}<source src="{{ cdn }}{{ msg[ message.moviekeywebm ] }}" type="video/webm">{% endif %}
                                          Your browser does not support the video tag.
                                        </video>
                                    {% endif %}
                                    
                                    {% if keyowner or keydate %}
                                    <span class="attribution">
                                    
                                        {% set labels = [] %}

                                        {% for it in message.labels if ( it.label and msg[ it.labeldepends ] ) %}
                                            {% set labels = labels|merge([it.label]) %}
                                        {% endfor %}
                                        
                                        

                                        {{msg[ keyowner ]|t(40)}}{{ labels is not empty ? ' (' }}{% for label in labels %}<b>{{ label }}</b>{{ loop.revindex0 ? ', ' }}{% endfor %}{{ labels is not empty  ? ')' }}{% if keyowner and keydate %}, {% endif %}{{msg[ keydate ]|ago}}
                                    </span>
                                    {% endif %}

                                </div>
                            </div>
                        {% endfor %}

    {% if init %}

                    </div>
            <div id="{{windowid}}end"></div>
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

            {% if transloadit or message or buttons or filestack %}
                <div class="message-controls">
                    <div class="pull-right">
            {% endif %}

                        {% if transloadit %}
                        <div class="uploader" style="width:155px">
                            <input id="{{ id }}msgp" name="{{ id }}msgp" class="btn btn-default btn-loading" type="file" onchange="$('form#{{ formname }}').data('transloadit.uploader')._options['myfwmode']=2;$('form#{{ formname }}').data('transloadit.uploader')._options['myfwmodeopt']={'u':'{{ urlimage }}','w':'#{{ id }}wait'};$('form#{{ formname }}').data('transloadit.uploader')._options['exclude']='input:not([name={{ id }}msgp])';$('form#{{ formname }}').data('transloadit.uploader')._options['signature']='{{ transloadit.signature }}';$('form#{{ formname }}').data('transloadit.uploader')._options['params']=JSON.parse('{{ transloadit.params }}');"></input>
                                <span style="-moz-user-select:none" class="filename">Send image{{ message.key and message.moviekeythumb ? ' or video' }}</span>
                                <span style="-moz-user-select:none" class="action">Choose file</span>
                        </div>
                        {% endif %}

                        {% if filestack %}
                            {% if filestack.urlimage %}
                                &nbsp;<button onClick="filepicker.pickAndStore({multiple:false,{{ filestack.security }}mimetypes:['image/jpg','image/jpeg','image/png','image/bmp'],services:['COMPUTER','CONVERT'],conversions:['crop','rotate','filter']},{{ filestack.fsoptions|json_encode }},function(Blobs){ myfwsubmit('{{ filestack.urlimage }}','Processing ...',{img:Blobs[0].url},false,'info');});" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-image2"></i></button>
                            {% endif %}
                            {% if filestack.urlvideo %}
                                &nbsp;<button onClick="filepicker.pickAndStore({multiple:false,{{ filestack.security }}mimetype:'video/*',services:['COMPUTER']},{{ filestack.fsoptions|json_encode }},function(Blobs){myfwsubmit('{{ filestack.urlvideo }}','Processing ...',{mov:Blobs[0].url},false,'info');});" class="btn btn-default btn-xs btn-icon" type="button"><i class="icon-movie"></i></button>
                            {% endif %}
                        {% endif %}

                        {% if message %}
                            &nbsp;<button onClick="if($('#{{ id }}msg').val()!==''){myfwsubmit('{{ message.url }}','Sending ...',{msg:$('#{{ id }}msg').val()});$('#{{ id }}msg').val('');}" class="btn btn-primary btn-xs" type="button">{{ message.caption|default( 'Send message' ) }}</button>
                        {% endif %}

                        {% for button in buttons %}
                            <a style="margin-left:5px;padding: 6px 12px;" type="button" class="btn btn-{{ button.class|default( 'primary' ) }}" onclick="{{ button.onclick }}">{% if button.icon %}<i class="{{ button.icon }}"></i>{% endif %} {{ button.label }}</a>
                        {% endfor %}


            {% if transloadit or message or buttons %}
                    </div>
                </div>
            {% endif %}

    {% endif %}