<ul class="uk-list uk-list-divider">
    {% for package in packages %}
        <li>
            <div class="package-container uk-grid-small" uk-grid>
                <div class="uk-width-expand">
                    <span class="uk-text-emphasis">
                        {{ package['title'][language] is defined ? package['title'][language] : package['title']['en-GB'] }}
                    </span>
                    <small class="uk-text-truncate uk-text-italic">
                        {{ package['description'][language] is defined ? package['description'][language] : package['description']['en-GB'] }}
                    </small>
                    <div class="uk-text-meta uk-margin-small-top">
                        <div class="uk-grid-small uk-grid-divider" uk-grid>
                            <div>
                                {{ _('version-s', ['version': package['version']]) }}
                            </div>
                            <div>
                                {{ icon('user') ~ package['author'] }}
                            </div>
                            <div>
                                {{ package['authorUrl'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="uk-width-auto">
                    <a class="uk-button uk-button-text uk-button-small btn-install"
                       href="{{ package['source'] }}">
                        {% set plugin = helper('Event::getPlugin', package['group'], package['name']) %}
                        {% if plugin %}
                            {% if version_compare(package['version'], plugin.get('manifest.version'), 'gt') %}
                                <span class="uk-text-warning">
                                    {{ icon('cloud-download') ~ '&nbsp;' ~ _('update') ~ '&nbsp;' ~ _('version-s', ['version': package['version']]) }}
                                </span>
                            {% else %}
                                <span class="uk-text-success">
                                    {{ icon('cloud-check') ~ '&nbsp;' ~ _('reinstall') }}
                                </span>
                            {% endif %}
                        {% else %}
                            <span class="uk-text-primary">
                            {{ icon('cloud-download') ~ '&nbsp;' ~ _('install') }}
                        </span>
                        {% endif %}
                    </a>
                </div>
            </div>
        </li>
    {% endfor %}
</ul>