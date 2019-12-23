<div class="slider-news">
    <div uk-slider="autoplay: true">
        <div class="uk-slider-container uk-position-relative uk-visible-toggle">
            <ul class="uk-slider-items uk-grid uk-grid-small uk-child-width-1-4@m uk-child-width-1-2@s">
                {% for post in posts %}
                    <li>
                        <div class="uk-card uk-background-muted uk-grid-collapse"
                             uk-grid>
                            {% set image = helper('Image::loadImage', post.t('image')) %}
                            {% if image is not empty %}
                                <div class="uk-card-media-left uk-cover-container uk-width-1-3">
                                    <img data-src="{{ image.getResize(300, 120) }}"
                                         alt="{{ post.t('title') | escape_attr }}" uk-cover uk-img/>
                                    <canvas width="300" height="120"></canvas>
                                </div>
                            {% endif %}

                            <div class="uk-width-2-3">
                                <div class="uk-padding-small">
                                    <h4 class="uk-h5 uk-margin-remove uk-text-truncate">
                                        <a class="uk-link-reset" href="{{ post.link }}"
                                           title="{{ post.t('title') | escape_attr }}">
                                            {{ post.t('title') }}
                                        </a>
                                    </h4>
                                    <small class="uk-text-meta">
                                        {{ helper('Date::relative', post.createdAt) }}
                                    </small>
                                    <p class="uk-margin-remove uk-text-meta uk-text-truncate">
                                        {{ post.summary() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>
                {% endfor %}
            </ul>
            <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#"
               uk-slidenav-previous
               uk-slider-item="previous"></a>
            <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#"
               uk-slidenav-next
               uk-slider-item="next"></a>
        </div>
    </div>
</div>
