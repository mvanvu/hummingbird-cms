{% if paginator is defined %}
    {% set paginate = paginator.paginate() %}
    {% set count = paginate.getTotalItems() %}

{% else %}
    {% set count = 0 %}
{% endif %}

{% if (count > 0) %}
    {{ helper('Text::Plural', 'about-results-count', count, ['count': count]) }}
    {% for item in paginate.getItems() %}
        <div class="result-item uk-margin">
            <article class="uk-article">
                <div class="result-title">
                    <a class="uk-text-lead" href="{{ route(item.t('route')) }}">
                        {{ item.t('title') }}
                    </a>
                </div>
                <div class="result-summary">
                    <div class="uk-grid-small" uk-grid>
                        {% set image = helper('Image::loadImage', item.t('image')) %}
                        {% if image %}
                            <div class="uk-width-auto">
                                <img src="{{ image.getResize(85, 85) }}" alt="{{ item.title | escape_attr }}"/>
                            </div>
                        {% endif %}
                        <div class="{{ image ? 'uk-width-expand' : 'uk-width-1-1' }}">
                            <p class="uk-text-meta">
                                {{ helper('StringHelper::truncate', item.summary(), 160) }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        {{ partial('Pagination/Pagination') }}
    {% endfor %}
{% else %}
    <div class="uk-alert uk-alert-warning">
        {{ _('no-results') }}
    </div>
{% endif %}