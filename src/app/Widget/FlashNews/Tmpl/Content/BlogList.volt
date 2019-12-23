<div class="blog-list">
    {% for post in posts %}
        <article class="uk-section uk-section-small uk-padding-remove-top">
            <header>
                <h3 class="uk-margin-remove-adjacent uk-text-bold uk-margin-small-bottom">
                    <a class="uk-link-reset" href="{{ post.link }}">
                        {{ post.t('title') }}
                    </a>
                </h3>
                <p class="uk-article-meta">
                    {{ _('written-on', ['date': helper('Date::relative', post.createdAt)]) }}

                    {% set category = post.category %}
                    {% if category is not empty %}
                        {{ '. ' ~ _('posted-in') ~ '. ' }}
                        <a href="{{ category.link }}">
                            {{ category.t('title') ~ ' | ' }}
                        </a>
                    {% endif %}
                    {{ helper('IconSvg::render', 'eye') ~ ' ' ~ helper('Text::plural', 'hits', post.hits, ['hits' : post.hits]) ~ '.' }}
                </p>
            </header>

            {% set image = helper('Image::loadImage', post.image) %}
            {% if image is not empty %}
                <img data-src="{{ image.getResize(850) }}"
                     alt="{{ post.t('title') | escape_attr }}" uk-img/>
            {% endif %}

            {% set summary = post.summary() | trim %}
            {% if summary is not empty %}
                <div class="post-summary uk-text-meta uk-margin">
                    {{ summary }}
                </div>
            {% endif %}

            <a href="{{ post.link }}"
               class="uk-button uk-button-default uk-button-small uk-margin">
                {{ _('read-more') }}
            </a>
            <hr/>
        </article>
    {% endfor %}
    {{ pagination }}
</div>