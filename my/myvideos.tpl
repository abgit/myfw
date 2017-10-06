                        

    {% if values %}
                    <div class="tab-pane fade active in" id="videos">
						<div class="row">
                            {% for value in values %}
							<div class="col-lg-4 col-md-6 col-sm-6">
								<div class="block">
								    <div class="thumbnail thumbnail-boxed">
								    	<div class="thumb videothumb">
                                            {{ value[ elements.embed.keyembed ]|raw }}
                                        </div>
								    	<div class="caption videocaption">
                                            {% if value[ elements.title.keyhref ] and value[ elements.title.keytitle ] %}
    								        <a href="{{ value[ elements.title.keyhref ]|raw }}" class="caption-title">{{ value[ elements.title.keytitle ]|t(25) }}</a>
                                            {% endif %}

                                            <div class="videodesc">{{ value[ elements.description.key ]|t(90) }}</div>

                                            {% if elements.info %}
                                                <p class="text-muted videoinfo">
                                                {% set array = [] %}

                                                {% for info in elements.info if value[ info.key ] %}
                                                    {% set array = array|merge([ value[ info.key ]|t(10) ~ info.sufix|t(10) ]) %}
                                                {% endfor %}

                                                {% for info in array %}
                                                    {{ info }}{% if not loop.last %}, {% endif %}
                                                {% endfor %}

                                                </p>
                                            {% endif %}
								    	</div>
								    </div>
								</div>
							</div>
                            {% endfor %}

						</div>
                    </div>
    {% endif %}