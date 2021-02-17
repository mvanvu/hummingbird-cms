<form class="admin-plugins-list-form" id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider uk-table-striped"
               id="plugins-table" data-sort-handle="plugin/reorder">
            <thead>
            <tr>
                <th class="uk-text-nowrap uk-table-shrink">
                    {{ partial('Grid/Sort', ['text': _('state'), 'column': 'active', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-table-shrink">
                    {{ partial('Grid/Sort', ['text': _('group'), 'column': 'group', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-width-expand">
                    {{ partial('Grid/Sort', ['text': _('name'), 'column': 'name', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-table-shrink">
                    {{ _('action') }}
                </th>
                <th class="uk-text-nowrap uk-table-shrink">
                    {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-text-center uk-table-shrink">
                    {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-table-shrink" style="min-width: 35px">
                    {{ partial('Grid/Sort', ['text': icon('sort-alpha-asc'), 'column': 'ordering', 'activeOrder': activeOrder]) }}
                </th>
            </tr>
            </thead>
            <tbody>
            {% set paginate = paginator.paginate() %}
            {% set isOrderingActive = strpos(activeOrder, 'ordering ') === 0 %}
            {% for item in paginate.getItems() %}
                {% set active = item.yes('active') %}
                {% if false === active %}
                    {% do helper('Event::loadPluginLanguage', item.group, item.name) %}
                {% endif %}
                {% set manifest = registry(item.manifest) %}
                {% set title = _(manifest.get('title', item.name) | lower) ~ '&nbsp;<span class="uk-text-meta version">' ~ _('version-s', ['version': manifest.get('version')]) ~ '</span>' %}
                {% set protected = item.yes('protected') %}
                <tr data-sort-id="{{ item.id }}" data-sort-group="{{ item.group }}"
                    data-name="{{ item.name }}" data-version="{{ item.version }}"
                    data-title="{{ title | escape_attr }}"
                    data-ordering="{{ item.ordering }}">
                    <td class="uk-text-nowrap">
                        {% do switcher.set('disabled', protected) %}
                        {% do switcher.set('id', item.group ~ '-' ~ item.name) %}
                        {{ switcher.setValue(protected OR active ? 'Y' : 'N') }}
                    </td>
                    <td class="uk-text-nowrap uk-text-center">
                        {{ item.group }}
                    </td>
                    <td class="uk-text-nowrap">
                        {% if item.isCheckedIn() %}
                            {{ partial('Grid/CheckedIn', ['item': item, 'title': title]) }}
                        {% else %}
                            <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                                {{ title }}
                            </a>
                        {% endif %}
                        {% if protected %}
                            <span{{ protected ? ' class="uk-text-warning"' : '' }}>
                                    {{ _('cms-core') }}
                            </span>
                        {% endif %}
                    </td>
                    <td class="uk-text-nowrap">
                        <ul class="uk-iconnav uk-flex-center">
                            {% if !protected %}
                            <li>
                                <a class="btn-uninstall uk-text-danger" uk-icon="icon: close" uk-tooltip="{{ _('uninstall') }}"></a>
                            </li>
                            {% endif %}
                            <li>
                                <a class="btn-export uk-text-success" href="{{ uri.routeTo('export/' ~ item.id) }}" uk-icon="icon: cloud-download" uk-tooltip="{{ _('download') }}"></a>
                            </li>
                        </ul>
                    </td>
                    <td class="uk-text-nowrap">
                        {{ helper('Date::relative', item.createdAt) }}
                    </td>
                    <td class="uk-text-nowrap uk-text-center">
                        {{ item.id }}
                    </td>
                    <td class="uk-text-center uk-text-nowrap">
                        {% if isOrderingActive %}
                            <input class="uk-input" name="ordering" type="number" min="1"
                                   value="{{ item.ordering }}" style="width: 65px"/>
                        {% else %}
                            <div class="uk-text-small uk-text-muted">
                                {{ item.ordering }}
                            </div>
                        {% endif %}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
        {{ partial('Pagination/Pagination') }}
        {{ csrfInput() }}
    </div>
</form>
<div id="plugin-modal-container" class="uk-modal-container" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header uk-background-muted">
            <h2 class="uk-modal-title">{{ _('installation-packages') }}</h2>
        </div>
        <div class="uk-modal-body">
            <div class="uk-flex uk-flex-center">
                <span uk-spinner="ratio: 1.5"></span>
            </div>
        </div>
        <div class="uk-modal-footer uk-text-center uk-background-muted">
            <div class="js-upload" uk-form-custom>
                <input type="file" accept="application/zip"/>
                <button class="uk-button uk-button-primary" type="button" tabindex="-1">
                    {{ _('upload-zip-package') }}
                </button>
            </div>
        </div>
    </div>
</div>