<form id="admin-plugin-form" action="{{ helper('Uri::getActive') }}" method="post">
    <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider uk-table-responsive">
        <thead>
        <tr>
            <th class="uk-text-nowrap" style="width: 75px">
                {{ _('state') }}
            </th>
            <th class="uk-width-small uk-text-nowrap">
                {{ _('name') }}
            </th>
            <th class="uk-table-expand">
                {{ _('description') }}
            </th>
            <th class="uk-width-small uk-text-nowrap">
                {{ _('author') }}
            </th>
        </tr>
        </thead>
        <tbody class="uk-text-small">
        {% for group, plugin in plugins %}
            {% for className, cfg in plugin %}
                {% set name = cfg.get('manifest.name') %}
                {% set title = cfg.get('manifest.title', name) | lower %}
                {% set desc = cfg.get('manifest.description') | lower %}
                {% set isCmsCore = cfg.get('isCmsCore', false) %}
                {% set isActive = cfg.get('active', false) %}

                {% if !isActive %}
                    {{ helper('Event::loadPluginLanguage', group, name) | void }}
                {% endif %}

                <tr data-group="{{ group }}" data-plugin="{{ className }}">
                    <td class="uk-text-nowrap">
                        {% if isCmsCore %}
                            <div class="uk-text-warning">
                                <span uk-icon="icon: check"></span>
                                {{ _('activate') }}
                            </div>
                        {% else %}
                            {% if isActive %}
                                <a class="uk-text-success" href="#"
                                   data-text-confirm="{{ _('deactivate-plugin-confirm', ['group': group, 'name': name]) | escape_attr }}">
                                    <span uk-icon="icon: check"></span>
                                    {{ _('activate') }}
                                </a>
                            {% else %}
                                <a class="uk-text-danger" href="#"
                                   data-text-confirm="{{ _('activate-plugin-confirm', ['group': group, 'name': name]) | escape_attr }}">
                                    <span uk-icon="icon: close"></span>
                                    {{ _('deactivate') }}
                                </a>
                            {% endif %}
                        {% endif %}
                    </td>
                    <td class="uk-text-nowrap">
                        <a class="uk-link-muted{{ isCmsCore ? ' uk-text-warning' : '' }}"
                           href="{{ route('plugin/' ~ group ~ '/' ~ name) }}">

                            {% if isCmsCore %}
                                {{ _('cms-core') }}
                            {% endif %}

                            {{ _(title) ~ '.' }}
                            <span class="uk-text-meta{{ isCmsCore ? ' uk-text-warning' : '' }}">
                                {{ _('version-s', ['version': cfg.get('manifest.version')]) }}
                            </span>
                        </a>
                    </td>
                    <td class="uk-text-meta uk-text-truncate">
                        {{ _(desc) }}
                    </td>
                    <td class="uk-text-meta">
                        <div uk-tooltip="title: {{ cfg.get('manifest.authorUrl') }} . '<br/>' . {{ cfg.get('manifest.authorEmail') }}">
                            {{ cfg.get('manifest.author') }}
                        </div>
                    </td>
                </tr>

            {% endfor %}
        {% endfor %}
        </tbody>
    </table>
    <input type="hidden" name="group" value=""/>
    <input type="hidden" name="plugin" value=""/>
    {{ helper('Form::tokenInput') }}
</form>
