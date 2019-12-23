<div id="widget-container" data-ajax="{{ ajaxData | json_encode | escape_attr }}">
    <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
        <div class="widgets-list uk-width-1-3@m">
            <div uk-margin>
                {% for widgetClass, widgetConfig in widgets %}
                    {% set widgetName = widgetConfig.get('manifest.name') %}
                    <div class="uk-card uk-card-small uk-background-muted toggle-parent">
                        <div class="uk-card-header widget-toggle">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    <div class="uk-card-title">
                                        {{ _(widgetConfig.get('manifest.title', widgetName)) }}
                                    </div>
                                </div>
                                <div class="uk-width-auto">
                                    <span uk-icon="icon: chevron-down"></span>
                                </div>
                            </div>
                        </div>
                        <div class="uk-card-body uk-background-default uk-box-shadow-small uk-grid-item-match toggle-body"
                             hidden>
                            {% if widgetConfig.has('manifest.description') %}
                            <div class="uk-text-meta">
                                {{ _(widgetConfig.get('manifest.description')) }}
                            </div>
                            {% endif %}
                            <div class="uk-grid-small uk-flex-middle uk-margin action-box" uk-grid>
                                <div class="uk-width-expand">
                                    {{ positionsSelect }}
                                </div>
                                <div class="uk-width-auto">
                                    <button class="uk-button uk-button-small uk-button-primary add" type="button"
                                            data-widget="{{ widgetName }}">
                                        <span uk-icon="icon: plus"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                {% endfor %}
            </div>
        </div>
        <div class="positions-list uk-width-2-3@m">
            <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
                {% for position in positions %}
                    <div>
                        <div class="uk-card uk-card-small uk-background-muted widget-card">
                            <div class="uk-card-header">
                                <div class="uk-card-title">
                                    {{ _('widget-position', ['position': position]) }}
                                </div>
                            </div>
                            <div class="uk-card-body uk-background-default uk-box-shadow-small">
                                <div data-position="{{ position }}"
                                     uk-sortable="group: template-position" uk-margin>
                                    {% if widgetItems[position] is defined %}
                                        {% for widgetConfig in widgetItems[position] %}
                                            {{ partial('Widget/Item', ['widgetConfig': widgetConfig]) }}
                                        {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                        </div>
                    </div>
                {% endfor %}
            </div>
        </div>
    </div>
</div>
