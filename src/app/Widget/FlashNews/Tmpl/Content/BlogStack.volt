<div class="blog-stack">
    {% for post in posts %}
        <div class="uk-card uk-background-muted uk-grid-collapse uk-margin" uk-grid>
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
    {% endfor %}
</div>