<div class="uk-container">
    <article class="uk-article">
        <div class="uk-text-center">
            {{ partial('Breadcrumb/Breadcrumb') }}
        </div>
        <h1 class="uk-article-title">
            {{ post.t('title') }}
        </h1>
        <p class="uk-article-meta">
            {{ _('written-on', ['date': helper('Date::relative', post.createdAt)]) ~ '. ' ~ _('posted-in') ~ '. ' }}
            <a href="{{ post.category.link }}">
                {{ post.category.t('title') }}
            </a>
            {{ ' | ' ~ helper('IconSvg::render', 'eye') ~ ' ' ~ helper('Text::plural', 'hits', post.hits, ['hits' : post.hits]) ~ '.' }}
        </p>

        {% set summary = post.t('summary') | trim %}
        {% if summary is not empty %}
            <div class="post-summary uk-text-lead uk-margin">
                {{ summary | escape }}
            </div>
        {% endif %}

        {% set images = helper('Image::loadImage', post.t('image'), false) %}
        {% if images | length > 0 %}
            {% if images | length > 1 %}
                <div class="post-images">
                    <div class="uk-position-relative" uk-slideshow>
                        <ul class="uk-slideshow-items" uk-lightbox>
                            {% set thumbNav = '' %}
                            {% for i, image in images %}
                                <li>
                                    <a href="{{ image.getUri() }}">
                                        <img data-src="{{ image.getUri() }}" alt="{{ post.t('title') | escape_attr }}"
                                             uk-img uk-cover/>
                                    </a>
                                </li>
                                {% set thumbNav = thumbNav ~ '<li uk-slideshow-item="' ~ i ~ '"><a href="#"><img src="' ~ image.getResize(100) ~ '" width="100" alt=""></a></li>' %}
                            {% endfor %}
                        </ul>
                        <div class="uk-position-bottom-center uk-position-small">
                            <ul class="uk-thumbnav">
                                {{ thumbNav }}
                            </ul>
                        </div>
                    </div>
                </div>
            {% else %}
                <div class="post-image" uk-lightbox>
                    <a href="{{ images[0].getUri() }}">
                        <img data-src="{{ images[0].getUri() }}" alt="{{ post.t('title') | escape_attr }}" uk-img/>
                    </a>
                </div>
            {% endif %}
        {% endif %}

        {# Description (fields) for this post #}
        {{ partial('UcmItem/Description') }}

    </article>

    {# Comments for this post #}
    {{ partial('Comment/Comment') }}
</div>