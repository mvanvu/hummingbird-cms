{% set paginate = paginator.paginate() %}
{% set currentId = user.getEntity().id %}
{% set rolesMap = [
    'R': _('role-register'),
    'A': _('role-author'),
    'M': _('role-manager'),
    'S': _('role-super')
] %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider uk-table-striped">
            <thead>
            <tr>
                <th class="uk-table-shrink uk-text-nowrap">
                    <input class="uk-checkbox check-all" type="checkbox"/>
                </th>
                <th class="uk-table-expand uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('name'), 'column': 'name', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('email'), 'column': 'email', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('username'), 'column': 'username', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('state'), 'column': 'active', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder': activeOrder]) }}
                </th>
            </tr>
            </thead>
            <tbody>
            {% for item in paginate.getItems() %}

            {% set isRoot = item.isRoot() %}
            {% if user.isRoot() %}
                {% set disabled = false %}
            {% else %}
                {% if (user.role === 'M' AND item.role === 'S') OR (item.role === user.role AND item.id != user.id) %}
                    {% set disabled = true %}
                {% endif %}
            {% endif %}

            {% set name = item.name ~ ' <small class="uk-text-meta">' ~ rolesMap[item.role] ~ '</small>' %}

            {% if isRoot %}
                {% set name = name ~ '<span uk-icon="icon: bolt"></span>' %}
            {% endif %}

            {% set isCheckedIn = item.isCheckedIn() %}
            {% set disabledCheckbox = disabled OR (currentId == item.id AND !isCheckedIn) %}

            <tr{{ disabled ? ' class="uk-disabled"' : '' }}>
                <td>
                    <input class="uk-checkbox" type="checkbox"
                           name="cid[]" value="{{ item.id }}"{{ disabledCheckbox ? ' disabled' : '' }}/>
                </td>
                <td class="uk-table-link">
                    {% if isCheckedIn %}
                    {{ partial('Grid/CheckedIn', ['item': item, 'title': name]) }}
                    {% else %}
                        <a class="uk-link-reset" href="{{ uri.routeTo('/edit/' ~ item.id) }}">
                            {{ name }}
                        </a>
                    {% endif %}
                </td>
                <td class="uk-text-nowrap">
                    {{ item.email }}
                </td>
                <td class="uk-text-nowrap">
                    {{ item.username }}
                </td>
                <td class="uk-text-nowrap">
                    {{ _(item.active === 'Y' ? 'active' : 'banned') }}
                </td>
                <td class="uk-text-nowrap">
                    {{ helper('Date::relative', item.createdAt) }}
                </td>
                <td class="uk-text-nowrap">
                    {{ item.id }}
                </td>
            </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {{ partial('Pagination/Pagination') }}
</form>