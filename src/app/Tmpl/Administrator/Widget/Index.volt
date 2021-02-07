<div id="widget-container">
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
                                            data-widget-name="{{ widgetName }}"
                                            data-widget-title="{{ _(widgetConfig.get('manifest.title', widgetName)) | escape_attr }}">
                                        <span uk-icon="icon: plus"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                {% endfor %}
            </div>
            <div class="uk-margin">
                <form action="{{ uri.routeTo('toggle-template') }}" method="post">
                    <div class="uk-h5 uk-heading-bullet">{{ _('template-select') }}</div>
                    {{ templates }}
                    {{ csrfInput() }}
                </form>
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
                                <div class="widget-position" data-position="{{ position }}"
                                     uk-sortable="group: template-position" uk-margin>
                                    {% if widgetItems[position] is defined %}
                                        {% for widget in widgetItems[position] %}
                                            <div class="widget-item"
                                                 data-name="{{ widget['manifest.name'] | escape_attr }}"
                                                 data-title="{{ widget.title | escape_attr }}"
                                                 data-position="{{ position | escape_attr }}"
                                                 data-id="{{ widget.id }}">
                                                <div class="uk-padding-small uk-background-muted">
                                                    <div class="uk-grid-small" uk-grid>
                                                        <div class="uk-width-expand">
                                                            <span class="uk-text-uppercase widget-title">{{ widget.get('title') }}</span>
                                                            <span class="uk-text-meta uk-margin-small-left widget-type">{{ widget['manifest.name'] }}</span>
                                                        </div>
                                                        <div class="uk-width-auto uk-sortable-nodrag">
                                                            <a class="uk-text-primary uk-button uk-button-link widget-edit uk-margin-small-right">
                                                                {{ _('edit') }}
                                                            </a>
                                                            <a class="uk-text-danger uk-button uk-button-link widget-delete">
                                                                {{ _('delete') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    <div class="uk-modal-container" id="widget-modal-container" uk-modal>
        <div class="uk-modal-dialog">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <div class="uk-modal-header uk-background-muted">
                <h2 class="uk-modal-title"></h2>
            </div>
            <div class="uk-modal-body" uk-overflow-auto></div>
            <div class="uk-modal-footer uk-background-muted uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close" type="button">
                    {{ _('close') }}
                </button>
                <button class="uk-button uk-button-primary widget-save" type="button">
                    {{ _('save') }}
                </button>
            </div>
        </div>
    </div>
</div>
